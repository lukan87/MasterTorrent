<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteOldTorrents extends Command
{
    protected $signature = 'torrents:cleanup';
    protected $description = 'Delete torrents older than 3 years with 0 seeders, including related data and torrent files.';

    // Base folder where torrent files are stored
    private string $torrentFolder = '/var/www/html/lastfiles/public/files/torrents';

    public function handle(): int
    {
        $this->info("🔍 Starting torrents cleanup...");

        $threeYearsAgo = Carbon::now()->subYears(3);

        $torrents = DB::table('torrents')
            ->where('created_at', '<', $threeYearsAgo)
            ->where('seeders', 0)
            ->select('id', 'name', 'file_name', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($torrents->isEmpty()) {
            $this->info("✅ No torrents found for deletion.");
            return SymfonyCommand::SUCCESS;
        }

        $this->info("⚠️  Found {$torrents->count()} torrents to delete.\n");

        // Create progress bar
        $bar = $this->output->createProgressBar($torrents->count());
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% | ⏳ %elapsed:6s% | 📦 %message%");
        $bar->start();

        foreach ($torrents as $torrent) {
            $name = $this->truncate($torrent->name, 40);
            $bar->setMessage("ID: {$torrent->id} - {$name}");

            // Delete the torrent file
            if (!empty($torrent->file_name)) {
                $filePath = $this->torrentFolder . '/' . $torrent->file_name;
                if (file_exists($filePath)) {
                    try {
                        unlink($filePath);
                    } catch (\Exception $e) {
                        $this->warn("⚠️ Could not delete file: {$filePath} ({$e->getMessage()})");
                    }
                }
            }

            // Delete related data
            DB::table('history')->where('torrent_id', $torrent->id)->delete();
            DB::table('peers')->where('torrent_id', $torrent->id)->delete();
            DB::table('comments')->where('torrent_id', $torrent->id)->delete();
            DB::table('files')->where('torrent_id', $torrent->id)->delete();
            DB::table('torrent_genre')->where('torrent_id', $torrent->id)->delete(); // <--- added
            DB::table('torrents')->where('id', $torrent->id)->delete();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("🎉 Cleanup complete. Deleted {$torrents->count()} torrents, related data, torrent files, and genres.");

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Truncate long strings for cleaner console output.
     */
    private function truncate(string $text, int $maxLength = 50): string
    {
        return strlen($text) > $maxLength
            ? substr($text, 0, $maxLength - 3) . '...'
            : $text;
    }
}
