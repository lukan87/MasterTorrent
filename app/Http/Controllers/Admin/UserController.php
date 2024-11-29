<?php

namespace App\Http\Controllers\Admin;

use App\Models\Peer;
use App\Models\User;
use App\Models\Torrent;
use App\Models\Comment;
use App\Models\Message;
use App\Models\History;
use App\Models\UserClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // Show all users with search functionality
    public function index(Request $request)
    {
        // Search users by name or email
        $searchTerm = $request->input('search');
        $users = User::query()
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })
            ->paginate(10); // Paginate results

        return view('admin.users.index', compact('users', 'searchTerm'));
    }

// Edit a user
public function edit($id)
{
    $user = User::findOrFail($id);
    return view('admin.users.edit', compact('user'));
}



public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $currentUser = auth()->user();

    // Validate basic user fields
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'user_class' => 'nullable|integer',
    ]);

    // Update basic user fields
    $user->name = $request->name;
    $user->email = $request->email;
    $user->info = $request->info;

    // Update profile image if provided
    if ($request->filled('profile_image')) {
        $user->profile_image = $request->profile_image;
    }

    // Check and update user class
    if ($request->filled('user_class') && $request->user_class !== $user->user_class) {
        // Prevent self-promotion and promote only if it's to a lower class
        if (
            $request->user_class >= $currentUser->user_class || // New class is equal or higher than the current user's class
            $id === $currentUser->id // Prevent self-promotion
        ) {
            return redirect()->back()->withErrors('You don\'t have the permission to do this.');
        }

        // If all checks pass, update the user class
        $user->user_class = $request->user_class;
    }

    // Save the changes
    $user->save();

    return redirect()->route('admin.users.index')->with('status', 'User updated successfully!');
}


public function show($name)
{
    // Find the user by username (or use the ID)
    $user = User::where('name', $name)->firstOrFail();

    // Get the list of torrents uploaded by the user
    $torrentsUploaded = Torrent::where('owner', $user->id)->paginate(50);

    // Get the list of torrents downloaded by the user
    $torrentsDownloaded = History::where('user_id', $user->id)->paginate(50);

    // Get the list of torrents the user is seeding
    $seedingTorrents = Peer::where('user_id', $user->id)->where('seeder', 1)->paginate(50);

    // Get the list of torrents the user is leeching
    $leechingTorrents = Peer::where('user_id', $user->id)->where('seeder', 0)->paginate(50);

    // Get the list of comments made by the user
    $comments = Comment::where('user_id', $user->id)->paginate(50);

    // Get the list of messages sent by the user
    $messages = Message::where('sender_id', $user->id)->paginate(50);

    // Pass all data to the view
    return view('admin.users.show', compact(
        'user',
        'torrentsUploaded',
        'torrentsDownloaded',
        'seedingTorrents',
        'leechingTorrents',
        'comments',
        'messages'
    ));
}







// Delete a user
public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('admin.users.index')->with('status', 'User deleted successfully!');
}



}

