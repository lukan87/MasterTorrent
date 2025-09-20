<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Torrent;
use App\Models\TorrentThank;
use App\Models\Comment;
use App\Models\UserClass;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CoderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->user_class !== UserClass::WEB_DEVELOPER) {
            abort(403, 'Unauthorized access.');
        }

        $usersCount = User::count();
        $torrentsCount = Torrent::count();

        // Users registered in the last 30 days
        $usersLast30Days = User::where('created_at', '>=', now()->subDays(30))->count();
        $commentsCount = Comment::count(); // assuming you have a Comment model
        $thanksCount = TorrentThank::count();
        $activeUsers = User::where('updated_at', '>=', now()->subMinutes(5))->count();



        return view('coder.index', compact('usersCount', 'torrentsCount', 'usersLast30Days', 'commentsCount', 'thanksCount', 'activeUsers'));
    }
    public function torrents(Request $request)
{
    $user = Auth::user();
    if (!$user || $user->user_class !== UserClass::WEB_DEVELOPER) {
        abort(403, 'Unauthorized access.');
    }

    $query = Torrent::withCount(['comments', 'thanks']);

    // Search by name
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    // Sorting
    $sortable = ['seeders', 'leechers', 'times_completed', 'created_at'];
    $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
    $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';
    $query->orderBy($sort, $direction);

    $torrents = Torrent::with(['comments.user', 'thanks.user'])
    ->withCount(['comments', 'thanks'])
    ->orderBy($sort, $direction)
    ->paginate(50)
    ->withQueryString();


    return view('coder.torrents', compact('torrents', 'sort', 'direction'));
}

}
