<?php

namespace App\Services;

use App\Models\Torrent;
use App\Models\User;
use App\Models\TorrentSubscription;
use App\Helpers\FormatHelper;

/**
 * Manages per-title torrent subscriptions.
 *
 * A user subscribes to a title identified by its imdbid/tmdbid (usually from a
 * torrent that carries a valid IMDb link). Whenever a new torrent sharing the
 * same imdbid OR tmdbid is uploaded, every matching subscriber receives a
 * private message describing the new upload.
 */
class TorrentSubscriptionService
{
    /**
     * Register a subscription for a user based on the given torrent's ids.
     * Returns false if there is nothing to subscribe to or it already exists.
     */
    public function subscribe(User $user, Torrent $torrent): bool
    {
        $imdbid = $torrent->imdbid ? trim((string) $torrent->imdbid) : null;
        $tmdbid = $torrent->tmdbid ? trim((string) $torrent->tmdbid) : null;

        if (!$this->hasSubscribeableId($imdbid, $tmdbid)) {
            return false;
        }

        if (TorrentSubscription::existsFor($user->id, $imdbid, $tmdbid)) {
            return false;
        }

        TorrentSubscription::create([
            'user_id'           => $user->id,
            'imdbid'            => $imdbid,
            'tmdbid'            => $tmdbid,
            'source_torrent_id' => $torrent->id,
        ]);

        return true;
    }

    /**
     * Remove any subscription for this user matching the torrent's ids.
     */
    public function unsubscribe(User $user, Torrent $torrent): bool
    {
        $imdbid = $torrent->imdbid ? trim((string) $torrent->imdbid) : null;
        $tmdbid = $torrent->tmdbid ? trim((string) $torrent->tmdbid) : null;

        $query = TorrentSubscription::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($imdbid, $tmdbid) {
                if ($imdbid !== null && $imdbid !== '') {
                    $q->orWhere('imdbid', $imdbid);
                }
                if ($tmdbid !== null && $tmdbid !== '') {
                    $q->orWhere('tmdbid', $tmdbid);
                }
                if (($imdbid === null || $imdbid === '') && ($tmdbid === null || $tmdbid === '')) {
                    $q->whereRaw('1 = 0');
                }
            });

        return $query->delete() > 0;
    }

    /**
     * Is the user already subscribed to a title matching these ids?
     */
    public function isSubscribed(User $user, Torrent $torrent): bool
    {
        $imdbid = $torrent->imdbid ? trim((string) $torrent->imdbid) : null;
        $tmdbid = $torrent->tmdbid ? trim((string) $torrent->tmdbid) : null;

        if (!$this->hasSubscribeableId($imdbid, $tmdbid)) {
            return false;
        }

        return TorrentSubscription::existsFor($user->id, $imdbid, $tmdbid);
    }

    /**
     * Can this torrent be subscribed to? Requires at least one of imdbid/tmdbid.
     */
    public function canSubscribe(Torrent $torrent): bool
    {
        return $this->hasSubscribeableId(
            $torrent->imdbid ? trim((string) $torrent->imdbid) : null,
            $torrent->tmdbid ? trim((string) $torrent->tmdbid) : null
        );
    }

    /**
     * Notify every subscriber whose saved imdbid/tmdbid matches the newly
     * uploaded torrent. Returns the number of messages sent. The uploader is
     * skipped so they don't get a notice about their own upload.
     */
    public function notifyUpload(Torrent $torrent): int
    {
        $imdbid = $torrent->imdbid ? trim((string) $torrent->imdbid) : null;
        $tmdbid = $torrent->tmdbid ? trim((string) $torrent->tmdbid) : null;

        if (!$this->hasSubscribeableId($imdbid, $tmdbid)) {
            return 0;
        }

        $subscriptions = TorrentSubscription::query()
            ->where(function ($q) use ($imdbid, $tmdbid) {
                if ($imdbid !== null && $imdbid !== '') {
                    $q->orWhere('imdbid', $imdbid);
                }
                if ($tmdbid !== null && $tmdbid !== '') {
                    $q->orWhere('tmdbid', $tmdbid);
                }
            })
            ->where('user_id', '!=', (int) $torrent->owner)
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $senderId = (int) $torrent->owner;
        $subject  = 'New upload: ' . $torrent->name;

        $viewUrl = route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]);

        foreach ($subscriptions as $sub) {
            SystemMessageService::send(
                $senderId,
                (int) $sub->user_id,
                $subject,
                $this->buildMessageBody($torrent, $viewUrl)
            );
        }

        return count($subscriptions);
    }

    /**
     * Draft the detailed private message body for a new upload.
     */
    protected function buildMessageBody(Torrent $torrent, string $viewUrl): string
    {
        $lines = [];
        $lines[] = 'A torrent matching a title you are subscribed to has just been uploaded.';
        $lines[] = '';
        $lines[] = 'Title  : ' . $torrent->name;
        $lines[] = 'Uploader: ' . ($torrent->uploader->name ?? 'N/A');

        if ($torrent->category) {
            $lines[] = 'Category: ' . $torrent->category->name;
        }
        if ($torrent->type) {
            $lines[] = 'Type   : ' . $torrent->type;
        }
        if ($torrent->size) {
            $lines[] = 'Size   : ' . FormatHelper::formatSize($torrent->size);
        }
        if ($torrent->imdbid) {
            $lines[] = 'IMDb   : https://www.imdb.com/title/' . $torrent->imdbid;
        }
        if ($torrent->tmdbid) {
            $lines[] = 'TMDB   : https://www.themoviedb.org/movie/' . $torrent->tmdbid;
        }
        $lines[] = '';
        $lines[] = 'View it here: ' . $viewUrl;

        return implode(PHP_EOL, $lines);
    }

    protected function hasSubscribeableId(?string $imdbid, ?string $tmdbid): bool
    {
        return ($imdbid !== null && $imdbid !== '')
            || ($tmdbid !== null && $tmdbid !== '');
    }
}