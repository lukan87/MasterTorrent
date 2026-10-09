<?php

namespace App\Services;

use App\Models\Torrent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LibraryBrowseService
{
    private Collection $health;

    public function paginate(Builder $titles, string $type): LengthAwarePaginator
    {
        $request = request();
        $table = $titles->getModel()->getTable();
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'between:1800,2200'],
            'availability' => ['nullable', 'in:all,seeded,online,torrents'],
            'sort' => ['nullable', 'in:latest,title,rating,year'],
        ]);

        if ($request->filled('q')) {
            $titles->where('title', 'like', '%'.$request->input('q').'%');
        }
        if ($request->filled('year')) {
            $titles->where('year', $request->integer('year'));
        }
        if ($request->input('availability') === 'seeded') {
            $titles->whereExists(Torrent::query()
                ->selectRaw('1')
                ->whereColumn('torrents.tmdbid', $table.'.tmdbid')
                ->where('tmdb_type', $type)
                ->where('seeders', '>', 0)->toBase());
        }

        if (in_array($request->input('availability'), ['online', 'torrents'], true)) {
            if ($request->input('availability') === 'online') {
                $onlineTable = $type === 'movie' ? 'movies' : 'series';
                $titles->whereExists(function ($query) use ($onlineTable, $table) {
                    $query->selectRaw('1')->from($onlineTable)
                        ->whereColumn($onlineTable.'.tmdb_id', $table.'.tmdbid')
                        ->where('online_enabled', true)->whereNotNull('imdb_id')->where('imdb_id', '!=', '');
                });
            } else {
                $titles->whereExists(Torrent::query()->selectRaw('1')
                    ->whereColumn('torrents.tmdbid', $table.'.tmdbid')->where('tmdb_type', $type)->toBase());
            }
        }

        [$column, $direction] = match ($request->input('sort', 'latest')) {
            'title' => ['title', 'asc'],
            'rating' => ['rating', 'desc'],
            'year' => ['year', 'desc'],
            default => ['created_at', 'desc'],
        };
        $results = $titles->orderBy($column, $direction)->orderByDesc('id')
            ->paginate(24)->withQueryString();

        // Updates need page availability only; the initial hero shares the full map.
        $partial = $this->isPartial();
        $health = $partial && $results->isEmpty() ? collect() : Torrent::query()
            ->where('tmdb_type', $type)
            ->whereNotNull('tmdbid')
            ->when($partial, fn ($query) => $query->whereIn('tmdbid', $results->pluck('tmdbid')))
            ->select('tmdbid', DB::raw('MAX(seeders) as max_seeders'))
            ->groupBy('tmdbid')->get()->keyBy(fn ($torrent) => (int) $torrent->tmdbid);
        $this->health = $health;
        $results->getCollection()->each(function ($title) use ($health) {
            $title->poster = $title->poster_path;
            $title->background = $title->backdrop_path;
            $title->max_seeders = $health->get((int) $title->tmdbid)?->max_seeders ?? 0;
        });

        return $results;
    }

    public function seederHealth(): Collection
    {
        return $this->health;
    }

    public function isPartial(): bool
    {
        return request()->header('X-Library-Browse') === '1' && request()->expectsJson();
    }

    public function response(string $view, array $data)
    {
        return response()->json(['html' => view($view, $data)->render()])
            ->header('Cache-Control', 'private, no-store')
            ->header('Vary', 'Accept, X-Library-Browse');
    }
}
