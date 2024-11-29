<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $endpoint = $type === 'movie' ? "movie" : "tv";

        $response = Http::get("https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}", [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
        ]);

        return $response->successful() ? $response->json() : null;
    }
}
