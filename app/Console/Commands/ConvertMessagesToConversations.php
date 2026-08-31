<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;

class ConvertMessagesToConversations extends Command
{
    protected $signature = 'messages:convert-conversations';

    protected $description = 'Convert existing messages into conversations';

    public function handle()
    {
        $this->info('Starting message conversion...');

        $messages = Message::whereNull('conversation_id')
            ->orderBy('created_at')
            ->get();

        $bar = $this->output->createProgressBar($messages->count());
        $bar->start();

foreach ($messages as $message) {

    $sender = User::find($message->sender_id);
    $receiver = User::find($message->receiver_id);

    // Skip messages with deleted users
    if (!$sender || !$receiver) {
        $this->warn("Skipping message {$message->id} (user missing)");
        continue;
    }

    $userA = min($sender->id, $receiver->id);
    $userB = max($sender->id, $receiver->id);

    $conversation = Conversation::where('user_one',$userA)
        ->where('user_two',$userB)
        ->first();

    if (!$conversation) {

        $conversation = Conversation::create([
            'user_one' => $userA,
            'user_two' => $userB,
            'subject' => $message->subject ?? 'Conversation',
            'last_message_at' => $message->created_at
        ]);

    }

    $message->conversation_id = $conversation->id;
    $message->save();

    $conversation->update([
        'last_message_at' => $message->created_at
    ]);

    $bar->advance();
}

        $bar->finish();

        $this->info("\nConversion complete!");
    }
}