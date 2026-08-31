<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\UserTimeline;
use Carbon\Carbon;

class DemoteInactiveUploaders extends Command
{
    protected $signature = 'users:demote-inactive-uploaders';

    protected $description = 'Demote users with user_class 5 who have not uploaded in the last 5 days';

    public function handle()
    {
        $currentDate = Carbon::now();
        $fiveDaysAgo = $currentDate->copy()->subDays(5);

        $demotedUsers = [];

        User::where('user_class', 5)
            ->whereNotNull('last_upload')
            ->where('last_upload', '<', $fiveDaysAgo)

            ->chunkById(200, function ($users) use ($currentDate, &$demotedUsers) {

                foreach ($users as $user) {

                    $oldClass = $user->user_class;

                    /*
                    |--------------------------------------------------------------------------
                    | Demote user
                    |--------------------------------------------------------------------------
                    */

                    $user->update([
                        'user_class' => 1
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Timeline
                    |--------------------------------------------------------------------------
                    */

                    UserTimeline::create([
                        'user_id'  => $user->id,
                        'staff_id' => 2,
                        'comment'  => "User was demoted from class {$oldClass} to 1 due to inactivity (no uploads for 5+ days).",
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Notify user
                    |--------------------------------------------------------------------------
                    */

                    $body = "
Hello {$user->name},

You have been demoted from the [b]Uploader[/b] class due to inactivity.

No torrents were uploaded in the last 5 days.

If you wish to regain the uploader class, please contact staff or start uploading again.

— LastFiles Team
";

                    $this->sendSystemMessage(
                        $user->id,
                        'Uploader Status Removed',
                        $body
                    );

                    $demotedUsers[] = [
                        'user_id' => $user->id,
                        'old_class' => $oldClass,
                        'new_class' => 1,
                        'demoted_at' => $currentDate,
                    ];
                }

            });

        /*
        |--------------------------------------------------------------------------
        | Console output
        |--------------------------------------------------------------------------
        */

        if (count($demotedUsers) > 0) {

            foreach ($demotedUsers as $demotedUser) {

                $this->info(
                    "User ID {$demotedUser['user_id']} demoted from {$demotedUser['old_class']} to {$demotedUser['new_class']} at {$demotedUser['demoted_at']}"
                );

            }

        } else {

            $this->info("No users were demoted.");

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message via conversation
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