<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassMessageChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $message;
    protected array $userIds;

    public function __construct(string $message, array $userIds)
    {
        $this->message = $message;
        $this->userIds = $userIds;
    }

    public function handle()
    {
        $messages = [];

        foreach ($this->userIds as $userId) {
            $messages[] = [
                'sender_id'   => 2,
                'receiver_id' => $userId,
                'subject'     => 'Mass Message',
                'body'        => $this->message,
                'is_read'     => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        Message::insert($messages);
    }
}
