<?php

namespace App\Services\Torrent;

use App\Models\Category;
use App\Models\Torrent;
use Illuminate\Http\Request;

class TorrentFeaturedService
{
    public const PER_PAGE = 7;
    public function card(string $kind, Request $request): array
    {
        abort_unless(in_array($kind, ['sticky', 'hot'], true), 404);
        if ($kind === 'hot') {
            $torrents = app(HotTorrentRankingService::class)->torrents();
            $page = max(1, $request->integer('hot_page', 1));
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                $torrents->forPage($page, self::PER_PAGE)->values(), $torrents->count(), self::PER_PAGE, $page,
                ['pageName' => 'hot_page']
            );
        } else {
            $items = Torrent::query()->whereNotIn('category_id', Category::ADULT_IDS)
                ->select(['id', 'name', 'slug', 'category_id', 'size', 'seeders', 'leechers', 'poster', 'tmdbid', 'tmdb_type', 'imdbid'])
                ->with('category:id,name')->where('sticky', true)
                ->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(self::PER_PAGE, ['*'], 'sticky_page', max(1, $request->integer('sticky_page', 1)));
        }
        app(TorrentPreviewService::class)->decorate($items->getCollection());
        $items->appends($request->except($kind.'_page'));
        $items->withPath(route('home'));

        return ['kind' => $kind, 'items' => $items];
    }
}
