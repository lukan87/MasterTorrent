<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\User;
use App\Notifications\TorrentDeletedNotification;
use App\Models\TorrentThank;
use App\Models\Peer;
use App\Models\TorrentLog;
use App\Models\History;
use App\Models\Comment;
use App\Models\Warning;
use App\Models\UserSlot;
use App\Models\TorrentImage;
use App\Models\Subtitle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TorrentDestroyService
{
    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE (Moderator delete)
    |--------------------------------------------------------------------------
    | Keeps all related data.
    | Only marks torrent as deleted.
    */
public function handle(Torrent $torrent, int $userId, string $reason): void
{
    DB::transaction(function () use ($torrent, $userId, $reason) {


        $torrentName = $torrent->name;
        $deletedBy = User::find($userId);

        // Store deletion metadata BEFORE soft delete
        $torrent->deleted_by = $userId;
        $torrent->deletion_reason = $reason;
        $torrent->save();

        // ----------------------------
        // Notify owner
        // ----------------------------
        if ($torrent->owner) {
            $owner = User::find($torrent->owner);

            if ($owner) {
                $owner->notify(
                    new TorrentDeletedNotification($torrent, $reason, $deletedBy)
                );
            }
        }

        // ----------------------------
        // Clear H&R + NeedToSeed logic
        // ----------------------------

        // Get affected users first
        $affectedUserIds = History::where('torrent_id', $torrent->id)
            ->where('hitrun', true)
            ->pluck('user_id')
            ->unique();

        // Remove H&R flags
        History::where('torrent_id', $torrent->id)
            ->update([
                'hitrun' => false,
                'active' => false,
            ]);

        // Decrement user hit_and_run_count safely
        foreach ($affectedUserIds as $uid) {
            User::where('id', $uid)
                ->where('hit_and_run_count', '>', 0)
                ->decrement('hit_and_run_count');
        }

        // Remove active peers (optional but recommended)
        Peer::where('torrent_id', $torrent->id)
            ->update(['active' => false]);

            // ----------------------------
// Remove warnings related to this torrent
// ----------------------------

$warnings = Warning::where('torrent', $torrent->id)->get();

foreach ($warnings as $warning) {

    // If you track warning count per user, decrement it safely
    $user = User::find($warning->user_id);

    if ($user && $user->warnings_count > 0) {
        $user->decrement('warnings_count');
    }

    $warning->delete();
}

        // ----------------------------
        // Soft delete
        // ----------------------------
           $torrent->seeders = 0;
           $torrent->leechers = 0;
           $torrent->save();

        $torrent->delete();

        
TorrentLog::create([
    'user_id'    => $userId,
    'torrent_id' => $torrent->id,
    'action'     => 'deleted',
    'description'=> 'Deleted torrent "' . $torrentName . '" | Reason: ' . $reason,
]);
    });
}

    /*
    |--------------------------------------------------------------------------
    | RESTORE TORRENT
    |--------------------------------------------------------------------------
    */
public function restore(Torrent $torrent, int $userId): void
{
    if ($torrent->trashed()) {

        $torrentName = $torrent->name;

    

        $torrent->update([
            'deleted_by' => null,
            'deletion_reason' => null,
            'deleted_at' => null,
        ]);

     
        // ✅ LOG RESTORE (CONSISTENT)
        TorrentLog::create([
            'user_id'    => $userId,
            'torrent_id' => $torrent->id,
            'action'     => 'restored',
            'description'=> 'Restored torrent "' . $torrentName . '"',
        ]);


           // $torrent->restore();
    }
}


public function forceDelete($id)
{
    $torrent = Torrent::withTrashed()->findOrFail($id);

    $torrentName = $torrent->name;
    $torrentId   = $torrent->id;

    TorrentLog::create([
        'user_id'    => auth()->id(),
        'torrent_id' => $torrentId,
        'action'     => 'force_deleted',
        'description'=> 'Permanently deleted torrent "' . $torrentName . '"',
    ]);

    $torrent->purge();

    return redirect()
        ->route('admin.torrents.index', ['status' => 'trashed'])
        ->with('success', 'Torrent permanently deleted.');
}
}