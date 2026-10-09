<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use Carbon\CarbonImmutable;

class HotTorrentPolicy
{
    public const MAX_HOT_TORRENTS = 30;

    /** Normalize every factor to 0–100; activity counts saturate at 100. */
    public function normalize(float $value): float
    {
        return 100 * log1p(min(100, max(0, $value))) / log(101);
    }

    public function score(Torrent $torrent, int $completed24h, int $completed7d, CarbonImmutable $now): float
    {
        if (! $torrent->approved || ($torrent->seeders < 1 && $torrent->leechers < 1 && $completed7d < 1)) {
            return 0;
        }
        $ageDays = $torrent->created_at
            ? max(0, CarbonImmutable::parse($torrent->created_at)->diffInSeconds($now)) / 86400
            : 30;
        $freshness = 100 * max(0, 1 - $ageDays / 30);

        return round(
            .35 * $this->normalize($completed7d)
            + .25 * $this->normalize($completed24h)
            + .20 * $this->normalize((int) $torrent->leechers)
            + .10 * $this->normalize((int) $torrent->seeders)
            + .10 * $this->normalize($freshness), 6
        );
    }
}
