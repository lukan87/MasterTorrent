<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;

class RevertVipStatus extends Command
{
    protected $signature = 'users:revert-vip-status';
    protected $description = 'Revert VIP users to regular users once their VIP time expires.';

    public function handle()
    {
        $now = Carbon::now();

        User::whereNotNull('vip_until')
            ->where('vip_until','<=',$now)
            ->chunkById(200,function($users){

                foreach ($users as $user) {

                    /*
                    |--------------------------------------------------------------------------
                    | Revert VIP
                    |--------------------------------------------------------------------------
                    */

                    $user->update([
                        'user_class' => 1,
                        'is_immune' => 0,
                        'is_freeleech' => 0,
                        'vip_until' => null
                    ]);

                    $body = "
Dear {$user->name},

Your VIP status has expired and your account has been reverted to a regular user.

Thank you for supporting LastFiles.

LastFiles Team
";

                    $this->sendSystemMessage(
                        $user->id,
                        "VIP Expiration",
                        $body
                    );

                    $this->info("User {$user->name} reverted to regular user.");
                }

            });
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