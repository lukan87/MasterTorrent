<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\TorrentMovie;
use App\Services\TorrentSubscriptionService;
use App\Services\TMDBService;
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

        // ── Seeder health map (one query, cached per request) ──
        $health = Torrent::query()
            ->where('tmdb_type', 'movie')
            ->whereNotNull('tmdbid')
            ->select('tmdbid', DB::raw('MAX(seeders) as max_seeders'))
            ->groupBy('tmdbid')
            ->get()
            ->keyBy(fn ($t) => (int) $t->tmdbid);

        // ── All library movies ──
        $movies = TorrentMovie::query()
            ->when($query, fn ($q) => $q->where('title', 'like', "%{$query}%"))
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        // Attach fields the Blade cards expect
        $movies->getCollection()->transform(function ($movie) use ($health) {
            $t = $health->get((int) $movie->tmdbid);
            $movie->poster      = $movie->poster_path;
            $movie->background  = $movie->backdrop_path;
            $movie->max_seeders = $t?->max_seeders ?? 0;
            return $movie;
        });

        // ── Featured row (hero + cards) ──
        $featured = TorrentMovie::query()
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

        return view('library.movies.index', compact('movies', 'query', 'featured'));
    }

    /**
     * Show a single library movie.
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
                'movie',
                $firstTorrent->imdbid
            )
            : null;

        // Try the local library record first; fall back to TMDB API.
        $libraryEntry = TorrentMovie::where('tmdbid', $tmdbid)->first();

        $movie = cache()->remember("tmdb_movie_v2_{$tmdbid}", 86400, function () use ($tmdbid) {
            return Http::get("https://api.themoviedb.org/3/movie/{$tmdbid}", [
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
                'title'  => $r['title'] ?? $r['name'] ?? null,
                'poster' => isset($r['poster_path'])
                    ? "https://image.tmdb.org/t/p/w185{$r['poster_path']}"
                    : null,
                'rating' => $r['vote_average'] ?? null,
                'year'   => substr($r['release_date'] ?? $r['first_air_date'] ?? '', 0, 4),
            ])
            ->all();

        // Generate correct slug
        $correctSlug = Str::slug($movie['title'] ?? 'movie');

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

        // "Watch online" link — only when the movie exists in the online catalogue
        // (movies table). The movies.show route is keyed on the DB primary id + slug.
        $watchMovie = \App\Models\Movie::where('tmdb_id', $tmdbid)->first();
        $watchUrl = $watchMovie
            ? route('movies.show', [$watchMovie->id, $watchMovie->slug])
            : null;

        return view('library.movies.show', compact(
            'movie', 'torrents', 'tmdbid', 'isSubscribed',
            'subscribers', 'recommendations', 'display', 'libraryEntry', 'watchUrl'
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

    return redirect()->route('library.movies.show', [$tmdbid, $tmdb->slug])
        ->with('success', 'Subscription removed. You will no longer receive notifications for this movie.');
}


}