<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthenticationExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiled = sys_get_temp_dir().'/fileiplay-auth-test-views-'.getmypid();
        if (! is_dir($compiled)) {
            mkdir($compiled, 0700, true);
        }
        config(['cache.default' => 'array', 'session.driver' => 'array', 'view.compiled' => $compiled]);
        // Exercise auth middleware with no user or browser cookies, without database access.
        Auth::shouldReceive('guard')->andReturnSelf();
        Auth::shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('guest')->andReturn(true);
        Auth::shouldReceive('user')->andReturn(null);
        Route::get('/__test/session-required', fn () => 'Protected content')->middleware('auth');
    }

    private function request(string $accept = 'text/html', string $uri = '/__test/session-required'): Request
    {
        $request = Request::create($uri, 'GET', [], [], [], ['HTTP_ACCEPT' => $accept]);
        $session = new Store('auth-regression', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);
        $this->app->instance('session.store', $session);
        $this->app['redirect']->setSession($session);
        $this->app->instance('request', $request);

        return $request;
    }

    public function test_cookie_less_protected_page_redirects_to_login_and_keeps_intended_url(): void
    {
        $request = $this->request();
        // Router dispatch includes the real auth middleware but avoids external proxy middleware.
        $response = $this->app['router']->dispatch($request);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(route('login'), $response->headers->get('Location'));
        self::assertSame($request->fullUrl(), $request->session()->get('url.intended'));
    }

    public function test_cookie_less_json_request_returns_401_instead_of_html_500(): void
    {
        $request = $this->request('application/json');
        $response = $this->app->make(ExceptionHandler::class)->render($request, new AuthenticationException);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(['message' => 'Unauthenticated.'], json_decode($response->getContent(), true));
    }

    public function test_real_server_errors_still_use_the_private_general_error_page(): void
    {
        $request = $this->request();
        $response = $this->app->make(ExceptionHandler::class)->render($request, new \RuntimeException('private internal detail'));

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('Something Went Wrong', $response->getContent());
        self::assertStringNotContainsString('private internal detail', $response->getContent());
    }

    public function test_background_count_request_without_json_headers_does_not_replace_intended_page(): void
    {
        $request = $this->request('*/*', '/announcements-unread-count');
        $request->session()->put('url.intended', url('/torrents'));
        $response = $this->app->make(ExceptionHandler::class)->render($request, new AuthenticationException);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(['message' => 'Unauthenticated.'], json_decode($response->getContent(), true));
        self::assertSame(url('/torrents'), $request->session()->get('url.intended'));
    }

    public function test_login_discards_an_already_saved_count_destination(): void
    {
        $request = $this->request();
        $request->session()->put('url.intended', url('/announcements-unread-count').'?old=1');
        $method = new \ReflectionMethod(LoginController::class, 'redirectAfterLogin');
        $response = $method->invoke(new LoginController, $request);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(url('/'), $response->headers->get('Location'));
        self::assertFalse($request->session()->has('url.intended'));
    }

    public function test_login_keeps_a_real_page_destination(): void
    {
        $request = $this->request();
        $destination = url('/torrents').'?search=example';
        $request->session()->put('url.intended', $destination);
        $method = new \ReflectionMethod(LoginController::class, 'redirectAfterLogin');
        $response = $method->invoke(new LoginController, $request);

        self::assertSame($destination, $response->headers->get('Location'));
        self::assertFalse($request->session()->has('url.intended'));
    }
}
