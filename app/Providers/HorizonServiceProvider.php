<?php

namespace App\Providers;

use App\Models\UserClass;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Register the Horizon gate
        $this->gate();

        // Optional: you can configure notifications here if needed
        // Horizon::routeMailNotificationsTo('developer@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            // Only allow logged-in users with user_class = WEB_DEVELOPER
            return $user && $user->user_class === UserClass::WEB_DEVELOPER;
        });
    }
}
