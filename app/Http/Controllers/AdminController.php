<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Admin dashboard stats
        $userCount = User::count();
        $movieCount = Movie::count();
        $seriesCount = Series::count();

        return view('admin.index', compact('userCount', 'movieCount', 'seriesCount'));
    }

    public function manageUsers(Request $request)
    {
        // Get all users
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        $users = User::where('name', 'LIKE', "%$query%")->orWhere('email', 'LIKE', "%$query%")->get();
        return view('admin.users.index', compact('users'));
    }

    public function siteInfo()
    {
        // Get site information
        return view('admin.site_info');
    }


    public function editUser(User $user)
{
    return view('admin.users.edit', compact('user'));
}

public function updateUser(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        // Add other validation rules as needed
    ]);

    // Update the user information
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    // Update other fields as necessary
    $user->save();

    return redirect()->route('admin.users')->with('status', 'User updated successfully!');
}

public function destroyUser(User $user)
{
    // Optional: Check if the user is not the currently logged-in user
    if (Auth::user()->id === $user->id) {
        return redirect()->route('admin.users')->withErrors(['error' => 'You cannot delete your own account.']);
    }

    // Delete the user
    $user->delete();

    return redirect()->route('admin.users')->with('status', 'User deleted successfully!');
}
}

