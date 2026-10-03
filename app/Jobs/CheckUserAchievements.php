<?php

namespace App\Jobs;

use App\Services\AchievementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckUserAchievements implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $userId)
    {
        $this->afterCommit();
    }

    public function handle(AchievementService $service): void
    {
        $service->award($this->userId);
    }
}
