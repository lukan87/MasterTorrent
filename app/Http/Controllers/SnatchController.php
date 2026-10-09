<?php

namespace App\Http\Controllers;

use App\Models\Peer;
use App\Models\History;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    $validated = request()->validate(['q' => ['nullable', 'string', 'max:200']]);
    $search = trim($validated['q'] ?? '');
    $requiredSeedtime = (int) config('hitrun.seedtime', 43200);

    $query = History::where('user_id', $userId);

    if ($search !== '') {
        // Search the full retained history, including soft-deleted torrents.
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
        $query->whereHas('torrent', function ($torrentQuery) use ($pattern) {
            $torrentQuery->whereRaw("name LIKE ? ESCAPE '!'", [$pattern]);
        });
    } else {
        // Recent entries remain visible; older entries remain only while both targets are unmet.
        $query->where(function ($historyQuery) use ($requiredSeedtime) {
            $historyQuery->where('created_at', '>=', now()->subDays(10))
                ->orWhere(function ($unmetQuery) use ($requiredSeedtime) {
                    $unmetQuery->whereRaw('COALESCE(seedtime, 0) < ?', [$requiredSeedtime])
                        ->whereRaw('(
                            (COALESCE(actual_downloaded, downloaded, 0) > 0
                                AND COALESCE(uploaded, 0) < COALESCE(actual_downloaded, downloaded, 0))
                            OR (COALESCE(actual_downloaded, downloaded, 0) <= 0
                                AND COALESCE(uploaded, 0) <= 0)
                        )');
                });
        })->whereHas('torrent', function ($torrentQuery) {
            $torrentQuery->whereNull('deleted_at');
        });
    }

    $snatchlist = $query->with('torrent')
        ->orderByDesc('created_at')
        ->orderByDesc('id')
        ->tap(fn ($query) => $this->filterActivity($query, false))
        ->paginate(20)->withQueryString()
        ->appends($search !== '' ? ['q' => $search] : []);

    return \App\Services\PageBrowse::view('snatch.snatchlist', [
        'snatchlist' => $snatchlist,
        'search' => $search,
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
        ->tap(fn ($query) => $this->filterActivity($query))
        ->paginate(30)->withQueryString();

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

    $this->attachSnatchHistory($seeding, $userId);

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
    $sorted = request()->filled('sort') ? $seeding->getCollection() : $seeding->getCollection()->sortByDesc(fn($peer) => $peer->torrent->created_at ?? now());
    $seeding->setCollection($sorted->values());

    return \App\Services\PageBrowse::view('snatch.seeding', [
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
            ->where('active', 1)
            ->with('torrent')
            ->tap(fn ($query) => $this->filterActivity($query))
            ->paginate(10)->withQueryString();

        $this->attachSnatchHistory($leeching, $userId);

        return \App\Services\PageBrowse::view('snatch.leeching', [
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
            ->tap(fn ($query) => $this->filterActivity($query))
            ->paginate(20)->withQueryString();

        return \App\Services\PageBrowse::view('snatch.hit_and_run', [
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

        ->with('torrent')
        ->orderBy('created_at', 'desc')
        ->tap(fn ($query) => $this->filterActivity($query))
        ->paginate(20)->withQueryString();

    return \App\Services\PageBrowse::view('snatch.need_to_seed', [
        'needToSeed' => $needToSeed,
        'user' => User::find($userId),
        'userId' => $userId,
    ]);
}


/** Attach only this user's latest history; torrent IDs alone do not identify a user. */
private function attachSnatchHistory($peers, $userId): void
{
    $histories = History::where('user_id', $userId)
        ->whereIn('torrent_id', $peers->pluck('torrent_id'))
        ->orderByDesc('last_event_at')
        ->orderByDesc('id')
        ->get()
        ->unique('torrent_id')
        ->keyBy('torrent_id');

    foreach ($peers as $peer) {
        $peer->setRelation('snatchHistory', $histories->get($peer->torrent_id));
    }
}

public function deleteHistory($historyId)
{
    abort_unless(Auth::user()->user_class >= UserClass::ADMIN, 403, 'Unauthorized action.');

    DB::transaction(function () use ($historyId) {
        $history = History::whereKey($historyId)->lockForUpdate()->firstOrFail();

        if ((int) $history->user_id === (int) Auth::id()) {
            abort_if(
                $history->torrent && (int) $history->torrent->owner === (int) Auth::id(),
                403,
                'Torrent owners cannot delete their own snatch.'
            );
        }

        if ($history->hitrun) {
            User::whereKey($history->user_id)
                ->where('hit_and_run_count', '>', 0)
                ->decrement('hit_and_run_count');
        }

        $history->delete();
    });

    return redirect()->back()->with('success', 'Snatch removed from history.');
}

public function deleteNeedToSeed($userId, $torrentId)
{
    abort_unless(Auth::user()->user_class >= UserClass::ADMIN, 403, 'Unauthorized action.');

    $history = History::where('user_id', $userId)->where('torrent_id', $torrentId)->firstOrFail();

    return $this->deleteHistory($history->id);
}

public function deleteHNR($userId, $torrentId)
{
    return $this->deleteNeedToSeed($userId, $torrentId);
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
        ->tap(fn ($query) => $this->filterActivity($query))
        ->paginate(20)->withQueryString();

    return \App\Services\PageBrowse::view('snatch.hnr_fixer', [
        'hnrFixer' => $hnrFixer,
        'user' => User::find($userId),
        'userId' => $userId,
        'requiredSeedtime' => $requiredSeedtime
    ]);
}


private function filterActivity($query, bool $applySearch = true): void
{
    $validated = request()->validate(['q' => ['nullable', 'string', 'max:200'], 'sort' => ['nullable', 'in:latest,oldest']]);
    if ($applySearch && ($search = trim($validated['q'] ?? '')) !== '') {
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
        $query->whereHas('torrent', fn ($torrent) => $torrent->whereRaw("name LIKE ? ESCAPE '!'", [$pattern]));
    }
    if (!empty($validated['sort'])) $query->reorder('created_at', $validated['sort'] === 'oldest' ? 'asc' : 'desc')->orderBy('id', $validated['sort'] === 'oldest' ? 'asc' : 'desc');
}

}
