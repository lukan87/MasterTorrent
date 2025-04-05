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
            'parent_id' => 'nullable|integer', 
            'comment' => 'required|string',
        ]);
    
        
        $commentsToday = Comment::where('user_id', $user->id)
                                ->whereDate('created_at', today()) 
                                ->count();
    
        
        if ($commentsToday >= 5) {
            return redirect()->back()->with('error', 'You can only make 5 comments per day. If you want more bonus points, SEED UNTIL YOU BLEED!');
        }
    
       
        $user->seedbonus += 1;
        $user->save();
    
        // Create the new comment
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->commentable_id = $request->commentable_id;
        $comment->commentable_type = $request->commentable_type;
        $comment->parent_id = $request->parent_id; // Set the parent_id if present
        $comment->comment = $request->comment;
        $comment->torrent_id = $request->torrent_id;
        $comment->save();
    
        return redirect()->back()->with('success', 'Comment added successfully! You also have earned 1 bonus point!');
    }
    

    public function destroy($id)
    {
        // Find the comment
        $comment = Comment::findOrFail($id);
    
        // Check if the logged-in user is the owner or a staff member
        if ($comment->user_id !== Auth::id() && Auth::user()->user_class <= 5) {
            return redirect()->back()->with('error', 'You are not authorized to delete this comment.');
        }
    
        // Delete the comment
        $comment->delete();
    
        return redirect()->back()->with('success', 'Comment deleted successfully!');
    }
    

    public function update(Request $request, $id)
{
    // Validate the incoming request
    $request->validate([
        'comment' => 'required|string',
    ]);

    // Find the comment by the ID
    $comment = Comment::findOrFail($id);

    // Check if the logged-in user is the owner of the comment or a staff member
    if ($comment->user_id !== Auth::id() && Auth::user()->user_class <= 5) {
        return redirect()->back()->with('error', 'You are not authorized to edit this comment.');
    }

    // Update the comment
    $comment->comment = $request->comment;
    $comment->save();

    return redirect()->back()->with('success', 'Comment updated successfully!');
}

    
    
    

}

