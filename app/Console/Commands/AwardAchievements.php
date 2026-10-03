<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Console\Command;

class AwardAchievements extends Command
{
    protected $signature = 'achievements:award {--user= : Evaluate one user ID}';

    protected $description = 'Award newly reached achievements and reconcile existing members';

    public function handle(AchievementService $service): int
    {
        if (! config('achievements.awarding_enabled', false) || ! $service->available()) {
            $this->warn('Achievement payouts are disabled or their migration has not run.');

            return self::SUCCESS;
        }
        $awarded = 0;
        User::query()->where('enabled', '!=', 'no')->whereNull('banned_until')
            ->when($this->option('user'), fn ($q, $unused) => $q->whereKey($this->option('user')))
            ->select('id')->chunkById(100, function ($users) use ($service, &$awarded) {
                foreach ($users as $user) {
                    $awarded += $service->award($user->id);
                }
            });
        $this->info("Awarded {$awarded} achievements.");

        return self::SUCCESS;
    }
}
