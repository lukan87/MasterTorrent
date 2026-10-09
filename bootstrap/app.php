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
        api: __DIR__.'/../routes/api.php',
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
            'upload-api-access' => \App\Http\Middleware\UploadApiAccess::class,
            'upload-api-size' => \App\Http\Middleware\UploadApiRequestSize::class,
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
            if (request()->is('api/v1', 'api/v1/*')) return $response;

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

        $exceptions->report(function (\Throwable $exception) {
            if (request()->is('api/v1', 'api/v1/*')) {
                \Illuminate\Support\Facades\Log::error('Upload API request failed', ['exception_type' => get_class($exception)]);
                return false;
            }
        });

        $exceptions->render(function (
            \Throwable $exception,
            \Illuminate\Http\Request $request
        ) {

            // The versioned API always returns safe JSON, even without an Accept header.
            if ($request->is('api/v1', 'api/v1/*')) {
                $status = match (true) {
                    $exception instanceof \App\Exceptions\DuplicateTorrentException => 409,
                    $exception instanceof AuthenticationException => 401,
                    $exception instanceof ValidationException => 422,
                    $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                    default => 500,
                };
                $code = match ($status) {
                    401 => 'unauthenticated', 403 => 'forbidden', 404 => 'not_found',
                    409 => 'duplicate_torrent', 413 => 'request_too_large', 422 => 'validation_failed',
                    429 => 'rate_limited', default => 'request_failed',
                };
                $message = match ($status) {
                    401 => 'A valid personal bearer token is required.', 403 => 'This action is not permitted.',
                    404 => 'The resource was not found.', 409 => 'This torrent already exists on the tracker.',
                    413 => 'The request exceeds the upload limit.', 422 => 'The supplied fields are invalid.',
                    429 => 'Too many requests. Please retry later.', default => 'The request could not be completed.',
                };
                $error = ['code' => $code, 'message' => $message];
                if ($status === 422) $error['fields'] = $exception->errors();
                $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];
                return response()->json(['error' => $error], $status, $headers)->header('Cache-Control', 'private, no-store');
            }

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
