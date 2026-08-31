<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class SyncHistoryFromRedis implements ShouldQueue
{

use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('tracker-sync');
    }

    public function handle()
    {
        $keys = Redis::keys('history_buffer:*');

        foreach ($keys as $key) {

            $parts = explode(':', $key);

            $userId = $parts[1];
            $torrentId = $parts[2];

            $data = Redis::hgetall($key);

            if (!$data) {
                continue;
            }

            DB::table('history')
                ->where('user_id', $userId)
                ->where('torrent_id', $torrentId)
                ->update([
                    'uploaded' => DB::raw("uploaded + ".($data['uploaded'] ?? 0)),
                    'downloaded' => DB::raw("downloaded + ".($data['downloaded'] ?? 0)),
                    'actual_uploaded' => DB::raw("actual_uploaded + ".($data['actual_uploaded'] ?? 0)),
                    'actual_downloaded' => DB::raw("actual_downloaded + ".($data['actual_downloaded'] ?? 0)),
                ]);

            Redis::del($key);
        }
    }
}