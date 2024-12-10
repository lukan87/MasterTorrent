<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpFoundation\Response;
use Monicahq\Cloudflare\Middleware\TrustProxies; // Import the middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //$app->withMiddleware(function (Middleware $middleware) {
       $middleware->alias([
        'last_activity' => \App\Http\Middleware\CheckOnlineUsers::class,
        'save_ip' => \App\Http\Middleware\SaveUserIP::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
       ]);

       $middleware->replace(
        \Illuminate\Http\Middleware\TrustProxies::class,
        \Monicahq\Cloudflare\Http\Middleware\TrustProxies::class
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
        //
    })->create();
