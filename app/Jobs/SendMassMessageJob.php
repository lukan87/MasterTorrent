<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $message;
    protected array $userIds;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, array $userIds)
    {
        $this->message = $message;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
   public function handle()
    {
        // Process users in chunks to avoid memory issues
        $chunks = array_chunk($this->userIds, 500); // Adjust chunk size if needed

        foreach ($chunks as $chunk) {
            $messages = [];

            foreach ($chunk as $userId) {
                $messages[] = [
                    'sender_id' => 2, // Admin ID
                    'receiver_id' => $userId,
                    'subject' => 'Mass Message',
                    'body' => $this->message,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert all at once using "upsert" to prevent duplicates
            Message::upsert(
                $messages,
                ['sender_id', 'receiver_id', 'subject', 'body'], // Unique constraint to avoid duplicates
                ['is_read', 'updated_at'] // Fields to update if exists
            );
        }
    }
}
