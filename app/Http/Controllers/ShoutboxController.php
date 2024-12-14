<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shoutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShoutboxController extends Controller
{

    public function index()
    {
        // Fetch the latest 20 shoutbox messages and eager load user information
        $messages = Shoutbox::with('user', 'replies.user')
            ->whereNull('parent_id')
            ->latest()
            ->take(20) // Limit to the latest 20 messages
            ->get();

        return view('shoutbox.index', ['messages' => $messages]);
    }

public function iframe()
{
    $messages = Shoutbox::with('user', 'replies.user')->whereNull('parent_id')->latest()->get();

    return view('shoutbox.shoutbox_iframe', compact('messages'));
}

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Add this line to check if validation is passing
// dd('Validation passed');

        // Create a new shoutbox message
        $user = $request->user();
        $messages = $user->shoutbox()->create([
            'message' => $request->input('content'),
        ]);

        return redirect()->route('shoutbox.index');
    }



    public function edit($id)
{
    $messages = Shoutbox::findOrFail($id);

    return view('shoutbox.edit', ['messages' => $messages]);
}

public function update(Request $request, $id)
{
    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $messages = Shoutbox::findOrFail($id);
    $messages->update(['message' => $request->input('content')]);

    return redirect()->route('shoutbox.index')->with('success', 'Shout updated successfully!');
}

public function destroy($id)
{
    $message = Shoutbox::findOrFail($id);

    // Delete replies
    $message->replies()->delete();

    // Check if the authenticated user is the owner of the shout
    // if (auth()->user()->id === $message->user_id && auth()->user()->group->is_owner) {
        $message->delete();
         return redirect()->route('shoutbox.index')->with('success', 'Shout deleted successfully!');
    // } else {
    //     return redirect()->route('shoutbox.index')->with('error', 'You do not have permission to delete this shout.');
    // }
}




public function reply(Request $request, $id)
{

    $user = $request->user();
    // Validate the request
    $request->validate([
        'content' => 'required|string|max:400',
    ]);

    // Find the shoutbox message to which the user is replying
    $parentMessage = Shoutbox::findOrFail($id);

    // Create a new shoutbox message as a reply
    $reply = new Shoutbox([
        'user_id' => $user->id,
        'message' => $request->input('content'),
    ]);

    // Set the parent_id for the reply
    $reply->parent_id = $parentMessage->id;

    // Save the reply
    $parentMessage->replies()->save($reply);

    return redirect()->route('shoutbox.index');
}

public function showReplyForm($id)
{
    // Find the shoutbox message to which the user is replying
    $parentMessage = Shoutbox::findOrFail($id);

    return view('shoutbox.reply', compact('parentMessage'));
}

public function getShoutboxContent()
{
    $messages = Shoutbox::with('user', 'replies.user')->whereNull('parent_id')->latest()->get();

    return view('shoutbox.index', ['messages' => $messages]);
}




}
