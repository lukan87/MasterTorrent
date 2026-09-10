<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Shoutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ShoutboxController extends Controller
{

    public function index()
    {

        // Define the list of emoji codes
    $emojiCodes = [
        ':smile:',
        ':heart:',
        ':thumbsup:',
        ':wink:',
        ':laugh:',
        ':sad:',
        ':love:',
        ':cool:',
        ':cry:',
        ':angry:',
        ':kiss:',
        ':surprised:',
        ':blush:',
        ':grin:',
        // Add more emoji codes as needed
    ];

    // Map emoji codes to actual emojis using the emoji() helper
    $emojis = [];
    foreach ($emojiCodes as $code) {
        $emojis[$code] = emoji($code);
    }
        // Fetch the latest 20 shoutbox messages and eager load user information
        $messages = Shoutbox::with('user', 'replies.user')
            ->whereNull('parent_id')
            ->latest()
            ->take(20) // Limit to the latest 20 messages
            ->get();

            return view('shoutbox.index', [
                'messages' => $messages,
                'emojis' => $emojis, // Pass the emojis array to the view
            ]);
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

    // Create a new shoutbox message
    $user = $request->user();

    $message = $user->shoutbox()->create([
        'message' => $request->input('content'),
    ]);

    // 🔥 If request comes from AJAX (home page)
    if ($request->ajax()) {
        // clear cached home shoutbox
        Cache::forget('home_shoutbox_messages');

        return response()->json([
            'status'  => 'ok',
            'message' => $message->load('user'),
        ]);
    }

    // 👇 Normal shoutbox page behavior
    return redirect()->route('shoutbox.index');
}



   public function edit($id)
{
    $message = Shoutbox::findOrFail($id);

    if (
        auth()->id() !== $message->user_id &&
        auth()->user()->user_class < \App\Models\UserClass::MODERATOR
    ) {
        abort(403);
    }

    return view('shoutbox.edit', ['messages' => $message]);
}


public function update(Request $request, $id)
{
    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $message = Shoutbox::findOrFail($id);

    if (
        auth()->id() !== $message->user_id &&
        auth()->user()->user_class < \App\Models\UserClass::MODERATOR
    ) {
        abort(403);
    }

    $message->update([
        'message' => $request->input('content')
    ]);

    // 🔥 AJAX (home page)
    if ($request->ajax()) {
        return response()->json([
            'status'  => 'ok',
            'message' => $message->fresh('user'),
        ]);
    }

    return redirect()
        ->route('shoutbox.index')
        ->with('success', 'Message updated successfully!');
}


public function destroy($id)
{
    $message = Shoutbox::findOrFail($id);

    if (auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403);
    }

    // Delete replies if parent
    $message->replies()->delete();

    $message->delete();

   if (request()->ajax()) {
    Cache::forget('home_shoutbox_messages');
    return response()->json(['status' => 'deleted']);
}

return redirect()
    ->route('shoutbox.index')
    ->with('success', 'Message deleted successfully!');

}





public function reply(Request $request, $id)
{
    $user = $request->user();

    // Validate the request
    $request->validate([
        'content' => 'required|string|max:400',
    ]);

    // Find the shoutbox message
    $parentMessage = Shoutbox::findOrFail($id);

    // 🚫 Block replies to system messages (user_id = 2)
    if ($parentMessage->user_id == 2) {

        if ($request->ajax()) {
            return response()->json([
                'error' => 'You cannot reply to system messages.'
            ], 403);
        }

        return redirect()->back()->with('error', 'You cannot reply to system messages.');
    }

    // Create reply
    $reply = new Shoutbox([
        'user_id' => $user->id,
        'message' => $request->input('content'),
    ]);

    $reply->parent_id = $parentMessage->id;

    // Save reply
    $parentMessage->replies()->save($reply);

    // 🔥 AJAX (home)
    if ($request->ajax()) {
        Cache::forget('home_shoutbox_messages');

        return response()->json([
            'html' => view('partials.reply', [
                'reply' => $reply->load('user')
            ])->render()
        ]);
    }

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


public function typing()
{
    $users = Cache::get('typing_users', []);

    $users[auth()->id()] = [
        'name' => auth()->user()->name,
        'time' => now()->timestamp
    ];

    Cache::put('typing_users', $users, now()->addSeconds(10));

    return response()->json(['ok' => true]);
}

public function stopTyping()
{
    $users = Cache::get('typing_users', []);

    unset($users[auth()->id()]);

    Cache::put('typing_users', $users, now()->addSeconds(10));

    return response()->json(['ok' => true]);
}

public function typingUsers()
{
    $users = Cache::get('typing_users', []);

    $active = [];

    foreach ($users as $id => $user) {

        if ($user['time'] >= now()->subSeconds(5)->timestamp) {

            if ($id != auth()->id()) {
                $active[] = $user['name'];
            }

        }

    }

    return response()->json($active);
}

public function poll(Request $request)
{
    $lastId = (int) $request->query('after', 0);

    $query = Shoutbox::with(['user', 'replies.user'])
        ->whereNull('parent_id')
        ->latest();

    if ($lastId > 0) {
        $query->where('id', '>', $lastId);
    }

    $messages = $query->take(30)->get()->reverse();

    return response()->json([
        'messages' => $messages,
        'count' => $messages->count(),
    ]);
}

}
