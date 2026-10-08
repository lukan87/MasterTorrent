<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Support\Collection;

class MediaRecommendationService
{
    /** A small vote sample stays close to the prior instead of dominating the list. */
    public function score(float $rating, int $votes): float
    {
        if ($rating <= 0 || $rating > 10 || $votes <= 0) {
            return 0;
        }

        return round(($votes * $rating + 1000 * 6.5) / ($votes + 1000), 4);
    }

    public function genres(array $genres): array
    {
        return collect($genres)->map(fn ($genre) => is_array($genre) ? ($genre['name'] ?? '') : $genre)
            ->filter(fn ($genre) => is_string($genre) && trim($genre) !== '')
            ->map(fn ($genre) => trim($genre))->unique()->values()->all();
    }

    public function forTitle(Movie|Series $title, array $fallbackGenres = [], int $limit = 10): Collection
    {
        $isMovie = $title instanceof Movie;
        $dateColumn = $isMovie ? 'release_date' : 'first_air_date';
        $genres = $this->genres($title->genres ?: $fallbackGenres);
        $query = $title->newQuery()->where('id', '<>', $title->id)
            ->select(['id', 'tmdb_id', 'slug', 'name', 'poster_path', $dateColumn, 'imdb_rating', 'imdb_votes', 'recommendation_score']);

        if ($genres !== []) {
            $query->where(function ($query) use ($genres) {
                foreach ($genres as $genre) {
                    $query->orWhereJsonContains('genres', $genre);
                }
            });
            $matches = implode(' + ', array_fill(0, count($genres), 'CASE WHEN JSON_CONTAINS(genres, ?) THEN 1 ELSE 0 END'));
            $query->selectRaw("({$matches}) AS genre_matches", array_map(fn ($genre) => json_encode($genre), $genres))
                ->orderByDesc('genre_matches');
        }

        return $query->orderByDesc('recommendation_score')->orderByDesc('imdb_votes')->orderByDesc('id')
            ->limit(max(1, min($limit, 50)))->get()->map(fn ($item) => [
                'tmdb_id' => $item->tmdb_id,
                'name' => $item->name,
                'poster' => $item->poster_path ? 'https://image.tmdb.org/t/p/w500'.$item->poster_path : null,
                'year' => $item->{$dateColumn}?->format('Y'),
                'rating' => $item->imdb_rating ? number_format($item->imdb_rating, 1) : null,
                'votes' => $item->imdb_votes,
                'in_library' => true,
                'db_url' => route($isMovie ? 'movies.show' : 'series.show', [$item->id, $item->slug]),
            ]);
    }
}
