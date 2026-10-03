<?php

namespace App\Services;

use App\Services\Torrent\MetadataHttpCache;

class SteamService
{
    public function fetchSteamData(string $steamId): ?array
    {
        if (! preg_match('/^[1-9][0-9]*$/', $steamId)) {
            return null;
        }

        return app(MetadataHttpCache::class)->get(
            "steam:{$steamId}",
            'https://store.steampowered.com/api/appdetails',
            ['appids' => $steamId, 'l' => 'english'],
            fn (array $data) => ($data[$steamId]['success'] ?? false) === true
                && ! empty($data[$steamId]['data']['name'])
        );
    }
}
