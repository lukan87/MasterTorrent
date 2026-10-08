<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class MovieOfTheDayService
{
    protected array $categoryIds = [11, 24, 31, 54];

    public function get(): ?Torrent
    {
        $topTorrents = $this->getTopSince(now()->subDay());

        if ($topTorrents->isEmpty()) {
            $topTorrents = $this->getTopSince(now()->subWeek());
        }

        if ($topTorrents->isEmpty()) {
            return null;
        }

        // Load metadata only for the chosen release; the other candidates need no category query.
        return $topTorrents->random()->load('category:id,name');
    }

    protected function getTopSince(CarbonInterface $date): Collection
    {
        return Torrent::query()
            ->select(['id', 'name', 'slug', 'poster', 'category_id', 'created_at', 'seeders', 'leechers', 'times_completed'])
            ->whereIn('category_id', $this->categoryIds)
            ->where('created_at', '>=', $date)
            ->orderByDesc('seeders')
            ->orderByDesc('times_completed')
            ->take(5)
            ->get();
    }
}
