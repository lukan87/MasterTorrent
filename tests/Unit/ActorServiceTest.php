<?php

namespace Tests\Unit;

use App\Services\ActorService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ActorServiceTest extends TestCase
{
    private Container $previous;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = Container::getInstance();
        $app = new Container();
        $app->instance('config', new Config(['services' => ['tmdb' => ['key' => 'test-key']]]));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();
        Cache::swap(new Repository(new ArrayStore()));
        Http::swap(new Factory());
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previous);
        Container::setInstance($this->previous);
        parent::tearDown();
    }

    public function test_fetches_related_data_in_one_request_and_caches_the_profile(): void
    {
        Http::fake(['*' => Http::response($this->person())]);
        $service = new ActorService();
        self::assertSame('Example Actor', $service->find(42)['person']['name']);
        self::assertSame('Example Actor', $service->find(42)['person']['name']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.themoviedb.org/3/person/42?')
            && $request['append_to_response'] === 'combined_credits,external_ids,images');
    }

    public function test_groups_multiple_roles_without_merging_movie_and_tv_ids(): void
    {
        $person = $this->person();
        $person['combined_credits']['cast'] = [
            ['id' => 7, 'media_type' => 'movie', 'title' => 'Older movie', 'release_date' => '2000-02-01', 'character' => 'Alex'],
            ['id' => 7, 'media_type' => 'movie', 'title' => 'Older movie', 'character' => 'Sam'],
            ['id' => 7, 'media_type' => 'movie', 'title' => 'Older movie', 'character' => 'Alex'],
            ['id' => 7, 'media_type' => 'tv', 'name' => 'A series', 'first_air_date' => '2020-01-01', 'episode_count' => 12],
            ['id' => 8, 'media_type' => 'movie', 'title' => 'New movie', 'release_date' => '2024-01-01', 'vote_count' => 500],
            ['id' => 9, 'media_type' => 'person', 'name' => 'Invalid credit'],
        ];
        $profile = (new ActorService())->profile($person);
        self::assertCount(2, $profile['movies']);
        self::assertCount(1, $profile['series']);
        self::assertSame('New movie', $profile['movies'][0]['title']);
        self::assertSame(['Alex', 'Sam'], $profile['movies'][1]['roles']);
        self::assertSame(12, $profile['series'][0]['episodes']);
        self::assertSame('New movie', $profile['knownFor'][0]['title']);
    }

    public function test_collects_departments_and_crew_jobs(): void
    {
        $person = $this->person();
        $person['combined_credits']['crew'] = [
            ['id' => 9, 'media_type' => 'movie', 'title' => 'A movie', 'department' => 'Directing', 'job' => 'Director'],
            ['id' => 9, 'media_type' => 'movie', 'title' => 'A movie', 'department' => 'Production', 'job' => 'Producer'],
        ];
        $profile = (new ActorService())->profile($person);
        self::assertSame(['Acting', 'Directing', 'Production'], $profile['departments']);
        self::assertCount(1, $profile['crew']);
        self::assertSame(['Directing · Director', 'Production · Producer'], $profile['crew'][0]['roles']);
    }

    public function test_missing_optional_information_has_empty_fallbacks(): void
    {
        $profile = (new ActorService())->profile(['id' => 42, 'name' => 'Example Actor']);
        foreach (['movies', 'series', 'crew', 'photos', 'knownFor', 'links', 'departments'] as $field) {
            self::assertSame([], $profile[$field]);
        }
        self::assertNull($profile['portrait']);
    }

    public function test_social_links_are_encoded_and_unsafe_homepages_are_omitted(): void
    {
        $person = $this->person();
        $person['homepage'] = 'javascript:alert(1)';
        $person['external_ids'] = ['instagram_id' => 'actor/name?x=1', 'imdb_id' => 'nm123'];
        $profile = (new ActorService())->profile($person);
        self::assertArrayNotHasKey('Official website', $profile['links']);
        self::assertSame('https://www.instagram.com/actor%2Fname%3Fx%3D1', $profile['links']['Instagram']);
        self::assertSame('https://www.imdb.com/name/nm123', $profile['links']['IMDb']);
    }

    public function test_not_found_is_not_cached(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 404)->push($this->person())]);
        $service = new ActorService();
        try {
            $service->find(42);
            self::fail('Expected a provider error.');
        } catch (RequestException $e) {
            self::assertSame(404, $e->response->status());
        }
        self::assertSame('Example Actor', $service->find(42)['person']['name']);
        Http::assertSentCount(2);
    }

    public function test_partial_provider_failure_is_not_cached_as_empty_credits(): void
    {
        $partial = $this->person();
        $partial['combined_credits'] = ['success' => false];
        Http::fake(['*' => Http::sequence()->push($partial)->push($this->person())]);
        $service = new ActorService();
        try {
            $service->find(42);
            self::fail('Expected an incomplete response error.');
        } catch (RuntimeException $e) {
            self::assertSame('Incomplete TMDB person response.', $e->getMessage());
        }
        self::assertSame('Example Actor', $service->find(42)['person']['name']);
        Http::assertSentCount(2);
    }

    public function test_full_filmography_is_retained_beyond_ten_titles(): void
    {
        $person = $this->person();
        $person['combined_credits']['cast'] = array_map(fn ($id) => [
            'id' => $id, 'media_type' => 'movie', 'title' => "Movie {$id}", 'vote_count' => $id,
        ], range(1, 25));
        $profile = (new ActorService())->profile($person);
        self::assertCount(25, $profile['movies']);
        self::assertCount(10, $profile['knownFor']);
        self::assertSame('Movie 25', $profile['knownFor'][0]['title']);
    }

    private function person(): array
    {
        return [
            'id' => 42, 'name' => 'Example Actor', 'known_for_department' => 'Acting',
            'combined_credits' => ['cast' => [], 'crew' => []], 'external_ids' => [], 'images' => ['profiles' => []],
        ];
    }
}
