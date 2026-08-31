<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ResetExpiredCookieConsent extends Command
{
    protected $signature = 'consent:reset-expired';
    protected $description = 'Reset cookie consent older than 6 months';

    public function handle()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $affected = User::whereNotNull('cookie_consent')
            ->where('cookie_consent_at', '<=', $sixMonthsAgo)
            ->update([
                'cookie_consent' => null,
                'cookie_consent_at' => null,
            ]);

        $this->info("Reset {$affected} expired cookie consents.");

        return Command::SUCCESS;
    }
}