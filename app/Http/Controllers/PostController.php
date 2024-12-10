<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PostController extends Controller
{

    public function show($id)
{
    $topic = Topic::with('posts.replies')->findOrFail($id); // Eager load posts and replies
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

    public function reply(Post $post)
{
    return view('posts.reply', compact('post'));
}

public function storeReply(Request $request, Post $post)
{
    // Validate the reply content
    $request->validate([
        'content' => 'required|string|min:5', // Adjust validation as needed
    ]);

    // Create a new reply post
    $reply = new Post();
    $reply->content = $request->content;
    $reply->user_id = auth()->id();
    $reply->parent_id = $post->id;
    $reply->topic_id = $post->topic_id; // Assign the topic_id from the parent post
    $reply->save();

    // Notify the post owner via message
    if ($post->user_id !== auth()->id()) { // Avoid notifying the user replying to their own post
        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $post->user_id,
            'subject' => 'Reply to your forum post',
            'body' => auth()->user()->name . ' replied to your post: "' . Str::limit($post->content, 50) . '".',
            'is_read' => false,
        ]);
    }

    // Debugging step - check if redirect is correct
    //dd('Redirecting to topic show with ID: '.$post->topic_id);

    // Redirect to the topic page
    return redirect()->route('topics.show', $post->topic_id)->with('success', 'Reply posted successfully!');
}

public function destroyReply($replyId)
{
    $reply = Post::findOrFail($replyId);
    
    // Ensure that the user is either the owner or a moderator
    if (auth()->user()->id === $reply->user_id || auth()->user()->user_class >= \App\Models\UserClass::MODERATOR) {
        $reply->delete();  // Delete the reply
        return redirect()->back()->with('success', 'Reply deleted successfully.');
    }

    return redirect()->back()->with('error', 'You do not have permission to delete this reply.');
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
