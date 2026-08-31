<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Inbox (Conversations)
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $conversations = Conversation::where('user_one', Auth::id())
            ->orWhere('user_two', Auth::id())
            ->with(['lastMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('messages.inbox', compact('conversations'));
    }

    /*
    |--------------------------------------------------------------------------
    | Show Conversation
    |--------------------------------------------------------------------------
    */

    public function show(Conversation $conversation)
    {

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

        return view('messages.show', compact('conversation','messages'));
    }

}