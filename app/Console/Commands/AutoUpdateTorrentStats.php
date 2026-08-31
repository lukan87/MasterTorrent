<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Torrent;
use App\Models\Peer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoUpdateTorrentStats extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'auto:update_torrent_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and update seeders/leechers counts for active torrents (where seeders > 0).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🔍 Starting torrent stats verification for active torrents...');

        try {
            // Count how many torrents need updates
            $totalTorrents = Torrent::where('seeders', '>', 0)->count();

            if ($totalTorrents === 0) {
                $this->info('✅ No active torrents found that need updating.');
                return;
            }

            $this->info("📊 Found {$totalTorrents} torrents that need verification.\n");

            // Initialize progress bar
            $bar = $this->output->createProgressBar($totalTorrents);
            $bar->start();

            $updatedCount = 0;
            $skippedCount = 0;

            Torrent::where('seeders', '>', 0)
                ->select('id', 'seeders', 'leechers')
                ->orderBy('id')
                ->chunkById(500, function ($torrents) use ($bar, &$updatedCount, &$skippedCount) {
                    foreach ($torrents as $torrent) {
                        $seeders = Peer::where('torrent_id', $torrent->id)
                            ->where('left', 0)
                            ->where('active', 1)
                            ->count();

                        $leechers = Peer::where('torrent_id', $torrent->id)
                            ->where('left', '>', 0)
                            ->where('active', 1)
                            ->count();

                        // Only update if changed
                        if ($seeders !== $torrent->seeders || $leechers !== $torrent->leechers) {
                            DB::table('torrents')
                                ->where('id', $torrent->id)
                                ->update([
                                    'seeders' => $seeders,
                                    'leechers' => $leechers,
                                    'updated_at' => now(),
                                ]);
                            $updatedCount++;
                        } else {
                            $skippedCount++;
                        }

                        $bar->advance();
                    }
                });

            $bar->finish();
            $this->newLine(2);

            $this->info("✅ Torrent stats updated successfully.");
            $this->line("📈 Updated: {$updatedCount} | ⏭️ Skipped (no change): {$skippedCount}");
            Log::info("[AutoUpdateTorrentStats] Completed. Updated: {$updatedCount}, Skipped: {$skippedCount}");
        } catch (\Throwable $e) {
            Log::error('[AutoUpdateTorrentStats] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
