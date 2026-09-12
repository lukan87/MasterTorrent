<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\UserClass;
use App\Services\SystemMessageService;

class HitRunReminder extends Command
{
    protected $signature = 'hitrun:reminder';
    protected $description = 'Send reminder after 48h if torrent stopped before meeting requirements';

    public function handle()
    {
        if (!config('hitrun.enabled', false)) {
            return;
        }

        $requiredSeedTime = config('hitrun.seedtime', 43200);

        $this->info('Scanning for stopped torrents...');

        History::with(['user','torrent'])
            ->where('hitrun',0)
            ->where('prewarn',0)
            ->where('left',0)
            ->where('active',0)
            ->where('seedtime','<',$requiredSeedTime)
            ->where('completed_at','<', now()->subHours(48))
            ->where('updated_at','<', now()->subHours(48))

            ->whereHas('user', function ($q) {
                $q->whereIn('user_class', [
                    UserClass::USER,
                    UserClass::ELITE_USER
                ]);
            })

            ->chunkById(100,function($rows) use ($requiredSeedTime){

                foreach($rows as $row){

                    if(!$row->torrent || !$row->user){
                        continue;
                    }

                    $downloadPercent = $row->torrent->size > 0
                        ? $row->downloaded / $row->torrent->size
                        : 0;

                    if($downloadPercent < 0.25){
                        continue;
                    }

                    $ratio = $row->downloaded > 0
                        ? $row->uploaded / $row->downloaded
                        : 0;

                    if($ratio >= 1){
                        continue;
                    }

                    $remaining = max(0,$requiredSeedTime - $row->seedtime);

                    $torrentLink = route('torrents.show',[
                        'id'=>$row->torrent->id,
                        'slug'=>$row->torrent->slug
                    ]);

                    $body = "
[b][color=orange]SEEDING REMINDER[/color][/b]

You stopped seeding a torrent before meeting the requirements.

[b]Torrent:[/b] [url={$torrentLink}]{$row->torrent->name}[/url]

[b]Required:[/b]
• Seedtime: 12 hours
• OR Ratio: 1.00

[b]Current Seedtime:[/b] ".gmdate('H:i:s',$row->seedtime)."
[b]Remaining Seedtime:[/b] ".gmdate('H:i:s',$remaining)."
[b]Ratio:[/b] ".number_format($ratio,2)."

You must reach the requirement within [b]7 days[/b] after completing the download.

If neither 12 hours of seeding nor a ratio of 1.00 is reached within this period, the torrent will be marked as [b]Hit & Run[/b].
";

                    $this->sendSystemMessage(
                        $row->user_id,
                        'Seeding Reminder',
                        $body
                    );

                    $row->update([
                        'prewarn'=>1,
                        'prewarned_at'=>now()
                    ]);

                    $this->info("Reminder sent → {$row->torrent->name}");
                }

            });

        $this->info('Reminder scan completed.');
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