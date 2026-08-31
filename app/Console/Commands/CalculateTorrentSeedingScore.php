<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;

class CalculateTorrentSeedingScore extends Command
{
    protected $signature = 'tracker:calculate-torrent-seeding-score';

    protected $description = 'Calculate seeding score per torrent';

    public function handle()
    {

        $requiredSeedTime = 43200;

        History::join('torrents','history.torrent_id','=','torrents.id')
        ->select('history.id','history.seedtime','torrents.size')
        ->chunk(500,function($rows) use ($requiredSeedTime){

            foreach($rows as $row){

                $seedtime = $row->seedtime ?? 0;

                $sizeGB = $row->size / (1024 ** 3);

                $timeFactor = min(1, $seedtime / $requiredSeedTime);

                $sizeFactor = log($sizeGB + 1, 2);

                $score = $timeFactor * $sizeFactor;

                History::where('id',$row->id)
                    ->update(['seeding_score'=>$score]);

            }

        });

        $this->info("Torrent seeding scores updated.");
    }
}