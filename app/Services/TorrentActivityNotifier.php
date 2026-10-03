<?php

namespace App\Services;

use App\Models\Torrent;
use App\Models\User;
use App\Notifications\TorrentActivityNotification;

class TorrentActivityNotifier
{
    public function send(Torrent $torrent, User $actor, ?int $commentId = null, ?string $reaction = null): void
    {
        if ((int) $torrent->owner === (int) $actor->id) {
            return;
        }

        $owner = $torrent->uploader;
        if (!$owner || $owner->trashed()) {
            return;
        }

        $owner->notify(new TorrentActivityNotification($torrent, $actor, $commentId, $reaction));
    }
}
