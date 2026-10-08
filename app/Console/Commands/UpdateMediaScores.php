<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\Series;
use App\Services\MediaRatingService;
use App\Services\MediaRecommendationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateMediaScores extends Command
{
    protected $signature = 'media:update-scores
        {--type=all : all, movies, or series}
        {--limit=0 : Maximum titles to process across both types; 0 processes all}
        {--days=7 : Refresh ratings older than this many days}
        {--force : Refresh ratings even when recently updated}
        {--delay=250 : Milliseconds between provider requests}
        {--cached : Import existing OMDb cache entries without provider requests}
        {--recalculate : Recalculate scores from stored IMDb ratings without provider requests}';

    protected $description = 'Refresh library IMDb ratings, votes, and vote-weighted recommendation scores';

    public function handle(MediaRatingService $ratings, MediaRecommendationService $recommendations): int
    {
        $type = $this->option('type');
        foreach (['limit', 'days', 'delay'] as $option) {
            if (!ctype_digit((string) $this->option($option))) {
                $this->error("--{$option} must be a non-negative integer.");
                return self::FAILURE;
            }
        }
        if (!in_array($type, ['all', 'movies', 'series'], true) || ($this->option('cached') && $this->option('recalculate'))) {
            $this->error('Choose --type=all, movies, or series; --cached and --recalculate cannot be combined.');
            return self::FAILURE;
        }
        $offline = $this->option('cached') || $this->option('recalculate');
        if (!$offline && !$ratings->apiKey()) {
            $this->error('Configure OMDB_API_KEY before refreshing IMDb ratings.');
            return self::FAILURE;
        }
        $classes = $type === 'movies' ? [Movie::class] : ($type === 'series' ? [Series::class] : [Movie::class, Series::class]);
        $processed = $updated = $skipped = $failed = 0;
        $limit = (int) $this->option('limit');
        foreach ($classes as $class) {
            $query = $class::query();
            if (!$offline && !$this->option('force')) {
                $query->where(fn ($query) => $query->whereNull('ratings_updated_at')
                    ->orWhere('ratings_updated_at', '<=', now()->subDays((int) $this->option('days'))));
            }
            foreach ($query->lazyById(100) as $title) {
                if ($limit > 0 && $processed >= $limit) {
                    break 2;
                }
                $processed++;
                if ($this->option('recalculate')) {
                    $title->forceFill(['recommendation_score' => $recommendations->score((float) $title->imdb_rating, (int) $title->imdb_votes)])->saveQuietly();
                    $result = 'updated';
                } elseif ($this->option('cached')) {
                    $data = Cache::get($ratings->cacheKey($title));
                    $result = is_array($data) && $ratings->store($title, $data, false) ? 'updated' : 'skipped';
                } else {
                    $result = $ratings->refresh($title);
                }
                if ($result === 'blocked') {
                    $this->error('OMDb rejected the API key or reported a quota limit. Stopped; existing ratings were preserved.');
                    return self::FAILURE;
                }
                if ($result === 'updated') {
                    $updated++;
                } elseif ($result === 'skipped') {
                    $skipped++;
                } else {
                    $failed++;
                    $this->warn("Could not refresh {$title->getTable()} #{$title->id}; existing ratings were preserved.");
                }
                if (!$offline && (int) $this->option('delay') > 0) {
                    usleep((int) $this->option('delay') * 1000);
                }
            }
        }
        $this->info("Processed {$processed}: updated {$updated}, skipped {$skipped}, failed {$failed}.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
