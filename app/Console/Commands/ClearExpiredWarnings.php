<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\UserTimeline;
use Carbon\Carbon;
use App\Services\SystemMessageService;

class ClearExpiredWarnings extends Command
{
    protected $signature = 'users:clear-expired-warnings';

    protected $description = 'Clears expired user warnings and sends a notification';

    public function handle()
    {
        $now = Carbon::now();

        $count = 0;

        User::where('warned', true)
            ->whereNotNull('warned_until')
            ->where('warned_until', '<', $now)

            ->chunkById(200, function ($users) use (&$count) {

                foreach ($users as $user) {

                    /*
                    |--------------------------------------------------------------------------
                    | Clear warning
                    |--------------------------------------------------------------------------
                    */

                    $user->update([
                        'warned' => false,
                        'warned_until' => null
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Send system message
                    |--------------------------------------------------------------------------
                    */

                    $body = "
Your warning has expired.

Please continue following the rules and keep up the good behavior.

Best of luck!

— LastFiles Team
";

                    $this->sendSystemMessage(
                        $user->id,
                        'Your Warning Has Expired!',
                        $body
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Timeline log (optional)
                    |--------------------------------------------------------------------------
                    */

                    // UserTimeline::create([
                    //     'user_id' => $user->id,
                    //     'staff_id' => 2,
                    //     'comment' => 'User warning expired and status reset automatically.'
                    // ]);

                    $count++;
                }

            });

        $this->info("Cleared warnings and notified {$count} user(s).");

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message in conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = 2;

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}