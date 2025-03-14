<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInfohash extends Command
{
    protected $signature = 'fix:infohash';
    protected $description = 'Find and fix duplicate infohash entries for the same torrent ID';

    public function handle()
    {
        // Get all torrent IDs with duplicate infohash entries
        $duplicates = DB::table('history')
            ->select('torrent_id', DB::raw('COUNT(*) as count'))
            ->groupBy('torrent_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate infohash entries found.');
            return;
        }

        foreach ($duplicates as $duplicate) {
            $torrentId = $duplicate->torrent_id;

            // Get all entries for this torrent ID sorted by created_at
            $entries = DB::table('history')
                ->where('torrent_id', $torrentId)
                ->orderBy('created_at', 'asc')
                ->get();

            // Separate entries into completed and non-completed
            $completedEntries = $entries->whereNotNull('completed_at');
            $nonCompletedEntries = $entries->whereNull('completed_at');

            // Determine which entry to keep
            if ($completedEntries->isNotEmpty()) {
                // Keep the newest completed entry
                $latestEntry = $completedEntries->last();
            } else {
                // If no completed entries exist, keep the newest non-completed entry
                $latestEntry = $entries->last();
            }

            $newInfohash = $latestEntry->info_hash;

            // Update all entries for this torrent ID to use the latest infohash
            DB::table('history')
                ->where('torrent_id', $torrentId)
                ->update(['info_hash' => $newInfohash]);

            // Delete all but the kept entry
            DB::table('history')
                ->where('torrent_id', $torrentId)
                ->where('id', '!=', $latestEntry->id)
                ->delete();

            $this->info("Fixed torrent ID: {$torrentId} - Kept ID: {$latestEntry->id}, Updated infohash: {$newInfohash}");
        }

        $this->info('Duplicate infohash entries have been fixed.');
    }
}

