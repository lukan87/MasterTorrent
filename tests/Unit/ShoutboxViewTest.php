<?php

namespace Tests\Unit;

use App\Http\Controllers\ShoutboxController;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ShoutboxViewTest extends TestCase
{
    private object $viewer;

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
        $app->instance('config', new Repository);
        $this->temporary = sys_get_temp_dir().'/chat-views-'.bin2hex(random_bytes(6));
        mkdir($this->temporary.'/layouts', 0700, true);
        mkdir($this->temporary.'/compiled');
        file_put_contents($this->temporary.'/layouts/app.blade.php', '<html><body>@yield("content")</body></html>');
        $files = new Filesystem;
        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $this->temporary.'/compiled')));
        $this->views = new Factory($resolver, new FileViewFinder($files, [$this->temporary, dirname(__DIR__, 2).'/resources/views']), new Dispatcher($app));
        $this->views->setContainer($app);
        $this->views->share('__env', $this->views);
        $this->views->share('onlineUsers', collect());
        $app->instance('view', $this->views);
        $app->instance('Illuminate\Contracts\View\Factory', $this->views);
        $router = new Router(new Dispatcher($app), $app);
        $router->get('profile/{id}', fn () => '')->name('profile.show');
        foreach (['store', 'update', 'reply', 'destroy', 'sticky'] as $action) {
            $router->post('shoutbox/'.$action.'/{id?}', fn () => '')->name('shoutbox.'.$action);
        }
        $router->get('shoutbox/older', fn () => '')->name('shoutbox.older');
        $router->getRoutes()->refreshNameLookups();
        $app->instance('url', new UrlGenerator($router->getRoutes(), Request::create('https://example.test/')));
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('id')->andReturn(10);
        $auth->shouldReceive('user')->andReturnUsing(fn () => $this->viewer);
        $app->instance('auth', $auth);
        $app->instance('session', new Store('test', new ArraySessionHandler(120)));
        $app->instance('request', Request::create('https://example.test/'));
        foreach (['Auth' => Auth::class, 'Str' => Str::class] as $alias => $class) {
            if (! class_exists($alias)) {
                class_alias($class, $alias);
            }
        }
        $this->viewer = (object) ['id' => 10, 'user_class' => 1, 'chatblock' => false];
        $app->instance('Illuminate\Contracts\Routing\ResponseFactory', new ResponseFactory($this->views, $this->createMock(Redirector::class)));
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->temporary);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previous);
        Container::setInstance($this->previous);
        \Mockery::close();
        parent::tearDown();
    }

    private function messages()
    {
        $user = (object) ['id' => 10, 'user_class' => 1, 'name' => 'Member <test>', 'role_name' => 'User'];

        return collect([(object) [
            'id' => 20, 'user_id' => 10, 'user' => $user, 'sticky' => true,
            'message' => '[b]Hello[/b]', 'created_at' => now(), 'updated_at' => now(), 'replies' => collect(),
        ]]);
    }

    public function test_fragment_keeps_formatting_and_escapes_member_names(): void
    {
        $html = $this->views->make('partials.shoutbox-messages', ['messages' => $this->messages()])->render();
        self::assertStringContainsString('<strong>Hello</strong>', $html);
        self::assertStringContainsString('Member &lt;test&gt;', $html);
        self::assertStringContainsString('data-sticky="1"', $html);
        self::assertStringContainsString('Pinned', $html);
        self::assertStringNotContainsString('shoutbox-delete-form', $html);
    }

    public function test_moderator_can_see_delete_control(): void
    {
        $this->viewer->user_class = 6;
        $html = $this->views->make('partials.shoutbox-messages', ['messages' => $this->messages()])->render();
        self::assertStringContainsString('shoutbox-delete-form', $html);
    }

    public function test_chat_controls_render_with_empty_messages(): void
    {
        $html = $this->views->make('partials.shoutbox', ['messages' => collect()])->render();
        self::assertStringContainsString('id="chat-search"', $html);
        self::assertStringContainsString('id="chat-send"', $html);
        self::assertStringNotContainsString('id="chat-discard"', $html);
        self::assertStringContainsString('title="Send message" hidden disabled', $html);
        self::assertStringContainsString('No messages yet.', $html);
        self::assertStringContainsString('aria-label="Your chat message"', $html);
    }

    public function test_chat_restrictions_are_enforced_before_controller_actions(): void
    {
        $controller = new ShoutboxController;
        $middleware = $controller->getMiddleware();
        self::assertSame('auth', $middleware[0]['middleware']);
        $request = Request::create('/shoutbox/poll');
        $request->setUserResolver(fn () => (object) ['chatblock' => true]);
        $this->expectException(HttpException::class);
        $middleware[1]['middleware']($request, fn () => self::fail('Restricted members must not reach chat actions.'));
    }

    public function test_media_controls_and_previews_render_without_loading_video_iframes(): void
    {
        $messages = $this->messages();
        $messages->first()->message = '[img]https://example.test/image.jpg[/img] [youtube]https://youtu.be/dQw4w9WgXcQ[/youtube]';
        $html = $this->views->make('partials.shoutbox', ['messages' => $messages])->render();
        self::assertStringContainsString('data-chat-media="image"', $html);
        self::assertStringContainsString('data-chat-media="youtube"', $html);
        self::assertStringContainsString('data-chat-format="spoiler"', $html);
        self::assertStringContainsString('data-youtube-id="dQw4w9WgXcQ"', $html);
        self::assertStringContainsString('class="bbcode-image chat-embedded-image"', $html);
        self::assertStringNotContainsString('<iframe', $html);
    }

    public function test_pins_stay_outside_the_chronological_scrolling_timeline(): void
    {
        $pin = $this->messages()->first();
        $older = clone $pin;
        $older->id = 21;
        $older->sticky = false;
        $older->created_at = now()->subMinutes(2);
        $newer = clone $older;
        $newer->id = 22;
        $newer->created_at = now();
        $html = $this->views->make('partials.shoutbox', [
            'messages' => collect([$newer, $pin, $older]),
        ])->render();
        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new \DOMXPath($dom);
        self::assertSame(1, $xpath->query('//*[@id="shoutbox-pinned"]//*[@data-id="20" and @data-sticky="1"]')->length);
        self::assertSame(0, $xpath->query('//*[@id="shoutbox-container"]//*[@data-sticky="1"]')->length);
        $timeline = $xpath->query('//*[@id="shoutbox-messages"]//*[@data-sticky="0"]');
        self::assertSame(2, $timeline->length);
        self::assertSame('21', $timeline->item(0)->getAttribute('data-id'));
        self::assertSame('22', $timeline->item(1)->getAttribute('data-id'));
    }
}
