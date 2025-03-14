<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    // Generate and create a new invite for the logged-in user
    public function createInvite()
    {
        $user = \Illuminate\Support\Facades\Auth::user(); // Get the logged-in user
        if (!$user instanceof \App\Models\User) {
            throw new \Exception('Authenticated user is not an instance of User model.');
        }
        
        // Get the user's invites
        $invites = Invite::where('inviter_id', $user->id)->get(); // assuming 'inviter_id' is the correct column

        // Generate a unique invite code
        $inviteCode = Str::random(16);

         // Get the number of invites the user has available (from the 'invites' column)
         $inviteCount = $user->invites;

         // If the user has no invites left, return an error message
    if ($inviteCount <= 0) {
        return redirect()->route('invites.index')->with('error', 'You have no invites left.');
    }

        // Create a new invite record in the database
        $invite = Invite::create([
            'inviter_id' => $user->id, // The inviter is the logged-in user
            'invite_code' => $inviteCode, // Unique invite code
            'is_used' => 0, // Initially, the invite is not used
            'is_expired' => false, // Initially, the invite is not expired
        ]);

        // Decrement the invites count for the user
    $user->invites -= 1;
    $user->save();

        // Return a response or view showing the invite code
        return redirect()->route('invites.index')->with('success', 'Invite code created successfully: ' . $inviteCode);
    }

    // Use the invite code to register a new user
    public function useInvite(Request $request)
    {
        // Validate invite code
        $request->validate([
            'invite_code' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if the invite code is valid and not used
        $invite = Invite::where('invite_code', $request->invite_code)
                        ->where('is_used', false)
                        ->where('is_expired', false)
                        ->first();

        if (!$invite) {
            return redirect()->route('invite.failed')->with('error', 'Invalid or expired invite code.');
        }

        // Create a new user with the provided details
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'invited_by' => $invite->inviter_id, // The user who invited the new user
        ]);

        // Mark the invite as used
        $invite->update(['is_used' => true]);

        // Optionally, you can increase the invites count for the inviter
        $invite->inviter->increment('invites');

        // Redirect to the login page or a welcome page
        return redirect()->route('login')->with('success', 'Your account has been created successfully!');
    }

    // Show a list of all invites created by the logged-in user
    public function showInvites()
    {
        $user = \Illuminate\Support\Facades\Auth::user(); // Get the logged-in user
    
        // Get the user's invites and eager load the 'usedBy' relationship
        $invites = Invite::with('usedBy')
            ->where('inviter_id', $user->id)
            ->get();
    
        // Get the number of invites the user has available
        $inviteCount = $user->invites;
    
        // Pass invites and invite count to the view
        return view('invites.index', compact('invites', 'inviteCount'));
    }
    
    
    
    

    public function deleteInvite($id)
{
    // Find the invite by ID
    $invite = Invite::find($id);

    // Check if the invite exists
    if (!$invite) {
        return redirect()->route('invites.index')->with('error', 'Invite not found.');
    }

   // Retrieve the inviter (the user who created the invite)
   $inviter = $invite->inviter;

   // Delete the invite
   $invite->delete();

   // Increment the inviter's invite count
   if ($inviter) {
       $inviter->increment('invites');
   }

    // Redirect back to the invites index page with a success message
    return redirect()->route('invites.index')->with('success', 'Invite deleted successfully.');
}


}
