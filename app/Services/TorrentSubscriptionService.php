<?php

namespace App\Services;

use App\Models\Torrent;
use App\Models\User;
use App\Models\TorrentMovie;
use App\Models\TorrentSeries;
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

        // Resolve the clean TMDB title + type from the torrent_movies /
        // torrent_series metadata tables by tmdbid when possible, so the
        // profile shows the real title instead of the raw uploaded filename.
        $meta = $this->resolveMeta($torrent, $tmdbid);

        TorrentSubscription::create([
            'user_id'           => $user->id,
            'imdbid'            => $imdbid,
            'tmdbid'            => $tmdbid,
            'source_torrent_id' => $torrent->id,
            'title'             => $meta['title'] ?? null,
            'type'              => $meta['type'] ?? null,
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
     * All distinct users subscribed to the title identified by the given ids.
     * A user matches if any of their subscriptions carries the same imdbid OR tmdbid.
     *
     * @param string|null $imdbid
     * @param string|null $tmdbid
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function subscribers(?string $imdbid = null, ?string $tmdbid = null): \Illuminate\Support\Collection
    {
        if (!$this->hasSubscribeableId($imdbid, $tmdbid)) {
            return collect();
        }

        $imdbid = $imdbid ? trim($imdbid) : null;
        $tmdbid = $tmdbid ? trim($tmdbid) : null;

        return User::whereIn('id', function ($q) use ($imdbid, $tmdbid) {
            $q->select('user_id')
                ->from((new TorrentSubscription())->getTable())
                ->where(function ($w) use ($imdbid, $tmdbid) {
                    if ($imdbid !== null) {
                        $w->orWhere('imdbid', $imdbid);
                    }
                    if ($tmdbid !== null) {
                        $w->orWhere('tmdbid', $tmdbid);
                    }
                });
        })->select(['id', 'name'])->get();
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
     * Subscribe a user straight from a TMDB title (e.g. a library page). This is
     * used instead of subscribe() when we already know the clean TMDB title, so
     * the subscription stores that name rather than the uploaded filename.
     */
    public function subscribeToTitle(
        User $user,
        string $tmdbid,
        string $title,
        string $type,
        ?int $sourceTorrentId = null,
        ?string $imdbid = null
    ): bool {
        $tmdbid = trim($tmdbid);
        $title  = trim($title);

        if ($tmdbid === '' || $title === '') {
            return false;
        }

        if (TorrentSubscription::existsFor($user->id, null, $tmdbid)) {
            return false;
        }

        TorrentSubscription::create([
            'user_id'           => $user->id,
            'imdbid'            => ($imdbid !== null && $imdbid !== '') ? trim($imdbid) : null,
            'tmdbid'            => $tmdbid,
            'source_torrent_id' => $sourceTorrentId,
            'title'             => $title,
            'type'              => $type,
        ]);

        return true;
    }

    /**
     * Remove any subscription the user holds on the given TMDB id.
     */
    public function unsubscribeByTmdb(User $user, string $tmdbid): bool
    {
        $tmdbid = trim($tmdbid);

        if ($tmdbid === '') {
            return false;
        }

        return TorrentSubscription::query()
            ->where('user_id', $user->id)
            ->where('tmdbid', $tmdbid)
            ->delete() > 0;
    }

    /**
     * Is the user already subscribed to the given TMDB id?
     */
    public function isSubscribedByTmdb(User $user, string $tmdbid): bool
    {
        $tmdbid = trim($tmdbid);

        if ($tmdbid === '') {
            return false;
        }

        return TorrentSubscription::query()
            ->where('user_id', $user->id)
            ->where('tmdbid', $tmdbid)
            ->exists();
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

    /**
     * Look up the clean TMDB title + type for a torrent's tmdbid using the
     * torrent_movies / torrent_series metadata tables (keyed by tmdbid). Falls
     * back to searching both tables if the torrent has no type flag or the
     * metadata row has not been synced yet.
     *
     * @return array{title: ?string, type: ?string}
     */
    protected function resolveMeta(Torrent $torrent, ?string $tmdbid): array
    {
        if ($tmdbid === null || $tmdbid === '') {
            return [null, null];
        }

        $type = strtolower((string) $torrent->tmdb_type);

        if ($type === 'tv') {
            if ($row = TorrentSeries::where('tmdbid', $tmdbid)->first()) {
                return ['title' => $row->title, 'type' => 'tv'];
            }
        } elseif ($type === 'movie') {
            if ($row = TorrentMovie::where('tmdbid', $tmdbid)->first()) {
                return ['title' => $row->title, 'type' => 'movie'];
            }
        }

        // No type flag (or type-specific row missing) — try both tables.
        if ($row = TorrentMovie::where('tmdbid', $tmdbid)->first()) {
            return ['title' => $row->title, 'type' => 'movie'];
        }

        if ($row = TorrentSeries::where('tmdbid', $tmdbid)->first()) {
            return ['title' => $row->title, 'type' => 'tv'];
        }

        return ['title' => null, 'type' => null];
    }

    protected function hasSubscribeableId(?string $imdbid, ?string $tmdbid): bool
    {
        return ($imdbid !== null && $imdbid !== '')
            || ($tmdbid !== null && $tmdbid !== '');
    }
}