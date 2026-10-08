<?php

namespace App\Console\Commands;

use App\Services\SystemMessageService;
use Illuminate\Console\Command;

class PruneEmptyConversations extends Command
{
    protected $signature = 'messages:prune-empty';

    protected $description = 'Remove conversations with no messages, including after direct database deletions';

    public function handle(): int
    {
        $count = SystemMessageService::pruneEmptyConversations();
        $this->info("Removed {$count} empty conversations. No messages were deleted.");

        return self::SUCCESS;
    }
}
