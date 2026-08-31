<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;

class ForumTopicController extends Controller
{
    /**
     * Show a topic with its posts
     */

public function show($topicId)
{
    $userClass = Auth::user()?->user_class ?? 0;

    // Try to find the topic visible to this user
    $topic = ForumTopic::visibleTo($userClass)->find($topicId);

    if (!$topic) {
        // Redirect back to forums index with error
        return redirect()->route('forums.index')
                         ->with('error', 'Topic does not exist or you do not have permission to view it.');
    }

    // Get the first post (original post)
    $firstPost = $topic->posts()->with('author')->oldest()->first();

    // Get the rest of the posts, paginated
    $posts = $topic->posts()
                   ->with('author')
                   ->where('id', '<>', $firstPost->id) // exclude first post
                   ->latest()
                   ->paginate(10);  // 10 replies per page

    return view('topics.show', compact('topic', 'firstPost', 'posts'));
}






    /**
     * Create a new topic in a forum
     */
    // ForumTopicController.php
public function create(Forum $forum)
{
    $userClass = auth()->user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'create_topics')) {
        abort(403);
    }

    return view('topics.create', compact('forum'));
}


    public function store(Request $request, Forum $forum)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'create_topics')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $topic = ForumTopic::create([
            'forum_id' => $forum->id,
            'user_id' => Auth::id(),
            'title' => $request->title,
        ]);

        ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => Auth::id(),
            // 'content' => $request->content,
            'content' => $request->input('content'),
        ]);

        return redirect()->route('topics.show', $topic->id);
    }

    public function edit(ForumTopic $topic)
{
    $user = auth()->user();

    if (
        $user->user_class < UserClass::MODERATOR &&
        $topic->user_id !== $user->id
    ) {
        abort(403);
    }

    return view('topics.edit', compact('topic'));
}

public function update(Request $request, ForumTopic $topic)
{
    $user = auth()->user();

    if (
        $user->user_class < UserClass::MODERATOR &&
        $topic->user_id !== $user->id
    ) {
        abort(403);
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    // Update topic title
    $topic->update([
        'title' => $request->title,
    ]);

    // Update first post content
    $firstPost = $topic->posts()->oldest()->first();

    if ($firstPost) {
        $firstPost->update([
            'content' => $request->input('content'),
        ]);
    }

    return redirect()
        ->route('topics.show', $topic)
        ->with('success', 'Topic updated successfully.');
}




public function destroy(ForumTopic $topic)
{
    $user = auth()->user();

    if (
        $user->user_class < UserClass::MODERATOR &&
        $topic->user_id !== $user->id
    ) {
        abort(403);
    }

    $topic->delete();

    return back()->with('success', 'Topic deleted.');
}

}
