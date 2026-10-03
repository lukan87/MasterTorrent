<?php

namespace App\Services;

use App\Services\Torrent\MetadataHttpCache;

class FanartService
{
    public function getMovieArt($tmdbId): array
    {
        return $this->art('movies', $tmdbId);
    }

    public function getTvArt($tvdbId): array
    {
        return $this->art('tv', $tvdbId);
    }

    private function art(string $type, $id): array
    {
        $key = config('services.fanart.key');
        if (! $key || ! $id) {
            return [];
        }

        return app(MetadataHttpCache::class)->get(
            "fanart:{$type}:{$id}",
            "https://webservice.fanart.tv/v3/{$type}/{$id}",
            ['api_key' => $key],
            fn (array $data) => ! isset($data['error'])
        ) ?? [];
    }
}
