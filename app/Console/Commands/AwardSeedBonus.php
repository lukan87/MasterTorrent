<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
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
    protected $description = 'Award 0.15 seedbonus points for each torrent being seeded every hour based on history, excluding torrent owners';

    /**
     * Execute the console command.
     */
    final public function handle()
    {
        $this->info('Starting the seed bonus awarding process...');
        Log::info('Seed bonus awarding process started.');

        try {
            $current_time = now();

            // Eager load user and torrent relationships to reduce DB queries
            History::with(['torrent', 'user'])
                ->where('seeder', true)
                ->where(function ($query) use ($current_time) {
                    $query->whereNull('last_awarded')
                          ->orWhere('last_awarded', '<=', $current_time->copy()->subHour());
                })
                ->chunk(100, function ($historyRecords) use ($current_time) {
                    $userPoints = [];

                    foreach ($historyRecords as $record) {
                        $user = $record->user;
                        $torrent = $record->torrent;

                        // Skip if missing user/torrent or user is the owner
                        if (!$user || !$torrent || $torrent->owner == $user->id) {
                            $this->info("Skipping bonus for user ID {$record->user_id} (owner or missing data).");
                            continue;
                        }

                        $key = "{$user->id}-{$torrent->id}";

                        if (!isset($userPoints[$key])) {
                            $userPoints[$key] = [
                                'user_id' => $user->id,
                                'points' => 0.15, 
                            ];
                        }

                        // Update last_awarded timestamp
                        $record->last_awarded = $current_time;
                        $record->save();

                        $this->info("Processed history record for user ID {$user->id} and torrent ID {$torrent->id}.");
                    }

                    // Award seedbonus points
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
