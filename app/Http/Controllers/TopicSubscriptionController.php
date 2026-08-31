<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Models\TopicSubscription;
use Illuminate\Support\Facades\Auth;

class TopicSubscriptionController extends Controller
{
    public function subscribe(ForumTopic $topic)
    {
        $user = Auth::user();

        // Ensure user can view topic
       if ($user->user_class < $topic->forum->min_class_required) {
    abort(403);
}
        TopicSubscription::firstOrCreate([
            'user_id' => $user->id,
            'forum_topic_id' => $topic->id,
        ]);

        return back()->with('success', 'Subscribed to topic.');
    }

    public function unsubscribe(ForumTopic $topic)
    {
        $user = Auth::user();

        TopicSubscription::where([
            'user_id' => $user->id,
            'forum_topic_id' => $topic->id,
        ])->delete();

        return back()->with('success', 'Unsubscribed from topic.');
    }
}
