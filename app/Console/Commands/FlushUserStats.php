<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FlushUserStats extends Command
{
    protected $signature = 'tracker:flush-user-stats';
    protected $description = 'Flush buffered user upload/download stats from Redis to MySQL';

    public function handle(): int
    {
        $cursor = 0;
        $processed = 0;

        do {
            [$cursor, $keys] = Redis::scan($cursor, [
                'match' => 'user_stats:*',
                'count' => 100
            ]);

            if (empty($keys)) {
                continue;
            }

            foreach ($keys as $key) {

                $userId = (int) str_replace('user_stats:', '', $key);
                $processingKey = $key . ':processing';

                // 🔒 Atomic lock via RENAMENX (prevents race conditions)
                if (!Redis::renamenx($key, $processingKey)) {
                    continue;
                }

                try {
                    $stats = Redis::hgetall($processingKey);

                    if (empty($stats)) {
                        Redis::del($processingKey);
                        continue;
                    }

                    $uploaded   = (int) ($stats['uploaded'] ?? 0);
                    $downloaded = (int) ($stats['downloaded'] ?? 0);

                    if ($uploaded === 0 && $downloaded === 0) {
                        Redis::del($processingKey);
                        continue;
                    }

                    // 💾 Persist to DB (atomic increment)
                    User::where('id', $userId)->update([
                        'uploaded'   => DB::raw("uploaded + {$uploaded}"),
                        'downloaded' => DB::raw("downloaded + {$downloaded}")
                    ]);

                    // 🧹 Cleanup processed key
                    Redis::del($processingKey);

                    $processed++;

                } catch (\Throwable $e) {
                    // ⚠️ If something fails, restore key so it's not lost
                    Redis::rename($processingKey, $key);

                    \Log::error("FlushUserStats failed for user {$userId}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } while ($cursor != 0);

        $this->info("✅ Flushed stats for {$processed} users.");

        return self::SUCCESS;
    }
}