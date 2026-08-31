<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request, $topicId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
    
        $topic = Topic::findOrFail($topicId); // Find the topic
        $user = auth()->user(); // Get the currently authenticated user
    
        // Create a new post for the topic
        Post::create([
            'topic_id' => $topic->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);
    
        // Redirect to the specific topic view, passing both forumId and topicId
        return redirect()->route('topics.show', ['forumId' => $topic->forum->id, 'topicId' => $topic->id])
                         ->with('success', 'Post created successfully!');
    }

    public function edit(Post $post)
{
    // Authorize that the user can edit the post
    if (auth()->id() !== $post->user_id && auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    return view('posts.edit', compact('post'));
}

public function update(Request $request, Post $post)
{
    // Authorize that the user can edit the post
    if (auth()->id() !== $post->user_id && auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $post->update([
        'content' => $request->input('content'),
    ]);

    return redirect()->route('topics.show', [$post->topic->forum_id, $post->topic_id])
        ->with('success', 'Post updated successfully.');
}

public function reply(Request $request, Post $post)
{
    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $reply = $post->replies()->create([
        'user_id' => auth()->id(),
        'content' => $request->content,
        'topic_id' => $post->topic_id, // Ensure the topic_id matches the parent post
        'parent_id' => $post->id,      // Set the parent_id to the post being replied to
    ]);
    // Send a message to the creator of the post
    $creator = $post->user; // The creator of the post being replied to

    if ($creator->id !== auth()->id()) { // Prevent sending a message to yourself
        $replyAuthor = auth()->user(); // Get the user who made the reply
        $postLink = route('topics.show', [
            'forumId' => $post->topic->forum->id,
            'topicId' => $post->topic->id,
        ]); // Generate the link to the post
    
        Message::create([
            'sender_id' => $replyAuthor->id, // The user who created the reply
            'receiver_id' => $creator->id, // The user who created the original post
            'subject' => 'You have a new reply to your post!',
            'body' => "Hello {$creator->name},\n\n" .
                      "Your post has received a new reply from {$replyAuthor->name}:\n\n" .
                      "\"{$request->content}\"\n\n" .
                      "Click [url][here]({$postLink})[/url] to view the post and the reply.\n\n" .
                      "Best regards,\nYour Forum Team",
            'is_read' => false, // Mark the message as unread
        ]);
    }
    

    return redirect()->back()->with('success', 'Reply added successfully!');
}



public function destroy(Post $post)
{
    // Ensure the user has permission to delete the post
    if (auth()->id() !== $post->user_id && auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

     // Delete the replies associated with the post
     $post->replies()->delete();

    // Delete the post
    $post->delete();

    // Redirect back with a success message
    return redirect()->back()->with('success', 'Post deleted successfully.');
}




    
}

