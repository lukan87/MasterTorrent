<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Traits\WarnsForHitRun;

class HitRunBackfill extends Command
{
    use WarnsForHitRun;

    protected $signature = 'hitrun:backfill-warn
                            {--dry-run : Preview which existing H&R rows would be warned without changing anything}';
    protected $description = 'Backfill the Hit & Run warning + enriched message for existing hitrun=1 rows';

    public function handle()
    {
        $requiredSeedTime = config('hitrun.seedtime', 43200);
        $dryRun           = (bool) $this->option('dry-run');

        $this->info('Backfilling Hit & Run warnings for existing H&R rows '.($dryRun ? '(DRY RUN — no changes)' : ''));

        $total = 0;

        History::with(['user','torrent'])
            ->where('hitrun', 1)
            ->whereNull('hitrun_warned_at')
            ->chunkById(200, function ($rows) use ($requiredSeedTime, $dryRun, &$total) {

                foreach ($rows as $row) {

                    if (!$row->user || !$row->torrent) {
                        $this->warn("Skipping history {$row->id} (missing user/torrent)");
                        continue;
                    }

                    $ratio = $row->effectiveRatio();

                    // Already meets the requirement — let hitrun:recover handle it.
                    if ($ratio >= 1 || $row->seedtime >= $requiredSeedTime) {
                        $this->line("Skip history {$row->id}: already meets requirements");
                        continue;
                    }

                    $total++;

                    if ($dryRun) {
                        $this->line("Would warn user {$row->user_id} for history {$row->id} ({$row->torrent->name})");
                        continue;
                    }

                    $result = $this->applyHitRunWarning($row->user, $row, $requiredSeedTime, $ratio);

                    $row->update([
                        'hitrun_warned_at' => now(),
                    ]);

                    $this->info("Warned user {$row->user_id} ({$result}) for history {$row->id} → {$row->torrent->name}");
                }
            });

        $this->info('Backfill complete. '.($dryRun ? 'Would warn' : 'Warned').' '.$total.' H&R row(s).');
    }
}