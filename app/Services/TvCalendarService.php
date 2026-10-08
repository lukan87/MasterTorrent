<?php

namespace App\Services;

use App\Models\Series;
use App\Models\Torrent;
use App\Models\TorrentSeries;
use App\Models\TorrentSubscription;
use App\Models\TvShowFollow;
use Illuminate\Support\Collection;

class TvCalendarService
{
    public function decorate(array $episodes, int $userId): array
    {
        $ids = collect($episodes)->pluck('imdbid')->filter()->unique()->values();
        $series = $ids->isEmpty() ? collect() : Series::whereIn('imdb_id', $ids)->get()->keyBy('imdb_id');
        $tmdbIds = $series->pluck('tmdb_id')->filter()->unique();
        $torrents = $ids->isEmpty() ? collect() : Torrent::query()
            ->where(function ($q) use ($ids, $tmdbIds) {
                $q->whereIn('imdbid', $ids);
                if ($tmdbIds->isNotEmpty()) {
                    $q->orWhere(fn ($tv) => $tv->where('tmdb_type', 'tv')->whereIn('tmdbid', $tmdbIds));
                }
            })
            ->where(fn ($q) => $q->whereNull('tmdb_type')->orWhere('tmdb_type', 'tv')->orWhere('tmdb_type', ''))
            ->select(['id', 'slug', 'name', 'imdbid', 'tmdbid', 'seeders', 'times_completed'])->orderByDesc('seeders')->get();
        $byImdb = $torrents->groupBy('imdbid');
        $byTmdb = $torrents->groupBy('tmdbid');
        $follows = TvShowFollow::where('user_id', $userId)->get()->keyBy('tvmaze_id');
        $subscriptions = TorrentSubscription::where('user_id', $userId)->get();
        foreach ($episodes as &$episode) {
            $imdb = $episode['imdbid'];
            $local = $imdb ? $series->get($imdb) : null;
            $matches = $imdb ? $byImdb->get($imdb, collect()) : collect();
            $tmdb = $local?->tmdb_id ?: $matches->first()?->tmdbid;
            if ($tmdb) {
                $matches = $matches->merge($byTmdb->get($tmdb, collect()))->unique('id');
            }
            $follow = $follows->get($episode['show_id']);
            $legacy = $this->subscribed($subscriptions, $imdb, $tmdb ? (string) $tmdb : null);
            $episode['followed'] = $follow !== null || $legacy;
            $episode['calendar_follow'] = $follow !== null;
            $episode['notify_upload'] = ($follow?->notify_upload ?? false) || $legacy;
            $episode['legacy_notify'] = $legacy;
            $episode['can_notify'] = $imdb !== null || $tmdb !== null;
            $episode['upload_count'] = $matches->count();
            $episode['popularity'] = $matches->sum('times_completed');
            $exact = $matches->first(fn ($torrent) => $this->matchesEpisode($torrent->name, $episode));
            $episode['available'] = $exact !== null;
            $episode['torrent_url'] = $exact ? route('torrents.show', ['id' => $exact->id, 'slug' => $exact->slug]) : null;
            $episode['series_url'] = $tmdb ? route('library.series.show', ['tmdbid' => $tmdb])
                : ($matches->isNotEmpty() ? route('torrents.show', ['id' => $matches->first()->id, 'slug' => $matches->first()->slug]) : null);
            $episode['online_url'] = $local ? route('series.show', ['id' => $local->id, 'slug' => $local->slug]) : null;
        }
        unset($episode);

        return $episodes;
    }

    /** All followed series, including title subscriptions that are not airing in this date range. */
    public function watchlist(int $userId): Collection
    {
        $entries = TvShowFollow::where('user_id', $userId)->get()->map(fn ($follow) => (object) [
            'tvmaze_id' => $follow->tvmaze_id, 'title' => $follow->title,
            'imdbid' => $follow->imdbid, 'tmdbid' => $follow->tmdbid,
            'notify_upload' => $follow->notify_upload, 'legacy_notify' => false,
            'url' => 'https://www.tvmaze.com/shows/'.$follow->tvmaze_id, 'manage_url' => null,
        ]);
        $subscriptions = TorrentSubscription::with('sourceTorrent')->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('type')->orWhere('type', 'tv')->orWhere('type', ''))->get();
        if ($subscriptions->isEmpty()) {
            return $entries->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        $imdbIds = $subscriptions->pluck('imdbid')->merge($entries->pluck('imdbid'))->filter()->unique();
        $tmdbIds = $subscriptions->pluck('tmdbid')->merge($subscriptions->pluck('sourceTorrent.tmdbid'))->filter()->unique();
        $local = Series::where(function ($q) use ($imdbIds, $tmdbIds) {
            $q->whereIn('imdb_id', $imdbIds)->orWhereIn('tmdb_id', $tmdbIds);
        })->get();
        $byImdb = $local->keyBy('imdb_id');
        $byTmdb = $local->keyBy('tmdb_id');
        $library = TorrentSeries::whereIn('tmdbid', $tmdbIds)->get()->keyBy('tmdbid');
        $releases = Torrent::where('tmdb_type', 'tv')->where(function ($q) use ($imdbIds, $tmdbIds) {
            $q->whereIn('imdbid', $imdbIds)->orWhereIn('tmdbid', $tmdbIds);
        })->get();
        foreach ($subscriptions as $subscription) {
            $source = $subscription->sourceTorrent;
            if ($subscription->type !== 'tv' && $source?->tmdb_type === 'movie') {
                continue;
            }
            $release = $source?->tmdb_type === 'tv' ? $source : $releases->first(fn ($torrent) => ($subscription->imdbid && $torrent->imdbid === $subscription->imdbid)
                || ($subscription->tmdbid && (string) $torrent->tmdbid === (string) $subscription->tmdbid));
            $imdb = $subscription->imdbid ?: $release?->imdbid;
            $tmdb = $subscription->tmdbid ?: $release?->tmdbid;
            $series = ($imdb ? $byImdb->get($imdb) : null) ?? ($tmdb ? $byTmdb->get($tmdb) : null);
            $meta = $tmdb ? $library->get($tmdb) : null;
            if ($subscription->type !== 'tv' && ! $release && ! $series && ! $meta) {
                continue;
            }
            $tmdb = $tmdb ?: $series?->tmdb_id;
            $imdb = $imdb ?: $series?->imdb_id;
            $url = $tmdb ? route('library.series.show', ['tmdbid' => $tmdb])
                : ($release ? route('torrents.show', ['id' => $release->id, 'slug' => $release->slug])
                    : route('profile.show', ['id' => $userId]));
            $existing = $entries->first(fn ($entry) => ($imdb && $entry->imdbid === $imdb)
                || ($tmdb && (string) $entry->tmdbid === (string) $tmdb)
                || ($entry->imdbid && $series && $entry->imdbid === $series->imdb_id));
            if ($existing) {
                $existing->legacy_notify = true;
                $existing->notify_upload = true;
                $existing->manage_url = $url;

                continue;
            }
            $entries->push((object) [
                'tvmaze_id' => null, 'title' => $subscription->title ?: $series?->name ?: $meta?->title ?: $release?->name ?: 'Followed series '.($imdb ?: $tmdb),
                'imdbid' => $imdb, 'tmdbid' => $tmdb, 'notify_upload' => true,
                'legacy_notify' => true, 'url' => $url, 'manage_url' => $url,
            ]);
        }

        return $entries->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    private function subscribed(Collection $subscriptions, ?string $imdb, ?string $tmdb): bool
    {
        return $subscriptions->contains(fn ($sub) => ($imdb && $sub->imdbid === $imdb)
            || ($tmdb && (string) $sub->tmdbid === $tmdb && $sub->type === 'tv'));
    }

    /** Deliberately conservative: a different episode or season pack is not an exact episode upload. */
    public function matchesEpisode(string $name, array $episode): bool
    {
        if ($episode['season'] === null || $episode['number'] === null) {
            return false;
        }
        $season = (int) $episode['season'];
        $number = (int) $episode['number'];
        if (preg_match_all('/(?<![a-z0-9])S(\d{1,2})[ ._-]*E(\d{1,3})(?!\d)/i', $name, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ((int) $match[1] === $season && (int) $match[2] === $number) {
                    return true;
                }
            }
        }

        return (bool) preg_match('/(?<![a-z0-9])0*'.$season.'x0*'.$number.'(?!\d)/i', $name);
    }
}
