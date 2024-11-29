<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;


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
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            // Initialize variables
            $seedingCount = 0;
            $leechingCount = 0;
            $messages = collect();
            $unreadMessagesCount = 0; // Initialize to 0 in case there is no authenticated user


            if ($user) {
                // If the user is authenticated, fetch seeding and leeching counts
                $seedingCount = $user->seedingCount(); // Call the method to get seeding count
                $leechingCount = $user->leechingCount(); // Call the method to get leeching count
                $unreadMessagesCount = Message::where('receiver_id', $user->id)
                                              ->where('is_read', false)
                                              ->count();

                // Fetch the last 3 messages for the authenticated user
                $messages = Message::where('receiver_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();
            }

            $view->with(compact('seedingCount', 'leechingCount', 'messages', 'unreadMessagesCount'));
        });
    }
}

