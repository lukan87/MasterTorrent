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

         // Get the user classes to display in the form
         $userClasses = UserClass::getClasses();  // This will return the array of user classes

        //  dd($userClasses);

        // Search users by name or email
        $searchTerm = $request->input('search');
        $users = User::query()
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })
            ->paginate(10); // Paginate results


            // Get the current time
    $now = now();

    // Calculate the number of users created in the last 24 hours, one week, and one month
    $last24Hours = User::where('created_at', '>=', $now->subDay())->count();
    $lastWeek = User::where('created_at', '>=', $now->copy()->subWeek())->count();
    $lastMonth = User::where('created_at', '>=', $now->copy()->subMonth())->count();

        return view('admin.users.index', compact('users', 'searchTerm', 'last24Hours', 'lastWeek', 'lastMonth', 'userClasses'));
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



public function sendMassMessage(Request $request)
{
    // Validate the input
    $request->validate([
        'message' => 'required|string',
        'user_ids' => 'nullable|array',
        'user_ids.*' => 'exists:users,id', // Ensure users exist
        'user_class' => 'nullable|array', // Allow an array of user classes
        'user_class.*' => 'in:' . implode(',', array_keys(UserClass::getClasses())), // Validate each user_class is a valid class
    ]);

    // Get the users to send the message to, optionally filtered by user class or specific user IDs
    $query = User::query();



    // If user classes are selected, filter by those
    if ($request->filled('user_class')) {
        $query->whereIn('user_class', $request->user_class);
    }

    // Retrieve the users
    $users = $query->get();

    // Send a message to each selected user
    foreach ($users as $user) {
        Message::create([
            'sender_id' => 2, // Assuming the admin is sending the message
            'receiver_id' => $user->id,
            'subject' => 'Mass Message',
            'body' => $request->message,
            'is_read' => false, // You can customize the status as needed
        ]);
    }

    return redirect()->route('admin.users.index')->with('success', 'Messages sent successfully!');
}





// Delete a user
public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('admin.users.index')->with('status', 'User deleted successfully!');
}



}

