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
     * Series library - every title that has at least one live TV torrent.
     * Ordered by most recent upload so the newest addition is shown first.
     */
    public function index()
    {
        $query = request('q');

        $series = Torrent::query()
            ->select(
                'torrents.tmdbid',
                DB::raw('MAX(torrents.name) as name'),
                DB::raw('MAX(torrents.created_at) as latest_created_at'),
                DB::raw('MAX(torrents.seeders) as max_seeders')
            )
            ->join('torrent_series', 'torrent_series.tmdbid', '=', 'torrents.tmdbid')
            ->whereNotNull('torrents.tmdbid')
            ->where('torrents.tmdb_type', 'tv')

            // 🔍 REAL SEARCH (TMDB title)
            ->when($query, function ($q) use ($query) {
                $q->where('torrent_series.title', 'like', "%{$query}%");
            })

            ->groupBy('torrents.tmdbid')
            ->havingRaw('MAX(torrents.seeders) > 0')
            ->orderByDesc('latest_created_at')
            ->paginate(24)
            ->withQueryString();

        // ✅ Attach cached DB data (NO API calls here)
        $series->getCollection()->transform(function ($item) {

            $tmdb = TorrentSeries::where('tmdbid', $item->tmdbid)->first();

            if ($tmdb) {
                $item->title = $tmdb->title;
                $item->poster = $tmdb->poster_path;
                $item->background = $tmdb->backdrop_path;
                $item->slug = $tmdb->slug;
                $item->rating = $tmdb->rating;
                $item->year = $tmdb->year;
            } else {
                // ⚠️ fallback (rare case)
                $item->title = $item->name;
                $item->poster = null;
                $item->background = null;
                $item->slug = Str::slug($item->name);
                $item->rating = null;
                $item->year = null;
            }

            return $item;
        });

        $featured = Torrent::select(
            'torrents.tmdbid',
            DB::raw('MAX(torrents.seeders) as seeders')
        )
        ->join('torrent_series', 'torrent_series.tmdbid', '=', 'torrents.tmdbid')
        ->where('torrents.tmdb_type', 'tv')
        ->groupBy('torrents.tmdbid')
        ->havingRaw('MAX(torrents.seeders) > 0')
        ->orderByDesc('seeders')
        ->take(25)
        ->get()
        ->map(function ($item) {
            $tmdb = TorrentSeries::where('tmdbid', $item->tmdbid)->first();

            $item->title = $tmdb->title ?? '';
            $item->backdrop = $tmdb->backdrop_path;
            $item->poster = $tmdb->poster_path;
            $item->slug = $tmdb->slug;
            $item->rating = $tmdb->rating;
            $item->year = $tmdb->year;

            return $item;
        });

        return view('library.series.index', compact('series', 'query', 'featured'));
    }

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

        return view('library.series.show', compact('movie', 'torrents', 'tmdbid', 'isSubscribed', 'subscribers', 'recommendations', 'display'));
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