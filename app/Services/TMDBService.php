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
    $cacheKey = "tmdb_v2_{$type}_{$tmdbId}";

    return Cache::remember($cacheKey, now()->addDays(30), function () use ($tmdbId, $type) {
        $endpoint = $type === 'movie' ? 'movie' : 'tv';

        $response = Http::get("https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}", [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
            'append_to_response' => implode(',', [
                'credits',
                'videos',
                'images',
                'keywords',
                'similar',          
                'recommendations',
                'watch_providers',
                'external_ids',
                'release_dates',
                'content_ratings',
            ]),
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


public function getDisplayPayload(int $tmdbId, string $type, ?string $imdbId = null): ?array
{
    $tmdb = $this->fetchTMDBData($tmdbId, $type);
    if (!$tmdb) return null;

    $omdb = $imdbId ? $this->fetchOMDBData($imdbId) : null;
    $isMovie = $type === 'movie';

    $crew = collect($tmdb['credits']['crew'] ?? []);

$director = $isMovie
    ? optional($crew->firstWhere('job', 'Director'))['name'] ?? null
    : null;

$writers = $isMovie
    ? $crew->whereIn('job', ['Writer', 'Screenplay'])
        ->pluck('name')
        ->unique()
        ->values()
        ->all()
    : [];

$creators = !$isMovie
    ? collect($tmdb['created_by'] ?? [])
        ->pluck('name')
        ->all()
    : [];

   $collection = null;
$collectionMovies = [];

if ($isMovie && !empty($tmdb['belongs_to_collection'])) {
    $collection = [
        'id' => $tmdb['belongs_to_collection']['id'],
        'name' => $tmdb['belongs_to_collection']['name'],
        'poster' => $tmdb['belongs_to_collection']['poster_path']
            ? "https://image.tmdb.org/t/p/w500{$tmdb['belongs_to_collection']['poster_path']}"
            : null,
        'current_tmdb_id' => $tmdbId,
    ];

    // Fetch collection details
    $collectionResponse = Http::get(
        "https://api.themoviedb.org/3/collection/{$collection['id']}",
        [
            'api_key' => $this->apiKey,
            'language' => 'en-US',
        ]
    );

    if ($collectionResponse->successful()) {
       $collectionMovies = collect(
    $collectionResponse->json()['parts'] ?? []
)->map(function ($m) {

    $torrents = \App\Models\Torrent::where('tmdbid', $m['id'])
        ->orderByDesc('seeders')
        ->get();

    return [
        'tmdb_id' => $m['id'],
        'title'   => $m['title'],
        'poster'  => $m['poster_path']
            ? "https://image.tmdb.org/t/p/w300{$m['poster_path']}"
            : null,

        // DB status
        'in_db'   => $torrents->isNotEmpty(),

        // backward compatibility (your current UI)
        'torrent' => $torrents->first(),

        // NEW: all versions
        'torrents' => $torrents,
    ];
})
->sortBy('release_date')
->values()
->all();

    }
}





   return $this->normalizeDisplay([
        
        // CORE
        'id'      => $tmdbId,
        'type'    => $type,
        'title'   => $isMovie ? $tmdb['title'] : $tmdb['name'],
        'year'    => substr(
            $isMovie ? $tmdb['release_date'] ?? '' : $tmdb['first_air_date'] ?? '',
            0,
            4
        ),
        'tagline' => $tmdb['tagline'] ?? null,
        'overview'=> $tmdb['overview'] ?? null,

        // IMAGES
        'poster'   => isset($tmdb['poster_path']) ? "https://image.tmdb.org/t/p/w500{$tmdb['poster_path']}" : null,
        'backdrop' => isset($tmdb['backdrop_path']) ? "https://image.tmdb.org/t/p/original{$tmdb['backdrop_path']}" : null,

        // META
        'genres'  => collect($tmdb['genres'] ?? [])->pluck('name')->all(),
        'runtime' => $isMovie
            ? ($tmdb['runtime'] ?? null)
            : ($tmdb['episode_run_time'][0] ?? null),

        'status'  => $tmdb['status'] ?? null,
        'seasons' => $tmdb['number_of_seasons'] ?? null,
        'episodes'=> $tmdb['number_of_episodes'] ?? null,

        // RATINGS (merged)
        'ratings' => [
            'tmdb' => isset($tmdb['vote_average']) ? round($tmdb['vote_average'], 1) : null,
            'imdb' => $omdb['imdbRating'] ?? null,
            'rt'   => collect($omdb['Ratings'] ?? [])
                ->firstWhere('Source', 'Rotten Tomatoes')['Value'] ?? null,
            'votes'=> $omdb['imdbVotes'] ?? null,
            'rated'=> $omdb['Rated'] ?? null,
        ],

        // CERTIFICATION (from TMDB — richer than OMDB)
        'certification' => $this->extractCertification($tmdb, $isMovie),

        // CAST
        'cast' => collect($tmdb['credits']['cast'] ?? [])
            ->take(9)
            ->map(fn ($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
                'character' => $c['character'],
                'photo' => $c['profile_path']
                    ? "https://image.tmdb.org/t/p/w185{$c['profile_path']}"
                    : null,
            ])
            ->all(),

        // TRAILER
        'trailer' => collect($tmdb['videos']['results'] ?? [])
            ->first(fn ($v) => $v['type'] === 'Trailer' && $v['site'] === 'YouTube'),

        // TV INTELLIGENCE
        'is_miniseries' => !$isMovie &&
            (($tmdb['type'] ?? '') === 'Miniseries' || ($tmdb['number_of_seasons'] ?? 0) === 1),

        'in_production' => $tmdb['in_production'] ?? false,

'production_companies' => collect($tmdb['production_companies'] ?? [])
    ->map(fn ($c) => [
        'name' => $c['name'],
        'logo' => $c['logo_path']
            ? "https://image.tmdb.org/t/p/w154{$c['logo_path']}"
            : null,
    ])
    ->all(),

    'release_date' => $tmdb['release_date'] ?? null,
    'certification_country' => $omdb['Country'] ?? null,
// CREW
'director' => $director,
'writer'   => !empty($writers) ? implode(', ', $writers) : null,
'creators' => $creators,
'collection' => $collection,
'collection_movies' => $collectionMovies,

// NETWORKS (TV)
'networks' => collect($tmdb['networks'] ?? [])
    ->map(fn ($n) => [
        'name' => $n['name'],
        'logo' => $n['logo_path']
            ? "https://image.tmdb.org/t/p/w92{$n['logo_path']}"
            : null,
    ])
    ->all(),

// COUNTRIES
'countries' => collect($tmdb['production_countries'] ?? [])
    ->pluck('iso_3166_1')
    ->all(),

'origin_countries' => $isMovie ? [] : ($tmdb['origin_country'] ?? []),

// LANGUAGES
'original_language' => $tmdb['original_language'] ?? null,
'spoken_languages' => collect($tmdb['spoken_languages'] ?? [])
    ->pluck('english_name')
    ->filter()
    ->take(5)
    ->values()
    ->all(),

// KEYWORDS
'keywords' => collect($isMovie
        ? ($tmdb['keywords']['keywords'] ?? [])
        : ($tmdb['keywords']['results'] ?? [])
    )
    ->pluck('name')
    ->filter()
    ->take(12)
    ->values()
    ->all(),

// FINANCIALS (movies)
'budget'   => $isMovie && !empty($tmdb['budget']) ? $tmdb['budget'] : null,
'revenue'  => $isMovie && !empty($tmdb['revenue']) ? $tmdb['revenue'] : null,

// VOTE COUNT
'vote_count' => $tmdb['vote_count'] ?? null,

// POPULARITY
'popularity' => $tmdb['popularity'] ?? null,

// HOMEPAGE
'homepage' => $tmdb['homepage'] ?? null,

// ORIGINAL TITLE (for foreign films)
'original_title' => $isMovie
    ? ($tmdb['original_title'] ?? null)
    : ($tmdb['original_name'] ?? null),

// EXTERNAL IDS
'external_ids' => [
    'imdb_id'     => $tmdb['external_ids']['imdb_id'] ?? null,
    'facebook_id' => $tmdb['external_ids']['facebook_id'] ?? null,
    'twitter_id'  => $tmdb['external_ids']['twitter_id'] ?? null,
    'instagram_id'=> $tmdb['external_ids']['instagram_id'] ?? null,
    'wikipedia_id'=> $tmdb['external_ids']['wikidata_id'] ?? null,
],

// WATCH PROVIDERS (streaming availability)
'watch_providers' => $this->extractWatchProviders($tmdb),

// TV — NEXT EPISODE TO AIR
'next_episode_to_air' => !$isMovie && !empty($tmdb['next_episode_to_air'])
    ? [
        'name'              => $tmdb['next_episode_to_air']['name'] ?? null,
        'overview'          => $tmdb['next_episode_to_air']['overview'] ?? null,
        'season_number'     => $tmdb['next_episode_to_air']['season_number'] ?? null,
        'episode_number'    => $tmdb['next_episode_to_air']['episode_number'] ?? null,
        'air_date'          => $tmdb['next_episode_to_air']['air_date'] ?? null,
        'still_path'        => $tmdb['next_episode_to_air']['still_path']
            ? "https://image.tmdb.org/t/p/w780{$tmdb['next_episode_to_air']['still_path']}"
            : null,
        'vote_average'      => $tmdb['next_episode_to_air']['vote_average'] ?? null,
    ]
    : null,

// TV — LAST EPISODE TO AIR
'last_episode_to_air' => !$isMovie && !empty($tmdb['last_episode_to_air'])
    ? [
        'name'              => $tmdb['last_episode_to_air']['name'] ?? null,
        'overview'          => $tmdb['last_episode_to_air']['overview'] ?? null,
        'season_number'     => $tmdb['last_episode_to_air']['season_number'] ?? null,
        'episode_number'    => $tmdb['last_episode_to_air']['episode_number'] ?? null,
        'air_date'          => $tmdb['last_episode_to_air']['air_date'] ?? null,
        'still_path'        => $tmdb['last_episode_to_air']['still_path']
            ? "https://image.tmdb.org/t/p/w780{$tmdb['last_episode_to_air']['still_path']}"
            : null,
        'vote_average'      => $tmdb['last_episode_to_air']['vote_average'] ?? null,
    ]
    : null,

// TV — SEASON DETAILS
'season_details' => !$isMovie
    ? collect($tmdb['seasons'] ?? [])
        ->reject(fn ($s) => ($s['season_number'] ?? 0) === 0)
        ->take(10)
        ->map(fn ($s) => [
            'season_number' => $s['season_number'],
            'name'          => $s['name'] ?? null,
            'overview'      => $s['overview'] ?? null,
            'air_date'      => $s['air_date'] ?? null,
            'episode_count' => $s['episode_count'] ?? 0,
            'vote_average'  => $s['vote_average'] ?? null,
            'poster'        => $s['poster_path']
                ? "https://image.tmdb.org/t/p/w300{$s['poster_path']}"
                : null,
        ])
        ->all()
    : [],

// SIMILAR / RECOMMENDATIONS (from TMDB)
'similar' => collect($tmdb['similar']['results'] ?? [])
    ->take(6)
    ->map(fn ($s) => [
        'id'     => $s['id'],
        'title'  => $s['title'] ?? $s['name'] ?? null,
        'poster' => isset($s['poster_path'])
            ? "https://image.tmdb.org/t/p/w185{$s['poster_path']}"
            : null,
        'rating' => $s['vote_average'] ?? null,
        'year'   => substr($s['release_date'] ?? $s['first_air_date'] ?? '', 0, 4),
    ])
    ->all(),

'recommendations' => collect($tmdb['recommendations']['results'] ?? [])
    ->take(6)
    ->map(fn ($r) => [
        'id'     => $r['id'],
        'title'  => $r['title'] ?? $r['name'] ?? null,
        'poster' => isset($r['poster_path'])
            ? "https://image.tmdb.org/t/p/w185{$r['poster_path']}"
            : null,
        'rating' => $r['vote_average'] ?? null,
        'year'   => substr($r['release_date'] ?? $r['first_air_date'] ?? '', 0, 4),
    ])
    ->all(),


   ]);
}

public function fetchCollection(int $collectionId): ?array
{
    return Cache::remember("collection_{$collectionId}", 86400, function () use ($collectionId) {
        return Http::get("https://api.themoviedb.org/3/collection/{$collectionId}", [
            'api_key' => $this->apiKey,
            'language'=> 'en-US',
        ])->json();
    });
}


/**
 * Extract watch/providers data from TMDB response, filtered by US region.
 */
private function extractWatchProviders(array $tmdb): array
{
    $raw = $tmdb['watch_providers']['results'] ?? [];

    // Try US first, fall back to first available region
    $region = $raw['US'] ?? reset($raw) ?: null;

    if (!$region) {
        return [];
    }

    $providers = [];

    foreach (['flatrate', 'rent', 'buy', 'free', 'ads'] as $type) {
        if (!empty($region[$type])) {
            foreach ($region[$type] as $p) {
                $providers[] = [
                    'type'  => $type,
                    'name'  => $p['provider_name'] ?? null,
                    'logo'  => isset($p['provider_path'])
                        ? "https://image.tmdb.org/t/p/w92{$p['provider_path']}"
                        : null,
                ];
            }
        }
    }

    return $providers;
}


/**
 * Extract US certification from release_dates (movie) or content_ratings (TV).
 */
private function extractCertification(array $tmdb, bool $isMovie): ?string
{
    if ($isMovie) {
        $releases = $tmdb['release_dates']['results'] ?? [];

        // Try US first
        foreach ($releases as $country) {
            if (($country['iso_3166_1'] ?? '') === 'US') {
                $first = collect($country['release_dates'] ?? [])
                    ->firstWhere('certification', fn ($c) => !empty($c));
                if ($first) return $first['certification'];
            }
        }

        // Fall back to first non-empty certification
        foreach ($releases as $country) {
            $cert = collect($country['release_dates'] ?? [])
                ->firstWhere('certification', fn ($c) => !empty($c));
            if ($cert) return $cert['certification'];
        }
    } else {
        $ratings = $tmdb['content_ratings']['results'] ?? [];

        foreach ($ratings as $country) {
            if (($country['iso_3166_1'] ?? '') === 'US') {
                if (!empty($country['rating'])) return $country['rating'];
            }
        }

        foreach ($ratings as $country) {
            if (!empty($country['rating'])) return $country['rating'];
        }
    }

    return null;
}
private function normalizeDisplay(array $display): array
{
    return array_replace_recursive([
        // CORE
        'id'       => null,
        'type'     => null,
        'title'    => null,
        'year'     => null,
        'overview' => null,
        'tagline'  => null,

        // MEDIA
        'poster'   => null,
        'backdrop' => null,
        'trailer'  => null,

        // META
        'genres'   => [],
        'runtime'  => null,
        'status'   => null,
        'seasons'  => null,
        'episodes' => null,

        // CREW
        'director' => null,
        'writer'   => null,
        'creators' => [],

        // PRODUCTION
        'production_companies' => [],
        'networks'             => [],
        'countries'            => [],
        'origin_countries'     => [],
        'language'             => null,
        'original_language'    => null,
        'spoken_languages'     => [],
        'release_date'         => null,
        'certification_country'=> null,

        // KEYWORDS
        'keywords' => [],

        // FINANCIALS
        'budget'  => null,
        'revenue' => null,

        // RATINGS
        'ratings' => [
            'tmdb'  => null,
            'imdb'  => null,
            'rt'    => null,
            'rated' => null,
            'votes' => null,
        ],

        // CERTIFICATION
        'certification' => null,

        'vote_count' => null,

        // CAST
        'cast' => [],

        // SIMILAR / RECOMMENDATIONS
        'similar'         => [],
        'recommendations' => [],

        // TV INTELLIGENCE
        'is_miniseries' => false,
        'in_production' => false,

        // POPULARITY
        'popularity' => null,

        // HOMEPAGE
        'homepage' => null,

        // ORIGINAL TITLE
        'original_title' => null,

        // EXTERNAL IDS
        'external_ids' => [
            'imdb_id'      => null,
            'facebook_id'  => null,
            'twitter_id'   => null,
            'instagram_id' => null,
            'wikipedia_id' => null,
        ],

        // WATCH PROVIDERS
        'watch_providers' => [],

        // TV EPISODES
        'next_episode_to_air' => null,
        'last_episode_to_air' => null,
        'season_details'      => [],

    ], $display);
}




}
