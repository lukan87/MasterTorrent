<?php

namespace Tests\Integration;

use App\Services\TorrentMetadataService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class SeedboxMetadataLookupTest extends TestCase
{
    protected function setUp(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['services.tmdb.key' => 'configured-test-key']);
        Http::preventStrayRequests();
        Log::spy();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        restore_error_handler();
        restore_exception_handler();
    }

    private function fakeDetails(string $endpoint = 'movie'): void
    {
        Http::fake([
            "api.themoviedb.org/3/{$endpoint}/55/external_ids*" => Http::response(['imdb_id' => 'tt1234567']),
            "api.themoviedb.org/3/{$endpoint}/55*" => Http::response(['title' => 'Known Title', 'overview' => 'A plot', 'poster_path' => '/poster.jpg']),
        ]);
    }

    public function test_seedbox_filename_lookup_uses_the_configured_key_on_every_provider_request(): void
    {
        $this->fakeDetails();
        Http::fake(['api.themoviedb.org/3/search/movie*' => function ($request) {
            self::assertSame('Practical Magic 2', $request['query']);
            self::assertSame('2026', $request['year']);

            return Http::response(['results' => [['id' => 55, 'title' => 'Practical Magic 2', 'release_date' => '2026-09-10']]]);
        }]);
        $service = new TorrentMetadataService;
        $parsed = $service->cleanNameAndExtractData('Practical.Magic.2.2026.1080p.TELESYNC.x264-DKS.mkv');
        self::assertSame('https://www.imdb.com/title/tt1234567', $service->fetchTmdb($parsed['clean'], $parsed['year'], 'movie')[1]);
        Http::assertSentCount(3);
        foreach (Http::recorded() as [$request]) {
            self::assertSame('configured-test-key', $request['api_key']);
        }
    }

    public function test_empty_year_filtered_results_retry_without_year(): void
    {
        $this->fakeDetails();
        Http::fake(['api.themoviedb.org/3/search/movie*' => function ($request) {
            self::assertSame('Known Title', $request['query']);

            return Http::response(['results' => isset($request['year']) ? [] : [['id' => 55, 'title' => 'Known Title', 'release_date' => '2020-01-01']]]);
        }]);
        [$id, $link, $description] = (new TorrentMetadataService)->fetchTmdb('Known Title', '2021', 'movie');
        self::assertSame('tt1234567', $id);
        self::assertSame('https://www.imdb.com/title/tt1234567', $link);
        self::assertStringContainsString('Known Title', $description);
        Http::assertSentCount(4);
    }

    public function test_exact_title_is_preferred_over_a_more_popular_partial_match(): void
    {
        $this->fakeDetails();
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response(['results' => [
            ['id' => 999, 'title' => 'Known Title Returns'],
            ['id' => 55, 'title' => 'Known Title'],
        ]])]);
        self::assertSame('tt1234567', (new TorrentMetadataService)->fetchTmdb('Known Title', null, 'movie')[0]);
        Http::assertSentCount(3);
    }

    public function test_tv_search_uses_the_tv_endpoint_and_aired_year(): void
    {
        $this->fakeDetails('tv');
        Http::fake(['api.themoviedb.org/3/search/tv*' => function ($request) {
            self::assertSame('2022', $request['year']);

            return Http::response(['results' => [['id' => 55, 'name' => 'Known Title']]]);
        }]);
        self::assertSame('tt1234567', (new TorrentMetadataService)->fetchTmdb('Known Title', '2022', 'tv')[0]);
    }

    public function test_temporary_provider_failure_is_retried(): void
    {
        $this->fakeDetails();
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::sequence()
            ->push([], 503)
            ->push(['results' => [['id' => 55, 'title' => 'Known Title']]])]);
        self::assertSame('tt1234567', (new TorrentMetadataService)->fetchTmdb('Known Title', null, 'movie')[0]);
        Http::assertSentCount(4);
    }

    public function test_authentication_errors_are_logged_without_keys_and_without_unfiltered_search(): void
    {
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response([], 401)]);
        self::assertSame([null, null, null], (new TorrentMetadataService)->fetchTmdb('Known Title', '2020', 'movie'));
        Http::assertSentCount(1);
        Log::shouldHaveReceived('warning')->once()->with('Seedbox metadata lookup failed', \Mockery::on(function ($context) {
            return $context['status'] === 401 && ! array_key_exists('api_key', $context) && ! array_key_exists('message', $context);
        }));
    }

    public function test_no_match_is_distinguished_from_a_provider_failure(): void
    {
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response(['results' => []])]);
        self::assertSame([null, null, null], (new TorrentMetadataService)->fetchTmdb('Unknown Title', null, 'movie'));
        Log::shouldHaveReceived('info')->once();
        Log::shouldNotHaveReceived('warning');
    }

    public function test_an_empty_title_does_not_call_the_provider(): void
    {
        Http::fake();
        self::assertSame([null, null, null], (new TorrentMetadataService)->fetchTmdb('', null, 'movie'));
        Http::assertNothingSent();
    }
}
