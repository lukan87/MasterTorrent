<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;

class CleanupDuplicateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup duplicate history records where completed_at is null for the same user and torrent, with active = 1 and seeder = 0';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get all the records where completed_at is null, active is 1, and seeder is 0
        $historyRecords = History::whereNull('completed_at')
            
            ->get();

        $deletedCount = 0;

        // Loop through each record and check for duplicates based on torrent_id and user_id
        foreach ($historyRecords as $record) {
            // Check if there are multiple records for the same user_id and torrent_id
            $duplicates = History::where('user_id', $record->user_id)
                ->where('torrent_id', $record->torrent_id)
                ->whereNull('completed_at')
                
                ->where('id', '!=', $record->id) // Exclude the current record
                ->get();

            // If duplicates exist, delete them
            if ($duplicates->isNotEmpty()) {
                foreach ($duplicates as $duplicate) {
                    $duplicate->delete();
                    $deletedCount++;

                    // Output user_id, torrent_id, and info_hash for each deleted record
                    $this->info("Deleted duplicate record for User ID: {$duplicate->user_id}, Torrent ID: {$duplicate->torrent_id}, Info Hash: {$duplicate->info_hash}");
                }
            }
        }

        $this->info("Cleanup completed. Deleted {$deletedCount} duplicate records.");
    }
}
