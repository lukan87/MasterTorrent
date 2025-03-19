<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invite;
use Carbon\Carbon;

class ExpireOldInvites extends Command
{
    protected $signature = 'invites:expire';
    protected $description = 'Mark invites as expired if they are not used within two weeks';

    public function handle()
    {
        $expirationDate = Carbon::now()->subWeeks(2); // 2 weeks ago

        $expiredInvites = Invite::where('is_used', false)
            ->where('is_expired', false)
            ->where('created_at', '<', $expirationDate)
            ->update(['is_expired' => true]);

        $this->info("$expiredInvites invites expired as they were not used.");
    }
}
