<?php

namespace Tests\Unit;

use App\Http\Controllers\ActorsController;
use App\Services\ActorService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\TestCase;

class ActorPageTest extends TestCase
{
    private Container $previous;
    private Factory $views;
    private string $temporary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = Container::getInstance();
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();
        $app->instance('config', new Repository());
        $this->temporary = sys_get_temp_dir() . '/actor-views-' . bin2hex(random_bytes(6));
        mkdir($this->temporary . '/layouts', 0700, true);
        mkdir($this->temporary . '/compiled');
        file_put_contents($this->temporary . '/layouts/app.blade.php', '<html><body>@yield("content")</body></html>');
        $files = new Filesystem();
        $resolver = new EngineResolver();
        $resolver->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $this->temporary . '/compiled')));
        $this->views = new Factory($resolver, new FileViewFinder($files, [$this->temporary, dirname(__DIR__, 2) . '/resources/views']), new Dispatcher($app));
        $this->views->setContainer($app);
        $this->views->share('__env', $this->views);
        $app->instance('view', $this->views);
        $app->instance('Illuminate\Contracts\View\Factory', $this->views);
        $router = new Router(new Dispatcher($app), $app);
        $router->get('library/movies', fn () => '')->name('library.movies.index');
        $router->get('library/movies/{tmdbid}', fn () => '')->name('library.movies.show');
        $router->get('library/series/{tmdbid}', fn () => '')->name('library.series.show');
        $router->get('actors/{actor}', fn () => '')->name('actors.show');
        $router->getRoutes()->refreshNameLookups();
        $app->instance('url', new UrlGenerator($router->getRoutes(), Request::create('https://example.test/actors/42')));
        $app->instance('Illuminate\Contracts\Routing\ResponseFactory', new ResponseFactory($this->views, $this->createMock(Redirector::class)));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->temporary);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previous);
        Container::setInstance($this->previous);
        parent::tearDown();
    }

    public function test_profile_renders_missing_information_and_escapes_biography(): void
    {
        $profile = (new ActorService())->profile(['id' => 42, 'name' => 'An Actor', 'biography' => '<script>alert(1)</script>']);
        $html = $this->views->make('actors.show', $profile)->render();
        self::assertStringContainsString('An Actor', $html);
        self::assertStringContainsString('No portrait available', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_credit_container_scrolls_only_after_ten_and_keeps_all_titles(): void
    {
        foreach ([0, 10, 11] as $count) {
            $cast = $count ? array_map(fn ($id) => ['id' => $id, 'media_type' => 'movie', 'title' => "Movie {$id}"], range(1, $count)) : [];
            $profile = (new ActorService())->profile(['combined_credits' => ['cast' => $cast]]);
            $html = $this->views->make('actors.partials.credits', [
                'credits' => $profile['movies'], 'heading' => 'Movies', 'sectionId' => 'actor-movies',
            ])->render();
            self::assertSame($count, substr_count($html, 'class="actor-credit"'));
            self::assertSame($count > 10, str_contains($html, 'actor-credit-list-scroll'));
            if ($count > 10) {
                self::assertStringContainsString('tabindex="0"', $html);
                self::assertStringContainsString('Movie 11', $html);
            }
        }
    }

    public function test_controller_returns_a_friendly_503_on_connection_failure(): void
    {
        $service = $this->createMock(ActorService::class);
        $service->method('find')->willThrowException(new ConnectionException('Secret provider URL'));
        $response = (new ActorsController())->show(42, $service);
        self::assertSame(503, $response->getStatusCode());
        self::assertStringContainsString('temporarily unavailable', $response->getContent());
        self::assertStringNotContainsString('Secret provider URL', $response->getContent());
    }

    public function test_controller_returns_404_for_a_missing_person(): void
    {
        $service = $this->createMock(ActorService::class);
        $service->method('find')->willThrowException(new RequestException(new Response(new \GuzzleHttp\Psr7\Response(404))));
        $response = (new ActorsController())->show(42, $service);
        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('Actor not found', $response->getContent());
    }
}
