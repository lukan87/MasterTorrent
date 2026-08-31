<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Helpers\MediaInfo;
use App\Services\TMDBService;
use Illuminate\Support\Facades\Cache;

class TorrentDisplayService
{
    public function getDisplayData(Torrent $torrent): array
    {
        return [
            'mediainfo' => $this->getMediaInfo($torrent),
            'display'   => $this->getTmdbDisplay($torrent),
            'steamData' => $this->getSteamData($torrent),
            'fileTree'  => $this->getFileTree($torrent),
        ];
    }

    protected function getMediaInfo(Torrent $torrent)
    {
        if (!$torrent->mediainfo) {
            return null;
        }

        return (new MediaInfo())->parse($torrent->mediainfo);
    }

protected function getTmdbDisplay(Torrent $torrent)
{
    if (!$torrent->tmdbid) {
        return null;
    }

    $display = Cache::remember(
        "torrent_display_{$torrent->tmdbid}",
        now()->addMinutes(10),
        fn () => (new TMDBService())->getDisplayPayload(
            $torrent->tmdbid,
            $torrent->tmdb_type,
            $torrent->imdbid
        )
    );

    // 🔥 Merge Fanart
    $display['fanart'] = $this->getFanart($torrent, $display['type'] ?? 'movie');

    return $display;
}

protected function getFanart(Torrent $torrent, string $type): array
{
    // ✅ 1. Use DB first (FAST)
    if ($torrent->fanart_background || $torrent->fanart_poster) {
        return [
            'background' => $torrent->fanart_background,
            'poster'     => $torrent->fanart_poster,
            'logo'       => $torrent->fanart_logo,
            'banner'     => $torrent->fanart_banner,
        ];
    }

    // ✅ 2. Fallback to API (cached)
    $fanartService = app(\App\Services\FanartService::class);

    try {
        if ($type === 'tv' && $torrent->tvdbid) {

            $data = $fanartService->getTvArt($torrent->tvdbid);

            return [
                'background' => $data['showbackground'][0]['url'] ?? null,
                'poster'     => $data['tvposter'][0]['url'] ?? null,
                'logo'       => $data['hdtvlogo'][0]['url'] ?? null,
                'banner'     => $data['tvbanner'][0]['url'] ?? null,
            ];

        } elseif ($torrent->tmdbid) {

            $data = $fanartService->getMovieArt($torrent->tmdbid);

            return [
                'background' => $data['moviebackground'][0]['url'] ?? null,
                'poster'     => $data['movieposter'][0]['url'] ?? null,
                'logo'       => $data['hdmovielogo'][0]['url'] ?? null,
                'banner'     => $data['moviebanner'][0]['url'] ?? null,
            ];
        }

    } catch (\Throwable $e) {
        // silently fail (never break page)
    }

    // ✅ 3. Always return structure (NO crashes)
    return [
        'background' => null,
        'poster'     => null,
        'logo'       => null,
        'banner'     => null,
    ];
}

    protected function getSteamData(Torrent $torrent)
    {
        if (!$torrent->steamid) {
            return null;
        }

        return Cache::remember(
            "torrent_steam_{$torrent->steamid}",
            now()->addDay(),
            fn () => (new TMDBService())->fetchSteamData($torrent->steamid)
        );
    }

    protected function getFileTree(Torrent $torrent)
    {
        return \App\Helpers\TorrentHelper::buildFileTree($torrent->files);
    }
}
