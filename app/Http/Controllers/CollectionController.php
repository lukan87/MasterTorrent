<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Services\TMDBService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CollectionController extends Controller
{


    public function index()
{
    // Cache the paginated collections for 10 minutes
    $collections = cache()->remember('collections_paginated', 2, function () {
        return DB::table('movies')
            ->select('collection_id', DB::raw('count(*) as total'), 'collection_name')
            ->whereNotNull('collection_id')
            ->groupBy('collection_id', 'collection_name')
            ->paginate(8);
    });

    // TMDb API Key
    $apiKey = '325f0b42fccd356be82ede4d2be6312c';

    // Initialize an array to store collection details
    $collectionDetailsList = [];

    // Loop through each collection and fetch details from TMDb
    foreach ($collections as $collection) {
        if (!empty($collection->collection_id)) {
            // Cache the collection details for each collection_id for 10 minutes
            $collectionDetailsList[$collection->collection_id] = cache()->remember("collection_details_{$collection->collection_id}", 2, function () use ($apiKey, $collection) {
                // Make the API request to get collection details from TMDb
                $collectionDetailsResponse = Http::get("https://api.themoviedb.org/3/collection/{$collection->collection_id}?api_key={$apiKey}&language=en-US");

                // Check if the API call was successful and return the details
                if ($collectionDetailsResponse->successful()) {
                    return $collectionDetailsResponse->json();
                } else {
                    // Handle the case where the API call failed (optional)
                    return null;
                }
            });
        }
    }

    // Pass both the paginated collections and collection details to the view
    return view('collections.index', [
        'collections' => $collections,
        'collectionDetailsList' => $collectionDetailsList
    ]);
}




   public function show(int $tmdbId)
{
    $tmdb = app(TMDBService::class)->fetchCollection($tmdbId);
    if (!$tmdb) abort(404);

    // Sort movies by release date
    $movies = collect($tmdb['parts'] ?? [])
        ->sortBy(fn ($m) => $m['release_date'] ?? '9999-99-99')
        ->values();

    // 🔥 Fetch ALL torrents for ALL movies
    $torrents = Torrent::whereIn('tmdbid', $movies->pluck('id'))
        ->orderByDesc('seeders')
        ->get()
        ->groupBy('tmdbid');

        // Fetch all movies already added online
    $onlineMovies = DB::table('movies')
    ->whereIn('tmdb_id', $movies->pluck('id'))
    ->get()
    ->keyBy('tmdb_id');

    $mapped = $movies->map(function ($m, $index) use ($torrents, $onlineMovies) {
    $movieTorrents = $torrents->get($m['id'], collect());
    $onlineMovie   = $onlineMovies->get($m['id']);

    return [
        'index'   => $index,
        'tmdb_id' => $m['id'],
        'title'   => $m['title'],
        'year'    => substr($m['release_date'] ?? '', 0, 4),
        'overview'=> $m['overview'] ?? null,

        'poster'  => $m['poster_path']
            ? "https://image.tmdb.org/t/p/w342{$m['poster_path']}"
            : null,

        'backdrop_path' => $m['backdrop_path']
            ? "https://image.tmdb.org/t/p/original{$m['backdrop_path']}"
            : null,

        // STATUS FLAGS
        'has_torrents' => $movieTorrents->isNotEmpty(),
        'is_online'    => !is_null($onlineMovie),
        'movie_id'     => $onlineMovie->id ?? null,

        // DATA
        'torrents' => $movieTorrents->values(),
    ];
});


    return view('collections.show', [
        'collection' => [
            'id'       => $tmdb['id'],
            'name'     => $tmdb['name'],
            'overview' => $tmdb['overview'],
            'poster'   => $tmdb['poster_path']
                ? "https://image.tmdb.org/t/p/w500{$tmdb['poster_path']}"
                : null,
                'backdrop_path'  => $tmdb['backdrop_path']
                ? "https://image.tmdb.org/t/p/original{$tmdb['backdrop_path']}"
                : null,
            'total'    => $mapped->count(),
            'uploaded' => $mapped->where('exists', true)->count(),
        ],
        'movies' => $mapped,
    ]);
}


}
