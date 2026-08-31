<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\TorrentMovie;

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

    $movie = cache()->remember("tmdb_movie_{$tmdbid}", 86400, function () use ($tmdbid) {
        return Http::get("https://api.themoviedb.org/3/movie/{$tmdbid}", [
           'api_key' => config('services.tmdb.key'),
        ])->json();
    });

    // Generate correct slug
    $correctSlug = Str::slug($movie['title'] ?? 'movie');

    // Optional: redirect if slug is wrong
    if ($slug !== $correctSlug) {
        return redirect()->route('library.movies.show', [
            'tmdbid' => $tmdbid,
            'slug' => $correctSlug
        ]);
    }

    return view('library.movies.show', compact('movie', 'torrents'));
}


}