<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Torrent;
use Illuminate\Support\Facades\DB;

class CleanupMissingTorrents extends Command
{
    protected $signature = 'torrents:cleanup-missing 
                            {--force : Actually delete records}
                            {--limit=0 : Limit number of deletions}';

    protected $description = 'Delete torrents and all related data when file is missing';

    public function handle()
    {
        $this->info('🧹 Checking for missing torrent files...');

        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $checked = 0;
        $deleted = 0;
        $skipped = 0;

        Torrent::chunk(200, function ($torrents) use (&$checked, &$deleted, &$skipped, $force, $limit) {

            foreach ($torrents as $torrent) {

                // 🛑 Stop if limit reached
                if ($limit > 0 && $deleted >= $limit) {
                    return false;
                }

                $checked++;

                // ⚠ Skip if no filename
                if (empty($torrent->file_name)) {
                    $this->warn("⚠ Skipping ID {$torrent->id} (no file_name)");
                    $skipped++;
                    continue;
                }

                $fullPath = public_path('files/torrents/' . $torrent->file_name);

                // ✅ File exists → keep it
                if (file_exists($fullPath)) {
                    continue;
                }

                // ❌ Missing file
                $this->line("❌ Missing: [{$torrent->id}] {$torrent->name}");

                if (!$force) {
                    continue;
                }

                DB::transaction(function () use ($torrent) {

                    // 🔥 Delete ALL related data

                    DB::table('files')->where('torrent_id', $torrent->id)->delete();
                    DB::table('torrent_genre')->where('torrent_id', $torrent->id)->delete();
                    DB::table('torrent_images')->where('torrent_id', $torrent->id)->delete();
                    DB::table('comments')->where('torrent_id', $torrent->id)->delete();
                    DB::table('history')->where('torrent_id', $torrent->id)->delete();
                    DB::table('peers')->where('torrent_id', $torrent->id)->delete();

                    // 🗑 Delete torrent (SOFT DELETE)
                    //$torrent->delete();

                    // 👉 If you want HARD delete instead, use:
                     $torrent->forceDelete();
                });

                $deleted++;
            }
        });

        // 📊 Summary
        $this->newLine();
        $this->info("Checked: {$checked}");
        $this->error("Deleted: {$deleted}");
        $this->warn("Skipped (no file_name): {$skipped}");

        if (!$force) {
            $this->newLine();
            $this->warn('⚠️ DRY RUN ONLY — nothing was deleted');
            $this->line('👉 Run with --force to actually delete');
        } else {
            $this->newLine();
            $this->info('✅ Cleanup complete.');
        }
    }
}