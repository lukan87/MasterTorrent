<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class StoreSecurityVersionInSession
{
    public function handle(Login $event): void
    {
        session([
            'sv' => (int) ($event->user->security_version ?? 1),
        ]);
    }
}
