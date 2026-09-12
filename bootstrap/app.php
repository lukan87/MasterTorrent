<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // 1) Middleware global – blocheaza contul admin pana la cod
// $middleware->appendToGroup('web', [
//     \App\Http\Middleware\InvalidateSessionIfSecurityVersionChanged::class,
//     \App\Http\Middleware\SecurityGateForAdmins::class,
// ]);


        // 2) Alias-uri (ce aveai deja)
        $middleware->alias([
            'last_activity' => \App\Http\Middleware\CheckOnlineUsers::class,
            'save_ip'       => \App\Http\Middleware\SaveUserIP::class,
            'admin'         => \App\Http\Middleware\AdminMiddleware::class,
            'permission'    => \App\Http\Middleware\CheckPermission::class,
            'staff'         => \App\Http\Middleware\StaffMiddleware::class,
        ]);

        // 3) Inlocuire TrustProxies (Cloudflare)
        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            TrustProxies::class
        );
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 404) {
                return response()->view('errors.404', [
                    'message' => 'The page expired, please try again.'
                ], 404);
            }

            return $response;
        });

$exceptions->render(function (\Exception $exception, \Illuminate\Http\Request $request) {

    // IDs allowed to see debug errors
    $debugUsers = [1, 2, 3]; // add whatever user IDs you want

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Allow selected users to see Laravel debug page
    if (auth()->check() && in_array(auth()->id(), $debugUsers)) {

        if (config('app.debug')) {

            return (new Illuminate\Foundation\Exceptions\Handler(app()))
                ->render($request, $exception);

        }

    }

    // Everyone else sees custom error page
    return response()->view('errors.general', [], 500);

});


    })

    ->create();
