<?php

namespace App\Http\Controllers;

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




    public function show($id)
    {
        // Fetch all movies belonging to a particular collection and paginate the results
        $collection = DB::table('movies')
            ->select('id', 'name', 'slug', 'tmdb_id', 'poster_path', 'imdb_id', 'collection_id', 'collection_name')
            ->where('collection_id', $id)
            ->paginate(8);  // Adjust number 12 to how many items you want per page

        $apiKey = '325f0b42fccd356be82ede4d2be6312c';

        // Get movie details from TMDB
        $CollectionDetailsResponse = Http::get("https://api.themoviedb.org/3/collection/{$id}?api_key=$apiKey&language=en-US&append_to_response=credits,videos,images,external_ids");
        $CollectionDetails = $CollectionDetailsResponse->json();

        // Optionally, you might want to pass all movies (not just the ones in the collection)
        $movies = DB::table('movies')
            ->select('id', 'name', 'tmdb_id', 'poster_path', 'imdb_id', 'collection_id', 'collection_name')
            ->get();

        return view('collections.show', compact('collection', 'id', 'movies', 'CollectionDetails'));
    }

}
