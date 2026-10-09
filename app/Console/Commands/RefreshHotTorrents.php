<?php

namespace App\Console\Commands;

use App\Models\Torrent;
use App\Services\Torrent\HotTorrentPolicy;
use App\Services\Torrent\HotTorrentRankingService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefreshHotTorrents extends Command
{
    protected $signature = 'torrents:refresh-hot {--dry-run : Report changes without saving them}';
    protected $description = 'Publish the top 30 visible torrents ranked by recent downloads, active peers and freshness';

    public function handle(HotTorrentPolicy $policy, HotTorrentRankingService $ranking): int
    {
        $lock = Cache::lock('torrents:refresh-hot', 3600);
        if (! $lock->get()) {
            $this->warn('A hot torrent refresh is already running.');
            return self::FAILURE;
        }
        try {
            $snapshot = $ranking->calculate($policy, CarbonImmutable::now());
            $ids = array_keys($snapshot['scores']);
            $previous = $ranking->ids();
            $on = count(array_diff($ids, $previous));
            $off = count(array_diff($previous, $ids));
            if (! $this->option('dry-run')) {
                // Keep the existing database flags compatible with administrative integrations.
                // All page consumers use the atomic cache snapshot as their source of truth.
                DB::transaction(function () use ($snapshot) {
                    DB::table((new Torrent)->getTable())->update([
                        'hot' => false, 'hot_score' => 0, 'hot_until' => null, 'hot_cooldown_until' => null,
                    ]);
                    foreach ($snapshot['scores'] as $id => $score) {
                        DB::table((new Torrent)->getTable())->where('id', $id)->whereNull('deleted_at')
                            ->where('approved', true)->update(['hot' => true, 'hot_score' => $score]);
                    }
                });
                $ranking->publish($snapshot);
                // Retire the former independent homepage model cache.
                Cache::forget('trending_torrents');
            }
            $total = count($ids);
            $this->info(($this->option('dry-run') ? 'Dry run: ' : '')."{$on} switched on, {$off} switched off; {$total} hot torrents.");
            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
