<?php

namespace App\Jobs;

use App\Services\FanartService;
use App\Services\OMDBService;
use App\Services\SteamService;
use App\Services\TMDBService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

/** Queue identifiers only: provider credentials are resolved by the worker. */
class RefreshTorrentMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 15;

    public int $tries = 1;

    public function __construct(public string $key) {}

    public function handle(): void
    {
        try {
            if (preg_match('/^tmdb:images:(movie|tv):([1-9][0-9]*)$/', $this->key, $match)) {
                app(TMDBService::class)->fetchTMDBData((int) $match[2], $match[1]);
            } elseif (preg_match('/^collection:([1-9][0-9]*)$/', $this->key, $match)) {
                app(TMDBService::class)->fetchCollection((int) $match[1]);
            } elseif (preg_match('/^omdb:(tt[0-9]+)$/', $this->key, $match)) {
                app(OMDBService::class)->fetchOMDBData($match[1]);
            } elseif (preg_match('/^steam:([1-9][0-9]*)$/', $this->key, $match)) {
                app(SteamService::class)->fetchSteamData($match[1]);
            } elseif (preg_match('/^fanart:(movies|tv):([1-9][0-9]*)$/', $this->key, $match)) {
                $service = app(FanartService::class);
                $match[1] === 'movies'
                    ? $service->getMovieArt($match[2])
                    : $service->getTvArt($match[2]);
            }
        } finally {
            Cache::forget('torrent_metadata_v3:'.$this->key.':pending');
        }
    }
}
