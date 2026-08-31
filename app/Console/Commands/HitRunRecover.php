<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class HitRunRecover extends Command
{
    protected $signature = 'hitrun:recover';
    protected $description = 'Automatically remove Hit & Run when seeding requirements are met';

    public function handle()
    {
        $requiredSeedTime = config('hitrun.seedtime', 43200);

        $this->info('');
        $this->info('--------------------------------------');
        $this->info('Hit & Run Recovery Scan Started');
        $this->info('Required seedtime: '.$requiredSeedTime.' seconds');
        $this->info('--------------------------------------');

        $totalRecovered = 0;

        History::with(['user','torrent'])
            ->where('hitrun',1)
            ->where(function ($q) use ($requiredSeedTime) {

                $q->where('seedtime','>=',$requiredSeedTime)
                  ->orWhereRaw('uploaded >= downloaded');

            })
            ->chunkById(200,function ($rows) use ($requiredSeedTime,&$totalRecovered) {

                foreach ($rows as $row) {

                    if (!$row->user || !$row->torrent) {
                        $this->warn("Skipping history {$row->id} (missing user/torrent)");
                        continue;
                    }

                    $ratio = $row->downloaded > 0
                        ? $row->uploaded / $row->downloaded
                        : 0;

                    $this->line(
                        "Checking history {$row->id} | ".
                        "User {$row->user->username} ({$row->user_id}) | ".
                        "Seedtime {$row->seedtime}s | ".
                        "Ratio ".round($ratio,3)
                    );

                    if ($ratio >= 1 || $row->seedtime >= $requiredSeedTime) {

                        $firstRecovery = is_null($row->hitrun_removed_at);

                        /*
                        |--------------------------------------------------------------------------
                        | Remove Hit & Run
                        |--------------------------------------------------------------------------
                        */

                        $row->update([
                            'hitrun' => 0,
                            'hitrun_removed_at' => now()
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Reduce user's Hit & Run counter
                        |--------------------------------------------------------------------------
                        */

                        DB::table('users')
                            ->where('id',$row->user_id)
                            ->where('hit_and_run_count','>',0)
                            ->decrement('hit_and_run_count');

                        $user = $row->user->fresh();

                        /*
                        |--------------------------------------------------------------------------
                        | Console info for removal
                        |--------------------------------------------------------------------------
                        */

                        $this->info(
                            "H&R REMOVED → User: {$user->username} ({$user->id}) | ".
                            "Torrent: {$row->torrent->name} | ".
                            "Seedtime: {$row->seedtime}s | ".
                            "Ratio: ".round($ratio,3)
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Send message only first time
                        |--------------------------------------------------------------------------
                        */

                        if ($firstRecovery) {

                            $torrentLink = route('torrents.show',[
                                'id'=>$row->torrent_id,
                                'slug'=>$row->torrent->slug
                            ]);

                            $this->sendSystemMessage(
                                $user->id,
                                'Hit & Run Removed',
                                "
Your Hit & Run has been removed.

Torrent: [url={$torrentLink}]{$row->torrent->name}[/url]

Thank you for completing the seeding requirements.
"
                            );
                        }

                        $totalRecovered++;

                        /*
                        |--------------------------------------------------------------------------
                        | Restore download privileges
                        |--------------------------------------------------------------------------
                        */

                        if ($user->hit_and_run_count < 20 && $user->downloadpos === 'no') {

                            $this->info("Downloads restored for {$user->username} ({$user->id})");

                            $user->update([
                                'downloadpos' => 'yes'
                            ]);

                            $this->sendSystemMessage(
                                $user->id,
                                'Download Privileges Restored',
                                "
Your download privileges have been restored.

Thank you for reseeding torrents and reducing your Hit & Run count.
"
                            );
                        }
                    }
                }

            });

        $this->info('');
        $this->info('--------------------------------------');
        $this->info("Recovery scan completed");
        $this->info("Total H&R removed: ".$totalRecovered);
        $this->info('--------------------------------------');
        $this->info('');
    }

    /*
    |--------------------------------------------------------------------------
    | System message helper
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId,$subject,$body)
    {
        $systemId = config('hitrun.system_user_id',2);

        $conversation = Conversation::where(function ($q) use ($systemId,$userId) {

            $q->where('user_one',$systemId)
              ->where('user_two',$userId);

        })
        ->orWhere(function ($q) use ($systemId,$userId) {

            $q->where('user_one',$userId)
              ->where('user_two',$systemId);

        })->first();

        if (!$conversation) {

            $conversation = Conversation::create([
                'user_one'=>$systemId,
                'user_two'=>$userId,
                'subject'=>'System Notifications',
                'last_message_at'=>now(),
            ]);

        }

        Message::create([
            'conversation_id'=>$conversation->id,
            'receiver_id'=>$userId,
            'sender_id'=>$systemId,
            'subject'=>$subject,
            'body'=>$body,
            'is_read'=>0
        ]);

        $conversation->update([
            'last_message_at'=>now()
        ]);
    }
}