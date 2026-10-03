<?php

namespace App\Services\Torrent;

use App\Services\SteamService;

class TorrentSteamService
{
    public function fetch(string $steamId): ?array
    {
        $data = app(SteamService::class)->fetchSteamData($steamId);

        return $data[$steamId]['data'] ?? null;
    }

    public function normalize(array $steamData): array
    {
        return [
            'name' => $steamData['name'] ?? null,
            'description' => $steamData['short_description'] ?? null,
            'poster' => $steamData['header_image'] ?? null,
            'background' => $steamData['background_raw']
                ?? $steamData['background']
                ?? null,
        ];
    }
}
