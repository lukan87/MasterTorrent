<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    /**
     * List messages with filters, search, pagination
     */
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:read,unread']);
        $query = Message::with(['sender', 'receiver'])
            ->orderByDesc('created_at');

        // Filter: read / unread
        if ($request->filled('status')) {
            if ($request->status === 'read') {
                $query->where('is_read', 1);
            } elseif ($request->status === 'unread') {
                $query->where('is_read', 0);
            }
        }

        // Search: subject, body, or username
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('sender', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('receiver', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $messages = $query->paginate(50)->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    /**
     * Show a single message
     */
    public function show(Message $message)
    {
        return view('admin.messages.show', compact('message'));
    }

    /**
     * Delete a single message
     */
    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('success', 'Message deleted');
    }

    /**
     * Bulk actions
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer|distinct|exists:messages,id',
        ]);

        $messages = Message::whereIn('id', $request->ids);

        if ($request->action === 'delete') {
            $messages->delete();
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'Bulk action applied successfully');
    }
}
