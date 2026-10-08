<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\TopicSubscription;
use App\Services\ForumAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TopicSubscriptionController extends Controller
{
    /**
     * Follow a forum topic.
     */
    public function store(
        ForumCategory $category,
        ForumTopic $topic
    ): RedirectResponse {
        // Make sure the topic belongs to this category.
        ForumAccess::authorizeView($category);
        ForumAccess::authorizeParticipation();

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        DB::transaction(function () use ($topic) {
            ForumTopic::whereKey($topic->id)->lockForUpdate()->firstOrFail();
            TopicSubscription::firstOrCreate(['user_id' => auth()->id(), 'topic_id' => $topic->id]);
        }, 3);

        return back()->with(
            'success',
            'You are now following this topic.'
        );
    }

    /**
     * Stop following a forum topic.
     */
    public function destroy(
        ForumCategory $category,
        ForumTopic $topic
    ): RedirectResponse {
        // Make sure the topic belongs to this category.
        ForumAccess::authorizeView($category);
        ForumAccess::authorizeParticipation();

        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        TopicSubscription::where('user_id', auth()->id())
            ->where('topic_id', $topic->id)
            ->delete();

        return back()->with(
            'success',
            'You are no longer following this topic.'
        );
    }
}
