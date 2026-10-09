<?php

namespace App\Services\Torrent;

use App\Models\Movie;
use App\Models\Series;
use App\Models\Torrent;
use App\Services\OMDBService;
use App\Services\TMDBService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Small, cached hover previews: provider requests never block page rendering. */
class TorrentPreviewService
{
    private array $media = [];

    public function decorate(Collection $torrents): void
    {
        foreach (['movie' => Movie::class, 'tv' => Series::class] as $type => $model) {
            $ids = $torrents->filter(fn ($torrent) => $torrent->tmdb_type === $type && $torrent->tmdbid > 0)
                ->pluck('tmdbid')->unique();
            $local = $ids->isEmpty() ? collect() : $model::whereIn('tmdb_id', $ids)->get()->keyBy('tmdb_id');
            foreach ($ids as $id) {
                $key = $type.':'.$id;
                if (array_key_exists($key, $this->media)) continue;
                $saved = $local->get($id);
                $torrent = $torrents->first(fn ($torrent) => $torrent->tmdb_type === $type && (int) $torrent->tmdbid === (int) $id);
                $this->media[$key] = app(MetadataHttpCache::class)->defer(function () use ($id, $type, $saved, $torrent) {
                    $tmdb = app(TMDBService::class)->fetchTMDBData((int) $id, $type) ?? [];
                    $imdbId = collect([$saved?->imdb_id, $tmdb['external_ids']['imdb_id'] ?? null, $tmdb['imdb_id'] ?? null, $torrent?->imdbid])
                        ->first(fn ($id) => is_string($id) && preg_match('/^tt[0-9]+$/', $id));
                    $omdb = $imdbId ? app(OMDBService::class)->fetchOMDBData($imdbId) : null;
                    $date = $type === 'movie' ? ($tmdb['release_date'] ?? $saved?->release_date) : ($tmdb['first_air_date'] ?? $saved?->first_air_date);
                    $cast = collect($tmdb['credits']['cast'] ?? [])->pluck('name')->filter()->take(4)->all();
                    if (! $cast && ! empty($omdb['Actors']) && $omdb['Actors'] !== 'N/A') {
                        $cast = array_slice(array_map('trim', explode(',', $omdb['Actors'])), 0, 4);
                    }
                    return [
                        'title' => $tmdb[$type === 'movie' ? 'title' : 'name'] ?? $saved?->name,
                        'year' => $date ? substr((string) $date, 0, 4) : null,
                        'poster' => $this->poster($tmdb['poster_path'] ?? $saved?->poster_path, true),
                        'overview' => Str::limit(strip_tags($tmdb['overview'] ?? $saved?->overview ?? ''), 210),
                        'imdb' => $this->rating($saved?->imdb_rating) ?? $this->rating($omdb['imdbRating'] ?? null),
                        'tmdb' => $this->rating($tmdb['vote_average'] ?? $saved?->vote_average),
                        'cast' => $cast,
                    ];
                });
            }
        }
        foreach ($torrents as $torrent) {
            $media = $this->media[$torrent->tmdb_type.':'.$torrent->tmdbid] ?? [];
            $torrent->featured_preview = $media + ['title' => null, 'year' => null, 'overview' => '', 'imdb' => null, 'tmdb' => null, 'cast' => []];
            $torrent->featured_preview = array_replace($torrent->featured_preview, [
                'poster' => $media['poster'] ?? $this->poster($torrent->poster) ?? asset('images/noposter.jpg'),
            ]);
        }
    }

    private function rating(mixed $value): ?string
    {
        return is_numeric($value) && $value > 0 && $value <= 10 ? number_format((float) $value, 1) : null;
    }

    private function poster(?string $path, bool $tmdb = false): ?string
    {
        if (! $path) return null;
        if ($tmdb && preg_match('~^/[a-zA-Z0-9_.-]+\.(?:jpg|png|webp)$~i', $path)) {
            return 'https://image.tmdb.org/t/p/w185'.$path;
        }
        if (str_starts_with($path, '/') && ! str_starts_with($path, '//')) return $path;
        return filter_var($path, FILTER_VALIDATE_URL) && in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true) ? $path : null;
    }
}
