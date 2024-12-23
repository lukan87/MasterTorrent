<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\History;
use Carbon\Carbon;

class HitAndRunController extends Controller
{
    // Display torrents that need to be seeded
    public function index()
    {
        $user = Auth::user();
        $current = now();

        $torrents = History::where('user_id', $user->id)
            ->where('created_at', '>', '2024-12-15 00:00:00')
            ->where('actual_downloaded', '>', 0)
            ->where('seedtime', '<', config('hitrun.seedtime'))
            ->where('active', '=', 0)
            //->where('immune', '=', 0)
            //->where('prewarn', '=', 0)
            ->has('torrent')
            // ->where('updated_at', '<', $current->subDays(config('hitrun.prewarn')))
            // ->whereHas('user', function ($query) {
            //     // Filter based on the 'is_immune' field in the related User model
            //     $query->where('is_immune', false)
            //           ->where('donor', 'no');  // Assuming is_immune is a boolean field
            // })
            ->whereHas('torrent', function ($query) {
                $query->whereRaw('history.actual_downloaded > torrents.size * ?', [config('hitrun.buffer') / 100])
                      ->where('seeders', '>', 0);  // Ensure there are seeders greater than 0
            })
            ->whereDoesntHave('user.warnings', fn ($query) => $query->withTrashed()->whereColumn('warnings.torrent', '=', 'history.torrent_id'))
            ->paginate(10);

        return view('hitandrun.index', compact('torrents'));
    }

    // Display torrents that need to be seeded for other users
    public function showOtherUserHitAndRun($userId)
    {
        $user = Auth::user();
        $current = now();

        if ($user->id != $userId && $user->user_class <= 7) {
            abort(403, 'Unauthorized action.');
        }

        $viewedUser = User::findOrFail($userId);

        $torrents = History::where('user_id', $viewedUser->id)
             ->where('created_at', '>', '2024-12-15 00:00:00')
            // ->whereNull('prewarned_at')
            ->where('actual_downloaded', '>', 0)
            ->where('seedtime', '<', config('hitrun.seedtime'))
            ->where('active', '=', 0)
            //->where('immune', '=', 0)
            ->where('hitrun', '=', 0)
            //->where('updated_at', '<', $current->subDays(config('hitrun.prewarn')))
            ->has('torrent')
            // ->whereHas('user', function ($query) {
            //     // Filter based on the 'is_immune' field in the related User model
            //     $query->where('is_immune', false)
            //           ->where('donor', 'no');  // Assuming is_immune is a boolean field
            // })
            ->whereHas('torrent', function ($query) {
                $query->whereRaw('history.actual_downloaded > torrents.size * ?', [config('hitrun.buffer') / 100])
                      ->where('seeders', '>', 0);  // Ensure there are seeders greater than 0
            })
            ->whereDoesntHave('user.warnings', fn ($query) => $query->withTrashed()->whereColumn('warnings.torrent', '=', 'history.torrent_id'))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('hitandrun.index', compact('torrents', 'viewedUser'));
    }
}

