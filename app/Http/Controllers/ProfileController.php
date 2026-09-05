<?php

namespace App\Http\Controllers;

use App\Models\Subtitle;
use App\Models\User;
use App\Models\Peer;
use App\Models\History;
use App\Models\Torrent;
use App\Models\UserTimeline;
use App\Models\Message;
use App\Models\Comment;
use App\Models\ForumPost;
use App\Models\Warning;
use App\Models\UserSlot;
use App\Models\TorrentThank;
use App\Models\Ticket;
use App\Models\UserClass;
use App\Services\Torrent\TorrentDestroyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Notification;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function resolveUserOrFail($id, $name)
    {
        $user = User::findOrFail($id);

        if ($user->name !== $name) {
            abort(404);
        }

        return $user;
    }

    private function authorizeProfileEdit(User $targetUser)
    {
        $authUser = Auth::user();

        if (!$authUser) {
            abort(403);
        }

        // Cannot edit owner unless owner
        if (
            $authUser->user_class < UserClass::OWNER &&
            $targetUser->user_class == UserClass::OWNER
        ) {
            abort(403, 'Unauthorized action.');
        }

        // Only self or admin+
        if (
            $authUser->id !== $targetUser->id &&
            $authUser->user_class < UserClass::ADMIN
        ) {
            abort(403, 'Unauthorized action.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Display
    |--------------------------------------------------------------------------
    */

public function show($id, $name = null)
{
   $user = User::withTrashed()
    ->with('timeline.staff')
    ->findOrFail($id);

    if ($name === null || $name !== $user->name) {
        return redirect()->route('profile.show', [
            'id' => $user->id,
            'name' => $user->name
        ]);
    }

    /*
|--------------------------------------------------------------------------
| Community Stats
|--------------------------------------------------------------------------
*/


$commentCount = Comment::where('user_id', $user->id)->count();

$thanksCount = TorrentThank::where('user_id', $user->id)->count();

$forumPostCount = ForumPost::where('user_id', $user->id)->count();

    /*
    |--------------------------------------------------------------------------
    | Active Seeds
    |--------------------------------------------------------------------------
    */

    $activeSeeds = Peer::where('user_id', $user->id)
        ->where('seeder', 1)
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Seeding Size
    |--------------------------------------------------------------------------
    */

    $totalSeedSize = Peer::where('user_id', $user->id)
        ->where('seeder', 1)
        ->join('torrents', 'peers.torrent_id', '=', 'torrents.id')
        ->sum('torrents.size');

    /*
    |--------------------------------------------------------------------------
    | Seedtime
    |--------------------------------------------------------------------------
    */

    $totalSeedTime = History::where('user_id', $user->id)
        // ->where('seeder', 1)
        ->sum('seedtime');

    $avgSeedTime = $activeSeeds > 0
        ? $totalSeedTime / $activeSeeds
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Seeding Health (12h per torrent = 100%)
    |--------------------------------------------------------------------------
    */

    $requiredSeedTime = 43200;

    $torrentCount = History::where('user_id', $user->id)
        ->whereNotNull('seedtime')
        ->count();

    $totalHealth = 0;

    History::where('user_id', $user->id)
        ->select('seedtime')
        ->chunk(500, function ($histories) use (&$totalHealth, $requiredSeedTime) {

            foreach ($histories as $history) {

                $seedtime = $history->seedtime ?? 0;

                $health = min(1, $seedtime / $requiredSeedTime);

                $totalHealth += $health;
            }
        });

    $seedingHealth = $torrentCount > 0
        ? round(($totalHealth / $torrentCount) * 100)
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Seeder Reputation (time + size)
    |--------------------------------------------------------------------------
    */

$seedingReputation = $user->seeding_reputation;

    /*
|--------------------------------------------------------------------------
| Rank Display
|--------------------------------------------------------------------------
*/

$rankMap = [
    0 => ['New Seeder', '🌱'],
    1 => ['Bronze', '🥉'],
    2 => ['Silver', '🥈'],
    3 => ['Gold', '🥇'],
    4 => ['Elite', '💎'],
    5 => ['Legend', '👑'],
];

[$seederRank, $seederIcon] = $rankMap[$user->seeder_rank] ?? $rankMap[0];

$timeline = $user->timeline()->latest()->get();



    return view('profile.show', compact(
        'user',
        'activeSeeds',
        'totalSeedSize',
        'totalSeedTime',
        'avgSeedTime',
        'seedingHealth',
        'seedingReputation',
        'seederRank',
        'seederIcon',
        'timeline',
        'commentCount',
        'thanksCount',
        'forumPostCount'
    ));
}
    /*
    |--------------------------------------------------------------------------
    | Edit Profile
    |--------------------------------------------------------------------------
    */

    public function edit($id, $name)
    {
        $user = $this->resolveUserOrFail($id, $name);
        $this->authorizeProfileEdit($user);

        return view('profile.edit', compact('user'));
    }

   public function update(Request $request, $id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);
    $this->authorizeProfileEdit($user);

    $authUser = Auth::user();

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    $rules = [
        'profile_image_url' => 'nullable|url|max:2048',
        'cover'             => 'nullable|url|starts_with:https://|max:2048',
        'background'        => 'nullable|url|starts_with:https://|max:2048',
        'info'              => 'nullable|string',
        'timezone'          => 'nullable|timezone',
        'recovery_code'     => 'nullable|string|min:6',
    ];

    if ($authUser->user_class >= UserClass::ADMIN) {
        $rules['email'] = 'required|email|max:255';
    }

    if (
        $authUser->user_class >= UserClass::ADMIN &&
        $authUser->id !== $user->id
    ) {
        $rules['name'] = 'required|string|max:255|unique:users,name,' . $user->id;
        $rules['user_class'] = 'sometimes|integer';
    }

    $validated = $request->validate($rules);

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($user, $validated, $request, $authUser) {

        /*
        |--------------------------------------------------------------------------
        | Normalize empty strings to null
        |--------------------------------------------------------------------------
        */
        foreach ([
            'profile_image_url',
            'cover',
            'background',
            'info',
            'timezone'
        ] as $field) {
            if ($request->has($field)) {
                $validated[$field] = trim($validated[$field] ?? '') ?: null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Basic Profile Fields
        |--------------------------------------------------------------------------
        */

        if ($request->has('profile_image_url')) {
            $user->profile_image = $validated['profile_image_url'];
        }

        if ($request->has('cover')) {
            $user->cover = $validated['cover'];
        }

        if ($request->has('background')) {
            $user->background = $validated['background'];
        }

        if ($request->has('info')) {
            $user->info = $validated['info'];
        }

        if ($request->has('timezone')) {
            $user->timezone = $validated['timezone'];
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Editable Fields
        |--------------------------------------------------------------------------
        */

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        /*
        |--------------------------------------------------------------------------
        | Recovery Code
        |--------------------------------------------------------------------------
        */

        if ($request->filled('recovery_code')) {
            $user->recovery_code = Hash::make($request->recovery_code);
        }

        /*
        |--------------------------------------------------------------------------
        | Safe User Class Change
        |--------------------------------------------------------------------------
        */

        if ($request->has('user_class')) {
            $requestedClass = (int) $request->user_class;

            if ($authUser->user_class <= $requestedClass) {
                abort(403, 'You cannot assign equal or higher class than yourself.');
            }

            $user->user_class = $requestedClass;
        }

        $user->save();
    });

    return redirect()->route('profile.show', [
        'id'   => $user->id,
        'name' => $user->name
    ])->with('success', 'Profile updated successfully.');
}

    /*
    |--------------------------------------------------------------------------
    | Torrents & History
    |--------------------------------------------------------------------------
    */

    public function seedingTorrents($id, $name)
    {
        $user = $this->resolveUserOrFail($id, $name);

       $peers = Peer::select(
        'torrent_id',
        DB::raw('SUM(uploaded) as total_uploaded'),
        DB::raw('SUM(downloaded) as total_downloaded'),
        DB::raw('GROUP_CONCAT(agent) as agents')
    )
    ->where('user_id', $user->id)
    ->where('seeder', 1)
    ->whereHas('torrent', function ($q) {
        $q->whereNull('deleted_at');
    })
    ->groupBy('torrent_id')
    ->with('torrent')
    ->paginate(25);

        $histories = History::where('user_id', $user->id)
            ->whereIn('torrent_id', $peers->pluck('torrent_id'))
            ->get()
            ->keyBy('torrent_id');

        return view('profile.seeding-torrents', compact('user', 'peers', 'histories'));
    }

public function userTorrents($id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);

    $query = Torrent::where('owner', $user->id);

    if (auth()->check() && auth()->user()->user_class >= UserClass::MODERATOR) {
        $query->withTrashed();
    }

    $torrents = $query->orderByDesc('created_at')->paginate(50);

    return view('profile.torrents', compact('user', 'torrents'));
}

    public function downloadHistory($id, $name)
    {
        $user = $this->resolveUserOrFail($id, $name);

        $history = History::where('user_id', $user->id)
            ->with('torrent')
            ->latest('completed_at')
            ->paginate(25);

        return view('profile.download-history', compact('user', 'history'));
    }

    public function activeTokens($id, $name)
    {
        $user = $this->resolveUserOrFail($id, $name);

        $slots = $user->slots()->with('torrent')->get();

        return view('profile.active-tokens', compact('user', 'slots'));
    }

    /*
    |--------------------------------------------------------------------------
    | Torrent Deletion
    |--------------------------------------------------------------------------
    */

public function destroyTorrent(Request $request, Torrent $torrent)
{
    $user = Auth::user();

    if (
        $user->id !== $torrent->owner &&
        $user->user_class < UserClass::WEB_DEVELOPER
    ) {
        abort(403);
    }

    $reason = $request->input('deletion_reason') ?? 'No reason provided';

    if ($request->input('deletion_reason') === 'custom') {
        $reason = $request->input('custom_reason') ?: 'No reason provided';
    }

    app(TorrentDestroyService::class)
        ->handle($torrent, $user->id, $reason);

    return back()->with('success', 'Torrent deleted successfully.');
}

    /*
    |--------------------------------------------------------------------------
    | Account Deletion
    |--------------------------------------------------------------------------
    */

public function deleteAccount(Request $request, $id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);

    if (Auth::id() !== $user->id) {
        abort(403);
    }

    $request->validate([
        'password' => ['required'],
    ]);

    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'password' => 'Wrong password. Account cannot be deleted.'
        ]);
    }

    DB::transaction(function () use ($user) {

        // Stop active tracker activity
        Peer::where('user_id', $user->id)->delete();
        History::where('user_id', $user->id)->delete();

        // Delete torrents with 0 seeders
        $torrentsToDelete = Torrent::where('owner', $user->id)
            ->where('seeders', 0)
            ->get();

        foreach ($torrentsToDelete as $torrent) {
            $this->deleteTorrentCompletely($torrent);
        }

        // Reassign torrents still being seeded
        Torrent::where('owner', $user->id)
            ->where('seeders', '>', 0)
            ->update(['owner' => 2]); // system user

        // Soft delete user (keeps comments, timeline, etc)
        $user->delete();
    });

    Auth::logout();

    return redirect('/')->with('success', 'Account deleted successfully.');
}

public function permanentlyDeleteUser($id)
{
    $user = User::withTrashed()->findOrFail($id);

    DB::transaction(function () use ($user) {

        Peer::where('user_id', $user->id)->delete();
        History::where('user_id', $user->id)->delete();

        Message::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->delete();

        Comment::where('user_id', $user->id)->delete();
        UserTimeline::where('user_id', $user->id)->delete();
        Warning::where('user_id', $user->id)->delete();
        UserSlot::where('user_id', $user->id)->delete();
        Ticket::where('user_id', $user->id)->delete();

        Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->delete();

        $torrents = Torrent::withTrashed()->where('owner', $user->id)->get();

        foreach ($torrents as $torrent) {
            $this->deleteTorrentCompletely($torrent);
        }

        // Permanently remove user
        $user->forceDelete();
    });

    return redirect()->back()->with('success', 'User permanently deleted.');
}

private function deleteTorrentCompletely(Torrent $torrent): void
{
    /*
    |--------------------------------------------------------------------------
    | Related DB Cleanup
    |--------------------------------------------------------------------------
    */

    TorrentThank::where('torrent_id', $torrent->id)->delete();
    Peer::where('torrent_id', $torrent->id)->delete();
    History::where('torrent_id', $torrent->id)->delete();
    Comment::where('torrent_id', $torrent->id)->delete();
    Warning::where('torrent', $torrent->id)->delete();
    UserSlot::where('torrent_id', $torrent->id)->delete();

    /*
    |--------------------------------------------------------------------------
    | Images (WebP + fallback)
    |--------------------------------------------------------------------------
    */

    $torrent->images()->each(function ($image) {

        if (!empty($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        if (!empty($image->fallback)) {
            Storage::disk('public')->delete($image->fallback);
        }

        $image->delete();
    });

    /*
    |--------------------------------------------------------------------------
    | Subtitles
    |--------------------------------------------------------------------------
    */

    Subtitle::where('torrent_id', $torrent->id)->each(function ($subtitle) {

        if (!empty($subtitle->file_path)) {
            Storage::delete($subtitle->file_path);
        }

        $subtitle->delete();
    });

    /*
    |--------------------------------------------------------------------------
    | Torrent File
    |--------------------------------------------------------------------------
    */

    if (!empty($torrent->file_name)) {
        Storage::disk('public')->delete('files/torrents/' . $torrent->file_name);
    }

    /*
    |--------------------------------------------------------------------------
    | Pivot Tables (Genres etc.)
    |--------------------------------------------------------------------------
    */

    $torrent->genres()->detach();

    /*
    |--------------------------------------------------------------------------
    | Final Delete
    |--------------------------------------------------------------------------
    */

    $torrent->forceDelete();
}

public function regeneratePasskey($id)
{
    $user = User::findOrFail($id);

    if (auth()->id() !== $user->id && 
        auth()->user()->user_class < UserClass::ADMIN) {
        abort(403);
    }

    $user->passkey = bin2hex(random_bytes(16));
    $user->save();
    Peer::where('user_id', $user->id)->delete();

    return back()->with('success', 'Passkey regenerated successfully.');
}


public function comments($id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);

    $comments = Comment::where('user_id', $user->id)
        ->with(['torrent' => function ($q) {
            $q->withTrashed();
        }])
        ->latest()
        ->paginate(25);

    return view('profile.comments', compact('user', 'comments'));
}


public function thanks($id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);

    $thanks = TorrentThank::where('user_id', $user->id)
         ->with(['torrent' => function ($q) {
            $q->withTrashed();
        }])
        ->latest()
        ->paginate(25);

    return view('profile.thanks', compact('user', 'thanks'));
}

public function forumPosts($id, $name)
{
    $user = $this->resolveUserOrFail($id, $name);

    $posts = ForumPost::where('user_id', $user->id)
        ->with([
            'topic.category',
        ])
        ->latest()
        ->paginate(25);

    return view('profile.posts', compact('user', 'posts'));
}

}
