<?php

namespace App\Services;

use App\Models\TorrentRequest;

/** Resolve only recognized provider IDs; never fetch a user-supplied URL. */
class RequestMetadataService
{
    public function forRequest(TorrentRequest $request): array
    {
        $result = ['media' => null, 'game' => null];
        $tmdb = $this->providerPath($request->tmdb_url, ['themoviedb.org', 'www.themoviedb.org']);
        $steam = $this->providerPath($request->steam_url, ['store.steampowered.com']);
        $imdb = $this->providerPath($request->imdb_url, ['imdb.com', 'www.imdb.com']);
        if ($tmdb && preg_match('~^/(movie|tv)/([1-9][0-9]*)(?:-[^/]*)?/?$~', $tmdb, $match)) {
            $data = app(TMDBService::class)->fetchTMDBData((int) $match[2], $match[1]);
            if ($data) {
                $ratings = null;
                $imdbId = $data['imdb_id'] ?? $data['external_ids']['imdb_id'] ?? null;
                if ($imdb && preg_match('~^/title/(tt[0-9]+)/?$~', $imdb, $imdbMatch)) {
                    $imdbId = $imdbMatch[1];
                }
                if (is_string($imdbId)) {
                    $ratings = app(OMDBService::class)->fetchOMDBData($imdbId);
                }
                $result['media'] = [
                    'title' => $data['title'] ?? $data['name'] ?? $request->name,
                    'type' => $match[1] === 'tv' ? 'TV series' : 'Movie',
                    'overview' => $data['overview'] ?? null,
                    'tagline' => $data['tagline'] ?? null,
                    'poster' => $this->tmdbImage($data['poster_path'] ?? null, 'w500'),
                    'backdrop' => $this->tmdbImage($data['backdrop_path'] ?? null, 'w1280'),
                    'release' => $data['release_date'] ?? $data['first_air_date'] ?? null,
                    'runtime' => $data['runtime'] ?? $data['episode_run_time'][0] ?? null,
                    'seasons' => $data['number_of_seasons'] ?? null,
                    'rating' => isset($data['vote_average']) ? round($data['vote_average'], 1) : null,
                    'imdb_rating' => $ratings['imdbRating'] ?? null,
                    'genres' => collect($data['genres'] ?? [])->pluck('name')->filter()->all(),
                    'cast' => collect($data['credits']['cast'] ?? [])->take(6)->pluck('name')->filter()->all(),
                ];
            }
        }
        if ($steam && preg_match('~^/app/([1-9][0-9]*)(?:/[^/]*)?/?$~', $steam, $match)) {
            $data = app(SteamService::class)->fetchSteamData($match[1]);
            $game = $data[$match[1]]['data'] ?? null;
            if ($game) {
                $result['game'] = [
                    'title' => $game['name'],
                    'overview' => html_entity_decode(strip_tags($game['short_description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'poster' => $request->safeUrl($game['header_image'] ?? null),
                    'release' => $game['release_date']['date'] ?? null,
                    'coming_soon' => $game['release_date']['coming_soon'] ?? false,
                    'developers' => $game['developers'] ?? [],
                    'publishers' => $game['publishers'] ?? [],
                    'genres' => collect($game['genres'] ?? [])->pluck('description')->filter()->all(),
                    'platforms' => array_keys(array_filter($game['platforms'] ?? [])),
                    'score' => $game['metacritic']['score'] ?? null,
                ];
            }
        }

        return $result;
    }

    private function providerPath(?string $url, array $hosts): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || ! in_array(strtolower($parts['host'] ?? ''), $hosts, true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        return $parts['path'] ?? null;
    }

    private function tmdbImage(?string $path, string $size): ?string
    {
        return $path && preg_match('~^/[a-zA-Z0-9_.-]+$~', $path) ? "https://image.tmdb.org/t/p/{$size}{$path}" : null;
    }
}
