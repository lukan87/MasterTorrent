<?php

namespace App\Services;

use App\Services\Torrent\MetadataHttpCache;

class OMDBService
{
    public function fetchOMDBData(string $imdbId): ?array
    {
        if (! preg_match('/^tt[0-9]+$/', $imdbId)) {
            return null;
        }

        return app(MetadataHttpCache::class)->get(
            "omdb:{$imdbId}",
            'https://www.omdbapi.com/',
            ['apikey' => env('OMDB_API_KEY', 'd3eb5201'), 'i' => $imdbId, 'plot' => 'full', 'r' => 'json'],
            fn (array $data) => ($data['Response'] ?? null) === 'True'
        );
    }

    public function fetchOMDBDataByTitle(string $title): ?array
    {
        $title = trim($title);
        if ($title === '') {
            return null;
        }

        return app(MetadataHttpCache::class)->get(
            'omdb:title:'.md5($title),
            'https://www.omdbapi.com/',
            ['apikey' => env('OMDB_API_KEY', 'd3eb5201'), 't' => $title, 'plot' => 'full', 'r' => 'json'],
            fn (array $data) => ($data['Response'] ?? null) === 'True'
        );
    }
}
