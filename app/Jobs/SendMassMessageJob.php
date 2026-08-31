<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $classes;
    protected $senderId;

    public function __construct($message, $classes, $senderId)
    {
        $this->message = $message;
        $this->classes = $classes;
        $this->senderId = $senderId;
    }

    public function handle()
    {
        $now = now();

        // system user id
        $systemId = 2;

        User::whereIn('user_class', $this->classes)
            ->whereNull('deleted_at')
            ->chunk(500, function ($users) use ($now, $systemId) {

                foreach ($users as $user) {

                    /*
                    |--------------------------------------------------------------------------
                    | Find existing conversation
                    |--------------------------------------------------------------------------
                    */

                    $conversation = Conversation::where(function ($q) use ($user, $systemId) {
                        $q->where('user_one', $systemId)
                          ->where('user_two', $user->id);
                    })
                    ->orWhere(function ($q) use ($user, $systemId) {
                        $q->where('user_one', $user->id)
                          ->where('user_two', $systemId);
                    })
                    ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Create conversation if missing
                    |--------------------------------------------------------------------------
                    */

                    if (!$conversation) {

                        $conversation = Conversation::create([
                            'user_one' => $systemId,
                            'user_two' => $user->id,
                            'subject' => 'Mass Message',
                            'last_message_at' => $now,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create message
                    |--------------------------------------------------------------------------
                    */

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $systemId,
                        'receiver_id' => $user->id,
                        'subject' => 'Mass Message',
                        'body' => $this->message,
                        'is_read' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    Cache::forget("user_messages_{$user->id}");
Cache::forget("user_unread_messages_{$user->id}");
Cache::forget("user_conversations_{$user->id}");

                    /*
                    |--------------------------------------------------------------------------
                    | Update conversation timestamp
                    |--------------------------------------------------------------------------
                    */

                    $conversation->update([
                        'last_message_at' => $now
                    ]);
                }
            });
    }
}