<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History; // Adjust the model if needed
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class AwardSeedBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:seedbonus_award';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award 0.5 seedbonus points for each torrent being seeded every hour based on history';

    /**
     * Execute the console command.
     */
    final public function handle()
    {
        $this->info('Starting the seed bonus awarding process...');
        Log::info('Seed bonus awarding process started.');

        try {
            $current_time = now();

            // Process history records in batches
            History::where('seeder', true)
                ->where(function ($query) use ($current_time) {
                    $query->whereNull('last_awarded')
                          ->orWhere('last_awarded', '<=', $current_time->subHour());
                })
                ->chunk(100, function ($historyRecords) use ($current_time) {
                    $userPoints = [];
                    $uniqueUsers = [];

                    foreach ($historyRecords as $record) {
                        $userId = $record->user_id;
                        $torrentId = $record->torrent_id;

                        $key = "{$userId}-{$torrentId}";

                        if (!isset($userPoints[$key])) {
                            $userPoints[$key] = [
                                'user_id' => $userId,
                                'points' => 0.15,
                            ];
                            $uniqueUsers[$userId] = true;
                        }

                        // Update the last_awarded timestamp for this history record
                        $record->last_awarded = $current_time;
                        $record->save();

                        $this->info("Processed history record for user ID {$userId} and torrent ID {$torrentId}.");
                    }

                    // Update user points in bulk
                    foreach ($userPoints as $data) {
                        User::where('id', $data['user_id'])->increment('seedbonus', $data['points']);
                        $this->info("User ID {$data['user_id']} awarded {$data['points']} points.");
                    }

                    $this->info('Batch processed successfully.');
                });

            $this->info('Seedbonus points awarding process completed.');
            Log::info('Seedbonus points awarding process completed.');

        } catch (Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            Log::error("Seedbonus awarding process failed: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
