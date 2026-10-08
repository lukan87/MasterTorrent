<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MediaRatingService
{
    public function __construct(private MediaRecommendationService $recommendations) {}

    public function apiKey(): ?string
    {
        return config('services.omdb.key') ?: env('OMDB_API_KEY');
    }

    public function cacheKey(Movie|Series $title): string
    {
        return ($title instanceof Movie ? 'movie_' : 'series_').$title->imdb_id.'_omdb';
    }

    public function refresh(Movie|Series $title): string
    {
        if (!preg_match('/^tt[0-9]+$/', (string) $title->imdb_id)) {
            return 'skipped';
        }
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)->get('https://www.omdbapi.com/', [
                'apikey' => $this->apiKey(), 'i' => $title->imdb_id, 'plot' => 'full', 'r' => 'json',
            ]);
            $data = $response->json();
            if (is_array($data) && preg_match('/limit|quota|api key/i', (string) ($data['Error'] ?? ''))) {
                return 'blocked';
            }
            if (!$response->successful() || !is_array($data) || ($data['Response'] ?? null) !== 'True') {
                return 'failed';
            }
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            return 'failed';
        }

        return $this->store($title, $data) ? 'updated' : 'skipped';
    }

    public function store(Movie|Series $title, array $data, bool $fresh = true): bool
    {
        $rating = $data['imdbRating'] ?? null;
        $votes = $data['imdbVotes'] ?? null;
        if (($data['Response'] ?? null) !== 'True'
            || (($data['imdbID'] ?? $title->imdb_id) !== $title->imdb_id)
            || !is_numeric($rating) || (float) $rating <= 0 || (float) $rating > 10
            || !preg_match('/^\d+(?:,\d{3})*$/', (string) $votes)) {
            return false;
        }
        $votes = (int) str_replace(',', '', (string) $votes);
        if ($votes <= 0) {
            return false;
        }
        $values = [
            'imdb_rating' => (float) $rating,
            'imdb_votes' => $votes,
            'recommendation_score' => $this->recommendations->score((float) $rating, $votes),
        ];
        if ($fresh) {
            $values['ratings_updated_at'] = now();
        }
        if (!$title->genres && !empty($data['Genre']) && $data['Genre'] !== 'N/A') {
            $values['genres'] = $this->recommendations->genres(explode(',', $data['Genre']));
        }
        $title->forceFill($values)->saveQuietly();
        if ($fresh) {
            Cache::put($this->cacheKey($title), $data, now()->addWeek());
        }
        if ($title instanceof Series) {
            Cache::forget('series_model_'.$title->slug);
        }

        return true;
    }
}
