<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;

class WarnUploaders extends Command
{
    protected $signature = 'warn:uploaders';
    protected $description = 'Warn uploaders who have not uploaded a torrent in the last 3 days';

    public function handle()
    {
        $now = Carbon::now();
        $threeDaysAgo = $now->copy()->subDays(3);
        $fiveDaysAgo = $now->copy()->subDays(5);

        $warnedUsers = [];

        User::where('user_class', 5)
            ->where(function ($query) use ($threeDaysAgo) {

                $query->where('last_upload', '<', $threeDaysAgo)
                      ->orWhereNull('last_upload');

            })
            ->chunkById(200,function($uploaders) use ($fiveDaysAgo,&$warnedUsers){

                foreach ($uploaders as $user) {

                    /*
                    |--------------------------------------------------------------------------
                    | Check if already warned recently
                    |--------------------------------------------------------------------------
                    */

                    $alreadyWarned = Message::where('receiver_id', $user->id)
                        ->where('subject', 'Warning: Upload Required')
                        ->where('created_at', '>', $fiveDaysAgo)
                        ->exists();

                    if ($alreadyWarned) {
                        continue;
                    }

                    $body = "
Hello {$user->name},

You have not uploaded a torrent in the last 3 days.

You have [b]2 more days[/b] to upload a new torrent or you may be demoted from the Uploader class.

Thank you for supporting the community.

LastFiles Team
";

                    $this->sendSystemMessage(
                        $user->id,
                        'Warning: Upload Required',
                        $body
                    );

                    $warnedUsers[] = $user->id;
                }

            });

        if (count($warnedUsers) > 0) {

            $this->info(
                'Warnings sent to inactive uploaders: ' .
                implode(', ', $warnedUsers)
            );

        } else {

            $this->info('No inactive uploaders found or warnings already sent.');

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message with conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = 2;

        $conversation = Conversation::where(function ($q) use ($systemId, $userId) {
            $q->where('user_one', $systemId)
              ->where('user_two', $userId);
        })
        ->orWhere(function ($q) use ($systemId, $userId) {
            $q->where('user_one', $userId)
              ->where('user_two', $systemId);
        })
        ->first();

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