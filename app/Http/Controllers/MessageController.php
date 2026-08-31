<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        $inboxMessages = Message::with('sender')
            ->where('receiver_id', $userId)
            ->latest()
            ->take(10)
            ->get();

        $outboxMessages = Message::with('receiver')
            ->where('sender_id', $userId)
            ->latest()
            ->take(10)
            ->get();

         $conversations = Conversation::where(function ($q) use ($userId) {
        $q->where('user_one', $userId)
          ->orWhere('user_two', $userId);
    })
    ->with(['lastMessage.sender', 'userOne', 'userTwo'])
    ->orderByDesc('last_message_at')
    ->take(10)
    ->get();

        return view('messages.index', compact('inboxMessages', 'outboxMessages','conversations'));
    }

// public function inbox()
// {
//     $userId = Auth::id();

//     $conversations = Conversation::where('user_one',$userId)
//         ->orWhere('user_two',$userId)
//         ->with(['lastMessage','userOne','userTwo'])
//         ->orderByDesc('last_message_at')
//         ->paginate(15);

//     return view('messages.inbox', compact('conversations'));
// }

public function inbox()
{
    $userId = Auth::id();

    $conversations = Conversation::where(function ($q) use ($userId) {
            $q->where('user_one', $userId)
              ->orWhere('user_two', $userId);
        })
        ->whereHas('lastMessage', function ($q) use ($userId) {
            $q->where('receiver_id', $userId); // ✅ ONLY messages TO me
        })
        ->with(['lastMessage','userOne','userTwo'])
        ->orderByDesc('last_message_at')
        ->paginate(15);

    return view('messages.inbox', compact('conversations'));
}

// public function outbox()
// {
//     $userId = Auth::id();

//     $conversations = Conversation::where(function ($q) use ($userId) {
//         $q->where('user_one',$userId)
//           ->orWhere('user_two',$userId);
//     })
//     ->whereHas('messages')
//     ->with(['lastMessage','userOne','userTwo'])
//     ->orderByDesc('last_message_at')
//     ->paginate(15);

//     return view('messages.outbox', compact('conversations'));
// }

public function outbox()
{
    $userId = Auth::id();

    $conversations = Conversation::where(function ($q) use ($userId) {
            $q->where('user_one',$userId)
              ->orWhere('user_two',$userId);
        })
        ->whereHas('lastMessage', function ($q) use ($userId) {
            $q->where('sender_id', $userId); // ✅ sent by me
        })
        ->with(['lastMessage','userOne','userTwo'])
        ->orderByDesc('last_message_at')
        ->paginate(15);

    return view('messages.outbox', compact('conversations'));
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

    SystemMessageService::send(
        Auth::id(),
        $request->receiver_id,
        $request->subject ?? 'Conversation',
        $request->body
    );

    return redirect()->route('messages.outbox')
        ->with('success','Message sent successfully.');
}

public function show(Message $message)
{
    $conversation = $message->conversation;

    // If message has no conversation (old messages)
    if (!$conversation) {

        if (
            $message->sender_id !== Auth::id() &&
            $message->receiver_id !== Auth::id()
        ) {
            abort(403);
        }

        return view('messages.show', [
            'messages' => collect([$message]),
            'conversation' => null
        ]);
    }

    if (
        $conversation->user_one !== Auth::id() &&
        $conversation->user_two !== Auth::id()
    ) {
        abort(403);
    }

    $messages = $conversation->messages()
        ->with('sender')
        ->orderBy('created_at')
        ->get();

        // mark unread messages as read
    $conversation->messages()
        ->where('receiver_id', Auth::id())
        ->where('is_read', 0)
        ->update(['is_read' => 1]);

        $conversations = Conversation::where(function ($q) {
        $q->where('user_one', auth()->id())
          ->orWhere('user_two', auth()->id());
    })
    ->with(['lastMessage','userOne','userTwo'])
    ->orderByDesc('last_message_at')
    ->get();

    return view('messages.show', compact('messages','conversation', 'conversations'));
}

    public function reply(Message $message)
    {
        if ($message->receiver_id !== Auth::id()) {
            abort(403);
        }

        return view('messages.reply', compact('message'));
    }

public function storeReply(Request $request, Message $message)
{
    $request->validate([
        'body' => 'required|string',
    ]);

    $conversation = $message->conversation;

    $receiverId = Auth::id() == $conversation->user_one
        ? $conversation->user_two
        : $conversation->user_one;

    SystemMessageService::send(
        Auth::id(),
        $receiverId,
        $conversation->subject,
        $request->body
    );

    return back()->with('success', 'Reply sent.');
}

    public function destroy(Message $message)
    {
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully.');
    }

public function edit(Request $request, Message $message)
{
    abort_if($message->sender_id !== auth()->id(), 403);

    $request->validate([
        'body' => 'required|string|max:5000'
    ]);

    $message->update([
        'body' => $request->body
    ]);

    return response()->json([
        'success' => true
    ]);
}

public function delete(Message $message)
{
    abort_if($message->sender_id !== auth()->id(), 403);

    $message->delete();

    return response()->json([
        'success' => true
    ]);
}

public function destroyConversation($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    // Security: only participants can delete
    if (
        $conversation->user_one !== auth()->id() &&
        $conversation->user_two !== auth()->id()
    ) {
        abort(403);
    }

    // Delete messages
    Message::where('conversation_id', $conversation->id)->delete();

    // Delete conversation
    $conversation->delete();

    return redirect()->route('messages.index')
        ->with('success','Conversation deleted.');
}

}