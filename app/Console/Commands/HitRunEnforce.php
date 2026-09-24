<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Services\SystemMessageService;
use App\Traits\WarnsForHitRun;

class HitRunEnforce extends Command
{
    use WarnsForHitRun;

    protected $signature = 'hitrun:enforce';
    protected $description = 'Apply Hit & Run after 7 days if requirements are not met';

    public function handle()
    {
        if (!config('hitrun.enabled', false)) {
            return;
        }

        $requiredSeedTime = config('hitrun.seedtime', 43200);

        $this->info('Checking torrents for Hit & Run...');

        History::with(['user','torrent'])
            ->where('hitrun',0)
            ->where('left',0)
            ->where('active',0)
            ->whereNotNull('completed_at')
            ->where('completed_at','<',now()->subDays((int) config('hitrun.enforce_days', 7)))

            // "Haven't been in the client" — last_event_at is only stamped by the announce
            // pipeline, unlike updated_at which refreshes on every Eloquent write.
            ->where(function ($q) {
                $q->where('last_event_at','<',now()->subHours(48))
                  ->orWhereNull('last_event_at');
            })

            ->whereHas('user', function ($q) {
                $q->whereIn('user_class',[1,2])
                  ->where('enabled','yes');
            })

            ->chunkById(100,function($rows) use ($requiredSeedTime){

                foreach($rows as $row){

                    if (!$row->torrent) {
                        continue;
                    }

                    // Skip torrents the user barely downloaded (< download_threshold % of
                    // the size). Not their fault — torrent may have run out of seeders,
                    // they changed their mind, or found a better one.
                    if ($row->downloadPercent() < (float) config('hitrun.download_threshold', 25)) {
                        continue;
                    }

                    $ratio = $row->effectiveRatio();

                    if($ratio >= 1 || $row->seedtime >= $requiredSeedTime){
                        continue;
                    }

                    $row->update([
                        'hitrun'           => 1,
                        'hitrun_warned_at' => now(),
                    ]);

                    $row->user->increment('hit_and_run_count');

                    $user = $row->user->fresh();
                    $hnrCount = $user->hit_and_run_count;

                    /*
                    |--------------------------------------------------------------------------
                    | Warn on every H&R: 14 days, or +3 days if already warned
                    |--------------------------------------------------------------------------
                    */

                    $warnResult = $this->applyHitRunWarning($user, $row, $requiredSeedTime, $ratio);

                    $this->warn("User {$user->id} {$warnResult}.");

                    /*
                    |--------------------------------------------------------------------------
                    | Download restriction at 20 H&R
                    |--------------------------------------------------------------------------
                    */

                    if ($hnrCount >= 20) {

                        $user->update([
                            'downloadpos' => 'no'
                        ]);

                        $this->sendSystemMessage(
                            $user->id,
                            'Download Privileges Restricted',
                            "
[b][color=red]DOWNLOAD RESTRICTED[/color][/b]

You have reached {$hnrCount} Hit & Runs.

Your download privileges have been restricted.

To restore downloading ability you must reseed torrents and reduce your Hit & Run count below 20.
"
                        );

                        $this->error("User {$user->id} downloads restricted.");
                    }

                    $this->info("H&R applied → {$row->torrent->name}");
                }

            });

        $this->info('Hit & Run enforcement finished.');
    }

    /*
    |--------------------------------------------------------------------------
    | System message helper with conversations
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = config('hitrun.system_user_id', 2);

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}
