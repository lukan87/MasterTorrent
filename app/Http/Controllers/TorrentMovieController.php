<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\TorrentMovie;
use App\Services\TorrentSubscriptionService;
use Illuminate\Support\Facades\Auth;

class TorrentMovieController extends Controller
{

    /**
     * Library index — shows EVERY movie in the torrent_movies table,
     * not just those with active seeders.
     */
    public function index()
    {
        $query = request('q');

        $browser = app(\App\Services\LibraryBrowseService::class);
        $movies = $browser->paginate(app(\App\Services\LibraryCatalogueService::class)->query('movies'), 'movie');
        if ($browser->isPartial()) {
            return $browser->response('library.movies.results', compact('movies', 'query'));
        }

        $health = $browser->seederHealth();

        // ── Featured row (hero + cards) ──
        $featured = app(\App\Services\LibraryCatalogueService::class)->query('movies')
    ->whereNotNull('backdrop_path')
    ->orderByDesc('created_at')
    
    ->get()
    ->map(function ($m) use ($health) {
        $m->seeders = $health->get((int) $m->tmdbid)?->max_seeders ?? 0;
        $m->backdrop = $m->backdrop_path;
        return $m;
    })
    ->sortByDesc('seeders')
    ->values();

        return view('library.movies.index', compact('movies', 'query', 'featured'));
    }

    /**
     * Show a single library movie.
     * Works even when no torrent has been uploaded for the title yet.
     */
    public function show($tmdbid, $slug = null)
    {
        if (request()->header('X-Library-Detail') === 'subscription' && request()->expectsJson()) {
            $libraryEntry = TorrentMovie::where('tmdbid', $tmdbid)->first();
            $torrents = Torrent::where('tmdbid', $tmdbid)->where('tmdb_type', 'movie')->limit(1)->get(['id', 'imdbid']);
            abort_unless($libraryEntry || $torrents->isNotEmpty() || \App\Models\Movie::where('tmdb_id', $tmdbid)->exists(), 404);
            $service = app(TorrentSubscriptionService::class);
            $isSubscribed = Auth::check() && $service->isSubscribedByTmdb(Auth::user(), (string) $tmdbid);
            $subscribers = $service->subscribers($torrents->first()?->imdbid, (string) $tmdbid);
            return \App\Services\PageBrowse::json(['html' => view('library.movies.subscription', compact('tmdbid', 'isSubscribed', 'subscribers', 'libraryEntry', 'torrents'))->render()]);
        }

        $torrents = Torrent::where('tmdbid', $tmdbid)
            ->where('tmdb_type', 'movie')
            ->orderByDesc('seeders')
            ->get();

        // Build the rich premium header payload (same as the torrent detail page).
        $firstTorrent = $torrents->first();
        $onlineMedia = \App\Models\Movie::where('tmdb_id', $tmdbid)->first();
        $canWatchOnline = app(\App\Services\LibraryCatalogueService::class)->playable($onlineMedia)
            && (Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::USER;
        $libraryEntry = TorrentMovie::where('tmdbid', $tmdbid)->first();
        [$movie, $display] = app(\App\Services\LibraryCatalogueService::class)->metadata(
            'movies', (int) $tmdbid, $libraryEntry, $onlineMedia, $firstTorrent
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

            // Find matching movies already available in our database.
            $databaseMovies = \App\Models\Movie::whereIn('tmdb_id', $recommendationIds)
                ->get()
                ->keyBy(fn ($movie) => (int) $movie->tmdb_id);

            $recommendations = collect($movie['recommendations']['results'] ?? [])
                ->take(8)
                ->map(function ($r) use ($databaseMovies) {

                    $databaseMovie = $databaseMovies->get((int) $r['id']);

                    return [
                        'id'    => $r['id'],

                        'title' => $r['title'] ?? $r['name'] ?? null,

                        'poster' => isset($r['poster_path'])
                            ? "https://image.tmdb.org/t/p/w342{$r['poster_path']}"
                            : null,

                        'rating' => $r['vote_average'] ?? null,

                        'year' => substr(
                            $r['release_date'] ?? $r['first_air_date'] ?? '',
                            0,
                            4
                        ),

                        // Is this movie available on our website?
                        'in_database' => app(\App\Services\LibraryCatalogueService::class)->playable($databaseMovie),

                        // Our internal movie page
                        'url' => route('library.movies.show', [$r['id'], Str::slug($r['title'] ?? $r['name'] ?? '')]),
                    ];
                })
                ->all();

        }

        if (request()->header('X-Library-Detail') === 'recommendations' && request()->expectsJson()) {
            return \App\Services\PageBrowse::json(['html' => view('library.partials.recommendations', compact('recommendations'))->render()]);
        }

        if ($onlineMedia && !session()->has('catalogue_viewed_movies_'.$onlineMedia->id)) {
            session(['catalogue_viewed_movies_'.$onlineMedia->id => true]);
            $onlineMedia->recordView();
        }

        // Generate correct slug
        $correctSlug = Str::slug($display['title'] ?? 'movie');

        // Optional: redirect if slug is wrong
        if ($slug !== $correctSlug) {
            return redirect()->route('library.movies.show', [
                'tmdbid' => $tmdbid,
                'slug' => $correctSlug
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

        return view('library.movies.show', compact(
            'movie', 'torrents', 'tmdbid', 'isSubscribed',
            'subscribers', 'recommendations', 'display', 'libraryEntry', 'onlineMedia', 'canWatchOnline'
        ));
    }

public function subscribe($tmdbid, TorrentSubscriptionService $service)
{
    $tmdb = TorrentMovie::where('tmdbid', $tmdbid)->first();
    if (! $tmdb) {
        abort(404);
    }

    $user = Auth::user();

    $source = Torrent::where('tmdbid', $tmdbid)
        ->where('tmdb_type', 'movie')
        ->whereNull('deleted_at')
        ->orderByDesc('id')
        ->first();

    $ok = $service->subscribeToTitle(
        $user,
        (string) $tmdbid,
        $tmdb->title,
        'movie',
        $source ? (int) $source->id : null,
        $source ? $source->imdbid : null
    );

    if (request()->expectsJson()) {
        return \App\Services\PageBrowse::json(['message' => $ok ? 'Subscribed.' : 'Already subscribed.']);
    }

    if (! $ok) {
        return redirect()->route('library.movies.show', [$tmdbid, $tmdb->slug])
            ->with('info', 'You are already subscribed to this movie.');
    }

    return redirect()->route('library.movies.show', [$tmdbid, $tmdb->slug])
        ->with('success', "Subscribed to \"{$tmdb->title}\"! You will be notified whenever a new version is uploaded.");
}

public function unsubscribe($tmdbid, TorrentSubscriptionService $service)
{
    $tmdb = TorrentMovie::where('tmdbid', $tmdbid)->first();
    if (! $tmdb) {
        abort(404);
    }

    $service->unsubscribeByTmdb(Auth::user(), (string) $tmdbid);

    if (request()->expectsJson()) {
        return \App\Services\PageBrowse::json(['message' => 'Subscription removed.']);
    }

    return redirect()->route('library.movies.show', [$tmdbid, $tmdb->slug])
        ->with('success', 'Subscription removed. You will no longer receive notifications for this movie.');
}


}