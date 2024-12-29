<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TMDBService
{
    protected $apiKey;

    public function __construct()
    {
        // Set your TMDB API key here
        $this->apiKey = env('TMDB_API_KEY', '325f0b42fccd356be82ede4d2be6312c');
    }

    /**
     * Get TMDB ID and type based on IMDb ID.
     *
     * @param string $imdbId
     * @return array|null
     */
    public function getTMDBIdAndTypeByIMDbId($imdbId)
    {
        $response = Http::get("https://api.themoviedb.org/3/find/{$imdbId}", [
            'external_source' => 'imdb_id',
            'api_key' => $this->apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['movie_results'])) {
                return [
                    'tmdb_id' => $data['movie_results'][0]['id'],
                    'type' => 'movie',
                ];
            }

            if (!empty($data['tv_results'])) {
                return [
                    'tmdb_id' => $data['tv_results'][0]['id'],
                    'type' => 'tv',
                ];
            }
        }

        return null;
    }

    /**
     * Fetch TMDB data for a movie or TV show based on TMDB ID and type.
     *
     * @param int $tmdbId
     * @param string $type
     * @return array|null
     */
    public function fetchTMDBData($tmdbId, $type)
{
    // Define a unique cache key based on TMDB ID and type
    $cacheKey = "tmdb_{$type}_{$tmdbId}";

    // Attempt to get TMDB data from cache
    return Cache::remember($cacheKey, now()->addDays(30), function () use ($tmdbId, $type) {
        $endpoint = $type === 'movie' ? "movie" : "tv";

        // Make the API request to TMDB
        $response = Http::get("https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}", [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
            'append_to_response' => 'credits,videos,images,keywords'
        ]);

        // Return the response data if successful, or null if not
        return $response->successful() ? $response->json() : null;
    });
}


public function fetchOMDBData($imdbId)
{
    // Define a unique cache key based on the IMDb ID
    $omdbCacheKey = "omdb_{$imdbId}";

    // Attempt to get OMDB data from cache
    return Cache::remember($omdbCacheKey, now()->addDays(30), function () use ($imdbId) {
        // Make the API request to OMDB
        $response = Http::get("http://www.omdbapi.com/", [
            'apikey' => 'd3eb5201',  // Replace with your OMDB API key
            'i' => $imdbId,
            'plot' => 'full',  // or 'full' depending on your needs
            'r' => 'json'  // Ensure the response is in JSON format
        ]);

        // Return the response data if successful, or null if not
        return $response->successful() ? $response->json() : null;
    });
}

public function fetchSteamData($steamId)
{
    // Define a unique cache key based on the Steam ID
    $steamCacheKey = "steam_{$steamId}";

    // Attempt to get Steam data from cache
    return Cache::remember($steamCacheKey, now()->addDays(30), function () use ($steamId) {
        // Make the API request to Steam
        $response = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $steamId,
            'lang' => 'en'
        ]);

        // Return the response data if successful, or null if not
        return $response->successful() ? $response->json() : null;
    });
}

}
