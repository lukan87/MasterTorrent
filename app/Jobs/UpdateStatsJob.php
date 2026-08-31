<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public float $uploaded;
    public float $downloaded;
    public int $torrentId;
    public bool $completed;

    public function __construct(int $userId, float $uploaded, float $downloaded, int $torrentId, bool $completed = false)
    {
        $this->userId = $userId;
        $this->uploaded = $uploaded;
        $this->downloaded = $downloaded;
        $this->torrentId = $torrentId;
        $this->completed = $completed;
    }

    public function handle()
    {
        DB::transaction(function () {
            DB::table('users')->where('id', $this->userId)->update([
                'uploaded' => DB::raw("uploaded + {$this->uploaded}"),
                'downloaded' => DB::raw("downloaded + {$this->downloaded}")
            ]);

            if ($this->completed) {
                DB::table('torrents')->where('id', $this->torrentId)->increment('times_completed');
            }
        });
    }
}
