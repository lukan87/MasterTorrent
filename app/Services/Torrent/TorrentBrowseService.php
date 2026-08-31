<?php

namespace App\Services\Torrent;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TorrentBrowseService
{
    public function getNewTorrents(
        ?User $user,
        LengthAwarePaginator $paginator,
        string $timestampField
    ) {
        if (!$user) {
            return collect();
        }

        $lastBrowse = $user->{$timestampField};

        $new = $paginator->getCollection()->filter(
            fn ($torrent) =>
                !$lastBrowse || $torrent->created_at->gt($lastBrowse)
        );

        // Update timestamp
        $user->{$timestampField} = now();
        $user->save();

        return $new;
    }
}
