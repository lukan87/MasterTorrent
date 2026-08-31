<?php

namespace App\Http\Controllers;

use App\Models\Peer;
use App\Models\History;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SnatchController extends Controller
{
public function snatchlist($userId = null)
{
    $currentUserClass = Auth::user()->user_class;

    if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    $userId ??= Auth::id();

    $oneMonthAgo = Carbon::now()->subMonths(2);

    $snatchlist = History::where('user_id', $userId)
        ->where(function ($query) use ($oneMonthAgo) {

            $query->where('created_at', '>=', $oneMonthAgo)

                  ->orWhere(function ($q) {
                      $q->where('seedtime', '<', 43200);
                  });

        })
        ->whereHas('torrent', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    // Calculate HnR satisfaction
    foreach ($snatchlist as $history) {

        $ratio = $history->actual_downloaded > 0
            ? $history->uploaded / $history->actual_downloaded
            : ($history->uploaded > 0 ? INF : 0);

        $seedMet = $history->seedtime >= 43200;
        $ratioMet = $ratio >= 1;
        $infiniteRatio = ($history->actual_downloaded == 0 && $history->uploaded > 0);

        $history->hnr_satisfied = $seedMet || $ratioMet || $infiniteRatio;
    }

    return view('snatch.snatchlist', [
        'snatchlist' => $snatchlist,
        'user' => User::find($userId),
        'userId' => $userId,
    ]);
}
public function seeding($userId = null)
{
    $currentUserClass = Auth::user()->user_class;

    if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    $userId ??= Auth::id();

    // Get active peers that are seeders for the user, with torrent info
    $seeding = Peer::where('user_id', $userId)
        ->where('seeder', 1)
        ->where('active', 1)
        ->with('torrent')
        ->orderBy('created_at', 'desc')
        ->paginate(30);

    // Get all history totals in one query for current page peers
    $torrentIds = $seeding->pluck('torrent_id')->toArray();

$historyTotals = History::where('user_id', $userId)
    ->whereIn('torrent_id', $torrentIds)
    ->selectRaw('torrent_id,
                 SUM(uploaded) as uploaded,
                 SUM(downloaded) as downloaded,
                 SUM(actual_uploaded) as actual_uploaded,
                 SUM(actual_downloaded) as actual_downloaded,
                 SUM(seedtime) as total_seedtime,
                 MAX(completed_at) as completed_at')
    ->groupBy('torrent_id')
    ->get()
    ->keyBy('torrent_id');

    // Attach totals to each peer
    $seeding->getCollection()->transform(function ($peer) use ($historyTotals) {
        $totals = $historyTotals->get($peer->torrent_id);
        $peer->uploaded = $totals->uploaded ?? 0;
        $peer->downloaded = $totals->downloaded ?? 0;
        $peer->actual_uploaded = $totals->actual_uploaded ?? 0;
        $peer->actual_downloaded = $totals->actual_downloaded ?? 0;
        $peer->total_seedtime = $totals->total_seedtime ?? 0;
        $peer->completed_at = $totals->completed_at ?? null;
        return $peer;
    });

    // Sort the current page collection by torrent added date
    $sorted = $seeding->getCollection()->sortByDesc(fn($peer) => $peer->torrent->created_at ?? now());
    $seeding->setCollection($sorted->values());

    return view('snatch.seeding', [
        'seeding' => $seeding,
        'user' => User::find($userId),
        'userId' => $userId,
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
            ->whereHas('torrent', function ($query) {
                $query->whereNull('deleted_at');
                })
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
    $currentUserClass = Auth::user()->user_class;

    if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    $userId = $userId ?? Auth::id();

    $needToSeed = History::where('user_id', $userId)

        ->where(function ($query) {
            $query->where('seedtime', '<', config('hitrun.seedtime'))
                  ->orWhere('seedtime', '=', 0);
        })

        ->whereHas('torrent', function ($query) {
            $query->whereNull('deleted_at');
        })

        // ratio < 1 using actual download
        ->whereRaw(
            'uploaded / (CASE WHEN actual_downloaded = 0 THEN 1 ELSE actual_downloaded END) < ?',
            [1.00]
        )

        // require at least 25% of torrent downloaded
        ->whereHas('torrent', function ($query) {
            $query->whereRaw('history.actual_downloaded >= torrents.size * 0.25');
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

public function hitRunFixer($userId = null)
{
    $currentUserClass = Auth::user()->user_class;

    if ($userId !== null && $userId != Auth::id() && $currentUserClass < UserClass::MODERATOR) {
        abort(403, 'Unauthorized action.');
    }

    $userId = $userId ?? Auth::id();

    $requiredSeedtime = config('hitrun.seedtime', 43200);

    $hnrFixer = History::where('user_id', $userId)
        ->where('hitrun', true)
        ->whereHas('torrent', function ($q) {
            $q->whereNull('deleted_at');
        })
        ->with('torrent')
        ->orderBy('created_at','desc')
        ->paginate(20);

    return view('snatch.hnr_fixer', [
        'hnrFixer' => $hnrFixer,
        'user' => User::find($userId),
        'userId' => $userId,
        'requiredSeedtime' => $requiredSeedtime
    ]);
}

}
