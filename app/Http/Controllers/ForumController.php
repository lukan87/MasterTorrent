<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\ForumCategory;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Display a listing of all topics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ForumCategory::all();  // Get all forum categories
        $topics = Topic::all();  // You can modify this to get topics related to specific categories if needed
        return view('forum.index', compact('categories', 'topics'));
    }

    /**
     * Show the form for creating a new topic.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($categoryId)
    {
        $category = ForumCategory::findOrFail($categoryId); // Get the category by ID
        return view('forum.create', compact('category')); // Pass the category to the view
    }


    /**
     * Store a newly created topic in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $categoryId)
    {
        // Check if the user has permission to create a topic
        if (!auth()->user()->userHasPermission('create_topics')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Create the new topic and associate it with the category
        Topic::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'forum_category_id' => $categoryId, // Corrected: use $categoryId directly
        ]);

        // dd($categoryId);

        // Redirect back to the forum index with a success message
        return redirect()->route('forum.index')->with('success', 'Topic created successfully!');
    }

    /**
     * Display the specified topic and its posts.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch the topic along with its posts and user data
        $topic = Topic::with(['posts.user', 'user'])->findOrFail($id);

        // Return the view with topic data
        return view('forum.show', compact('topic'));
    }
    public function edit($id)
    {
        $topic = Topic::findOrFail($id);
        return view('topics.edit', compact('topic'));
    }
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Find the topic by ID
        $topic = Topic::findOrFail($id);

        // Update the topic with validated data
        $topic->title = $validatedData['title'];
        $topic->content = $validatedData['content'];
        $topic->save(); // Save the changes

        // Redirect back to the topic with a success message
        return redirect()->route('forum.show', $topic->id)->with('success', 'Topic updated successfully.');
    }

    public function destroy($id)
    {
        // Find the topic by ID
        $topic = Topic::findOrFail($id);

        // Check if the user has permission to delete the topic
        if (auth()->user()->userHasPermission('delete_posts')) {
            $topic->delete();

            return redirect()->route('forum.index')->with('success', 'Topic deleted successfully!');
        }

        return redirect()->route('forum.index')->with('error', 'You do not have permission to delete this topic.');
    }
}
