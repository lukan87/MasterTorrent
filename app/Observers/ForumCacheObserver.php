<?php

namespace App\Observers;

use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class ForumCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        if ($model instanceof User && ! $model->wasChanged(['name', 'profile_image', 'title', 'user_class', 'deleted_at'])) {
            return;
        }

        // A view counter should not evict every forum page on each visit.
        if ($model instanceof ForumTopic && ! $model->wasRecentlyCreated
            && ! array_diff(array_keys($model->getChanges()), ['views', 'updated_at'])) {
            return;
        }

        ForumService::invalidate();
    }

    public function deleted(Model $model): void
    {
        ForumService::invalidate();
    }

    public function restored(Model $model): void
    {
        ForumService::invalidate();
    }
}
