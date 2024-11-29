<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {

        $user = Auth::user();
                $request->validate([
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
            'parent_id' => 'nullable|integer', // Allow parent_id to be null
            'comment' => 'required|string',
        ]);

        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->commentable_id = $request->commentable_id;
        $comment->commentable_type = $request->commentable_type;
        $comment->parent_id = $request->parent_id; // Set the parent_id if present
        $comment->comment = $request->comment;
        $comment->torrent_id = $request->torrent_id;
        $comment->save();

        return redirect()->back()->with('success', 'Comment added successfully!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $comment->delete();
        return redirect()->back()->with('status', 'Comment deleted successfully!');
    }
}

