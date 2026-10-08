<?php

namespace Tests\Feature;

use App\Services\TvCalendarService;
use App\Services\TvmazeService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class TvmazeServiceTest extends TestCase
{
    private Container $previous;

    protected function setUp(): void
    {
        $this->previous = Container::getInstance();
        $app = new Container;
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();
        Cache::swap(new Repository(new ArrayStore));
        Http::swap(new Factory);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previous);
        Container::setInstance($this->previous);
    }

    private function episode(int $id = 1, ?string $country = null): array
    {
        return ['id' => $id, 'name' => 'Pilot', 'airdate' => '2026-10-06', 'airtime' => '20:00', 'season' => 1, 'number' => 1,
            'show' => ['id' => 10, 'name' => 'Example', 'genres' => ['Drama'], 'externals' => ['imdb' => 'tt123'],
                'summary' => '<p>Summary &amp; story</p>', 'webChannel' => ['name' => 'Streamer', 'country' => $country ? ['code' => $country] : null]]];
    }

    public function test_combines_global_streaming_deduplicates_and_excludes_other_countries(): void
    {
        $web = $this->episode(2);
        $web['_embedded']['show'] = $web['show'];
        unset($web['show']);
        Http::fake([
            'api.tvmaze.com/schedule?*' => Http::response([$this->episode()]),
            'api.tvmaze.com/schedule/web?*' => Http::response([$this->episode(), $web, $this->episode(3, 'GB')]),
        ]);
        $result = (new TvmazeService)->schedule(CarbonImmutable::parse('2026-10-06'), 1, 'US', 'all');
        self::assertSame([1, 2], array_column($result['episodes'], 'id'));
        self::assertSame('Summary & story', $result['episodes'][0]['summary']);
        self::assertSame([], $result['warnings']);
        Http::assertSentCount(2);
    }

    public function test_successful_schedules_are_cached_including_empty_days(): void
    {
        Http::fake(['*' => Http::response([])]);
        $service = new TvmazeService;
        for ($i = 0; $i < 2; $i++) {
            self::assertSame([], $service->schedule(CarbonImmutable::parse('2026-10-06'), 7, 'US', 'all')['episodes']);
        }
        Http::assertSentCount(14);
    }

    public function test_rate_limit_uses_stale_data_without_caching_failures(): void
    {
        Cache::put('tvmaze:stale:broadcast:US:2026-10-06', [$this->episode()], 600);
        Http::fake(['*' => Http::response([], 429)]);
        $result = (new TvmazeService)->schedule(CarbonImmutable::parse('2026-10-06'), 1, 'US', 'broadcast');
        self::assertCount(1, $result['episodes']);
        self::assertStringContainsString('Saved schedule', $result['warnings'][0]);
        self::assertFalse(Cache::has('tvmaze:fresh:broadcast:US:2026-10-06'));
    }

    public function test_failed_connection_returns_an_unavailable_schedule(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $result = (new TvmazeService)->schedule(CarbonImmutable::parse('2026-10-06'), 1, 'US', 'broadcast');
        self::assertSame([], $result['episodes']);
        self::assertCount(1, $result['warnings']);
    }

    public function test_daily_and_unscripted_filter_uses_show_type_genres_and_weekly_frequency(): void
    {
        $service = new TvmazeService;
        foreach (['News', 'Talk Show', 'Reality', 'Game Show', 'Variety'] as $type) {
            self::assertTrue($service->isDailyTalkOrReality(['show_type' => $type]));
        }
        self::assertTrue($service->isDailyTalkOrReality(['show_type' => 'Scripted', 'schedule_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]));
        self::assertTrue($service->isDailyTalkOrReality(['genres' => ['Reality']]));
        self::assertFalse($service->isDailyTalkOrReality(['show_type' => 'Scripted', 'genres' => ['Drama'], 'schedule_days' => ['Wednesday']]));
        $row = $this->episode();
        $row['show']['type'] = 'Talk Show';
        $row['show']['schedule']['days'] = ['Monday', 'Tuesday'];
        $normalized = $service->normalize($row, $row['show']);
        self::assertSame('Talk Show', $normalized['show_type']);
        self::assertSame(['Monday', 'Tuesday'], $normalized['schedule_days']);
    }

    public function test_external_markup_and_untrusted_image_urls_are_not_rendered(): void
    {
        $episode = $this->episode();
        $episode['show']['image']['medium'] = 'javascript:alert(1)';
        $episode['show']['externals']['imdb'] = 'anything';
        $row = (new TvmazeService)->normalize($episode, $episode['show']);
        self::assertNull($row['image']);
        self::assertNull($row['imdbid']);
        self::assertSame('S01E01', $row['episode_code']);
    }

    public function test_episode_matching_does_not_claim_other_episodes_or_season_packs(): void
    {
        $service = new TvCalendarService;
        $episode = ['season' => 1, 'number' => 2];
        self::assertTrue($service->matchesEpisode('Example.S01E02.1080p', $episode));
        self::assertTrue($service->matchesEpisode('Example 1x02 720p', $episode));
        self::assertTrue($service->matchesEpisode('Example S01 E02', $episode));
        self::assertFalse($service->matchesEpisode('Example.S01E020.1080p', $episode));
        self::assertFalse($service->matchesEpisode('Example.S02E02.1080p', $episode));
        self::assertFalse($service->matchesEpisode('Example.S01.Complete', $episode));
        self::assertFalse($service->matchesEpisode('Example.S01E02', ['season' => 1, 'number' => null]));
    }
}
