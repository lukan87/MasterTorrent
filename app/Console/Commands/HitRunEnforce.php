<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Models\Message;
use App\Models\Conversation;

class HitRunEnforce extends Command
{
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
            ->where('completed_at','<',now()->subDays(7))
            ->where('updated_at','<',now()->subHours(48))

            ->whereHas('user', function ($q) {
                $q->whereIn('user_class',[1,2])
                  ->where('enabled','yes');
            })

            ->limit(500)
            ->chunkById(100,function($rows) use ($requiredSeedTime){

                foreach($rows as $row){

                    if(!$row->torrent){
                        continue;
                    }

                    $ratio = $row->downloaded > 0
                        ? $row->uploaded / $row->downloaded
                        : 0;

                    if($ratio >= 1 || $row->seedtime >= $requiredSeedTime){
                        continue;
                    }

                    $row->update([
                        'hitrun'=>1
                    ]);

                    $row->user->increment('hit_and_run_count');

                    $user = $row->user->fresh();
                    $hnrCount = $user->hit_and_run_count;

                    /*
                    |--------------------------------------------------------------------------
                    | Warning at 10 H&R
                    |--------------------------------------------------------------------------
                    */

                    if ($hnrCount >= 10 && $user->warned == 0) {

                        $user->update([
                            'warned' => 1,
                            'warned_until' => now()->addDays(14),
                            'warned_reason' => 'You have more than 10 hit and runs!'
                        ]);

                        $this->sendSystemMessage(
                            $user->id,
                            'Warning: Too Many Hit & Runs',
                            "
[b][color=orange]WARNING[/color][/b]

You currently have {$hnrCount} Hit & Runs.

If you reach 20 Hit & Runs your download privileges will be restricted.

Please reseed your torrents to remove the Hit & Runs.

This warning will expire in 14 days.
"
                        );

                        $this->warn("User {$user->id} received a warning for H&R.");
                    }

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

                    /*
                    |--------------------------------------------------------------------------
                    | Hit & Run message
                    |--------------------------------------------------------------------------
                    */

                    $torrentLink = route('torrents.show',[
                        'id'=>$row->torrent->id,
                        'slug'=>$row->torrent->slug
                    ]);

                    $this->sendSystemMessage(
                        $row->user_id,
                        'Hit & Run Issued',
                        "You received a Hit & Run for torrent: [url={$torrentLink}]{$row->torrent->name}[/url]"
                    );

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

        $conversation = Conversation::where(function ($q) use ($systemId, $userId) {
            $q->where('user_one', $systemId)
              ->where('user_two', $userId);
        })
        ->orWhere(function ($q) use ($systemId, $userId) {
            $q->where('user_one', $userId)
              ->where('user_two', $systemId);
        })
        ->first();

        if (!$conversation) {

            $conversation = Conversation::create([
                'user_one' => $systemId,
                'user_two' => $userId,
                'subject' => 'System Notifications',
                'last_message_at' => now(),
            ]);

        }

        Message::create([
            'conversation_id' => $conversation->id,
            'receiver_id' => $userId,
            'sender_id' => $systemId,
            'subject' => $subject,
            'body' => $body,
            'is_read' => 0
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);
    }
}