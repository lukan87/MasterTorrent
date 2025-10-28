<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Models\User;
use App\Models\HappyHour;
use Illuminate\Support\Facades\Log;
use Exception;

class AwardSeedBonus extends Command
{
    protected $signature = 'auto:seedbonus_award';
    protected $description = 'Award seedbonus points per hour for active seeders, with Happy Hour bonuses.';

    public function handle()
    {
        $this->info('🟢 Starting seedbonus awarding process...');
        Log::info('Seedbonus awarding process started.');

        try {
            $currentTime = now();

            // Check if a Happy Hour is active
            $happyHour = HappyHour::where('active', true)
                            ->latest('start_at')
                            ->first();

            $baseBonus = 0.15;
            $multiplier = 1;

            if ($happyHour) {
                $this->info("🎉 Happy Hour active: {$happyHour->theme}");
                Log::info("Happy Hour active: {$happyHour->theme}");

                // Use upload_multiplier as multiplier
                $multiplier = $happyHour->upload_multiplier ?? 1;

                // Optional: extra bonus for free download
                if ($happyHour->free_download) {
                    $this->info("💎 Free Download bonus applied!");
                    $multiplier += 0.1; // add 0.1 bonus points
                }
            }

            $effectiveBonus = $baseBonus * $multiplier;

            // Process active seeders in chunks
            History::with(['user', 'torrent'])
                ->where('seeder', true)
                ->where('active', true)
                ->where(function ($query) use ($currentTime) {
                    $query->whereNull('last_awarded')
                          ->orWhere('last_awarded', '<=', $currentTime->copy()->subHour());
                })
                ->chunkById(200, function ($records) use ($currentTime, $effectiveBonus) {
                    $awardedUsers = [];

                    foreach ($records as $record) {
                        $user = $record->user;
                        $torrent = $record->torrent;

                        if (!$user || !$torrent) {
                            Log::warning("Skipping record ID {$record->id}: Missing user or torrent.");
                            continue;
                        }

                        if ($torrent->owner === $user->id) {
                            Log::info("Skipping user {$user->id}: owns torrent {$torrent->id}.");
                            continue;
                        }

                        if (!$record->seeder || !$record->active) {
                            Log::info("Skipping user {$user->id}: not active seeder.");
                            continue;
                        }

                        // Award seedbonus
                        $user->increment('seedbonus', $effectiveBonus);
                        $awardedUsers[] = $user->id;

                        $record->update(['last_awarded' => $currentTime]);

                        Log::info("Awarded {$effectiveBonus} points to user {$user->id} for torrent {$torrent->id}.");
                    }

                    if (!empty($awardedUsers)) {
                        $this->info('✅ Seedbonus awarded to users: ' . implode(', ', $awardedUsers));
                    }
                });

            $this->info('🎯 Seedbonus awarding process completed successfully.');
            Log::info('Seedbonus awarding process completed successfully.');

        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Seedbonus awarding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
