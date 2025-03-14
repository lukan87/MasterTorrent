<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
    // Display all topics for a specific forum
    public function index($forumId)
    {
        $forum = Forum::findOrFail($forumId); // Fetch the forum
        $topics = $forum->topics; // Fetch all topics related to the forum
        return view('topics.index', compact('forum', 'topics'));
    }

    // Show a specific topic's details
    public function show($forumId, $topicId)
    {
        $forum = Forum::findOrFail($forumId);
        $topic = Topic::findOrFail($topicId);
    
        // Fetch posts with pagination, 10 posts per page, ordered by most recent
        $posts = $topic->posts()->orderBy('created_at', 'desc')->paginate(10);
    
        return view('topics.show', compact('forum', 'topic', 'posts'));
    }
    


    // Show the form to create a new topic in a specific forum
    public function create($forumId)
    {
        $forum = Forum::findOrFail($forumId); // Fetch the forum
        return view('topics.create', compact('forum'));
    }

    // Store a new topic in a specific forum
    public function store(Request $request, $forumId)
    {

        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
    
        $forum = Forum::findOrFail($forumId); // Fetch the forum
    
        Topic::create([
            'forum_id' => $forum->id,
            'title' => $request->title,
            'body' => $request->body,
            'user_id' => $user->id, // Assign the currently authenticated user's ID
        ]);
        
    
        return redirect()->route('topics.index', $forum->id)->with('success', 'Topic created successfully!');
    }
    
}
