<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class SyncStatsFromRedis implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('tracker-sync');
    }

    public function handle()
    {
        $keys = Redis::keys('user:*:uploaded');

        foreach ($keys as $key) {
            $userId = explode(':', $key)[1];

            $up = Redis::getdel("user:$userId:uploaded");
            $down = Redis::getdel("user:$userId:downloaded");

            if ($up || $down) {
                DB::table('users')
                    ->where('id', $userId)
                    ->incrementEach([
                        'uploaded' => (int)$up,
                        'downloaded' => (int)$down
                    ]);
            }
        }
    }
}
