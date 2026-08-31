<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\UserTimeline;
use Carbon\Carbon;

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

        $conversation = Conversation::where(function ($q) use ($systemId, $userId) {

            $q->where('user_one', $systemId)
              ->where('user_two', $userId);

        })->orWhere(function ($q) use ($systemId, $userId) {

            $q->where('user_one', $userId)
              ->where('user_two', $systemId);

        })->first();

        if (!$conversation) {

            $conversation = Conversation::create([
                'user_one' => $systemId,
                'user_two' => $userId,
                'subject' => 'System Notifications',
                'last_message_at' => now(),
            ]);

        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $systemId,
            'receiver_id' => $userId,
            'subject' => $subject,
            'body' => $body,
            'is_read' => 0
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);
    }
}