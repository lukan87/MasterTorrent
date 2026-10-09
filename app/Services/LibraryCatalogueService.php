<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Series;
use App\Models\TorrentMovie;
use App\Models\TorrentSeries;
use Illuminate\Database\Eloquent\Builder;

class LibraryCatalogueService
{
    public function models(string $kind): array
    {
        abort_unless(in_array($kind, ['movies', 'series'], true), 404);
        return $kind === 'movies' ? [TorrentMovie::class, Movie::class] : [TorrentSeries::class, Series::class];
    }

    /** Include older online titles that have no corresponding library record, once per TMDB ID. */
    public function query(string $kind): Builder
    {
        [$library, $online] = $this->models($kind);
        $libraryTable = (new $library)->getTable();
        $onlineTable = (new $online)->getTable();
        $date = $kind === 'movies' ? 'release_date' : 'first_air_date';
        $columns = ['id', 'tmdbid', 'title', 'slug', 'poster_path', 'backdrop_path', 'rating', 'year', 'created_at'];
        $local = $library::query()->select($columns)->toBase();
        $extra = $online::query()->selectRaw("id, tmdb_id AS tmdbid, name AS title, slug, poster_path, backdrop_path, vote_average AS rating, YEAR({$date}) AS year, created_at")
            ->whereNotExists(function ($query) use ($libraryTable, $onlineTable) {
                $query->selectRaw('1')->from($libraryTable)->whereColumn($libraryTable.'.tmdbid', $onlineTable.'.tmdb_id');
            })->toBase();
        return $library::query()->fromSub($local->unionAll($extra), $libraryTable);
    }

    /** One metadata path for online, torrent-only, and library-only titles. */
    public function metadata(string $kind, int $tmdbid, $entry, $online, $torrent): array
    {
        $type = $kind === 'movies' ? 'movie' : 'tv';
        $date = $kind === 'movies' ? 'release_date' : 'first_air_date';
        $fallback = [
            $kind === 'movies' ? 'title' : 'name' => $entry?->title ?? $online?->name ?? ($kind === 'movies' ? 'Movie' : 'Series'),
            'poster_path' => $entry?->poster_path ?: ($online?->poster_path ?: null),
            'backdrop_path' => $entry?->backdrop_path ?: ($online?->backdrop_path ?: null),
            $date => $entry?->year ? $entry->year.'-01-01' : $online?->$date?->format('Y-m-d'),
            'overview' => $online?->overview,
            'vote_average' => $entry?->rating ?? $online?->vote_average,
            'genres' => collect($online?->genres ?? [])->map(fn ($name) => ['name' => $name])->all(),
        ];
        $movie = array_replace($fallback, app(TMDBService::class)->fetchTMDBData($tmdbid, $type)
            ?? cache()->get($kind === 'movies' ? 'tmdb_movie_v2_'.$tmdbid : 'tmdb_series_v2_'.$tmdbid, [])
            ?? []);
        $display = app(MediaDisplayService::class)->buildDisplayPayload($tmdbid, $type, $movie, $torrent?->imdbid ?? $online?->imdb_id, true);
        if ($entry) {
            $display['title'] = $entry->title;
            $display['year'] = $entry->year ?? $display['year'];
            foreach (['poster_path' => 'poster', 'backdrop_path' => 'backdrop'] as $field => $key) {
                if ($entry->$field) $display[$key] = 'https://image.tmdb.org/t/p/'.($key === 'poster' ? 'w500' : 'w1280').$entry->$field;
            }
        }
        if ($online && $online->overview) $display['overview'] = $online->overview;
        return [$movie, $display];
    }

    public function playable($media): bool
    {
        return $media && $media->online_enabled && preg_match('/^tt[0-9]+$/D', (string) $media->imdb_id);
    }
}
