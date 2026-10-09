<?php

namespace App\Services;

use App\Models\Torrent;

/** Compose movie/TV display data from independent metadata providers. */
class MediaDisplayService
{
    public function getDisplayPayload(int $tmdbId, string $type, ?string $imdbId = null, bool $includeCollection = true): ?array
    {
        $tmdb = app(TMDBService::class)->fetchTMDBData($tmdbId, $type);
        if (! $tmdb) {
            return null;
        }

        return $this->buildDisplayPayload($tmdbId, $type, $tmdb, $imdbId, $includeCollection);
    }

    /** Build the same hero payload from already available library metadata. */
    public function buildDisplayPayload(int $tmdbId, string $type, array $tmdb, ?string $imdbId = null, bool $includeCollection = false): array
    {
        $omdb = $imdbId ? app(OMDBService::class)->fetchOMDBData($imdbId) : null;
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

        $creators = ! $isMovie
            ? collect($tmdb['created_by'] ?? [])
                ->pluck('name')
                ->all()
            : [];

        $collection = null;
        $collectionMovies = [];

        if ($includeCollection && $isMovie && ! empty($tmdb['belongs_to_collection'])) {
            $collection = [
                'id' => $tmdb['belongs_to_collection']['id'],
                'name' => $tmdb['belongs_to_collection']['name'],
                'poster' => $tmdb['belongs_to_collection']['poster_path']
                    ? "https://image.tmdb.org/t/p/w500{$tmdb['belongs_to_collection']['poster_path']}"
                    : null,
                'current_tmdb_id' => $tmdbId,
            ];

            $parts = collect(app(TMDBService::class)->fetchCollection($collection['id'])['parts'] ?? []);
            // One query for all versions, with live stats and the soft-delete scope intact.
            $versions = $parts->isEmpty() ? collect() : Torrent::query()
                ->where('tmdb_type', 'movie')
                ->whereIn('tmdbid', $parts->pluck('id'))
                ->orderByDesc('seeders')
                ->get(['id', 'tmdbid', 'slug', 'name', 'created_at', 'size', 'seeders', 'leechers', 'times_completed'])
                ->groupBy('tmdbid');

            $collectionMovies = $parts->sortBy('release_date')->map(function ($movie) use ($versions) {
                $torrents = $versions->get($movie['id'], collect());

                return [
                    'tmdb_id' => $movie['id'],
                    'title' => $movie['title'] ?? '',
                    'release_date' => $movie['release_date'] ?? null,
                    'poster' => ! empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w300{$movie['poster_path']}" : null,
                    'in_db' => $torrents->isNotEmpty(),
                    'torrent' => $torrents->first(),
                    'torrents' => $torrents,
                ];
            })->values()->all();
        }

        return $this->normalizeDisplay([

            // CORE
            'id' => $tmdbId,
            'type' => $type,
            'title' => $isMovie ? $tmdb['title'] : $tmdb['name'],
            'year' => substr(
                $isMovie ? $tmdb['release_date'] ?? '' : $tmdb['first_air_date'] ?? '',
                0,
                4
            ),
            'tagline' => $tmdb['tagline'] ?? null,
            'overview' => $tmdb['overview'] ?? null,

            // IMAGES
            'poster' => isset($tmdb['poster_path']) ? "https://image.tmdb.org/t/p/w500{$tmdb['poster_path']}" : null,
            'backdrop' => isset($tmdb['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$tmdb['backdrop_path']}" : null,
            'backdrops' => collect($tmdb['images']['backdrops'] ?? [])
                ->pluck('file_path')
                ->filter(fn ($path) => is_string($path) && preg_match('~^/[a-zA-Z0-9_.-]+$~', $path))
                ->unique()
                ->take(8)
                ->map(fn ($path) => "https://image.tmdb.org/t/p/w1280{$path}")
                ->values()
                ->all(),

            // META
            'genres' => collect($tmdb['genres'] ?? [])->pluck('name')->all(),
            'runtime' => $isMovie
                ? ($tmdb['runtime'] ?? null)
                : ($tmdb['episode_run_time'][0] ?? null),

            'status' => $tmdb['status'] ?? null,
            'seasons' => $tmdb['number_of_seasons'] ?? null,
            'episodes' => $tmdb['number_of_episodes'] ?? null,

            // RATINGS (merged)
            'ratings' => [
                'tmdb' => isset($tmdb['vote_average']) ? round($tmdb['vote_average'], 1) : null,
                'imdb' => $omdb['imdbRating'] ?? null,
                'rt' => collect($omdb['Ratings'] ?? [])
                    ->firstWhere('Source', 'Rotten Tomatoes')['Value'] ?? null,
                'votes' => $omdb['imdbVotes'] ?? null,
                'rated' => $omdb['Rated'] ?? null,
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
            'is_miniseries' => ! $isMovie &&
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
            'writer' => ! empty($writers) ? implode(', ', $writers) : null,
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
            'budget' => $isMovie && ! empty($tmdb['budget']) ? $tmdb['budget'] : null,
            'revenue' => $isMovie && ! empty($tmdb['revenue']) ? $tmdb['revenue'] : null,

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
                'imdb_id' => $tmdb['external_ids']['imdb_id'] ?? null,
                'tvdb_id' => $tmdb['external_ids']['tvdb_id'] ?? null,
                'facebook_id' => $tmdb['external_ids']['facebook_id'] ?? null,
                'twitter_id' => $tmdb['external_ids']['twitter_id'] ?? null,
                'instagram_id' => $tmdb['external_ids']['instagram_id'] ?? null,
                'wikipedia_id' => $tmdb['external_ids']['wikidata_id'] ?? null,
            ],

            // WATCH PROVIDERS (streaming availability)
            'watch_providers' => $this->extractWatchProviders($tmdb),

            // TV — NEXT EPISODE TO AIR
            'next_episode_to_air' => ! $isMovie && ! empty($tmdb['next_episode_to_air'])
                ? [
                    'name' => $tmdb['next_episode_to_air']['name'] ?? null,
                    'overview' => $tmdb['next_episode_to_air']['overview'] ?? null,
                    'season_number' => $tmdb['next_episode_to_air']['season_number'] ?? null,
                    'episode_number' => $tmdb['next_episode_to_air']['episode_number'] ?? null,
                    'air_date' => $tmdb['next_episode_to_air']['air_date'] ?? null,
                    'still_path' => $tmdb['next_episode_to_air']['still_path']
                        ? "https://image.tmdb.org/t/p/w780{$tmdb['next_episode_to_air']['still_path']}"
                        : null,
                    'vote_average' => $tmdb['next_episode_to_air']['vote_average'] ?? null,
                ]
                : null,

            // TV — LAST EPISODE TO AIR
            'last_episode_to_air' => ! $isMovie && ! empty($tmdb['last_episode_to_air'])
                ? [
                    'name' => $tmdb['last_episode_to_air']['name'] ?? null,
                    'overview' => $tmdb['last_episode_to_air']['overview'] ?? null,
                    'season_number' => $tmdb['last_episode_to_air']['season_number'] ?? null,
                    'episode_number' => $tmdb['last_episode_to_air']['episode_number'] ?? null,
                    'air_date' => $tmdb['last_episode_to_air']['air_date'] ?? null,
                    'still_path' => $tmdb['last_episode_to_air']['still_path']
                        ? "https://image.tmdb.org/t/p/w780{$tmdb['last_episode_to_air']['still_path']}"
                        : null,
                    'vote_average' => $tmdb['last_episode_to_air']['vote_average'] ?? null,
                ]
                : null,

            // TV — SEASON DETAILS
            'season_details' => ! $isMovie
                ? collect($tmdb['seasons'] ?? [])
                    ->reject(fn ($s) => ($s['season_number'] ?? 0) === 0)
                    ->map(fn ($s) => [
                        'season_number' => $s['season_number'],
                        'name' => $s['name'] ?? null,
                        'overview' => $s['overview'] ?? null,
                        'air_date' => $s['air_date'] ?? null,
                        'episode_count' => $s['episode_count'] ?? 0,
                        'vote_average' => $s['vote_average'] ?? null,
                        'poster' => !empty($s['poster_path'])
                            ? "https://image.tmdb.org/t/p/w300{$s['poster_path']}"
                            : null,
                    ])
                    ->all()
                : [],

            // SIMILAR / RECOMMENDATIONS (from TMDB)
            'similar' => collect($tmdb['similar']['results'] ?? [])
                ->take(6)
                ->map(fn ($s) => [
                    'id' => $s['id'],
                    'title' => $s['title'] ?? $s['name'] ?? null,
                    'poster' => isset($s['poster_path'])
                        ? "https://image.tmdb.org/t/p/w185{$s['poster_path']}"
                        : null,
                    'rating' => $s['vote_average'] ?? null,
                    'year' => substr($s['release_date'] ?? $s['first_air_date'] ?? '', 0, 4),
                ])
                ->all(),

            'recommendations' => collect($tmdb['recommendations']['results'] ?? [])
                ->take(6)
                ->map(fn ($r) => [
                    'id' => $r['id'],
                    'title' => $r['title'] ?? $r['name'] ?? null,
                    'poster' => isset($r['poster_path'])
                        ? "https://image.tmdb.org/t/p/w185{$r['poster_path']}"
                        : null,
                    'rating' => $r['vote_average'] ?? null,
                    'year' => substr($r['release_date'] ?? $r['first_air_date'] ?? '', 0, 4),
                ])
                ->all(),

        ]);
    }

    /**
     * Extract watch/providers data from TMDB response, filtered by US region.
     */
    private function extractWatchProviders(array $tmdb): array
    {
        $raw = $tmdb['watch/providers']['results'] ?? [];

        // Try US first, fall back to first available region
        $region = $raw['US'] ?? reset($raw) ?: null;

        if (! $region) {
            return [];
        }

        $providers = [];

        foreach (['flatrate', 'rent', 'buy', 'free', 'ads'] as $type) {
            if (! empty($region[$type])) {
                foreach ($region[$type] as $p) {
                    $providers[] = [
                        'type' => $type,
                        'name' => $p['provider_name'] ?? null,
                        'logo' => isset($p['logo_path'])
                            ? "https://image.tmdb.org/t/p/w92{$p['logo_path']}"
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
                        ->first(fn ($release) => ! empty($release['certification']));
                    if ($first) {
                        return $first['certification'];
                    }
                }
            }

            // Fall back to first non-empty certification
            foreach ($releases as $country) {
                $cert = collect($country['release_dates'] ?? [])
                    ->first(fn ($release) => ! empty($release['certification']));
                if ($cert) {
                    return $cert['certification'];
                }
            }
        } else {
            $ratings = $tmdb['content_ratings']['results'] ?? [];

            foreach ($ratings as $country) {
                if (($country['iso_3166_1'] ?? '') === 'US') {
                    if (! empty($country['rating'])) {
                        return $country['rating'];
                    }
                }
            }

            foreach ($ratings as $country) {
                if (! empty($country['rating'])) {
                    return $country['rating'];
                }
            }
        }

        return null;
    }

    private function normalizeDisplay(array $display): array
    {
        return array_replace_recursive([
            // CORE
            'id' => null,
            'type' => null,
            'title' => null,
            'year' => null,
            'overview' => null,
            'tagline' => null,

            // MEDIA
            'poster' => null,
            'backdrop' => null,
            'backdrops' => [],
            'trailer' => null,

            // META
            'genres' => [],
            'runtime' => null,
            'status' => null,
            'seasons' => null,
            'episodes' => null,

            // CREW
            'director' => null,
            'writer' => null,
            'creators' => [],

            // PRODUCTION
            'production_companies' => [],
            'networks' => [],
            'countries' => [],
            'origin_countries' => [],
            'language' => null,
            'original_language' => null,
            'spoken_languages' => [],
            'release_date' => null,
            'certification_country' => null,

            // KEYWORDS
            'keywords' => [],

            // FINANCIALS
            'budget' => null,
            'revenue' => null,

            // RATINGS
            'ratings' => [
                'tmdb' => null,
                'imdb' => null,
                'rt' => null,
                'rated' => null,
                'votes' => null,
            ],

            // CERTIFICATION
            'certification' => null,

            'vote_count' => null,

            // CAST
            'cast' => [],

            // SIMILAR / RECOMMENDATIONS
            'similar' => [],
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
                'imdb_id' => null,
                'facebook_id' => null,
                'twitter_id' => null,
                'instagram_id' => null,
                'wikipedia_id' => null,
            ],

            // WATCH PROVIDERS
            'watch_providers' => [],

            // TV EPISODES
            'next_episode_to_air' => null,
            'last_episode_to_air' => null,
            'season_details' => [],

        ], $display);
    }
}
