<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;

class DeleteOldMessages extends Command
{
    protected $signature = 'messages:cleanup';

    protected $description = 'Delete messages older than 30 days and remove empty conversations';

    public function handle()
    {
        $cutoff = Carbon::now()->subDays(30);

        $this->info('Starting message cleanup...');

        $conversationIds = [];

        /*
        |--------------------------------------------------------------------------
        | Delete old messages in chunks (constant memory)
        |--------------------------------------------------------------------------
        */

        Message::where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(1000, function ($messages) use (&$conversationIds) {

                foreach ($messages as $message) {
                    $conversationIds[$message->conversation_id] = true;
                    $message->delete();
                }

            });

        $this->info('Old messages deleted.');

        /*
        |--------------------------------------------------------------------------
        | Remove empty conversations
        |--------------------------------------------------------------------------
        */

        $deletedConversations = 0;

        foreach (array_keys($conversationIds) as $conversationId) {

            $hasMessages = Message::where('conversation_id', $conversationId)->exists();

            if (!$hasMessages) {

                Conversation::where('id', $conversationId)->delete();
                $deletedConversations++;
            }

        }

        $this->info("Deleted {$deletedConversations} empty conversations.");

        $this->info('Cleanup completed successfully.');
    }
}