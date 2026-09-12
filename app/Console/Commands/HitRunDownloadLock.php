<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\SystemMessageService;

class HitRunDownloadLock extends Command
{
    protected $signature = 'hitrun:download-lock';
    protected $description = 'Disable downloads for users with too many hit and runs';

    public function handle()
    {
        $limit = 20;

        $this->info("Checking users for download restriction...");

        /*
        |--------------------------------------------------------------------------
        | LOCK USERS
        |--------------------------------------------------------------------------
        */

        $usersToLock = User::where('hit_and_run_count','>=',$limit)
            ->where('downloadpos','!=','no')
            ->where('enabled','yes')
            ->get();

        foreach ($usersToLock as $user) {

            $user->update([
                'downloadpos' => 'no'
            ]);

            $this->sendSystemMessage(
                $user->id,
                'Download Privileges Restricted',
                "
[b][color=red]DOWNLOAD RESTRICTED[/color][/b]

You currently have {$user->hit_and_run_count} Hit & Runs.

Your download privileges have been restricted.

To restore downloading ability you must reseed torrents and reduce your Hit & Run count below {$limit}.
"
            );

            $this->error("User {$user->id} downloads restricted.");
        }

        /*
        |--------------------------------------------------------------------------
        | UNLOCK USERS
        |--------------------------------------------------------------------------
        */

        $usersToUnlock = User::where('hit_and_run_count','<',$limit)
            ->where('downloadpos','!=','yes')
            ->where('enabled','yes')
            ->get();

        foreach ($usersToUnlock as $user) {

            $user->update([
                'downloadpos' => 'yes'
            ]);

            $this->sendSystemMessage(
                $user->id,
                'Download Privileges Restored',
                "
[b][color=green]DOWNLOAD RESTORED[/color][/b]

Good news!

Your Hit & Run count is now {$user->hit_and_run_count}, which is below the limit of {$limit}.

Your download privileges have been restored.
"
            );

            $this->info("User {$user->id} downloads restored.");
        }

        $this->info("Download restriction check finished.");
        $this->line("Users locked: ".$usersToLock->count());
        $this->line("Users unlocked: ".$usersToUnlock->count());
    }

    /*
    |--------------------------------------------------------------------------
    | System message helper with conversations
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = config('hitrun.system_user_id', 2);

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}
