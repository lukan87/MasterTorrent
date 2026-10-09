<?php

namespace App\Services\Torrent;

use App\Models\Category;
use App\Models\History;
use App\Models\Torrent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HotTorrentRankingService
{
    public const CACHE_KEY = 'torrents:hot:ranking:v1';

    private ?array $snapshot = null;

    public function visibleQuery(): Builder
    {
        // The public Browse/Home sections exclude adult categories and soft-deleted torrents.
        return Torrent::query()->where('approved', true)->whereNotIn('category_id', Category::ADULT_IDS);
    }

    /** Called only by the scheduled command, never by page requests. */
    public function calculate(HotTorrentPolicy $policy, CarbonImmutable $now): array
    {
        $completed = History::query()->select('torrent_id')
            ->selectRaw('COUNT(*) AS completed_7d')
            ->selectRaw('SUM(CASE WHEN completed_at >= ? THEN 1 ELSE 0 END) AS completed_24h', [$now->subDay()])
            ->whereBetween('completed_at', [$now->subDays(7), $now])->groupBy('torrent_id');
        $ranked = [];
        $this->visibleQuery()->leftJoinSub($completed, 'recent_downloads', fn ($join) =>
                $join->on('torrents.id', '=', 'recent_downloads.torrent_id'))
            ->select(['torrents.id', 'torrents.approved', 'torrents.created_at', 'torrents.seeders', 'torrents.leechers'])
            ->addSelect(DB::raw('COALESCE(completed_7d, 0) AS completed_7d, COALESCE(completed_24h, 0) AS completed_24h'))
            ->chunkById(500, function ($torrents) use ($policy, $now, &$ranked) {
                foreach ($torrents as $torrent) {
                    $score = $policy->score($torrent, (int) $torrent->completed_24h, (int) $torrent->completed_7d, $now);
                    if ($score > 0) {
                        $ranked[$torrent->id] = ['score' => $score, 'created_at' => $torrent->created_at?->getTimestamp() ?? 0];
                    }
                }
                // Keep memory bounded to the best 30 plus the current database batch.
                uksort($ranked, fn ($a, $b) => ($ranked[$b]['score'] <=> $ranked[$a]['score'])
                    ?: ($ranked[$b]['created_at'] <=> $ranked[$a]['created_at']) ?: ($b <=> $a));
                $ranked = array_slice($ranked, 0, HotTorrentPolicy::MAX_HOT_TORRENTS, true);
            }, 'torrents.id', 'id');

        return ['generated_at' => $now->toIso8601String(),
            'scores' => array_map(fn ($entry) => $entry['score'], $ranked)];
    }

    /** One Redis SET publishes IDs, order and scores together; retain the last successful result. */
    public function publish(array $snapshot): void
    {
        if (! Cache::forever(self::CACHE_KEY, $snapshot)) {
            throw new RuntimeException('Could not publish the hot torrent ranking.');
        }
        $this->snapshot = $snapshot;
    }

    public function ids(): array
    {
        $this->snapshot ??= Cache::get(self::CACHE_KEY, ['scores' => []]);
        return array_keys($this->snapshot['scores']);
    }

    public function contains(int $id): bool
    {
        $this->ids();
        return isset($this->snapshot['scores'][$id]);
    }

    /** Fetch at most 30 models and retain the cached order; do not aggregate or score here. */
    public function torrents(int $limit = HotTorrentPolicy::MAX_HOT_TORRENTS): Collection
    {
        $ids = $this->ids();
        if (! $ids || $limit <= 0) {
            return collect();
        }
        $order = array_flip($ids);
        return $this->visibleQuery()->whereIn('id', $ids)->with('category:id,name')->get()
            ->sortBy(fn ($torrent) => $order[$torrent->id])->take($limit)->values()
            ->each(function ($torrent) {
                $torrent->hot_score = $this->snapshot['scores'][$torrent->id];
            });
    }
}
