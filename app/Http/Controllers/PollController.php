<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PollController extends Controller
{

    public function index()
    {
        // Fetch all polls to display in the list view
        $polls = Poll::withCount('votes')->get(); // Fetch polls and count votes

        return view('polls.index', compact('polls'));
    }


    public function show(Request $request)
    {
        $user = $request->user(); // Get the authenticated user
        $poll = Poll::with('options.votes')->latest()->first(); // Fetch the latest poll with options and votes

        if (!$poll) {
            // Redirect or show a message if there are no polls available
            return redirect()->route('polls.index')->with('error', 'No polls available at the moment.');
        }

        // Check if user has voted on this poll
        $userVote = $user ? $poll->votes()->where('user_id', $user->id)->first() : null;

        return view('polls.show', compact('poll', 'user', 'userVote'));
    }


    public function create()
    {
        return view('polls.create');
    }

    public function vote(Request $request, $pollId)
    {
        $user = $request->user();
        $poll = Poll::findOrFail($pollId);

        // Check if the user has already voted in this poll
        $existingVote = PollVote::where('poll_id', $pollId)
                                ->where('user_id', $user->id)
                                ->first();

        if ($existingVote) {
            // User has already voted, so redirect back with a message
            return redirect()->back()->with('error', 'You have already voted in this poll.');
        }

        // Create a new vote if the user hasn't voted yet
        PollVote::create([
            'poll_id' => $poll->id,
            'option_id' => $request->option_id,
            'user_id' => $user->id,
        ]);
		
		// Clear all cache to ensure fresh data is loaded
            Cache::flush();

        return redirect()->back()->with('success', 'Your vote has been counted!');
    }


public function store(Request $request)
{
    // Validate incoming data
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'options' => 'required|array|min:2', // Ensure at least two options are provided
        'options.*' => 'required|string|max:255', // Each option is required and should be a string
    ]);

    // Create the poll
    $poll = Poll::create($request->only('title', 'description'));

    // Create the poll options
    foreach ($request->options as $optionText) {
        $poll->options()->create(['option_text' => $optionText]);
    }
	
	

    return redirect()->route('polls.index');
}

// PollController.php

public function edit(Request $request, $id)
{
    $user = $request->user();
    $poll = Poll::findOrFail($id);

    // Ensure that the user is an admin
    if ($user->user_class < \App\Models\UserClass::ADMIN) {
        return redirect()->route('polls.index')->with('error', 'Unauthorized access');
    }

    return view('polls.edit', compact('poll'));
}

public function update(Request $request, $id)
{
    $user = $request->user();
    $poll = Poll::findOrFail($id);

    // Ensure that the user is an admin
    if ($user->user_class < \App\Models\UserClass::ADMIN) {
        return redirect()->route('polls.index')->with('error', 'Unauthorized access');
    }

    // Validate the input
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'options' => 'required|array|min:2', // Ensure at least two options are provided
        'options.*' => 'required|string|max:255', // Each option is required and should be a string
    ]);

    // Update poll information
    $poll->update($validated);

    // Handle updating poll options
    if ($request->has('options')) {
        // Remove options that were not included in the update (i.e., deleted)
        foreach ($poll->options as $existingOption) {
            if (!isset($request->options[$existingOption->id])) {
                $existingOption->delete(); // Option removed
            }
        }

        // Update or create new options
        foreach ($request->options as $optionId => $optionText) {
            if ($optionId === 'new') {
                // Handle new options that have the key 'new'
                foreach ($optionText as $newOptionText) {
                    $poll->options()->create(['option_text' => $newOptionText]);
                }
            } else {
                // Update existing options
                $option = $poll->options()->find($optionId);
                if ($option) {
                    $option->update(['option_text' => $optionText]);
                }
            }
        }
    }

    // Clear all cache to ensure fresh data is loaded
    Cache::flush();

    return redirect()->route('polls.index')->with('success', 'Poll updated successfully');
}




public function destroy(Request $request, $id)
{
    $user = $request->user();
    $poll = Poll::findOrFail($id);

    // Check if the user is authorized to delete the poll
    if ($user->user_class < \App\Models\UserClass::ADMIN) {
        return redirect()->route('polls.index')->with('error', 'Unauthorized access');
    }

    $poll->delete();
	
	// Clear all cache to ensure fresh data is loaded
            Cache::flush();

    return redirect()->route('polls.index')->with('success', 'Poll deleted successfully');
}


}
