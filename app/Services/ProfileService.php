<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\ForumPost;
use App\Models\History;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\TorrentMovie;
use App\Models\TorrentSeries;
use App\Models\TorrentSubscription;
use App\Models\TorrentThank;
use App\Models\User;
use Closure;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileService
{
    public static function invalidate(int $userId): void
    {
        Cache::forget('profile_revision_v1_'.$userId);
    }

    private function remember(int $userId, string $key, Closure $callback): mixed
    {
        $revision = Cache::rememberForever('profile_revision_v1_'.$userId, fn () => (string) Str::uuid());

        return Cache::remember("profile_v1:{$userId}:{$revision}:{$key}",
            (int) config('cache.profile_duration', 30), $callback);
    }

    public function statistics(int $userId): array
    {
        return $this->remember($userId, 'statistics', function () use ($userId) {
            $community = DB::query()
                ->selectSub(Comment::where('user_id', $userId)->selectRaw('COUNT(*)'), 'commentCount')
                ->selectSub(TorrentThank::where('user_id', $userId)->selectRaw('COUNT(*)'), 'thanksCount')
                ->selectSub(ForumPost::where('user_id', $userId)->selectRaw('COUNT(*)'), 'forumPostCount')
                ->selectSub(Torrent::where('owner', $userId)->selectRaw('COUNT(*)'), 'torrents_count')
                ->selectSub(User::withTrashed()->where('invited_by', $userId)->selectRaw('COUNT(*)'), 'invitees_count')
                ->first();
            // Keep the original definition: all seeder peers, including inactive peers.
            $peers = Peer::where('peers.user_id', $userId)->where('peers.seeder', 1)
                ->leftJoin('torrents', 'peers.torrent_id', '=', 'torrents.id')
                ->selectRaw('COUNT(peers.id) AS active_seeds, COALESCE(SUM(torrents.size), 0) AS seed_size')->first();
            $history = History::where('user_id', $userId)
                ->selectRaw('COALESCE(SUM(seedtime), 0) AS seed_time, COUNT(seedtime) AS torrent_count,
                    COALESCE(SUM(LEAST(1, COALESCE(seedtime, 0) / 43200.000000000000)), 0) AS total_health')->first();
            $activeSeeds = (int) $peers->active_seeds;
            $totalSeedTime = (int) $history->seed_time;

            return [
                'commentCount' => (int) $community->commentCount,
                'thanksCount' => (int) $community->thanksCount,
                'forumPostCount' => (int) $community->forumPostCount,
                'torrents_count' => (int) $community->torrents_count,
                'invitees_count' => (int) $community->invitees_count,
                'activeSeeds' => $activeSeeds,
                'totalSeedSize' => (int) $peers->seed_size,
                'totalSeedTime' => $totalSeedTime,
                'avgSeedTime' => $activeSeeds > 0 ? $totalSeedTime / $activeSeeds : 0,
                'seedingHealth' => $history->torrent_count > 0
                    ? round($history->total_health / $history->torrent_count * 100) : 0,
            ];
        });
    }

    public function achievementProgress(User $user): array
    {
        return $this->remember($user->id, 'achievements',
            fn () => app(AchievementService::class)->progress($user));
    }

    public function inviteTree(User $user, int $page): LengthAwarePaginator
    {
        $members = $this->remember($user->id, 'invitees:'.$page, fn () => $user->invitees()
            ->orderBy('id')->forPage($page, 20)->get(['id', 'name', 'invited_by', 'deleted_at']));

        return new LengthAwarePaginator($members, $user->invitees_count, 20, $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'invitees_page']);
    }

    /** Called only after the controller authorizes owner/staff visibility. */
    public function subscribedTorrents(int $userId): Collection
    {
        $subscriptions = TorrentSubscription::with('sourceTorrent')->where('user_id', $userId)->orderByDesc('id')->get();
        if ($subscriptions->isEmpty()) {
            return collect();
        }

        // Match each release once per subscription, even when both identifiers match.
        $stats = DB::table('torrent_subscriptions as subscriptions')
            ->where('subscriptions.user_id', $userId)
            ->leftJoin('torrents as releases', function (JoinClause $join) {
                $join->whereNull('releases.deleted_at')->where(function (JoinClause $match) {
                    $match->where(function (JoinClause $imdb) {
                        $imdb->on('releases.imdbid', '=', 'subscriptions.imdbid')->where('subscriptions.imdbid', '!=', '');
                    })->orWhere(function (JoinClause $tmdb) {
                        $tmdb->on('releases.tmdbid', '=', 'subscriptions.tmdbid')->where('subscriptions.tmdbid', '!=', '');
                    });
                });
            })
            ->selectRaw('subscriptions.id, MAX(releases.id) AS latest_id,
                COALESCE(SUM(releases.seeders), 0) AS seeders,
                COALESCE(SUM(releases.leechers), 0) AS leechers,
                COALESCE(SUM(releases.times_completed), 0) AS times_completed')
            ->groupBy('subscriptions.id')->get()->keyBy('id');
        $fallbackIds = $subscriptions->filter(fn ($subscription) => ! $subscription->sourceTorrent)
            ->map(fn ($subscription) => $stats->get($subscription->id)?->latest_id)->filter()->unique();
        $fallbacks = $fallbackIds->isEmpty() ? collect() : Torrent::whereIn('id', $fallbackIds)->get()->keyBy('id');
        $selected = $subscriptions->map(function ($subscription) use ($fallbacks, $stats) {
            $torrent = $subscription->sourceTorrent ?? $fallbacks->get($stats->get($subscription->id)?->latest_id);

            return ['subscription' => $subscription, 'torrent' => $torrent ? clone $torrent : null];
        });
        $ids = $selected->pluck('torrent.tmdbid')->filter()->unique();
        $movies = $ids->isEmpty() ? collect() : TorrentMovie::whereIn('tmdbid', $ids)->get()->keyBy('tmdbid');
        $series = $ids->isEmpty() ? collect() : TorrentSeries::whereIn('tmdbid', $ids)->get()->keyBy('tmdbid');

        return $selected->map(function ($item) use ($stats, $movies, $series) {
            $subscription = $item['subscription'];
            $torrent = $item['torrent'];
            if (! $torrent) {
                return null;
            }
            $type = $subscription->type === 'tv' || strtolower((string) $torrent->tmdb_type) === 'tv' ? 'tv' : 'movie';
            if (! empty($subscription->title)) {
                $torrent->name = $subscription->title;
            }
            if (! empty($torrent->tmdbid)) {
                $meta = ($type === 'tv' ? $series : $movies)->get($torrent->tmdbid);
                if ($meta?->poster_path) {
                    $torrent->poster = 'https://image.tmdb.org/t/p/w92/'.$meta->poster_path;
                }
                $torrent->library_type = $type === 'tv' ? 'series' : 'movies';
                $torrent->library_slug = $meta?->slug ?? Str::slug((string) $torrent->name);
            }
            $totals = $stats->get($subscription->id);
            $torrent->seeders = (int) $totals->seeders;
            $torrent->leechers = (int) $totals->leechers;
            $torrent->times_completed = (int) $totals->times_completed;

            return $torrent;
        })->filter()->unique('id')->values();
    }
}
