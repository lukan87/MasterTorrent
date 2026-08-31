<?php

namespace App\Console\Commands;

use App\Models\History;
use App\Models\Peer;
use App\Models\Torrent;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:sync_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Torrent Seeders/Leechers/Times Completed Count.';

    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */
    final public function handle(): void
    {
        $syncedTorrents = 0;

        // Delete peers where active = 0
        DB::transaction(function (): void {
            Peer::where('active', 0)
                ->where('seeder', 0)
                ->delete();
        }, 5);

        // Sync Seeders and Leechers count
        $syncedTorrents += DB::transaction(function (): int {
            return Torrent::where('approved', 1) // Retrieve only torrents where approved = 1
                ->leftJoinSub(
                    Peer::query()
                        ->select('torrent_id')
                        ->addSelect(DB::raw('SUM(peers.left = 0 AND peers.active = 1) AS updated_seeders'))
                        ->addSelect(DB::raw('SUM(peers.left != 0 AND peers.active = 1) AS updated_leechers'))
                        ->groupBy('torrent_id'),
                    'seeders_leechers',
                    fn ($join) => $join->on('torrents.id', '=', 'seeders_leechers.torrent_id')
                )
                ->where(
                    fn ($query) => $query
                        ->where('seeders', '!=', DB::raw('COALESCE(updated_seeders, 0)'))
                        ->orWhere('leechers', '!=', DB::raw('COALESCE(updated_leechers, 0)'))
                )
                ->update([
                    'seeders'  => DB::raw('COALESCE(seeders_leechers.updated_seeders, 0)'),
                    'leechers' => DB::raw('COALESCE(seeders_leechers.updated_leechers, 0)'),
                ]);
        }, 5);

        // Sync Times Completed count
        $syncedTorrents += DB::transaction(function (): int {
            return Torrent::where('approved', 1) // Retrieve only torrents where approved = 1
                ->leftJoinSub(
                    History::query()
                        ->select('torrent_id')
                        ->addSelect(DB::raw('SUM(completed_at IS NOT NULL) as updated_times_completed'))
                        ->groupBy('torrent_id'),
                    'all_times_completed',
                    fn ($join) => $join->on('torrents.id', '=', 'all_times_completed.torrent_id'),
                )
                ->where('times_completed', '!=', DB::raw('COALESCE(updated_times_completed, 0)'))
                ->update([
                    'times_completed' => DB::raw('COALESCE(updated_times_completed, 0)'),
                ]);
        }, 5);

        // Clear all cache to ensure fresh data is loaded
        // Cache::flush();

        $message = "Torrent Seeders/Leechers/Times Completed Count Synced Successfully, and inactive peers deleted!";
        $message .= " Total torrents synced: {$syncedTorrents}.";

        // Log the result
        Log::info($message);

        // Output to console
        $this->info($message);
    }
}
