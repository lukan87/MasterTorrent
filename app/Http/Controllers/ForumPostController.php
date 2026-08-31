<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TopicReplyNotification;
use App\Models\TopicSubscription;

class ForumPostController extends Controller
{
    /**
     * Store a reply in a topic
     */
    public function store(Request $request, ForumTopic $topic)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'reply')) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $post = ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        // Notify subscribers (except author)
TopicSubscription::where('forum_topic_id', $topic->id)
    ->where('user_id', '!=', auth()->id())
    ->with('user')
    ->get()
    ->each(function ($subscription) use ($post) {
        $subscription->user->notify(
            new TopicReplyNotification($post)
        );
    });

        return redirect()->route('topics.show', $topic->id);
    }

    /**
     * Show the edit form for a post
     */
    public function edit(ForumPost $post)
    {
        $userClass = Auth::user()->user_class;

        // Only author or staff/moderator+ can edit
        if ($post->user_id !== Auth::id() &&
            !UserClass::userHasPermission($userClass, 'edit_posts')) {
            abort(403);
        }

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the post
     */
    public function update(Request $request, ForumPost $post)
    {
        $userClass = Auth::user()->user_class;

        if ($post->user_id !== Auth::id() &&
            !UserClass::userHasPermission($userClass, 'edit_posts')) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $post->content = $request->input('content');
        $post->save();

        return redirect()->route('topics.show', $post->forum_topic_id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Delete a post
     */
    public function destroy(ForumPost $post)
    {
        $userClass = Auth::user()->user_class;

        // Only staff/moderator+ can delete
        if (!UserClass::userHasPermission($userClass, 'delete_posts')) {
            abort(403);
        }

        $topicId = $post->forum_topic_id;
        $post->delete();

        return redirect()->route('topics.show', $topicId)
            ->with('success', 'Post deleted successfully.');
    }
}
