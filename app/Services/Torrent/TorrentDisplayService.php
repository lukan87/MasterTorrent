<?php

namespace App\Services\Torrent;

use App\Helpers\MediaInfo;
use App\Helpers\TorrentHelper;
use App\Models\Torrent;
use App\Services\FanartService;
use App\Services\MediaDisplayService;
use App\Services\SteamService;

class TorrentDisplayService
{
    public function getDisplayData(Torrent $torrent): array
    {
        return [
            'mediainfo' => $this->getMediaInfo($torrent),
            'display' => $this->getTmdbDisplay($torrent),
            'steamData' => $this->getSteamData($torrent),
            'fileTree' => $this->getFileTree($torrent),
        ];
    }

    protected function getMediaInfo(Torrent $torrent)
    {
        if (! $torrent->mediainfo) {
            return null;
        }

        return (new MediaInfo)->parse($torrent->mediainfo);
    }

    protected function getTmdbDisplay(Torrent $torrent)
    {
        if (! $torrent->tmdbid) {
            return null;
        }

        $type = $torrent->tmdb_type;
        if (! in_array($type, ['movie', 'tv'], true)) {
            return null;
        }

        // Provider responses are cached independently; collection availability stays live.
        $display = app(MediaDisplayService::class)->getDisplayPayload(
            $torrent->tmdbid, $type, $torrent->imdbid
        );
        if ($display === null) {
            return null;
        }
        $display['fanart'] = $this->getFanart($torrent, $type, $display['external_ids']['tvdb_id'] ?? null);

        return $display;
    }

    protected function getFanart(Torrent $torrent, string $type, $tvdbId = null): array
    {
        // ✅ 1. Use DB first (FAST)
        if ($torrent->fanart_background || $torrent->fanart_poster) {
            return [
                'background' => $torrent->fanart_background,
                'poster' => $torrent->fanart_poster,
                'logo' => $torrent->fanart_logo,
                'banner' => $torrent->fanart_banner,
            ];
        }

        // ✅ 2. Fallback to API (cached)
        $fanartService = app(FanartService::class);

        try {
            $tvdbId = $torrent->tvdbid ?: $tvdbId;
            if ($type === 'tv' && $tvdbId) {

                $data = $fanartService->getTvArt($tvdbId);

                return [
                    'background' => $data['showbackground'][0]['url'] ?? null,
                    'poster' => $data['tvposter'][0]['url'] ?? null,
                    'logo' => $data['hdtvlogo'][0]['url'] ?? null,
                    'banner' => $data['tvbanner'][0]['url'] ?? null,
                ];

            } elseif ($type === 'movie' && $torrent->tmdbid) {

                $data = $fanartService->getMovieArt($torrent->tmdbid);

                return [
                    'background' => $data['moviebackground'][0]['url'] ?? null,
                    'poster' => $data['movieposter'][0]['url'] ?? null,
                    'logo' => $data['hdmovielogo'][0]['url'] ?? null,
                    'banner' => $data['moviebanner'][0]['url'] ?? null,
                ];
            }

        } catch (\Throwable $e) {
            // silently fail (never break page)
        }

        // ✅ 3. Always return structure (NO crashes)
        return [
            'background' => null,
            'poster' => null,
            'logo' => null,
            'banner' => null,
        ];
    }

    protected function getSteamData(Torrent $torrent)
    {
        if (! $torrent->steamid) {
            return null;
        }

        return app(SteamService::class)->fetchSteamData($torrent->steamid);
    }

    protected function getFileTree(Torrent $torrent)
    {
        return TorrentHelper::buildFileTree($torrent->files);
    }
}
