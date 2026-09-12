<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invite;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;
use App\Services\SystemMessageService;

class ProcessInvites extends Command
{
    protected $signature = 'invites:process';
    protected $description = 'Expire old invites and delete expired ones after 14 days.';

    public function handle()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Expire invites after 14 days
        |--------------------------------------------------------------------------
        */

        $expiredCount = Invite::where('is_used', false)
            ->where('is_expired', false)
            ->where('created_at', '<', $now->copy()->subDays(14))
            ->update(['is_expired' => true]);

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} invites older than 14 days.");
        }

        /*
        |--------------------------------------------------------------------------
        | Delete invites 14 days after expiring
        |--------------------------------------------------------------------------
        */

        Invite::where('is_used', false)
            ->where('is_expired', true)
            ->where('updated_at', '<', $now->copy()->subDays(14))
            ->chunkById(100, function ($invites) {

                foreach ($invites as $invite) {

                    $user = User::find($invite->inviter_id);

                    if ($user) {

                        $body = "Your invite with code [b]{$invite->invite_code}[/b] was deleted because it was not used within 28 days.";

                        $this->sendSystemMessage(
                            $user->id,
                            'Invite Deleted',
                            $body
                        );
                    }

                    $this->line("Deleting invite [{$invite->invite_code}] from user {$invite->inviter_id}");

                    $invite->delete();
                }

            });

        $this->info("Invite processing finished.");
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message with conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = 2;

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}