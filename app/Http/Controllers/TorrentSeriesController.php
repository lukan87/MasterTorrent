<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Models\TorrentSeries;
use App\Services\TorrentSubscriptionService;
use Illuminate\Support\Facades\Auth;
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

        $browser = app(\App\Services\LibraryBrowseService::class);
        $series = $browser->paginate(app(\App\Services\LibraryCatalogueService::class)->query('series'), 'tv');
        if ($browser->isPartial()) {
            return $browser->response('library.series.results', compact('series', 'query'));
        }

        $health = $browser->seederHealth();

        // ── Featured row (hero + cards) ──
        $featured = app(\App\Services\LibraryCatalogueService::class)->query('series')
            ->whereNotNull('backdrop_path')
            ->orderByDesc('created_at')
            
            ->get()
            ->map(function ($m) use ($health) {
                $m->seeders = $health->get((int) $m->tmdbid)?->max_seeders ?? 0;
                $m->backdrop = $m->backdrop_path;
                $m->poster    = $m->poster_path;
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
        if (request()->header('X-Library-Detail') === 'subscription' && request()->expectsJson()) {
            $libraryEntry = TorrentSeries::where('tmdbid', $tmdbid)->first();
            $torrents = Torrent::where('tmdbid', $tmdbid)->where('tmdb_type', 'tv')->limit(1)->get(['id', 'imdbid']);
            abort_unless($libraryEntry || $torrents->isNotEmpty() || \App\Models\Series::where('tmdb_id', $tmdbid)->exists(), 404);
            $service = app(TorrentSubscriptionService::class);
            $isSubscribed = Auth::check() && $service->isSubscribedByTmdb(Auth::user(), (string) $tmdbid);
            $subscribers = $service->subscribers($torrents->first()?->imdbid, (string) $tmdbid);
            return \App\Services\PageBrowse::json(['html' => view('library.series.subscription', compact('tmdbid', 'isSubscribed', 'subscribers', 'libraryEntry', 'torrents'))->render()]);
        }

        $torrents = Torrent::where('tmdbid', $tmdbid)
            ->where('tmdb_type', 'tv')
            ->orderByDesc('seeders')
            ->get();

        // Build the rich premium header payload (same as the torrent detail page).
        $firstTorrent = $torrents->first();
        $onlineMedia = \App\Models\Series::where('tmdb_id', $tmdbid)->first();
        $canWatchOnline = app(\App\Services\LibraryCatalogueService::class)->playable($onlineMedia)
            && (Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::USER;
        $libraryEntry = TorrentSeries::where('tmdbid', $tmdbid)->first();
        [$movie, $display] = app(\App\Services\LibraryCatalogueService::class)->metadata(
            'series', (int) $tmdbid, $libraryEntry, $onlineMedia, $firstTorrent
        );

       // Build "You Might Like" recommendations from TMDB,
// then check which recommendations exist in our online database.
        $recommendations = [];
        if (request()->header('X-Library-Detail') === 'recommendations' || request()->boolean('full_details')) {
            $recommendationIds = collect($movie['recommendations']['results'] ?? [])
                ->take(8)
                ->pluck('id')
                ->filter()
                ->values();

            // Find matching series already available in our database.
            $databaseSeries = \App\Models\Series::whereIn('tmdb_id', $recommendationIds)
                ->get()
                ->keyBy(fn ($series) => (int) $series->tmdb_id);

            $recommendations = collect($movie['recommendations']['results'] ?? [])
                ->take(8)
                ->map(function ($r) use ($databaseSeries) {

                    $databaseSeriesEntry = $databaseSeries->get((int) $r['id']);

                    return [
                        'id'       => $r['id'],
                        'title'    => $r['name'] ?? $r['title'] ?? null,

                        'poster'   => isset($r['poster_path'])
                            ? "https://image.tmdb.org/t/p/w342{$r['poster_path']}"
                            : null,

                        'rating'   => $r['vote_average'] ?? null,

                        'year'     => substr(
                            $r['first_air_date'] ?? $r['release_date'] ?? '',
                            0,
                            4
                        ),

                        // Database availability
                        'in_database' => app(\App\Services\LibraryCatalogueService::class)->playable($databaseSeriesEntry),

                        // Internal series page
                        'url' => route('library.series.show', [$r['id'], Str::slug($r['title'] ?? $r['name'] ?? '')]),
                    ];
                })
                ->all();

        }

        if (request()->header('X-Library-Detail') === 'recommendations' && request()->expectsJson()) {
            return \App\Services\PageBrowse::json(['html' => view('library.partials.recommendations', compact('recommendations'))->render()]);
        }

        if ($onlineMedia && !session()->has('catalogue_viewed_series_'.$onlineMedia->id)) {
            session(['catalogue_viewed_series_'.$onlineMedia->id => true]);
            $onlineMedia->recordView();
        }

        // Generate correct slug
        $correctSlug = Str::slug($display['title'] ?? 'series');

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

        return view('library.series.show', compact(
            'movie', 'torrents', 'tmdbid', 'isSubscribed',
            'subscribers', 'recommendations', 'display', 'libraryEntry', 'onlineMedia', 'canWatchOnline'
        ));
    }

    /**
     * JSON payload for the season modal — fetched on-demand when a user
     * clicks a season on the library series page.
     */
    public function season($tmdbid, $season)
    {
        abort_unless(ctype_digit((string) $tmdbid) && (int) $tmdbid > 0 && ctype_digit((string) $season), 404);
        $online = \App\Models\Series::where('tmdb_id', $tmdbid)->first();
        $playable = app(\App\Services\LibraryCatalogueService::class)->playable($online)
            && (Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::USER;
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
                    'play_url' => $playable && !empty($e['episode_number']) && !empty($e['air_date']) && $e['air_date'] <= now()->toDateString()
                        ? 'https://v2.vidsrc.me/embed/'.$online->imdb_id.'/'.(int) $season.'-'.(int) $e['episode_number']
                        : null,
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

    if (request()->expectsJson()) {
        return \App\Services\PageBrowse::json(['message' => $ok ? 'Subscribed.' : 'Already subscribed.']);
    }

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

    if (request()->expectsJson()) {
        return \App\Services\PageBrowse::json(['message' => 'Subscription removed.']);
    }

        return redirect()->route('library.series.show', [$tmdbid, $tmdb->slug])
            ->with('success', 'Subscription removed. You will no longer receive notifications for this series.');
    }
}