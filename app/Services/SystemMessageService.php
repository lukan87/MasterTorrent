<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;

class SystemMessageService
{
    public static function send(
        int $senderId,
        int $receiverId,
        string $subject,
        string $body
    ): Message {

        /*
        |--------------------------------------------------------------------------
        | Normalize user order
        |--------------------------------------------------------------------------
        */

        $userOne = min($senderId, $receiverId);
        $userTwo = max($senderId, $receiverId);

        /*
        |--------------------------------------------------------------------------
        | Find conversation
        |--------------------------------------------------------------------------
        */

        $conversation = Conversation::where('user_one', $userOne)
            ->where('user_two', $userTwo)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Create conversation if missing
        |--------------------------------------------------------------------------
        */

        if (!$conversation) {

            $conversation = Conversation::create([
                'user_one' => $userOne,
                'user_two' => $userTwo,
                'subject' => $subject ?: 'Conversation',
                'last_message_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create message
        |--------------------------------------------------------------------------
        */

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'body' => $body,
            'is_read' => 0
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update conversation timestamp
        |--------------------------------------------------------------------------
        */

        $conversation->update([
            'last_message_at' => now()
        ]);

        return $message;
    }
}