<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Messenger (conversation list + chat pane)
    |--------------------------------------------------------------------------
    */

    /**
     * Load every conversation the user participates in for the sidebar, then
     * render the messenger with an empty chat pane (or the first one).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $conversations = $this->conversationList($userId, $request);

        if ($request->header('X-Messenger-Pane') === '1' && $request->expectsJson()) {
            return \App\Services\PageBrowse::json([
                'pane' => view('messages.pane', ['activeConversation' => null, 'messages' => collect(), 'hasOlder' => false])->render(),
                'sidebar' => view('messages.sidebar', ['conversations' => $conversations, 'activeConversation' => null])->render(),
                'unreadCount' => $this->unreadCount($userId),
            ]);
        }

        if ($request->header('X-Messenger-Sidebar') === '1' && $request->expectsJson()) {
            return \App\Services\PageBrowse::json(['unreadCount' => $this->unreadCount($userId),
                'sidebar' => view('messages.sidebar', ['conversations' => $conversations, 'activeConversation' => null])->render()]);
        }

        return view('messages.index', [
            'conversations' => $conversations,
            'activeConversation' => null,
            'messages' => collect(),
            'hasOlder' => false,
        ]);
    }

    /**
     * Open a single conversation inside the messenger.
     */
    public function show(Request $request, $conversationId)
    {
        // Reject non-numeric ids (e.g. /messages/9sdsd) instead of erroring
        if (! is_string($conversationId) || ! ctype_digit($conversationId) || (int) $conversationId <= 0) {
            return redirect()
                ->route('messages.index')
                ->with('error', 'Invalid message id.');
        }

        $conversation = Conversation::find((int) $conversationId);

        // A conversation/message that no longer exists
        if (! $conversation) {
            return redirect()
                ->route('messages.index')
                ->with('error', 'Conversation or message does not exist.');
        }

        $userId = Auth::id();

        abort_unless(
            $conversation->user_one === $userId || $conversation->user_two === $userId,
            403
        );

        if (! $conversation->messages()->exists()) {
            return redirect()->route('messages.index')->with('success', 'This conversation has no messages.');
        }

        /*
        |--------------------------------------------------------------------------
        | Thread (performance: only the latest batch + "load earlier" pagination)
        |--------------------------------------------------------------------------
        */

        $limit = 200;
        $before = $request->integer('before');
        $after = $request->header('X-Messenger-Thread') === '1' && $request->expectsJson()
            ? (int) ($request->validate(['after' => 'nullable|integer|min:0'])['after'] ?? 0) : 0;

        $query = $conversation->messages()->with(['sender', 'massDelivery.massMessage']);

        if ($before > 0) {
            $query->where('id', '<', $before);
        }

        if ($after > 0 && $before <= 0) {
            $messages = $query->where('id', '>', $after)->orderBy('id')->limit($limit)->get();
        } else {
            $messages = $query->orderByDesc('id')->limit($limit)->get()->reverse()->values();
        }

        $incomingIds = $messages->where('receiver_id', $userId)->where('is_read', false)->pluck('id');
        if ($incomingIds->isNotEmpty()) {
            $conversation->messages()->whereIn('id', $incomingIds)->update(['is_read' => true]);
            SystemMessageService::forgetUserCache($userId);
            $messages->each(function ($message) use ($incomingIds) {
                if ($incomingIds->contains($message->id)) {
                    $message->is_read = true;
                }
            });
        }

        $hasOlder = false;
        if ($messages->isNotEmpty()) {
            $oldestId = $messages->first()->id;
            $hasOlder = $conversation->messages()
                ->where('id', '<', $oldestId)
                ->exists();
        }

        $conversations = $this->conversationList($userId, $request);
        if ($request->header('X-Messenger-Pane') === '1' && $request->expectsJson()) {
            $activeConversation = $conversation->loadMissing(['userOne', 'userTwo']);
            return \App\Services\PageBrowse::json([
                'pane' => view('messages.pane', compact('activeConversation', 'messages', 'hasOlder'))->render(),
                'sidebar' => view('messages.sidebar', compact('conversations', 'activeConversation'))->render(),
                'unreadCount' => $this->unreadCount($userId),
            ]);
        }

        if ($request->header('X-Messenger-Thread') === '1' && $request->expectsJson()) {
            $activeConversation = $conversation;
            return \App\Services\PageBrowse::json([
                'html' => view('messages.thread', compact('messages', 'hasOlder', 'activeConversation'))->render(),
                'unreadCount' => $this->unreadCount($userId),
                'sidebar' => view('messages.sidebar', compact('conversations', 'activeConversation'))->render(),
                'hasOlder' => $hasOlder,
            ]);
        }

        return view('messages.index', compact('conversations', 'conversation', 'messages', 'hasOlder'))
            ->with('activeConversation', $conversation);
    }

    /** Read status for displayed messages; polling never marks unseen messages read. */
    public function receipts(Request $request, Conversation $conversation)
    {
        abort_unless(
            $conversation->user_one === Auth::id() || $conversation->user_two === Auth::id(),
            403
        );
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'required|integer|min:1|distinct',
        ]);

        return response()->json([
            'messages' => $conversation->messages()->whereIn('id', $data['ids'])
                ->get(['id', 'is_read']),
        ])->header('Cache-Control', 'no-store, private');
    }

    /*
    |--------------------------------------------------------------------------
    | Shared query builder
    |--------------------------------------------------------------------------
    */

    private function unreadCount(int $userId): int
    {
        return \Illuminate\Support\Facades\Cache::remember("user_unread_count_{$userId}", 30,
            fn () => \App\Models\Message::where('receiver_id', $userId)->where('is_read', false)->count());
    }

    private function conversationList(int $userId, Request $request)
    {
        return Conversation::where(function ($q) use ($userId) {
            $q->where('user_one', $userId)
                ->orWhere('user_two', $userId);
        })
            ->whereHas('messages')
            ->with(['lastMessage.massDelivery.massMessage', 'userOne', 'userTwo'])
            ->withCount([
            'messages as unread_count' => function ($q) use ($userId) {
                $q->where('receiver_id', $userId)->where('is_read', 0);
            },
        ])
            ->orderByDesc('last_message_at')
            ->paginate(25);
    }
}
