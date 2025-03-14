<?php

namespace App\Http\Controllers;

use App\Models\Overforum;
use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    // Show all forums for a specific overforum
    public function index($overforumId)
    {
        $overforum = Overforum::findOrFail($overforumId); // Fetch the overforum
        $forums = $overforum->forums; // Fetch all forums related to the overforum
        return view('forums.index', compact('overforum', 'forums'));
    }

    // Show a specific forum
    public function show($overforumId, $forumId)
    {
        $overforum = Overforum::findOrFail($overforumId); // Find the overforum
        $forum = Forum::findOrFail($forumId); // Find the forum
        return view('forums.show', compact('overforum', 'forum'));
    }

    // Show the form to create a new forum under a specific overforum
    public function create($overforumId)
    {
        $overforum = Overforum::findOrFail($overforumId); // Fetch the overforum
        return view('forums.create', compact('overforum'));
    }

    // Store a new forum under a specific overforum
    public function store(Request $request, $overforumId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $overforum = Overforum::findOrFail($overforumId); // Fetch the overforum

        Forum::create([
            'overforum_id' => $overforum->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('forums.index', $overforum->id)->with('success', 'Forum created successfully!');
    }
}
