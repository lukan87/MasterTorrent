<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

    public function index()
{
    $inboxMessages = Message::where('receiver_id', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();

    $outboxMessages = Message::where('sender_id', Auth::id())
                             ->orderBy('created_at', 'desc')
                             ->take(10)
                             ->get();

    return view('messages.index', compact('inboxMessages', 'outboxMessages'));
}

   public function inbox()
{
    
    $messages = Message::where('receiver_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

    return view('messages.inbox', compact('messages'));
}


    public function outbox()
    {
        $messages = Message::where('sender_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('messages.outbox', compact('messages'));
    }

    public function create(Request $request)
{
    $recipient = User::findOrFail($request->receiver_id);

    return view('messages.create', compact('recipient'));
}


    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:100',
            'body' => 'required|string',
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return redirect()->route('messages.outbox')->with('success', 'Message sent successfully.');
    }

    public function show(Message $message)
    {
        if ($message->receiver_id === Auth::id() || $message->sender_id === Auth::id()) {
            if ($message->receiver_id === Auth::id()) {
                $message->update(['is_read' => true]);
            }
            return view('messages.show', compact('message'));
        }
        abort(403);
    }

    public function reply(Message $message)
{
    // Ensure the user is replying to a message they received
    if ($message->receiver_id !== Auth::id()) {
        abort(403); // Only allow replies to messages received by the logged-in user
    }

    // Return the reply form with the original message and sender
    return view('messages.reply', compact('message'));
}

public function storeReply(Request $request, Message $message)
{
    $request->validate([
        'body' => 'required|string',
    ]);

    // Create a new message where the sender is the receiver of the original message, and the receiver is the sender of the original message
    Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $message->sender_id,
        'subject' => 'Re: ' . $message->subject,
        'body' => $request->body,
    ]);

    return redirect()->route('messages.outbox')->with('success', 'Reply sent successfully.');
}

    public function destroy(Message $message)
{
    if ($message->sender_id === Auth::id() || $message->receiver_id === Auth::id()) {
        $message->delete();
        return redirect()->route('messages.index')->with('success', 'Message deleted successfully.');
    }
    abort(403);
}

}
