<?php


namespace App\Services\Torrent;

use Illuminate\Support\Facades\Http;

class TorrentSteamService
{
    public function fetch(string $steamId): ?array
    {
        $response = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $steamId,
            'lang'   => 'en',
        ]);

        $data = $response->json();

        if (
            !isset($data[$steamId]['success']) ||
            $data[$steamId]['success'] !== true
        ) {
            return null;
        }

        return $data[$steamId]['data'] ?? null;
    }

    public function normalize(array $steamData): array
    {
        return [
            'name'        => $steamData['name'] ?? null,
            'description' => $steamData['short_description'] ?? null,
            'poster'      => $steamData['header_image'] ?? null,
            'background'  => $steamData['background_raw']
                ?? $steamData['background']
                ?? null,
        ];
    }
}
