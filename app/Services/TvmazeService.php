<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class TvmazeService
{
    private const BASE = 'https://api.tvmaze.com';

    /** Shared provider data only; user follows and site availability are never cached here. */
    public function schedule(CarbonImmutable $start, int $days, string $country, string $source): array
    {
        $jobs = [];
        for ($day = 0; $day < $days; $day++) {
            $date = $start->addDays($day)->toDateString();
            if ($source !== 'streaming') {
                $jobs["broadcast:$country:$date"] = ['/schedule', ['country' => $country, 'date' => $date]];
            }
            if ($source !== 'broadcast') {
                // Global streamers (Netflix, etc.) are omitted by the broadcast endpoint.
                $jobs["streaming:$date"] = ['/schedule/web', ['date' => $date]];
            }
        }
        $data = [];
        $missing = [];
        foreach ($jobs as $key => $job) {
            $cached = Cache::get('tvmaze:fresh:'.$key);
            if (is_array($cached)) {
                $data[$key] = $cached;
            } else {
                $missing[$key] = $job;
            }
        }
        $warnings = [];
        if ($missing) {
            $responses = Http::pool(function (Pool $pool) use ($missing) {
                $requests = [];
                foreach ($missing as $key => [$path, $params]) {
                    $requests[] = $pool->as($key)->acceptJson()->connectTimeout(3)->timeout(12)
                        ->get(self::BASE.$path, $params);
                }

                return $requests;
            }, concurrency: 4);
            foreach ($missing as $key => $job) {
                $response = $responses[$key] ?? null;
                $body = $response instanceof Response && $response->successful()
                    ? $response->json() : null;
                if (is_array($body) && array_is_list($body)) {
                    $data[$key] = $body;
                    Cache::put('tvmaze:fresh:'.$key, $body, 1800);
                    Cache::put('tvmaze:stale:'.$key, $body, 604800);
                } else {
                    $stale = Cache::get('tvmaze:stale:'.$key);
                    $data[$key] = is_array($stale) ? $stale : [];
                    $warnings[] = (is_array($stale) ? 'Saved schedule shown for ' : 'Schedule unavailable for ').$job[1]['date'].'.';
                }
            }
        }
        $episodes = [];
        foreach ($data as $key => $rows) {
            foreach ($rows as $row) {
                $show = $row['show'] ?? $row['_embedded']['show'] ?? null;
                if (! is_array($show) || empty($row['id']) || empty($show['id'])) {
                    continue;
                }
                $streamCountry = $show['webChannel']['country']['code'] ?? null;
                if (str_starts_with($key, 'streaming:') && $streamCountry !== null && $streamCountry !== $country) {
                    continue;
                }
                $episode = $this->normalize($row, $show);
                $episodes[$episode['id']] = $episode;
            }
        }
        usort($episodes, fn ($a, $b) => [$a['date'], $a['time'] ?: '99:99', $a['title'], $a['id']] <=> [$b['date'], $b['time'] ?: '99:99', $b['title'], $b['id']]);

        return ['episodes' => array_values($episodes), 'warnings' => array_values(array_unique($warnings))];
    }

    public function show(int $id): array
    {
        return Cache::remember('tvmaze:show:'.$id, 86400, function () use ($id) {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(12)->get(self::BASE.'/shows/'.$id);
            if (! $response->successful() || ! is_array($response->json()) || empty($response->json('id'))) {
                throw new RuntimeException('TVmaze could not load this show. Please try again.');
            }

            return $response->json();
        });
    }

    public function normalize(array $row, array $show): array
    {
        $season = $row['season'] ?? null;
        $number = $row['number'] ?? null;

        return [
            'id' => (int) $row['id'], 'show_id' => (int) $show['id'],
            'title' => $show['name'] ?? 'Untitled show', 'episode' => $row['name'] ?? 'TBA',
            'season' => $season, 'number' => $number,
            'episode_code' => $season !== null && $number !== null ? sprintf('S%02dE%02d', $season, $number) : 'Special / TBA',
            'date' => $row['airdate'] ?? '', 'time' => $row['airtime'] ?? '',
            'airstamp' => $row['airstamp'] ?? null, 'runtime' => $row['runtime'] ?? $show['runtime'] ?? null,
            'network' => $show['network']['name'] ?? $show['webChannel']['name'] ?? 'Network TBA',
            'show_type' => $show['type'] ?? '',
            'schedule_days' => $show['schedule']['days'] ?? [],
            'genres' => $show['genres'] ?? [], 'rating' => $show['rating']['average'] ?? null,
            'image' => $this->imageUrl($show['image']['medium'] ?? null),
            'summary' => Str::limit(trim(html_entity_decode(strip_tags($row['summary'] ?? $show['summary'] ?? ''), ENT_QUOTES, 'UTF-8')), 600),
            'imdbid' => $this->imdbId($show['externals']['imdb'] ?? null),
            'url' => 'https://www.tvmaze.com/shows/'.(int) $show['id'],
            'streaming' => ! empty($show['webChannel']),
        ];
    }

    public function isDailyTalkOrReality(array $episode): bool
    {
        $types = ['news', 'talk show', 'reality', 'game show', 'variety'];
        $genres = array_map('strtolower', $episode['genres'] ?? []);

        return in_array(strtolower($episode['show_type'] ?? ''), $types, true)
            || count(array_unique($episode['schedule_days'] ?? [])) >= 5
            || count(array_intersect($genres, ['news', 'talk show', 'reality'])) > 0;
    }

    public function imdbId(?string $id): ?string
    {
        return $id && preg_match('/^tt[0-9]+$/D', $id) ? $id : null;
    }

    private function imageUrl(?string $url): ?string
    {
        return $url && parse_url($url, PHP_URL_SCHEME) === 'https' && parse_url($url, PHP_URL_HOST) === 'static.tvmaze.com' ? $url : null;
    }
}
