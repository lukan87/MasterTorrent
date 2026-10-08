<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class ProfileCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        if ($model instanceof User) {
            if ($model->wasRecentlyCreated || $model->wasChanged(['created_at', 'invited_by', 'deleted_at'])) {
                ProfileService::invalidate((int) $model->id);
            }
            if ($model->wasRecentlyCreated || $model->wasChanged(['name', 'invited_by', 'deleted_at'])) {
                $this->invalidateInviters($model);
            }

            return;
        }
        $this->invalidateActivity($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidateActivity($model);
        if ($model instanceof User) {
            $this->invalidateInviters($model);
        }
    }

    public function restored(Model $model): void
    {
        $this->deleted($model);
    }

    private function invalidateActivity(Model $model): void
    {
        $userId = $model instanceof User ? $model->id : $model->user_id;
        if ($userId) {
            ProfileService::invalidate((int) $userId);
        }
    }

    private function invalidateInviters(User $user): void
    {
        foreach (array_unique([$user->invited_by, $user->getPrevious()['invited_by'] ?? $user->getOriginal('invited_by')]) as $inviterId) {
            if ($inviterId) {
                ProfileService::invalidate((int) $inviterId);
            }
        }
    }
}
