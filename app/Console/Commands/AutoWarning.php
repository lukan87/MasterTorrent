<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\History;
use App\Models\User;
use App\Models\Warning;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
use App\Services\SystemMessageService;

class AutoWarning extends Command
{
    protected $signature = 'auto:warning';

    protected $description = 'Automatically Gives Warning To Users And Records In Warnings Table';

    final public function handle(): void
    {
        if (config('hitrun.enabled') !== true) {
            $this->info('Hit and Run warning feature is disabled.');
            return;
        }

        try {

            History::with(['user','torrent'])

                ->where('created_at','>','2024-12-15 00:00:00')
                ->where('prewarned_at','<=',now()->subDays(config('hitrun.prewarn')))
                ->where('hitrun',0)
                ->where('immune',0)
                ->where('actual_downloaded','>',0)
                ->where('active',0)
                ->where('seedtime','<=',config('hitrun.seedtime'))
                ->where('updated_at','<',now()->subDays(config('hitrun.grace')))

                ->whereHas('user', fn($q) =>
                    $q->where('is_immune',false)
                      ->where('donor','no')
                      ->where('user_class','<',3)
                      ->where('enabled','yes')
                      ->where('warned',0)
                )

                ->whereHas('torrent', fn($q) =>
                    $q->whereRaw(
                        'history.actual_downloaded > torrents.size * ?',
                        [config('hitrun.buffer') / 100]
                    )->where('seeders','>',0)
                )

                ->whereRaw('(history.uploaded / NULLIF(history.actual_downloaded,0)) < 1.0')

                ->whereDoesntHave('user.warnings', fn($q) =>
                    $q->withTrashed()
                      ->whereColumn('warnings.torrent','=','history.torrent_id')
                )

                ->chunkById(100,function($rows){

                    foreach($rows as $hr){

                        if(!$hr->user || !$hr->torrent){
                            $this->warn("Skipping history {$hr->id} (missing user/torrent)");
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Extend warned_until
                        |--------------------------------------------------------------------------
                        */

                        $additionalDays = config('hitrun.expire');

                        $currentWarnedUntil = $hr->user->warned_until
                            ? now()->parse($hr->user->warned_until)
                            : now();

                        $newWarnedUntil = $currentWarnedUntil->addDays($additionalDays);

                        /*
                        |--------------------------------------------------------------------------
                        | Create Warning
                        |--------------------------------------------------------------------------
                        */

                        Warning::create([
                            'user_id' => $hr->user->id,
                            'warned_by' => config('hitrun.system_user_id',2),
                            'torrent' => $hr->torrent->id,
                            'reason' => "Hit and Run Warning For Torrent {$hr->torrent->name}",
                            'expires_on' => now()->addDays(config('hitrun.expire')),
                            'active' => true
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Mark history as H&R
                        |--------------------------------------------------------------------------
                        */

                        History::where('torrent_id',$hr->torrent_id)
                            ->where('user_id',$hr->user_id)
                            ->update(['hitrun'=>true]);

                        $hr->user->increment('hit_and_run_count');

                        $hr->user->update([
                            'warned'=>1,
                            'warned_until'=>$newWarnedUntil
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Send System Message
                        |--------------------------------------------------------------------------
                        */

                        $torrentLink = route('torrents.show',[
                            'id'=>$hr->torrent_id,
                            'slug'=>$hr->torrent->slug ?? ''
                        ]);

                        $body = "
You have received a Hit & Run warning.

Torrent:
[url={$torrentLink}]{$hr->torrent->name}[/url]

Please reseed the torrent to avoid further penalties.
";

                        $this->sendSystemMessage(
                            $hr->user_id,
                            'Hit and Run - Warning Added',
                            $body
                        );

                        $this->comment(
                            "Warning issued → User {$hr->user_id} | Torrent {$hr->torrent->name}"
                        );
                    }

                });

            /*
            |--------------------------------------------------------------------------
            | Check max warnings
            |--------------------------------------------------------------------------
            */

            $maxWarnings = config('hitrun.max_warnings');

            $users = Warning::query()
                ->select('user_id')
                ->where('active',1)
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= ?',[$maxWarnings])
                ->pluck('user_id');

            foreach($users as $userId){
                $this->comment("User {$userId} exceeded max warnings");
            }

            $this->comment('Automated User Warning Command Complete');

        }
        catch(Throwable $e){

            Log::error('Error sending warnings',[
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
                'trace'=>$e->getTraceAsString(),
            ]);

            $this->error('Error processing warnings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message inside conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = config('hitrun.system_user_id',2);

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}