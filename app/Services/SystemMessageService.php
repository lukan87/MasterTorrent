<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;

class SystemMessageService
{
    /**
     * Find (or create) the single private conversation between two users.
     *
     * Conversations always store the two participant ids in a normalized
     * (smaller id first) order so that there is exactly one conversation per
     * user pair.
     */
    public static function findOrCreate(
        int $userOne,
        int $userTwo,
        string $subject = 'Conversation'
    ): Conversation {
        $min = min($userOne, $userTwo);
        $max = max($userOne, $userTwo);

        $conversation = Conversation::where('user_one', $min)
            ->where('user_two', $max)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one' => $min,
                'user_two' => $max,
                'subject' => $subject ?: 'Conversation',
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Send a private message, creating/updating the underlying conversation,
     * and invalidate the cached unread counters for both participants.
     */
    public static function send(
        int $senderId,
        int $receiverId,
        string $subject,
        string $body
    ): Message {
        $conversation = self::findOrCreate($senderId, $receiverId, $subject);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'body' => $body,
            'is_read' => 0,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        self::forgetUserCache($senderId);
        self::forgetUserCache($receiverId);

        return $message;
    }

    /**
     * Drop the cached sidebar/preview + unread counter for a single user.
     */
    public static function forgetUserCache(int $userId): void
    {
        Cache::forget("user_unread_count_{$userId}");
        Cache::forget("user_conversations_{$userId}");
    }
}