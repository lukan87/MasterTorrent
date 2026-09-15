<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Models\TorrentSeries;
use App\Services\TorrentSubscriptionService;
use App\Services\TMDBService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TorrentSeriesController extends Controller
{
    /**
     * Series library — shows every title in the torrent_series table,
     * not just those with active seeders.
     */
    public function index()
    {
        $query = request('q');

        // ── Seeder health map (one query, cached per request) ──
        $health = Torrent::query()
            ->where('tmdb_type', 'tv')
            ->whereNotNull('tmdbid')
            ->select('tmdbid', DB::raw('MAX(seeders) as max_seeders'))
            ->groupBy('tmdbid')
            ->get()
            ->keyBy(fn ($t) => (int) $t->tmdbid);

        // ── All library series ──
        $series = TorrentSeries::query()
            ->when($query, fn ($q) => $q->where('title', 'like', "%{$query}%"))
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        // Attach fields the Blade cards expect
        $series->getCollection()->transform(function ($item) use ($health) {
            $t = $health->get((int) $item->tmdbid);
            $item->poster      = $item->poster_path;
            $item->background  = $item->backdrop_path;
            $item->max_seeders = $t?->max_seeders ?? 0;
            return $item;
        });

        // ── Featured row (hero + cards) ──
        $featured = TorrentSeries::query()
            ->whereNotNull('backdrop_path')
            ->orderByDesc('created_at')
            ->take(25)
            ->get()
            ->map(function ($m) use ($health) {
                $m->seeders = $health->get((int) $m->tmdbid)?->max_seeders ?? 0;
                $m->backdrop = $m->backdrop_path;
                return $m;
            })
            ->sortByDesc('seeders')
            ->values();

        return view('library.series.index', compact('series', 'query', 'featured'));
    }

    /**
     * Show a single library series.
     * Works even when no torrent has been uploaded for the title yet.
     */
    public function show($tmdbid, $slug = null)
    {
        $torrents = Torrent::where('tmdbid', $tmdbid)
            ->orderByDesc('seeders')
            ->get();

        // Build the rich premium header payload (same as the torrent detail page).
        $firstTorrent = $torrents->first();
        $display = $firstTorrent
            ? app(TMDBService::class)->getDisplayPayload(
                (int) $tmdbid,
                'tv',
                $firstTorrent->imdbid
            )
            : null;

        // Local library record (used for the request prompt when no torrents exist)
        $libraryEntry = TorrentSeries::where('tmdbid', $tmdbid)->first();

        $movie = cache()->remember("tmdb_series_v2_{$tmdbid}", 86400, function () use ($tmdbid) {
            return Http::get("https://api.themoviedb.org/3/tv/{$tmdbid}", [
                'api_key' => config('services.tmdb.key'),
                'append_to_response' => 'recommendations',
                'language' => 'en-US',
            ])->json();
        });

        // Build "You Might Like" recommendations from TMDB
        $recommendations = collect($movie['recommendations']['results'] ?? [])
            ->take(8)
            ->map(fn ($r) => [
                'id'     => $r['id'],
                'title'  => $r['name'] ?? $r['title'] ?? null,
                'poster' => isset($r['poster_path'])
                    ? "https://image.tmdb.org/t/p/w185{$r['poster_path']}"
                    : null,
                'rating' => $r['vote_average'] ?? null,
                'year'   => substr($r['first_air_date'] ?? $r['release_date'] ?? '', 0, 4),
            ])
            ->all();

        // Generate correct slug
        $correctSlug = Str::slug($movie['name'] ?? 'series');

        // Optional: redirect if slug is wrong
        if ($slug !== $correctSlug) {
            return redirect()->route('library.series.show', [
                'tmdbid' => $tmdbid,
                'slug' => $correctSlug,
            ]);
        }

        // Subscription state (library subscribe button uses the TMDB name)
        $isSubscribed = false;
        if (Auth::check()) {
            $isSubscribed = app(TorrentSubscriptionService::class)
                ->isSubscribedByTmdb(Auth::user(), (string) $tmdbid);
        }

        // Subscribers for this title (count + names shown beside the button)
        $subscribers = app(TorrentSubscriptionService::class)
            ->subscribers($firstTorrent?->imdbid, (string) $tmdbid);

        // "Watch online" link — only when the series exists in the online catalogue
        // (series table). The series.show route is keyed on the DB primary id + slug.
        $watchSeries = \App\Models\Series::where('tmdb_id', $tmdbid)->first();
        $watchUrl = $watchSeries
            ? route('series.show', [$watchSeries->id, $watchSeries->slug])
            : null;

        return view('library.series.show', compact(
            'movie', 'torrents', 'tmdbid', 'isSubscribed',
            'subscribers', 'recommendations', 'display', 'libraryEntry', 'watchUrl'
        ));
    }

    /**
     * JSON payload for the season modal — fetched on-demand when a user
     * clicks a season on the library series page.
     */
    public function season($tmdbid, $season)
    {
        $seasonData = cache()->remember(
            "tmdb_series_season_{$tmdbid}_v2_{$season}",
            86400,
            function () use ($tmdbid, $season) {
                return Http::get(
                    "https://api.themoviedb.org/3/tv/{$tmdbid}/season/{$season}",
                    [
                        'api_key'             => config('services.tmdb.key'),
                        'language'            => 'en-US',
                        'append_to_response'  => 'credits',
                    ]
                )->json();
            }
        );

        $payload = [
            'name'        => $seasonData['name'] ?? ('Season ' . $season),
            'season_number' => $seasonData['season_number'] ?? (int) $season,
            'overview'    => $seasonData['overview'] ?? null,
            'air_date'    => $seasonData['air_date'] ?? null,
            'poster'      => ! empty($seasonData['poster_path'])
                ? "https://image.tmdb.org/t/p/w342" . $seasonData['poster_path']
                : null,
            'episodes'    => collect($seasonData['episodes'] ?? [])
                ->map(fn ($e) => [
                    'episode_number' => $e['episode_number'] ?? null,
                    'name'           => $e['name'] ?? null,
                    'overview'       => $e['overview'] ?? null,
                    'air_date'       => $e['air_date'] ?? null,
                    'rating'         => isset($e['vote_average']) ? round($e['vote_average'], 1) : null,
                    'still'          => ! empty($e['still_path'])
                        ? "https://image.tmdb.org/t/p/w780" . $e['still_path']
                        : null,
                ])
                ->all(),
            'cast'        => collect($seasonData['credits']['cast'] ?? [])
                ->take(12)
                ->map(fn ($c) => [
                    'name'      => $c['name'] ?? null,
                    'character' => $c['character'] ?? null,
                    'photo'     => ! empty($c['profile_path'])
                        ? "https://image.tmdb.org/t/p/w185" . $c['profile_path']
                        : null,
                ])
                ->all(),
        ];

        return response()->json($payload);
    }

    public function subscribe($tmdbid, TorrentSubscriptionService $service)
    {
        $tmdb = TorrentSeries::where('tmdbid', $tmdbid)->first();
        if (! $tmdb) {
            abort(404);
        }

        $user = Auth::user();

        $source = Torrent::where('tmdbid', $tmdbid)
            ->where('tmdb_type', 'tv')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        $ok = $service->subscribeToTitle(
            $user,
            (string) $tmdbid,
            $tmdb->title,
            'tv',
            $source ? (int) $source->id : null,
            $source ? $source->imdbid : null
        );

        if (! $ok) {
            return redirect()->route('library.series.show', [$tmdbid, $tmdb->slug])
                ->with('info', 'You are already subscribed to this series.');
        }

        return redirect()->route('library.series.show', [$tmdbid, $tmdb->slug])
            ->with('success', "Subscribed to \"{$tmdb->title}\"! You will be notified whenever a new episode/season is uploaded.");
    }

    public function unsubscribe($tmdbid, TorrentSubscriptionService $service)
    {
        $tmdb = TorrentSeries::where('tmdbid', $tmdbid)->first();
        if (! $tmdb) {
            abort(404);
        }

        $service->unsubscribeByTmdb(Auth::user(), (string) $tmdbid);

        return redirect()->route('library.series.show', [$tmdbid, $tmdb->slug])
            ->with('success', 'Subscription removed. You will no longer receive notifications for this series.');
    }
}