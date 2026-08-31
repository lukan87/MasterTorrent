<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FanartService
{
    public function getMovieArt($tmdbId)
    {
        return Cache::remember("fanart_movie_{$tmdbId}", 86400, function () use ($tmdbId) {

            $response = Http::get("https://webservice.fanart.tv/v3/movies/{$tmdbId}", [
                'api_key' => config('services.fanart.key'),
            ]);

            if (!$response->successful()) {
                return [];
            }

            return $response->json();
        });
    }
}