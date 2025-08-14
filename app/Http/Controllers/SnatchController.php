<?php

namespace App\Http\Controllers;

use App\Models\Peer;
use App\Models\History;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;

class SnatchController extends Controller
{
    public function snatchlist($userId = null)
    {
        // Get the current user's class
        $currentUserClass = Auth::user()->user_class;
    
        // Check if the user is trying to view another user's snatchlist and if their class is below MODERATOR
        if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
            abort(403, 'Unauthorized action.');
        }
    
        $userId = $userId ?? Auth::id(); // Use the provided ID or the authenticated user's ID
        $snatchlist = History::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    
        return view('snatch.snatchlist', [
            'snatchlist' => $snatchlist,
            'user' => User::find($userId), // Pass the user's info for display
            'userId' => $userId, // Pass the user ID to the view
        ]);
    }
    

    public function seeding($userId = null)
    {
        // Get the current user's class
        $currentUserClass = Auth::user()->user_class;
    
        // Check if the user is trying to view another user's snatchlist and if their class is below MODERATOR
        if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
            abort(403, 'Unauthorized action.');
        }
    
        $userId = $userId ?? Auth::id();
    
        // Fetch data from the History model with conditions
        $seeding = History::where('user_id', $userId)
            ->where('seeder', 1)
            ->where('active', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(30);
    
        return view('snatch.seeding', [
            'seeding' => $seeding,
            'user' => User::find($userId), // Pass user info for display
            'userId' => $userId, // Pass the user ID to the view
        ]);
    }
    

    public function leeching($userId = null)
    {

         // Get the current user's class
      $currentUserClass = Auth::user()->user_class;


        // Check if the user is trying to view another user's snatchlist and if their class is below MODERATOR
        if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
            abort(403, 'Unauthorized action.');
        }
        $userId = $userId ?? Auth::id();
        $leeching = Peer::where('user_id', $userId)
            ->where('seeder', 0)
            ->with('history')
            ->paginate(10);

        return view('snatch.leeching', [
            'leeching' => $leeching,
            'user' => User::find($userId),
            'userId' => $userId, // Pass the user ID to the view
        ]);
    }

    public function hitAndRun($userId = null)
    {

         // Get the current user's class
      $currentUserClass = Auth::user()->user_class;

  
        // Check if the user is trying to view another user's snatchlist and if their class is below MODERATOR
        if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
            abort(403, 'Unauthorized action.');
        }
        $userId = $userId ?? Auth::id();
        $hitAndRun = History::where('user_id', $userId)
            ->where('hitrun', true)
            ->with('torrent')
            ->paginate(20);

        return view('snatch.hit_and_run', [
            'hitAndRun' => $hitAndRun,
            'user' => User::find($userId),
            'userId' => $userId, // Pass the user ID to the view
        ]);
    }

    public function needToSeed($userId = null)
{
    // Get the current user's class
    $currentUserClass = Auth::user()->user_class;


        // Check if the user is trying to view another user's snatchlist and if their class is below MODERATOR
        if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
            abort(403, 'Unauthorized action.');
        }

    // Default to the logged-in user if no $userId is provided
    $userId = $userId ?? Auth::id();

    // Retrieve torrents that need to be seeded based on the conditions
    $needToSeed = History::where('user_id', $userId)
        ->where(function ($query) {
            // Condition for seedtime < 86400 (less than 24 hours) or no seedtime (0)
            $query->where('seedtime', '<', config('hitrun.seedtime')) // Assuming seedtime is defined in hitrun config
                  ->orWhere('seedtime', '=', 0);
        })
        ->where(function ($query) {
            // Condition for uploaded ratio being less than 1.00 compared to actual_downloaded
            $query->whereRaw('uploaded / (CASE WHEN actual_downloaded = 0 THEN 1 ELSE actual_downloaded END) < ?', [1.00]);
        })
        ->where('created_at', '>', '2025-02-01 00:00:00')
        ->where('seeder', false)    
        ->where('hitrun', false)   
        ->where('active', false)   
        ->orderBy('created_at', 'desc')  
        ->paginate(20);  

    return view('snatch.need_to_seed', [
        'needToSeed' => $needToSeed,
        'user' => User::find($userId),
        'userId' => $userId, 
    ]);
}


public function deleteNeedToSeed($userId, $torrentId)
{
    
    if (Auth::user()->user_class < 5) {
        return redirect()->back()->with('error', 'Unauthorized action.');
    }

    
    $history = History::where('torrent_id', $torrentId)
                      ->where('user_id', $userId)
                      ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'No history record found for this torrent.');
    }

   
    $history->delete();

    return redirect()->back()->with('success', 'Torrent history removed for this user.');
}

public function deleteHNR($userId, $torrentId)
{
    // Check if the logged-in user is staff
    if (Auth::user()->user_class < 5) {
        return redirect()->back()->with('error', 'Unauthorized action.');
    }

    // Find the user's specific history for the torrent
    $history = History::where('torrent_id', $torrentId)
                      ->where('user_id', $userId)
                      ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'No history record found for this torrent.');
    }

    // Get the user
    $user = User::find($userId);
    
    if ($user) {
        // Decrement hit_and_run_count if it's greater than 0
        if ($user->hit_and_run_count > 0) {
            $user->decrement('hit_and_run_count');
        }
    }

    // Delete the specific history record
    $history->delete();

    return redirect()->back()->with('success', 'Torrent history removed and H&R count decreased for this user.');
}



}
