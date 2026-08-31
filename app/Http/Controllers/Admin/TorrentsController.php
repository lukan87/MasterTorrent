<?php

namespace App\Http\Controllers\Admin;

use App\Models\History;
use App\Models\Torrent;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\SystemMessageService;
use App\Services\Torrent\TorrentDestroyService;



class TorrentsController extends Controller
{
public function index(Request $request)
{
   $status = $request->get('status', 'active');

    $query = Torrent::with('uploader');

    /*
    |--------------------------------------------------------------------------
    | TRASHED FILTER
    |--------------------------------------------------------------------------
    */

    if ($status === 'trashed') {

        $query->onlyTrashed();

    } else {

        $query->whereNull('deleted_at');

        if ($status === 'active') {
            $query->where('seeders', '>', 0);
        }

        if ($status === 'dead') {
            $query->where('seeders', '=', 0);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOADER FILTER
    |--------------------------------------------------------------------------
    */

    if ($request->filled('uploader')) {

        $query->whereHas('uploader', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->uploader . '%');
        });

    }

    /*
    |--------------------------------------------------------------------------
    | FREELEECH
    |--------------------------------------------------------------------------
    */

    if ($request->filled('free')) {

        if ($request->free === 'yes') {
            $query->where('free', 1);
        }

        if ($request->free === 'no') {
            $query->where('free', 0);
        }

    }

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */

    if ($request->filled('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    */

   if ($request->status === 'trashed') {
    $query->orderBy('deleted_at', 'desc');
} else {
    $query->orderBy('created_at', 'desc');
}

$torrents = $query
    ->paginate(50)
    ->appends($request->all());

    return view('admin.torrents.index', compact('torrents'));
}



public function show($id)
{
    $torrent = Torrent::findOrFail($id);

    // Fetch paginated history
    $history = History::with(['user', 'peer'])
        ->where('info_hash', $torrent->info_hash)
        ->latest()
        ->paginate(10);

    // Seeders (from history)
    $seeders = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->where('seeder', true)
        ->latest()
        ->get();

    // Leechers (optional)
    $leechers = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->where('seeder', false)
        ->where('active', true)
        ->latest()
        ->get();

    // Completed users
    $completedUsers = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->whereNotNull('completed_at')
        ->select('user_id', DB::raw('MAX(completed_at) as last_completed'))
        ->groupBy('user_id')
        ->get();

    return view('admin.torrents.show', [
        'torrent'         => $torrent,
        'history'         => $history,
        'seeders'         => $seeders,
        'leechers'        => $leechers,
        'completedUsers'  => $completedUsers,
        'seedersCount'    => $seeders->count(),
        'leechersCount'   => $leechers->count(),
        'timesCompleted'  => $torrent->times_completed,
    ]);
}







    public function edit($id)
    {
        $torrent = Torrent::findOrFail($id);
        return view('admin.torrents.edit', compact('torrent'));
    }

    public function update(Request $request, $id)
{
    $torrent = Torrent::findOrFail($id);

    // Validate the incoming request data
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',

        // Add other fields for validation as needed
    ]);

    // Update movie details
    $torrent->name = $request->name;
    // Set other movie properties here
    $torrent->description = $request->description;


    $torrent->save();

    return redirect()->route('admin.torrents.index')->with('status', 'Movie updated successfully!');
}



public function destroy(Request $request, $id)
{
    $torrent = Torrent::findOrFail($id);

    $owner = $torrent->owner;

    $deletionReason = $request->input('deletion_reason');

    if (empty($deletionReason)) {
        $deletionReason = '0 seeders and 0 leechers';
    } elseif ($deletionReason === 'custom') {
        $deletionReason = $request->input('custom_reason');
    }

    /*
    |--------------------------------------------------------------------------
    | Notify uploader via conversation
    |--------------------------------------------------------------------------
    */

    if ($owner && User::where('id', $owner)->exists()) {

        SystemMessageService::send(
            auth()->id(), // or 2 if you want system user
            $owner,
            'Torrent Deletion',
            'Your torrent "<b>' . e($torrent->name) . '</b>" has been deleted.<br><br>
             <b>Reason:</b> ' . e($deletionReason)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store deletion info
    |--------------------------------------------------------------------------
    */

    $torrent->deleted_by = auth()->id();
    $torrent->deletion_reason = $deletionReason;

    // Reset cached tracker stats
    $torrent->seeders = 0;
    $torrent->leechers = 0;

    $torrent->save();

    /*
    |--------------------------------------------------------------------------
    | Soft delete torrent
    |--------------------------------------------------------------------------
    */

    $torrent->delete();

    return redirect()
        ->route('admin.torrents.index')
        ->with('success', 'Torrent deleted successfully.');
}

public function restore(Torrent $torrent, TorrentDestroyService $service)
{
    $service->restore($torrent, auth()->id());

    return redirect()
        ->route('admin.torrents.index', ['status' => 'trashed'])
        ->with('success', 'Torrent restored successfully.');
}

public function forceDelete($id, TorrentDestroyService $service)
{
    $service->forceDelete($id, auth()->id());

    return redirect()
        ->route('admin.torrents.index', ['status' => 'trashed'])
        ->with('success', 'Torrent permanently deleted.');
}

}
