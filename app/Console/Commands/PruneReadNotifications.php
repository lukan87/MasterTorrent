<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruneReadNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Delete read notifications older than X days';

    public function handle(): int
    {
        $days = config('notifications.prune_after_days', 28);

        $cutoff = Carbon::now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} old read notifications.");

        return self::SUCCESS;
    }
}

