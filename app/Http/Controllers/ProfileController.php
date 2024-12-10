<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Peer;
use App\Models\History;
use App\Models\Torrent;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // Show the profile page
    // Show the profile page by user name
    public function show($id, $name = null)
    {
        $user = User::findOrFail($id); // Fetch user by id

        // If the name is missing or doesn't match, redirect to the correct URL with the user's name
        if ($name === null || $name !== $user->name) {
            return redirect()->route('profile.show', ['id' => $user->id, 'name' => $user->name]);
        }

        // Count how many torrents the user is seeding
        $seededTorrentsCount = Peer::where('user_id', $user->id)
                                   ->where('seeder', 1)
                                   ->groupBy('torrent_id')
                                   ->count();

        return view('profile.show', compact('user', 'seededTorrentsCount'));
    }


public function edit($id, $name)
{
    $user = User::findOrFail($id); // Fetch user by id
    if ($user->name !== $name) {
        abort(404); // Optionally handle the case where the name doesn't match
    }

    // Check the user class and permissions
    $authUserClass = Auth::user()->user_class;
    $targetUserClass = $user->user_class;

    if ($authUserClass < \App\Models\UserClass::OWNER && $targetUserClass == \App\Models\UserClass::OWNER) {
        abort(403, 'Unauthorized action: Admins cannot edit the Owner\'s profile.');
    }

    if (Auth::user()->id !== $user->id && $authUserClass < \App\Models\UserClass::ADMIN) {
        abort(403, 'Unauthorized action.');
    }

    return view('profile.edit', compact('user'));
}

public function update(Request $request, $id, $name)
{
    $user = User::findOrFail($id); // Fetch user by id
    $currentUser = auth()->user();
    if ($user->name !== $name) {
        abort(404); // Optionally handle the case where the name doesn't match
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'profile_image_url' => 'nullable|url|max:2048',
        'info' => 'nullable|string',
        'user_class' => 'sometimes|integer',
    ]);

    // Update the user fields
    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('recovery_code')) {
        $user->recovery_code = Hash::make($request->recovery_code);
    }

    if ($request->filled('profile_image_url')) {
        $user->profile_image = $request->profile_image_url;
    }

    // Check and update user class only for moderators and above
    if ($currentUser->user_class >= \App\Models\UserClass::MODERATOR && $request->filled('user_class')) {
        if ($request->user_class !== $user->user_class) {
            // Prevent unauthorized promotions
            if (
                $request->user_class >= $currentUser->user_class || // New class is equal or higher than the current user's class
                $id === $currentUser->id // Prevent self-promotion
            ) {
                return redirect()->back()->withErrors('You don\'t have the permission to do this.');
            }

            // Update user class if checks pass
            $user->user_class = $request->user_class;
        }
    }

    $user->enabled = $request->has('enabled') && $request->enabled === 'yes' ? 'yes' : 'no';
    $user->donor = $request->has('donor') && $request->donor === 'yes' ? 'yes' : 'no';
    $user->uploadpos = $request->has('uploadpos') && $request->uploadpos === 'yes' ? 'yes' : 'no';
    $user->downloadpos = $request->has('downloadpos') && $request->downloadpos === 'yes' ? 'yes' : 'no';

    $user->info = $request->info;
    $user->IP = $request->ip();
    $user->save();

    return redirect()->route('profile.show', ['id' => $user->id, 'name' => $user->name])
                     ->with('success', 'Profile updated successfully.');
}

public function seedingTorrents($id, $name)
{
    $user = User::findOrFail($id); // Fetch user by ID
    if ($user->name !== $name) {
        abort(404); // Handle case where the name doesn't match
    }

    $seedingTorrents = Peer::select(
            'torrent_id',
            DB::raw('MAX(id) as id'),
            DB::raw('GROUP_CONCAT(agent) as agents'),
            DB::raw('SUM(uploaded) as total_uploaded'),
            DB::raw('SUM(downloaded) as total_downloaded')
        )
        ->where('user_id', $user->id)
        ->where('seeder', 1)
        ->groupBy('torrent_id')
        ->with('torrent')
        ->paginate(25);

    // Prepare the result to include torrent, agent, IP, and seeding information
    $result = [];
    foreach ($seedingTorrents as $torrent) {
        if ($torrent->torrent) {
            $history = History::where('torrent_id', $torrent->torrent_id)
                ->where('user_id', $user->id)
                ->first();

            $result[] = [
                'torrent' => $torrent->torrent,
                'agent' => $torrent->agents,
                'ip' => $torrent->ips,
                'seeding_time' => $history->seedtime ?? 'N/A',
                'uploaded' => $torrent->total_uploaded,
                'downloaded' => $torrent->total_downloaded,
            ];
        } else {
            $result[] = [
                'torrent' => null,
                'agent' => $torrent->agents,
                'ip' => $torrent->ips,
                'seeding_time' => 'Torrent Deleted',
                'uploaded' => $torrent->total_uploaded,
                'downloaded' => $torrent->total_downloaded,
            ];
        }
    }

    return view('profile.seeding-torrents', compact('user', 'result', 'seedingTorrents'));
}



public function userTorrents($id, $name)
{
    // Fetch user by id and name
    $user = User::where('id', $id)->where('name', $name)->firstOrFail();

    // Fetch torrents uploaded by the user
    $torrents = Torrent::where('owner', $user->id)
    ->orderBy('created_at', 'desc')
    ->paginate(50);


    return view('profile.torrents', compact('user', 'torrents'));
}


}
