<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\TopicSubscription;
use Illuminate\Http\RedirectResponse;

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
        if ($topic->category_id !== $category->id) {
            abort(404);
        }

        // Check whether the user is already subscribed.
        $subscription = TopicSubscription::where('user_id', auth()->id())
            ->where('topic_id', $topic->id)
            ->first();

        // Only create it if it doesn't already exist.
        if (!$subscription) {
            TopicSubscription::create([
                'user_id'  => auth()->id(),
                'topic_id' => $topic->id,
            ]);
        }

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