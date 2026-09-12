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

public function index()
{
    $query = request('q');

    $movies = Torrent::query()
        ->select(
            'torrents.tmdbid',
            DB::raw('MAX(torrents.name) as name'),
            DB::raw('MAX(torrents.created_at) as latest_created_at'),
            DB::raw('MAX(torrents.seeders) as max_seeders')
        )
        ->join('torrent_movies', 'torrent_movies.tmdbid', '=', 'torrents.tmdbid')
        ->whereNotNull('torrents.tmdbid')
        ->where('torrents.tmdb_type', 'movie')

        // 🔍 REAL SEARCH (TMDB title)
        ->when($query, function ($q) use ($query) {
            $q->where('torrent_movies.title', 'like', "%{$query}%");
        })

        ->groupBy('torrents.tmdbid')
        ->havingRaw('MAX(torrents.seeders) > 0')
        ->orderByDesc('latest_created_at')
        ->paginate(24)
        ->withQueryString();

    // ✅ Attach cached DB data (NO API calls here)
    $movies->getCollection()->transform(function ($movie) {

        $tmdb = TorrentMovie::where('tmdbid', $movie->tmdbid)->first();

        if ($tmdb) {
            $movie->title = $tmdb->title;
            $movie->poster = $tmdb->poster_path;
            $movie->background = $tmdb->backdrop_path;
            $movie->slug = $tmdb->slug;
            $movie->rating = $tmdb->rating;
            $movie->year = $tmdb->year;
        } else {
            // ⚠️ fallback (rare case)
            $movie->title = $movie->name;
            $movie->poster = null;
            $movie->background = null;
            $movie->slug = \Str::slug($movie->name);
            $movie->rating = null;
            $movie->year = null;
        }

        return $movie;
    });

    $featured = Torrent::select(
        'torrents.tmdbid',
        DB::raw('MAX(torrents.seeders) as seeders')
    )
    ->join('torrent_movies', 'torrent_movies.tmdbid', '=', 'torrents.tmdbid')
    ->where('torrents.tmdb_type', 'movie')
    ->groupBy('torrents.tmdbid')
    ->havingRaw('MAX(torrents.seeders) > 0')
    ->orderByDesc('seeders')
    ->take(25)
    ->get()
    ->map(function ($movie) {
        $tmdb = TorrentMovie::where('tmdbid', $movie->tmdbid)->first();

        $movie->title = $tmdb->title ?? '';
        $movie->backdrop = $tmdb->backdrop_path;
        $movie->poster = $tmdb->poster_path;
        $movie->slug = $tmdb->slug;
        $movie->rating = $tmdb->rating;
        $movie->year = $tmdb->year;

        return $movie;
    });

   return view('library.movies.index', compact('movies', 'query', 'featured'));
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
            'movie',
            $firstTorrent->imdbid
        )
        : null;

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

    return view('library.movies.show', compact('movie', 'torrents', 'tmdbid', 'isSubscribed', 'subscribers', 'recommendations', 'display'));
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