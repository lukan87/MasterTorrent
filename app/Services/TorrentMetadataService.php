<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TorrentMetadataService
{
    public function torrentName(array $decoded): ?string
    {
        foreach (['name.utf-8', 'name'] as $key) {
            $name = $decoded['info'][$key] ?? null;
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        return null;
    }

    private function normalizeReleaseName(string $name): string
    {
        $name = preg_replace('/\.(?:mkv|mp4|avi|mov|nfo|torrent)$/i', '', trim($name));
        // Remove website prefixes, but preserve titles such as [REC].
        $name = preg_replace('/^\[(?:www\.|https?:\/\/|[^\]]+\.(?:com|org|net|mx))[^\]]*\]\s*/i', '', $name);

        return trim(preg_replace('/\s+/', ' ', str_replace(['.', '_'], ' ', $name)));
    }

    private function episodeMarker(string $name): ?array
    {
        foreach ([
            '/\bS(\d{1,2})(?:\s*E(\d{1,3})(?:E\d{1,3})*)?\b/i',
            '/\b(\d{1,2})x(\d{1,3})\b/i',
            '/\bSeason\s*(\d{1,2})(?:\s*(?:Episode|Ep)\s*(\d{1,3}))?\b/i',
        ] as $pattern) {
            if (preg_match($pattern, $name, $match, PREG_OFFSET_CAPTURE)) {
                return [
                    'season' => (int) $match[1][0],
                    'episode' => isset($match[2]) && $match[2][0] !== '' ? (int) $match[2][0] : null,
                    'offset' => $match[0][1],
                ];
            }
        }
        if (preg_match('/\b(?:Ep|Episode)\s*(\d{1,3})\b/i', $name, $match, PREG_OFFSET_CAPTURE)) {
            return ['season' => null, 'episode' => (int) $match[1][0], 'offset' => $match[0][1]];
        }

        return null;
    }

    public function detectTypeFromName(string $name): string
    {
        $name = $this->normalizeReleaseName($name);

        return $this->episodeMarker($name) || preg_match('/\bcomplete\s+(?:series|collection)\b/i', $name)
            ? 'tv' : 'movie';
    }

    public function cleanNameAndExtractData(string $torrentName): array
    {
        $name = $this->normalizeReleaseName($torrentName);
        $episode = $this->episodeMarker($name);
        $end = $episode['offset'] ?? strlen($name);
        // Cut the release suffix instead of deleting ordinary title words globally.
        if (preg_match('/\b(?:\d{3,4}[pi]|WEB[ -]?DL|WEBRip|Blu[ -]?Ray|BDRip|BRRip|HDRip|HDTV|REMUX|DVD(?:Rip)?|HDDVD|[xh][ -]?26[45]|HEVC|AVC)\b/i', $name, $technical, PREG_OFFSET_CAPTURE)) {
            $end = min($end, $technical[0][1]);
        }
        $title = trim(substr($name, 0, $end), " \t\n\r\0\x0B-[]()");
        $year = null;
        preg_match_all('/\b(?:19|20)\d{2}\b/', $title, $years, PREG_OFFSET_CAPTURE);
        // The last plausible year preserves numeric titles: 1917 (2019),
        // Blade Runner 2049 (2017), and 2001: A Space Odyssey (1968).
        foreach (array_reverse($years[0]) as [$candidate, $offset]) {
            $prefix = trim(substr($title, 0, $offset), " \t\n\r\0\x0B-[]()");
            if ($prefix !== '' && (int) $candidate <= (int) date('Y') + 1) {
                $year = $candidate;
                $title = $prefix;
                break;
            }
        }
        return [
            'clean' => trim($title),
            'year' => $year,
            'season' => $episode['season'] ?? null,
            'episode' => $episode['episode'] ?? null,
        ];
    }

    private function tmdbGet(string $path, array $params): array
    {
        $response = Http::connectTimeout(5)->timeout(15)->retry(2, 200,
            fn ($exception) => $exception instanceof ConnectionException ||
                ($exception instanceof RequestException && $exception->response->serverError())
        )->get('https://api.themoviedb.org/3/'.$path, $params)->throw();
        $data = $response->json();
        if (! is_array($data)) {
            throw new \RuntimeException('TMDB returned an invalid response.');
        }

        return $data;
    }

    public function fetchTmdb(string $clean, ?string $year, string $type): array
    {
        if (trim($clean) === '') {
            return [null, null, null];
        }
        $endpoint = $type === 'tv' ? 'tv' : 'movie';
        try {
            $params = ['api_key' => config('services.tmdb.key'), 'query' => $clean];
            if ($year) {
                $params['year'] = $year;
            }
            $results = $this->tmdbGet('search/'.$endpoint, $params)['results'] ?? [];
            // Release years can differ by country or TV season. Retry the same
            // title without the year only after a successful search with no hits.
            if (empty($results) && $year) {
                unset($params['year']);
                $results = $this->tmdbGet('search/'.$endpoint, $params)['results'] ?? [];
            }
            if (empty($results)) {
                Log::info('Seedbox metadata title not found', ['title' => $clean, 'year' => $year, 'type' => $endpoint]);

                return [null, null, null];
            }
            $normalize = fn ($title) => preg_replace('/[^\pL\pN]+/u', '', mb_strtolower($title));
            $ranked = collect($results)->sortByDesc(function ($result) use ($normalize, $clean, $year, $endpoint) {
                $titleKey = $endpoint === 'tv' ? 'name' : 'title';
                $dateKey = $endpoint === 'tv' ? 'first_air_date' : 'release_date';
                $exact = $normalize($result[$titleKey] ?? '') === $normalize($clean) ||
                    $normalize($result['original_'.$titleKey] ?? '') === $normalize($clean);

                return ($exact ? 100 : 0) + ($year && substr($result[$dateKey] ?? '', 0, 4) === $year ? 10 : 0);
            });
            $tmdbId = $ranked->first()['id'];
            $credentials = ['api_key' => config('services.tmdb.key')];
            $details = $this->tmdbGet("{$endpoint}/{$tmdbId}", $credentials);
            $external = $this->tmdbGet("{$endpoint}/{$tmdbId}/external_ids", $credentials);
            $imdbId = $external['imdb_id'] ?? null;
            $imdbLink = $imdbId ? "https://www.imdb.com/title/{$imdbId}" : null;
            $desc = '';
            if (! empty($details['poster_path'])) {
                $desc .= "[img]https://image.tmdb.org/t/p/w342{$details['poster_path']}[/img]\n";
            }
            $desc .= '**Title:** '.($details['title'] ?? $details['name'] ?? $clean)."\n";
            if (! empty($details['overview'])) {
                $desc .= "**Plot:** {$details['overview']}\n";
            }

            return [$imdbId, $imdbLink, $desc];
        } catch (\Throwable $e) {
            // Do not log exception messages: HTTP exceptions can contain API keys.
            Log::warning('Seedbox metadata lookup failed', [
                'title' => $clean, 'year' => $year, 'type' => $endpoint,
                'exception' => get_class($e),
                'status' => $e instanceof RequestException ? $e->response->status() : null,
            ]);

            return [null, null, null];
        }
    }

  public function detectCategory(string $torrentName, string $type, ?string $year = null): int
{
    $tn = strtolower($torrentName);

    $isPack = str_contains($tn, 'complete')
        || (str_contains($tn, 'season') && ! preg_match('/s\d+e\d+/i', $tn));

    $isAnime = str_contains($tn, 'anime');

    $isDocumentary = str_contains($tn, 'documentary')
        || str_contains($tn, 'docu');

    /*
    |--------------------------------------------------------------------------
    | TV Categories
    |--------------------------------------------------------------------------
    */

    if ($type === 'tv') {
        return 20; // TV Episodes
    }

    /*
    |--------------------------------------------------------------------------
    | Movie Categories
    |--------------------------------------------------------------------------
    */

    if ($isAnime) {
        return 1; // Movies: Anime
    }

    if ($isDocumentary) {
        return 56; // Documentary
    }

    if ($isPack) {
        return 18; // Movies: Pack
    }

    if (str_contains($tn, '2160p') || str_contains($tn, '4k')) {
        return 31; // Movies: 4K
    }

    if (str_contains($tn, 'bluray') || str_contains($tn, 'bdrip')) {
        return 5; // Movies: BluRay
    }

    if (str_contains($tn, 'dvd')) {
        return 9; // Movies: DVD
    }

    if (str_contains($tn, 'xvid')) {
        return 24; // Movies: XVID
    }

    if (str_contains($tn, 'web-dl') || str_contains($tn, 'webrip')) {
        return 54; // Movies/WEB-DL
    }

    if (str_contains($tn, '1080p') || str_contains($tn, '720p')) {
        return 11; // Movies: HD
    }

    return 49; // Diverse
}
}
