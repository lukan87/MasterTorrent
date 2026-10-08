<?php

namespace App\Services;

use App\Models\Torrent;
use App\Models\User;
use App\Notifications\TorrentActivityNotification;

class TorrentActivityNotifier
{
    public function send(Torrent $torrent, User $actor, ?int $commentId = null, ?string $reaction = null): void
    {
        $owner = $torrent->uploader;
        if ($owner && ! $owner->trashed() && (int) $owner->id !== (int) $actor->id) {
            $owner->notify(new TorrentActivityNotification($torrent, $actor, $commentId, $reaction));
        }

        // EXISTS avoids duplicate alerts when a user has multiple history rows.
        User::whereKeyNot($actor->id)
            ->whereKeyNot($torrent->owner)
            ->whereHas('history', fn ($query) => $query
                ->where('torrent_id', $torrent->id)
                ->whereNotNull('completed_at'))
            ->chunkById(200, function ($downloaders) use ($torrent, $actor, $commentId, $reaction) {
                foreach ($downloaders as $downloader) {
                    $downloader->notify(new TorrentActivityNotification($torrent, $actor, $commentId, $reaction, downloaded: true));
                }
            });
    }
}
