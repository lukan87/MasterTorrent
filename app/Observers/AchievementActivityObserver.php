<?php

namespace App\Observers;

use App\Jobs\CheckUserAchievements;
use App\Models\Torrent;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class AchievementActivityObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Model $activity): void
    {
        if (! config('achievements.enabled', true) || ! config('achievements.awarding_enabled', false)) {
            return;
        }
        $userId = match (true) {
            $activity instanceof Torrent => $activity->owner,
            $activity instanceof User => $activity->invited_by,
            default => $activity->user_id,
        };
        if ($userId) {
            CheckUserAchievements::dispatch((int) $userId);
        }
    }
}
