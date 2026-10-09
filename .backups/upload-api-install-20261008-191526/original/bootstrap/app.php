<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        /*
        |--------------------------------------------------------------------------
        | Global Web Middleware
        |--------------------------------------------------------------------------
        |
        | Uncomment these when you want to enable the admin security gate.
        |
        */

        // $middleware->appendToGroup('web', [
        //     \App\Http\Middleware\InvalidateSessionIfSecurityVersionChanged::class,
        //     \App\Http\Middleware\SecurityGateForAdmins::class,
        // ]);

        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->appendToGroup('web', \App\Http\Middleware\RecordAchievementVisit::class);

        $middleware->alias([
            'last_activity' => \App\Http\Middleware\CheckOnlineUsers::class,
            'save_ip'       => \App\Http\Middleware\SaveUserIP::class,
            'admin'         => \App\Http\Middleware\AdminMiddleware::class,
            'permission'    => \App\Http\Middleware\CheckPermission::class,
            'staff'         => \App\Http\Middleware\StaffMiddleware::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cloudflare Trust Proxies
        |--------------------------------------------------------------------------
        */

        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            TrustProxies::class
        );
    })

    ->withExceptions(function (Exceptions $exceptions) {

        /*
        |--------------------------------------------------------------------------
        | Custom Response Handling
        |--------------------------------------------------------------------------
        |
        | Display the custom 404 page.
        |
        */

        $exceptions->respond(function (Response $response) {

            if ($response->getStatusCode() === 404 && request()->route()?->getName() !== 'torznab.api') {
                return response()->view('errors.404', [
                    'message' => 'The page could not be found.',
                ], 404);
            }

            return $response;
        });

        /*
        |--------------------------------------------------------------------------
        | Custom Exception Handling
        |--------------------------------------------------------------------------
        |
        | APP_DEBUG must remain FALSE in production.
        |
        | Only authenticated users whose IDs are listed in $debugUsers
        | can see the detailed errors.debug page.
        |
        | Everyone else receives errors.general without any sensitive
        | exception information.
        |
        */

        $exceptions->render(function (
            \Throwable $exception,
            \Illuminate\Http\Request $request
        ) {

            // Torznab clients need XML errors, including throttling and middleware failures.
            if ($request->route()?->getName() === 'torznab.api') {
                $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
                $description = $status === 429 ? 'Too many requests. Please retry later.' : 'API request failed.';
                $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

                return response('<?xml version="1.0" encoding="UTF-8"?><error code="900" description="'.$description.'" />', $status, $headers)
                    ->header('Content-Type', 'application/xml; charset=UTF-8')
                    ->header('Cache-Control', 'private, no-store');
            }

            /*
            |--------------------------------------------------------------------------
            | BitTorrent Announce Endpoint
            |--------------------------------------------------------------------------
            |
            | Never replace the BitTorrent tracker announce response with
            | an HTML error page.
            |
            | Route:
            |
            | GET /announce/{passkey}
            | Name: announce
            |
            */

            if ($request->route()?->getName() === 'announce') {
                return null;
            }

            // Old tabs may poll without an Accept header. Never save a count endpoint
            // as the intended destination when their session has expired.
            if ($exception instanceof AuthenticationException && $request->is('announcements-unread-count')) {
                return response()->json(['message' => 'Unauthenticated.'], 401)
                    ->header('Cache-Control', 'private, no-store');
            }

            /*
            |--------------------------------------------------------------------------
            | Authentication / Validation / HTTP Exceptions
            |--------------------------------------------------------------------------
            |
            | Let Laravel redirect guests to login (or return JSON 401) and
            | handle normal framework responses such as:
            |
            | 403 - Forbidden
            | 404 - Not Found
            | 419 - Page Expired
            | 429 - Too Many Requests
            |
            */

            if (
                $exception instanceof AuthenticationException ||
                $exception instanceof ValidationException ||
                $exception instanceof HttpExceptionInterface
            ) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Users Allowed To See Debug Information
            |--------------------------------------------------------------------------
            */

            $debugUsers = [
                1,
                2,
                //3,
            ];

            /*
            |--------------------------------------------------------------------------
            | Detailed Debug Page
            |--------------------------------------------------------------------------
            |
            | The user must be authenticated AND their user ID must appear
            | in the $debugUsers array above.
            |
            */

            if (
                auth()->check() &&
                in_array((int) auth()->id(), $debugUsers, true)
            ) {
                return response()->view('errors.debug', [
                    'exception' => $exception,
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Everyone Else
            |--------------------------------------------------------------------------
            |
            | Normal users and guests receive no:
            |
            | - exception messages
            | - stack traces
            | - source code
            | - file paths
            | - line numbers
            | - database errors
            | - internal application information
            |
            */

            return response()->view('errors.general', [], 500);
        });
    })

    ->create();
