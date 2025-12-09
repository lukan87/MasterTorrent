<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TMDBService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY', '325f0b42fccd356be82ede4d2be6312c');
    }

    /**
     * Get TMDB ID and type based on IMDb ID.
     *
     * @param string $imdbId
     * @return array|null
     */
    public function getTMDBIdAndTypeByIMDbId(string $imdbId): ?array
    {
        $response = Http::get("https://api.themoviedb.org/3/find/{$imdbId}", [
            'external_source' => 'imdb_id',
            'api_key' => $this->apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['movie_results'])) {
                return ['tmdb_id' => $data['movie_results'][0]['id'], 'type' => 'movie'];
            }

            if (!empty($data['tv_results'])) {
                return ['tmdb_id' => $data['tv_results'][0]['id'], 'type' => 'tv'];
            }
        }

        return null;
    }

    /**
     * Search TMDB by title (auto-upload friendly).
     *
     * @param string $title
     * @param string $type "movie" or "tv"
     * @return array|null
     */
    public function getMetadataByTitle(string $title, string $type = 'movie'): ?array
    {
        $cacheKey = "tmdb_search_{$type}_" . md5($title);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($title, $type) {
            $response = Http::get("https://api.themoviedb.org/3/search/{$type}", [
                'api_key' => $this->apiKey,
                'query' => $title,
                'language' => 'en-US',
            ]);

            if (!$response->successful()) return null;

            $results = $response->json()['results'] ?? [];

            if (empty($results)) return null;

            $first = $results[0];

            return [
                'tmdb_id' => $first['id'] ?? null,
                'title' => $first['title'] ?? $first['name'] ?? $title,
                'overview' => $first['overview'] ?? '',
                'poster' => isset($first['poster_path']) ? "https://image.tmdb.org/t/p/w500{$first['poster_path']}" : null,
                'backdrop' => isset($first['backdrop_path']) ? "https://image.tmdb.org/t/p/original{$first['backdrop_path']}" : null,
                'release_date' => $first['release_date'] ?? $first['first_air_date'] ?? null,
            ];
        });
    }

    /**
     * Fetch TMDB full data by TMDB ID and type.
     */
    public function fetchTMDBData(int $tmdbId, string $type): ?array
    {
        $cacheKey = "tmdb_{$type}_{$tmdbId}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($tmdbId, $type) {
            $endpoint = $type === 'movie' ? 'movie' : 'tv';
            $response = Http::get("https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
                'append_to_response' => 'credits,videos,images,keywords'
            ]);

            return $response->successful() ? $response->json() : null;
        });
    }

    /**
     * Fetch OMDB data by IMDb ID.
     */
    public function fetchOMDBData(string $imdbId): ?array
    {
        $cacheKey = "omdb_{$imdbId}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($imdbId) {
            $response = Http::get("http://www.omdbapi.com/", [
                'apikey' => env('OMDB_API_KEY', 'd3eb5201'),
                'i' => $imdbId,
                'plot' => 'full',
                'r' => 'json',
            ]);

            return $response->successful() ? $response->json() : null;
        });
    }

    /**
     * Fetch Steam game info by Steam ID.
     */
    public function fetchSteamData(string $steamId): ?array
    {
        $cacheKey = "steam_{$steamId}";

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($steamId) {
            $response = Http::get('https://store.steampowered.com/api/appdetails', [
                'appids' => $steamId,
                'lang' => 'en'
            ]);

            return $response->successful() ? $response->json() : null;
        });
    }

    /**
 * Search TMDB by title and return TMDB ID + type
 */
public function searchByTitle(string $cleanTitle)
{
    if (empty($title)) {
        return null;
    }

    $cacheKey = "tmdb_search_" . md5($cleanTitle);

    return Cache::remember($cacheKey, now()->addDays(30), function() use ($cleanTitle) {
        $response = Http::get("https://api.themoviedb.org/3/search/multi", [
            'api_key' => $this->apiKey,
            'query' => $cleanTitle,
            'language' => 'en-US',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        if (!empty($data['results'])) {
            // Take the first result that is movie or tv
            foreach ($data['results'] as $result) {
                if (in_array($result['media_type'], ['movie', 'tv'])) {
                    return [
                        'id' => $result['id'],
                        'type' => $result['media_type'],
                    ];
                }
            }
        }

        return null;
    });
}

public function fetchOMDBDataByTitle($title)
{
    $cacheKey = "omdb_search_" . md5($title);

    return Cache::remember($cacheKey, now()->addDays(30), function() use ($title) {
        $response = Http::get('http://www.omdbapi.com/', [
            'apikey' => env('OMDB_API_KEY', 'd3eb5201'),
            't' => $title,
            'plot' => 'full',
            'r' => 'json'
        ]);
        return $response->successful() ? $response->json() : null;
    });
}



}
