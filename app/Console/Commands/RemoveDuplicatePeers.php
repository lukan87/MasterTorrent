<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RemoveDuplicatePeers extends Command
{
    // The name and signature of the console command.
    protected $signature = 'peers:remove-duplicates';

    // The console command description.
    protected $description = 'Remove duplicate peers based on torrent_id, user_id, and peer_id and update seeders and leechers in the torrents table';

    // Create a new command instance.
    public function __construct()
    {
        parent::__construct();
    }

    // Execute the console command.
    public function handle()
    {
        // Fetch duplicates
        $duplicates = DB::table('peers')
            ->select(
                'torrent_id',
                'user_id',
                'peer_id',
                DB::raw('COUNT(*) as "count"')
            )
            ->groupBy('torrent_id', 'user_id', 'peer_id')
            ->having('count', '>', 1)
            ->get();

        // Loop through each duplicate entry
        foreach ($duplicates as $duplicate) {
            // Get all records with the same torrent_id, user_id, and peer_id
            $records = DB::table('peers')
                ->where('torrent_id', '=', $duplicate->torrent_id)
                ->where('user_id', '=', $duplicate->user_id)
                ->where('peer_id', '=', $duplicate->peer_id)
                ->get();

            // Get the first record (this will be kept)
            $first = $records->first();

            // Delete all other duplicate records except the first one
            DB::table('peers')
                ->whereIn('id', $records->where('id', '!=', $first->id)->pluck('id'))
                ->delete();

            // Optionally, you can output information about the deletion process
            $this->info("Deleted " . ($records->count() - 1) . " duplicates for Torrent ID: {$duplicate->torrent_id}, User ID: {$duplicate->user_id}, Peer ID: {$duplicate->peer_id}");
        }

        // Update seeders and leechers for each torrent
$torrents = DB::table('torrents')
->where('seeders', '>', 0) // Only fetch torrents that have at least one seeder
->pluck('id');


        foreach ($torrents as $torrentId) {
            $seeders = DB::table('peers')
                ->where('torrent_id', $torrentId)
                ->where('seeder', 1) // Seeder condition
                ->count();

            // Leechers with client_updated_at less than 1 hour ago
            $leechers = DB::table('peers')
                ->where('torrent_id', $torrentId)
                ->where('seeder', 0) // Leecher condition
                ->where('active', 1) // Active condition for leechers
                ->where('client_updated_at', '<', Carbon::now()->subHour()) // Leechers updated more than 1 hour ago
                ->count();

            // Update the seeders and leechers count in the torrents table
            DB::table('torrents')
                ->where('id', $torrentId)
                ->update([
                    'seeders' => $seeders,
                    'leechers' => $leechers,
                ]);

            // Optionally, you can output information about the update process
            $this->info("Updated seeders and leechers for Torrent ID: {$torrentId}. Seeders: {$seeders}, Leechers: {$leechers}");
        }

        $this->info('Duplicate peers removal and torrent stats update complete.');
    }
}
