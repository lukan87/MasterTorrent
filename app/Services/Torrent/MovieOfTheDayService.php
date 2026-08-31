<?php

namespace App\Services\Torrent;

use App\Models\Torrent;

class MovieOfTheDayService
{
    protected array $categoryIds = [11, 12, 24, 25, 31, 32, 54, 55, 81, 82];

   public function get()
{
    $topTorrents = $this->getTopSince(now()->subDay());

    if ($topTorrents->isEmpty()) {
        $topTorrents = $this->getTopSince(now()->subWeek());
    }

    if ($topTorrents->isEmpty()) {
        return null; // or fallback logic
    }

    return $topTorrents->random();
}

    protected function getTopSince($date)
    {
        return Torrent::whereIn('category_id', $this->categoryIds)
            ->where('created_at', '>=', $date)
            ->orderByDesc('seeders')
            ->orderByDesc('times_completed')
            ->take(5)
            ->get();
    }
}
