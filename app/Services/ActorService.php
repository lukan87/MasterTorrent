<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ActorService
{
    public function find(int $id): array
    {
        return Cache::remember("tmdb_actor_v1_{$id}", 21600, function () use ($id) {
            $key = config('services.tmdb.key');
            if (!$key) {
                throw new RuntimeException('TMDB is not configured.');
            }

            // Person details and related data in one request; credentials stay on the server.
            $person = Http::acceptJson()->connectTimeout(5)->timeout(20)
                ->get("https://api.themoviedb.org/3/person/{$id}", [
                    'api_key' => $key,
                    'language' => 'en-US',
                    'append_to_response' => 'combined_credits,external_ids,images',
                ])->throw()->json();

            if (!is_array($person) || empty($person['name']) || (int) ($person['id'] ?? 0) !== $id) {
                throw new RuntimeException('Invalid TMDB person response.');
            }

            // Do not cache partial failures as empty filmographies.
            foreach (['combined_credits', 'external_ids', 'images'] as $section) {
                if (!isset($person[$section]) || !is_array($person[$section]) || (isset($person[$section]['success']) && !$person[$section]['success'])) {
                    throw new RuntimeException('Incomplete TMDB person response.');
                }
            }

            return $this->profile($person);
        });
    }

    public function profile(array $person): array
    {
        $cast = $this->credits($person['combined_credits']['cast'] ?? [], false);
        $crew = $this->credits($person['combined_credits']['crew'] ?? [], true);
        $departments = array_filter(array_merge(
            [$person['known_for_department'] ?? null],
            $cast ? ['Acting'] : [],
            array_column($person['combined_credits']['crew'] ?? [], 'department')
        ));
        $knownFor = $cast ?: $crew;
        usort($knownFor, fn ($a, $b) => [$b['votes'], $b['popularity']] <=> [$a['votes'], $a['popularity']]);

        return [
            'person' => $person,
            'movies' => array_values(array_filter($cast, fn ($c) => $c['type'] === 'movie')),
            'series' => array_values(array_filter($cast, fn ($c) => $c['type'] === 'tv')),
            'crew' => $crew,
            'knownFor' => array_slice($knownFor, 0, 10),
            'departments' => array_values(array_unique($departments)),
            'links' => $this->links($person),
            'photos' => array_values(array_unique(array_filter(array_map(
                fn ($image) => $this->image($image['file_path'] ?? null),
                $person['images']['profiles'] ?? []
            )))),
            'portrait' => $this->image($person['profile_path'] ?? null),
        ];
    }

    private function credits(array $credits, bool $crew): array
    {
        $titles = [];
        foreach ($credits as $credit) {
            $type = $credit['media_type'] ?? '';
            $id = (int) ($credit['id'] ?? 0);
            if (!in_array($type, ['movie', 'tv'], true) || $id < 1) {
                continue;
            }
            $key = "{$type}:{$id}";
            $role = trim($credit[$crew ? 'job' : 'character'] ?? '');
            if ($crew && !empty($credit['department'])) {
                $role = $credit['department'] . ($role ? ' · ' . $role : '');
            }
            if (!isset($titles[$key])) {
                $date = $credit[$type === 'movie' ? 'release_date' : 'first_air_date'] ?? '';
                $titles[$key] = [
                    'id' => $id,
                    'type' => $type,
                    'title' => $credit['title'] ?? $credit['name'] ?? 'Untitled',
                    'date' => $date,
                    'year' => $date ? substr($date, 0, 4) : 'TBA',
                    'poster' => $this->image($credit['poster_path'] ?? null),
                    'rating' => (float) ($credit['vote_average'] ?? 0),
                    'votes' => (int) ($credit['vote_count'] ?? 0),
                    'popularity' => (float) ($credit['popularity'] ?? 0),
                    'episodes' => (int) ($credit['episode_count'] ?? 0),
                    'roles' => [],
                ];
            }
            if ($role !== '') {
                $titles[$key]['roles'][] = $role;
                $titles[$key]['roles'] = array_values(array_unique($titles[$key]['roles']));
            }
        }
        $titles = array_values($titles);
        usort($titles, fn ($a, $b) => strcmp($b['date'], $a['date']) ?: strcasecmp($a['title'], $b['title']));

        return $titles;
    }

    private function image(?string $path): ?string
    {
        return $path && preg_match('~^/[a-zA-Z0-9_.-]+$~', $path)
            ? 'https://image.tmdb.org/t/p/w342' . $path : null;
    }

    private function links(array $person): array
    {
        $links = [];
        $sources = [
            'imdb_id' => ['IMDb', 'https://www.imdb.com/name/'],
            'instagram_id' => ['Instagram', 'https://www.instagram.com/'],
            'twitter_id' => ['X / Twitter', 'https://x.com/'],
            'facebook_id' => ['Facebook', 'https://www.facebook.com/'],
            'tiktok_id' => ['TikTok', 'https://www.tiktok.com/@'],
            'youtube_id' => ['YouTube', 'https://www.youtube.com/channel/'],
            'wikidata_id' => ['Wikidata', 'https://www.wikidata.org/wiki/'],
        ];
        foreach ($sources as $field => [$label, $base]) {
            $value = trim($person['external_ids'][$field] ?? '');
            if ($value !== '') {
                $links[$label] = $base . rawurlencode($value);
            }
        }
        $homepage = $person['homepage'] ?? '';
        if (filter_var($homepage, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($homepage, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
            $links['Official website'] = $homepage;
        }

        return $links;
    }
}
