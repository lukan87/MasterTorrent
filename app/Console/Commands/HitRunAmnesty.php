<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HitRun\HitRunAmnestyService;

class HitRunAmnesty extends Command
{
    protected $signature = 'hitrun:amnesty'
        . '{--user= : Only process the hit & runs of this specific user id}'
        . '{--notify : Send a notification message to every affected user}'
        . '{--dry-run : Preview the changes without saving anything}';

    protected $description = 'Give every user a 1:1 ratio on each of their hit & runs and clear them all';

    public function handle(): int
    {
        $service = new HitRunAmnestyService();

        $dryRun = (bool) $this->option('dry-run');
        $userId = $this->option('user') ? (int) $this->option('user') : null;
        $notify = (bool) $this->option('notify');

        $stats = $dryRun
            ? $service->preview($userId)
            : $service->apply($userId, $notify);

        $scope = $userId ? " for user #{$userId}" : '';

        $this->info('');
        $this->info('-------------------------------------------');
        $this->info(($dryRun ? '[DRY RUN] Hit & Run Amnesty Preview' : '[APPLIED] Hit & Run Amnesty') . $scope);
        $this->info('-------------------------------------------');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Hit & run records processed', number_format($stats['records'])],
                ['Affected users', number_format($stats['users'])],
                ['Total upload credited (for 1:1)', number_format($stats['total_upload_credited']) . ' bytes'],
                ['Download privileges restored', number_format($stats['downloads_restored'])],
                ['H&R warnings cleared', number_format($stats['warnings_cleared'])],
            ]
        );

        $this->info('-------------------------------------------');

        if ($dryRun) {
            $this->warn('This was only a preview. No data was changed.');
            $this->line('Re-run without --dry-run to apply.');
        } elseif ($stats['records'] > 0 && !$notify) {
            $this->line('Tip: add --notify to send a summary to each affected user.');
        }

        $this->info('');

        return self::SUCCESS;
    }
}
