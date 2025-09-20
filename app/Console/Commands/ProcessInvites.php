<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invite;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;

class ProcessInvites extends Command
{
    protected $signature = 'invites:process';
    protected $description = 'Expire old invites and delete expired ones after 14 days.';

    public function handle()
    {
        $now = Carbon::now();

        // 1. Expire invites after 14 days (if not already expired or used)
        $expiredCount = Invite::where('is_used', false)
            ->where('is_expired', false)
            ->where('created_at', '<', $now->copy()->subDays(14))
            ->update(['is_expired' => true]);

        if ($expiredCount > 0) {
            $this->info("✅ Expired {$expiredCount} invites that were older than 14 days.");
        }

        // 2. Delete invites 14 days after expiring
        $expiredInvites = Invite::where('is_used', false)
            ->where('is_expired', true)
            ->where('updated_at', '<', $now->copy()->subDays(14))
            ->get();

        foreach ($expiredInvites as $invite) {
            $user = User::find($invite->inviter_id);

            if ($user) {
                // Send PM using Message model
                Message::create([
                    'sender_id'   => 2, // system user
                    'receiver_id' => $user->id,
                    'subject'     => 'Invite Deleted',
                    'body'        => "Your invite with code **{$invite->invite_code}** was deleted because it was not used within 28 days.",
                ]);
            }

            // Output to terminal
            $this->line("🗑️  Deleting invite [Code: {$invite->invite_code}] from inviter [User ID: {$invite->inviter_id}]");

            // Delete the invite
            $invite->delete();
        }

        $this->info("🎉 Invite processing finished.");
    }
}
