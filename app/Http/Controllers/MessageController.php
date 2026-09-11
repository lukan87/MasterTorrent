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
    /*
    |--------------------------------------------------------------------------
    | Compose / send
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $recipient = null;
        $error = null;

        if ($request->filled('receiver_id')) {
            $recipient = User::findOrFail($request->receiver_id);
        } elseif ($request->filled('username')) {
            $recipient = User::where('name', $request->username)->first();
            if (!$recipient) {
                $error = "No member found with the name \"{$request->username}\".";
            }
        }

        return view('messages.create', compact('recipient', 'error'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:100',
            'body' => 'required|string',
        ]);

        $message = SystemMessageService::send(
            Auth::id(),
            $request->receiver_id,
            $request->subject ?: 'Conversation',
            $request->body
        );

        return redirect()
            ->route('conversations.show', $message->conversation_id)
            ->with('success', 'Message sent successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Reply inside an open conversation
    |--------------------------------------------------------------------------
    */

    public function storeReply(Request $request, Conversation $conversation)
    {
        abort_unless(
            $conversation->user_one === Auth::id() || $conversation->user_two === Auth::id(),
            403
        );

        $request->validate([
            'body' => 'required|string',
        ]);

        $receiverId = Auth::id() == $conversation->user_one
            ? $conversation->user_two
            : $conversation->user_one;

        SystemMessageService::send(
            Auth::id(),
            $receiverId,
            $conversation->subject,
            $request->body
        );

        return redirect()
            ->route('conversations.show', $conversation)
            ->with('success', 'Reply sent.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX edit / delete single owned messages
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request, int $messageId)
    {
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'error' => 'This message has been deleted and no longer exists.',
            ], 404);
        }

        abort_if($message->sender_id !== auth()->id(), 403);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message->update([
            'body' => $request->body,
        ]);

        return response()->json(['success' => true]);
    }

    public function delete(int $messageId)
    {
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'error' => 'This message has been deleted and no longer exists.',
            ], 404);
        }

        abort_if($message->sender_id !== auth()->id(), 403);

        $message->delete();

        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete an entire conversation
    |--------------------------------------------------------------------------
    */

    public function destroyConversation($conversationId)
    {
        if (!is_string($conversationId) || !ctype_digit($conversationId) || (int) $conversationId <= 0) {
            return redirect()
                ->route('messages.index')
                ->with('error', 'Invalid message id.');
        }

        $conversation = Conversation::find((int) $conversationId);

        if (!$conversation) {
            return redirect()
                ->route('messages.index')
                ->with('error', 'Conversation or message does not exist.');
        }

        // Security: only participants can delete
        abort_unless(
            $conversation->user_one === auth()->id() || $conversation->user_two === auth()->id(),
            403
        );

        Message::where('conversation_id', $conversation->id)->delete();

        $conversation->delete();

        SystemMessageService::forgetUserCache(auth()->id());

        return redirect()->route('messages.index')
            ->with('success', 'Conversation deleted.');
    }
}
