<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TorrentMetadataService
{
    public function detectTypeFromName(string $name): string
    {
        $name = strtolower($name);

        if (preg_match('/\bs\d{1,2}e\d{1,2}\b/', $name)) return 'tv';
        if (preg_match('/\b\d{1,2}x\d{1,2}\b/', $name)) return 'tv';
        if (preg_match('/\bs\d{1,2}\b/', $name)) return 'tv';
        if (preg_match('/\bseason\s?\d{1,2}\b/', $name)) return 'tv';
        if (preg_match('/\b(ep|episode)\s?\d{1,3}\b/', $name)) return 'tv';
        if (preg_match('/season\s?\d/i', $name)) return 'tv';
        if (preg_match('/complete\s?(series|collection)/i', $name)) return 'tv';

        return 'movie';
    }

    public function cleanNameAndExtractData(string $torrentName): array
    {
        $clean = preg_replace('/\.(mkv|mp4|avi|mov|nfo)$/i', '', $torrentName);

        $clean = preg_replace('/\b(?:DDP?|DTS(?:-HD)?(?:\sMA)?|AAC|TrueHD)(?:\+)?\s?\d(?:[\.\s]\d)?\b/i','',$clean);
        $clean = preg_replace('/\bAtmos\b/i','',$clean);
        $clean = preg_replace('/\b(2160p|1080p|720p|480p|WEB[- ]DL|WEB-DL|WEBRip|HDRip|HDTV|BluRay|BDRip|REMUX|DVD|DTS|HDDVD|x264|x265|AAC|HQ|SDR|RoSubbed|BLOOM|CONDITION|PLEX|-HD|-MA|E-AC3-|HDR10|5|1|S|h\.?264|h\.?265|h[\.\s]?264|h[\.\s]?265|hevc|avc|proper|repack|8bit|10bit|12bit)\b/i','',$clean);
        $clean = preg_replace('/\b(English|TELESYNC|WEB)\b/i','',$clean);
        $clean = preg_replace('/\b(AMZN|NF|NETFLIX|HULU|DSNP|HBO|MAX|Ghost|QxR|VOYO|Dual|Panda|Msubs|GTM|HMAX|2CH)\b/i','',$clean);
        $clean = preg_replace('/\b\d{1,2}\s?\d{2}\s?[AP]\s?M\b/i','',$clean);

        $clean = str_replace(['.', '_'], ' ', $clean);

        preg_match('/\b(19|20)\d{2}\b/', $clean, $yearMatch);
        $year = $yearMatch[0] ?? null;

        if ($year) {
            $clean = preg_replace('/\b'.$year.'\b/', '', $clean);
        }

        $season = null;
        $episode = null;

        if (preg_match('/\bS(\d{1,2})E(\d{1,2})\b/i', $clean, $match)) {
            $season = (int)$match[1];
            $episode = (int)$match[2];
            $clean = preg_replace('/\bS\d{1,2}E\d{1,2}\b/i','',$clean);
        }

        if (preg_match('/\b(\d{1,2})x(\d{1,2})\b/i', $clean, $match)) {
            $season = (int)$match[1];
            $episode = (int)$match[2];
            $clean = preg_replace('/\b\d{1,2}x\d{1,2}\b/i','',$clean);
        }

        if (preg_match('/\bS(\d{1,2})\b/i', $clean, $match)) {
            $season = (int)$match[1];
            $clean = preg_replace('/\bS\d{1,2}\b/i','',$clean);
        }

        $clean = preg_replace('/-\w+$/', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        
        // dd($clean, $year, $season, $episode);

        return [
            'clean' => $clean,
            'year' => $year,
            'season' => $season,
            'episode' => $episode
        ];
    }

    public function fetchTmdb(string $clean, ?string $year, string $type): array
    {
        try {
            $tmdbKey = env('TMDB_API_KEY');
            $endpoint = $type === 'tv' ? 'tv' : 'movie';

            $params = [
                'api_key' => $tmdbKey,
                'query'   => $clean,
            ];

            if ($type === 'movie' && $year) {
                $params['year'] = $year;
            }

            $results = Http::get(
                "https://api.themoviedb.org/3/search/{$endpoint}",
                $params
            )->json('results');

            if (empty($results)) {
                return [null,null,null];
            }

            $tmdbId = $results[0]['id'];

            $details = Http::get(
                "https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}",
                ['api_key'=>$tmdbKey]
            )->json();

            $external = Http::get(
                "https://api.themoviedb.org/3/{$endpoint}/{$tmdbId}/external_ids",
                ['api_key'=>$tmdbKey]
            )->json();

            $imdbId = $external['imdb_id'] ?? null;
            $imdbLink = $imdbId ? "https://www.imdb.com/title/{$imdbId}" : null;

            $desc = '';

            if (!empty($details['poster_path'])) {
                $desc .= "[img]https://image.tmdb.org/t/p/w342{$details['poster_path']}[/img]\n";
            }

            $desc .= '**Title:** '.($details['title'] ?? $details['name'])."\n";

            if (!empty($details['overview'])) {
                $desc .= "**Plot:** {$details['overview']}\n";
            }

            return [$imdbId,$imdbLink,$desc];

        } catch (\Throwable $e) {
            return [null,null,null];
        }
    }

   public function detectCategory(string $torrentName, string $type, ?string $year = null): int
{
    $tn = strtolower($torrentName);

    $isRo = str_contains($tn, '-ro') || str_contains($tn, ' ro ');
    $isPack = str_contains($tn, 'complete') || str_contains($tn, 'season') && !preg_match('/s\d+e\d+/i', $tn);
    $isAnime = str_contains($tn, 'anime');
    $isDocumentary = str_contains($tn, 'documentary') || str_contains($tn, 'docu');

    /* =====================================================
       📺 TV CATEGORIES
    ===================================================== */

    if ($type === 'tv') {
        return $isRo ? 21 : 20; // TV Episodes / TV Episodes-Ro
    }

    /* =====================================================
       🎬 MOVIE CATEGORIES
    ===================================================== */

  
    if ($isAnime) {
        return $isRo ? 2 : 1;
    }

    
    if ($isDocumentary) {
        return $isRo ? 57 : 56;
    }

  
    if ($isPack) {
        return $isRo ? 19 : 18;
    }


    if (str_contains($tn, '2160p') || str_contains($tn, '4k')) {
        return $isRo ? 32 : 31;
    }

    
    if (str_contains($tn, 'x265') || str_contains($tn, 'hevc')) {
        return $isRo ? 81 : 82;
    }

  
    if (str_contains($tn, 'bluray') || str_contains($tn, 'bdrip')) {
        return $isRo ? 6 : 5;
    }

    
    if (str_contains($tn, 'dvd')) {
        return $isRo ? 10 : 9;
    }

   
    if (str_contains($tn, 'xvid')) {
        return $isRo ? 25 : 24;
    }

   
    if (str_contains($tn, 'web-dl') || str_contains($tn, 'webrip')) {
        return $isRo ? 55 : 54;
    }

 
    if ($year && (int)$year < 2000) {
        return $isRo ? 17 : 16;
    }

 
    if (str_contains($tn, '1080p') || str_contains($tn, '720p')) {
        return $isRo ? 12 : 11;
    }

    
    return 49; // Diverse
}
}