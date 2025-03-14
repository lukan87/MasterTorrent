<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanHistoryDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:clean-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate entries from the history table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Cleaning duplicate history entries...');
    
        $totalDeleted = 0;
        $batchSize = 1000; // Process in batches of 1000 rows

        DB::beginTransaction();
    
        try {
            do {
                // Select the IDs of the duplicates to delete
                $duplicatesQuery = "
                    SELECT h1.id
                    FROM history h1
                    JOIN (
                        SELECT user_id, torrent_id, MIN(id) AS keep_id
                        FROM history
                        GROUP BY user_id, torrent_id
                    ) h2
                    ON h1.user_id = h2.user_id
                    AND h1.torrent_id = h2.torrent_id
                    WHERE h1.id > h2.keep_id
                    LIMIT {$batchSize}
                ";
                
                $duplicates = DB::select($duplicatesQuery);
                $duplicateIds = array_map(function ($row) {
                    return $row->id;
                }, $duplicates);

                $duplicateCount = count($duplicateIds);

                if ($duplicateCount > 0) {
                    // Delete duplicates using the IDs
                    DB::table('history')->whereIn('id', $duplicateIds)->delete();
                    $totalDeleted += $duplicateCount;
                    $this->info("Removed {$duplicateCount} duplicate entries in this pass.");
                }
            } while ($duplicateCount > 0);
    
            DB::commit();
            $this->info("Duplicate history entries cleaned successfully. Total duplicates removed: {$totalDeleted}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error cleaning history duplicates: ' . $e->getMessage());
        }
    
        return 0;
    }
    
}
