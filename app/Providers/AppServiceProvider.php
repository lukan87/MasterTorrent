<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Monicahq\Cloudflare\LaravelCloudflare;
use Monicahq\Cloudflare\Facades\CloudflareProxies;
use App\Models\Message;
use Illuminate\Support\Facades\DB;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        //DB::statement("SET time_zone = '+00:00'");

        // Add your custom middleware here globally
        app('router')->pushMiddlewareToGroup('web', \App\Http\Middleware\CheckUserEnabled::class);
        app('router')->pushMiddlewareToGroup('web', \App\Http\Middleware\CheckUserBanned::class);

        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            // Initialize variables
            $seedingCount = 0;
            $leechingCount = 0;
            $messages = collect();
            $unreadMessagesCount = 0; // Initialize to 0 in case there is no authenticated user
          

            if ($user) {
                // If the user is authenticated, fetch seeding and leeching counts
                $seedingCount = $user->seedingCount(); 
                $leechingCount = $user->leechingCount(); 
                $unreadMessagesCount = Message::where('receiver_id', $user->id)
                                              ->where('is_read', false)
                                              ->count();

                
                $messages = Message::where('receiver_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();
            }

            $view->with(compact('seedingCount', 'leechingCount', 'messages', 'unreadMessagesCount'));
        });

        LaravelCloudflare::getProxiesUsing(fn() => CloudflareProxies::load());
    }
}

