<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemMessageService
{
    /** One continuous thread per pair, regardless of sender or message subject. */
    public static function findOrCreate(int $userOne, int $userTwo, string $subject = 'Conversation'): Conversation
    {
        $min = min($userOne, $userTwo);
        $max = max($userOne, $userTwo);

        return DB::transaction(function () use ($min, $max, $subject) {
            // Serializes first contact even before a conversation row exists.
            DB::table('users')->where('id', $min)->lockForUpdate()->first();
            $threads = Conversation::where(function ($query) use ($min, $max) {
                $query->where('user_one', $min)->where('user_two', $max);
            })->orWhere(function ($query) use ($min, $max) {
                $query->where('user_one', $max)->where('user_two', $min);
            })->orderBy('id')->lockForUpdate()->get();

            $conversation = $threads->first();
            if (! $conversation) {
                return Conversation::create([
                    'user_one' => $min, 'user_two' => $max,
                    'subject' => $subject ?: 'Conversation', 'last_message_at' => now(),
                ]);
            }

            $duplicates = $threads->skip(1)->pluck('id');
            if ($duplicates->isNotEmpty()) {
                // Move messages before deleting threads with cascading foreign keys.
                DB::table('messages')->whereIn('conversation_id', $duplicates)->update(['conversation_id' => $conversation->id]);
                Conversation::whereIn('id', $duplicates)->delete();
            }
            if ($duplicates->isNotEmpty() || (int) $conversation->user_one !== $min) {
                $conversation->update([
                    'user_one' => $min, 'user_two' => $max,
                    'last_message_at' => $conversation->messages()->max('created_at') ?? $threads->max('last_message_at'),
                ]);
                self::forgetUserCache($min);
                self::forgetUserCache($max);
            }

            return $conversation;
        });
    }

    public static function send(int $senderId, int $receiverId, string $subject, string $body): Message
    {
        return DB::transaction(function () use ($senderId, $receiverId, $subject, $body) {
            $conversation = self::findOrCreate($senderId, $receiverId, $subject);
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId, 'receiver_id' => $receiverId,
                'subject' => $subject, 'body' => $body, 'is_read' => 0,
            ]);
            $conversation->update(['last_message_at' => $message->created_at]);
            self::forgetUserCache($senderId);
            self::forgetUserCache($receiverId);

            return $message;
        }, 3);
    }

    /** Consolidate historical threads and attach messages written by older send paths. */
    public static function consolidateHistory(): void
    {
        Conversation::orderBy('id')->chunkById(200, function ($threads) {
            foreach ($threads as $thread) {
                self::findOrCreate($thread->user_one, $thread->user_two, $thread->subject ?? 'Conversation');
            }
        });
        Message::whereNull('conversation_id')->chunkById(200, function ($messages) {
            foreach ($messages as $message) {
                DB::transaction(function () use ($message) {
                    $thread = self::findOrCreate($message->sender_id, $message->receiver_id, $message->subject ?? 'Conversation');
                    DB::table('messages')->where('id', $message->id)->update(['conversation_id' => $thread->id]);
                    $thread->update(['last_message_at' => $thread->messages()->max('created_at')]);
                    self::forgetUserCache($message->sender_id);
                    self::forgetUserCache($message->receiver_id);
                });
            }
        });
    }

    public static function forgetUserCache(int $userId): void
    {
        Cache::forget("user_unread_count_{$userId}");
        Cache::forget("user_conversations_{$userId}");
    }
}
