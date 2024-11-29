<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function show($id)
    {
        $topic = Topic::findOrFail($id);
        return view('topics.show', compact('topic'));
    }
    // Store a new reply to a topic
    public function store(Request $request, $topicId)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $topic = Topic::findOrFail($topicId);

        // Check if user has permission to reply
        if (!auth()->user()->userHasPermission('reply')) {
            return redirect()->route('topics.show', $topicId)->with('error', 'You do not have permission to reply.');
        }

        $post = new Post();
        $post->content = $request->input('content');
        $post->user_id = auth()->id();
        $post->topic_id = $topicId;
        $post->save();

        return redirect()->route('topics.show', $topicId)->with('success', 'Reply posted successfully.');
    }

    // Edit a post
    public function edit($id)
    {
        $post = Post::findOrFail($id);

        // Check if user has permission to edit this post
        if (!auth()->user()->userHasPermission('edit_posts') && auth()->id() !== $post->user_id) {
            return redirect()->route('topics.show', $post->topic_id)->with('error', 'You do not have permission to edit this post.');
        }

        return view('posts.edit', compact('post'));
    }

    // Update a post
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $post = Post::findOrFail($id);

        // Check if user has permission to edit this post
        if (!auth()->user()->userHasPermission('edit_posts') && auth()->id() !== $post->user_id) {
            return redirect()->route('topics.show', $post->topic_id)->with('error', 'You do not have permission to update this post.');
        }

        $post->content = $request->input('content');
        $post->save();

        return redirect()->route('forum.show', $post->topic_id)->with('success', 'Post updated successfully.');
    }

    // Delete a post
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Check if user has permission to delete this post
        if (!auth()->user()->userHasPermission('delete_posts') && auth()->id() !== $post->user_id) {
            return redirect()->route('topics.show', $post->topic_id)->with('error', 'You do not have permission to delete this post.');
        }

        $post->delete();

        return redirect()->route('topics.show', $post->topic_id)->with('success', 'Post deleted successfully.');
    }
}
