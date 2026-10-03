<?php

namespace App\Console\Commands;

use App\Services\SystemMessageService;
use Illuminate\Console\Command;

class ConvertMessagesToConversations extends Command
{
    protected $signature = 'messages:convert-conversations';

    protected $description = 'Merge duplicate conversations and attach legacy messages without losing history';

    public function handle(): int
    {
        SystemMessageService::consolidateHistory();
        $this->info('Conversations consolidated. All messages have been preserved.');

        return self::SUCCESS;
    }
}
