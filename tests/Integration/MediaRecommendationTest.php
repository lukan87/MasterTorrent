<?php

namespace Tests\Integration;

use App\Models\Movie;
use App\Models\Series;
use App\Services\MediaRatingService;
use App\Services\MediaRecommendationService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class MediaRecommendationTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('MEDIA_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set MEDIA_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['cache.default' => 'array', 'services.omdb.key' => 'fixture-key']);
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.media_test' => $connection]);
        DB::setDefaultConnection('media_test');
        foreach (['movies', 'series'] as $table) {
            $date = $table === 'movies' ? 'release_date' : 'first_air_date';
            DB::statement("CREATE TEMPORARY TABLE {$table} (id BIGINT PRIMARY KEY, name VARCHAR(255), slug VARCHAR(255), tmdb_id VARCHAR(255), imdb_id VARCHAR(255), poster_path VARCHAR(255), {$date} DATE NULL, genres JSON NULL, vote_average DECIMAL(4,1), vote_count INT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)");
        }
        // Exercise the real additive migration on session-local tables only.
        (require __DIR__.'/../../database/migrations/2026_10_07_180000_add_imdb_scores_to_media_tables.php')->up();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        if (getenv('MEDIA_MYSQL_TEST') === '1') {
            DB::disconnect('media_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    private function title(int $id, array $values = [], bool $series = false): Movie|Series
    {
        $table = $series ? 'series' : 'movies';
        $date = $series ? 'first_air_date' : 'release_date';
        DB::table($table)->insert($values + [
            'id' => $id, 'name' => 'Title '.$id, 'slug' => 'custom-slug-'.$id,
            'tmdb_id' => (string) $id, 'imdb_id' => 'tt'.$id, 'poster_path' => '/poster.jpg',
            $date => '1980-01-01', 'genres' => json_encode(['Drama']),
            'vote_average' => 4.2, 'vote_count' => 25,
            'imdb_rating' => 7.6, 'imdb_votes' => 33926,
            'recommendation_score' => app(MediaRecommendationService::class)->score(7.6, 33926),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $series ? Series::findOrFail($id) : Movie::findOrFail($id);
    }

    private function metadata(string $id = 'tt1'): array
    {
        return ['Response' => 'True', 'imdbID' => $id, 'imdbRating' => '8.1', 'imdbVotes' => '123,456', 'Genre' => 'Drama, Mystery'];
    }

    public function test_suggestions_are_local_share_genres_and_use_vote_weighting(): void
    {
        $service = app(MediaRecommendationService::class);
        $source = $this->title(1, ['genres' => json_encode(['Drama', 'Mystery'])]);
        $this->title(2, ['genres' => json_encode(['Drama', 'Mystery'])]);
        $this->title(3, ['imdb_rating' => 9.9, 'imdb_votes' => 5, 'recommendation_score' => $service->score(9.9, 5), 'genres' => json_encode(['Drama', 'Mystery'])]);
        $this->title(4, ['imdb_rating' => 9.5, 'imdb_votes' => 100000, 'recommendation_score' => $service->score(9.5, 100000)]);
        $this->title(5, ['genres' => json_encode(['Comedy']), 'recommendation_score' => 10]);
        $this->title(6, ['recommendation_score' => 10], true);
        $items = $service->forTitle($source);
        self::assertSame(['Title 2', 'Title 3', 'Title 4'], $items->pluck('name')->all());
        self::assertTrue($items->every(fn ($item) => $item['in_library'] && str_contains($item['db_url'], '/movies/')));
        self::assertSame('7.6', $items[0]['rating']);
        self::assertSame(33926, $items[0]['votes']);
        self::assertSame('1980', $items[0]['year']);
        Http::assertNothingSent();
    }

    public function test_series_and_missing_genres_have_database_only_fallbacks(): void
    {
        $source = $this->title(1, ['genres' => null], true);
        $this->title(2, [], true);
        $this->title(3, ['genres' => json_encode(['Comedy']), 'recommendation_score' => 9], true);
        $this->title(4, ['recommendation_score' => 10]);
        $service = app(MediaRecommendationService::class);
        self::assertSame(['Title 2'], $service->forTitle($source, [['name' => 'Drama']])->pluck('name')->all());
        self::assertSame(['Title 3', 'Title 2'], $service->forTitle($source)->pluck('name')->all());
        self::assertStringContainsString('/series/2/custom-slug-2', $service->forTitle($source)[1]['db_url']);
    }

    public function test_recommendations_are_bounded_and_empty_matching_sets_stay_empty(): void
    {
        $source = $this->title(1);
        self::assertCount(0, app(MediaRecommendationService::class)->forTitle($source));
        foreach (range(2, 16) as $id) {
            $this->title($id);
        }
        self::assertCount(10, app(MediaRecommendationService::class)->forTitle($source));
        self::assertCount(3, app(MediaRecommendationService::class)->forTitle($source, [], 3));
        $source->genres = ['Not in the library'];
        self::assertCount(0, app(MediaRecommendationService::class)->forTitle($source));
    }

    public function test_refresh_updates_imdb_fields_without_overwriting_tmdb_or_slug(): void
    {
        $title = $this->title(1, ['genres' => null], true);
        Cache::put('series_model_'.$title->slug, $title, 60);
        Http::fake(['www.omdbapi.com/*' => Http::response($this->metadata())]);
        self::assertSame('updated', app(MediaRatingService::class)->refresh($title));
        $title->refresh();
        self::assertSame(8.1, $title->imdb_rating);
        self::assertSame(123456, $title->imdb_votes);
        self::assertSame(['Drama', 'Mystery'], $title->genres);
        self::assertSame(4.2, $title->vote_average);
        self::assertEquals(25, $title->vote_count);
        self::assertSame('custom-slug-1', $title->slug);
        self::assertNotNull($title->ratings_updated_at);
        self::assertGreaterThan(8, $title->recommendation_score);
        self::assertFalse(Cache::has('series_model_'.$title->slug));
        self::assertSame($this->metadata(), Cache::get('series_tt1_omdb'));
        Http::assertSent(fn ($request) => $request['i'] === 'tt1');
    }

    public function test_missing_invalid_or_wrong_title_ratings_preserve_existing_scores(): void
    {
        $title = $this->title(1);
        $service = app(MediaRatingService::class);
        foreach ([['imdbRating' => 'N/A'], ['imdbVotes' => 'N/A'], ['imdbRating' => '11'], ['imdbVotes' => '0'], ['imdbVotes' => '1,23'], ['imdbID' => 'tt999'], ['Response' => 'False']] as $invalid) {
            self::assertFalse($service->store($title, $invalid + $this->metadata()));
        }
        $title->refresh();
        self::assertSame(7.6, $title->imdb_rating);
        self::assertSame(33926, $title->imdb_votes);
        self::assertNull($title->ratings_updated_at);
    }

    public function test_command_skips_fresh_scores_and_force_refreshes_them(): void
    {
        $this->title(1, ['ratings_updated_at' => now()]);
        $this->title(2, ['ratings_updated_at' => now()->subDays(8)]);
        Http::fake(fn ($request) => Http::response($this->metadata($request['i'])));
        self::assertSame(0, Artisan::call('media:update-scores', ['--delay' => '0']));
        Http::assertSentCount(1);
        self::assertSame(7.6, Movie::find(1)->imdb_rating);
        self::assertSame(8.1, Movie::find(2)->imdb_rating);
        self::assertSame(0, Artisan::call('media:update-scores', ['--force' => true, '--limit' => '1', '--delay' => '0']));
        Http::assertSentCount(2);
        self::assertSame(8.1, Movie::find(1)->imdb_rating);
    }

    public function test_cached_and_recalculate_commands_need_no_http_and_limit_across_types(): void
    {
        $this->title(1, ['recommendation_score' => 0]);
        $this->title(2, ['recommendation_score' => 0], true);
        Cache::put('movie_tt1_omdb', $this->metadata());
        Cache::put('series_tt2_omdb', $this->metadata('tt2'));
        self::assertSame(0, Artisan::call('media:update-scores', ['--cached' => true, '--limit' => '1']));
        self::assertSame(8.1, Movie::find(1)->imdb_rating);
        self::assertNull(Movie::find(1)->ratings_updated_at);
        self::assertSame(7.6, Series::find(2)->imdb_rating);
        self::assertSame(0, Artisan::call('media:update-scores', ['--type' => 'series', '--recalculate' => true]));
        self::assertGreaterThan(7, Series::find(2)->recommendation_score);
        Http::assertNothingSent();
    }

    public function test_command_batches_more_than_one_hundred_titles(): void
    {
        foreach (range(1, 103) as $id) {
            $this->title($id, ['recommendation_score' => 0]);
        }
        self::assertSame(0, Artisan::call('media:update-scores', ['--recalculate' => true, '--limit' => '101']));
        self::assertSame(101, Movie::where('recommendation_score', '>', 0)->count());
        self::assertSame(0.0, Movie::find(102)->recommendation_score);
        Http::assertNothingSent();
    }

    public function test_quota_exhaustion_stops_before_requesting_more_titles(): void
    {
        $this->title(1);
        $this->title(2);
        Http::fake(['www.omdbapi.com/*' => Http::response(['Response' => 'False', 'Error' => 'Request limit reached!'])]);
        self::assertSame(1, Artisan::call('media:update-scores', ['--delay' => '0']));
        Http::assertSentCount(1);
        self::assertSame(7.6, Movie::find(1)->imdb_rating);
        self::assertNull(Movie::find(1)->ratings_updated_at);
    }

    public function test_failed_responses_do_not_erase_ratings_and_options_are_validated(): void
    {
        $this->title(1);
        Http::fake(['www.omdbapi.com/*' => Http::response([], 503)]);
        self::assertSame(1, Artisan::call('media:update-scores', ['--delay' => '0']));
        self::assertSame(7.6, Movie::find(1)->imdb_rating);
        self::assertSame(1, Artisan::call('media:update-scores', ['--limit' => '-1']));
        self::assertSame(1, Artisan::call('media:update-scores', ['--type' => 'invalid']));
        self::assertSame(1, Artisan::call('media:update-scores', ['--cached' => true, '--recalculate' => true]));
        Http::assertSentCount(1);
    }

    public function test_score_downweights_tiny_samples_and_genre_normalization(): void
    {
        $service = new MediaRecommendationService;
        self::assertLessThan($service->score(8, 100000), $service->score(9.9, 5));
        self::assertSame(0.0, $service->score(0, 100));
        self::assertSame(0.0, $service->score(8, 0));
        self::assertSame(0.0, $service->score(11, 100));
        self::assertSame(['Drama', 'Mystery'], $service->genres([' Drama ', ['name' => 'Mystery'], 'Drama', null, ['id' => 1]]));
    }
}
