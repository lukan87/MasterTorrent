<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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


        $exceptions->render(function (\Exception $exception, \Illuminate\Http\Request $request) {
            // Check if the user is not authenticated (logged out)
            if (!auth()->check()) {
                // If the user is not logged in, redirect them to the login page
                return redirect()->route('login');
            }
        
            // Check if the user is authenticated and has user id 3
            if (auth()->check() && auth()->user()->id == 3) {
                if (config('app.debug')) {
                    // Render the default Laravel error page (debug page) for the specific user (id == 3)
                    return (new Illuminate\Foundation\Exceptions\Handler(app()))->render($request, $exception);
                }
            }
        
            // For regular users (authenticated users who are not user with id 3), show the custom error page
            return response()->view('errors.general', [], 500);
        });
        
        //
    })->create();
