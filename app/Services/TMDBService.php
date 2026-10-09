<?php

namespace App\Services;

use App\Services\Torrent\MetadataHttpCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TMDBService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tmdb.key', '');
    }

    /**
     * Get TMDB ID and type based on IMDb ID.
     */
    public function getTMDBIdAndTypeByIMDbId(string $imdbId): ?array
    {
        if (!preg_match('/^tt[0-9]+$/D', $imdbId)) return null;
        return $this->findExternalId($imdbId, 'imdb_id');
    }

    public function getTMDBIdByTVDBId(int $tvdbId): ?array
    {
        return $tvdbId > 0 ? $this->findExternalId((string) $tvdbId, 'tvdb_id') : null;
    }

    private function findExternalId(string $id, string $source): ?array
    {
        if ($this->apiKey === '') return null;
        $data = app(MetadataHttpCache::class)->get(
            "tmdb:find:{$source}:{$id}", "https://api.themoviedb.org/3/find/{$id}",
            ['external_source' => $source, 'api_key' => $this->apiKey],
            fn (array $data) => isset($data['movie_results']) || isset($data['tv_results'])
        );
        foreach (['movie_results' => 'movie', 'tv_results' => 'tv'] as $key => $type) {
            $value = $data[$key][0]['id'] ?? null;
            if (is_int($value) && $value > 0) return ['tmdb_id' => $value, 'type' => $type];
        }
        return null;
    }

    /**
     * Search TMDB by title (auto-upload friendly).
     *
     * @param  string  $type  "movie" or "tv"
     */
    public function getMetadataByTitle(string $title, string $type = 'movie'): ?array
    {
        $cacheKey = "tmdb_search_{$type}_".md5($title);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($title, $type) {
            $response = Http::get("https://api.themoviedb.org/3/search/{$type}", [
                'api_key' => $this->apiKey,
                'query' => $title,
                'language' => 'en-US',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $results = $response->json()['results'] ?? [];

            if (empty($results)) {
                return null;
            }

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
        if ($this->apiKey === '' || $tmdbId < 1 || ! in_array($type, ['movie', 'tv'], true)) {
            return null;
        }

        return app(MetadataHttpCache::class)->get(
            "tmdb:images:{$type}:{$tmdbId}",
            "https://api.themoviedb.org/3/{$type}/{$tmdbId}",
            [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
                'include_image_language' => 'en,null',
                'append_to_response' => 'credits,videos,images,keywords,similar,recommendations,watch/providers,external_ids,release_dates,content_ratings',
            ],
            fn (array $data) => ($data['id'] ?? null) === $tmdbId && is_string($data[$type === 'movie' ? 'title' : 'name'] ?? null) && trim($data[$type === 'movie' ? 'title' : 'name']) !== ''
        );
    }

    /**
     * Search TMDB by title and return TMDB ID + type
     */
    public function searchByTitle(string $cleanTitle)
    {
        if (empty($cleanTitle)) {
            return null;
        }

        $cacheKey = 'tmdb_search_'.md5($cleanTitle);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($cleanTitle) {
            $response = Http::get('https://api.themoviedb.org/3/search/multi', [
                'api_key' => $this->apiKey,
                'query' => $cleanTitle,
                'language' => 'en-US',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (! empty($data['results'])) {
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

    public function fetchCollection(int $collectionId): ?array
    {
        return app(MetadataHttpCache::class)->get(
            "collection:{$collectionId}",
            "https://api.themoviedb.org/3/collection/{$collectionId}",
            ['api_key' => $this->apiKey, 'language' => 'en-US'],
            fn (array $data) => isset($data['id']) && is_array($data['parts'] ?? null)
        );
    }
}
