<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Carbon;

class ClearExpiredBans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bans:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired banned_until values from users table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the current time
        $now = Carbon::now();

        // Find all users with expired bans
        $expiredBans = User::whereNotNull('banned_until')
            ->where('banned_until', '<', $now)
            ->get();

        // Clear the banned_until field for these users
        foreach ($expiredBans as $user) {
            $user->banned_until = null;
            $user->failed_attempts = 0; // Optionally reset failed attempts
            $user->save();
        }

        // Log the result
        $this->info('Cleared ' . $expiredBans->count() . ' expired bans.');

        return 0;
    }
}
