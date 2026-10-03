<?php

namespace App\Helpers;

use App\Models\Torrent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TorrentHelper
{
    private const ADULT_CATEGORY_IDS = [27, 34];

    private const PER_PAGE = 50;

    private static array $allowedSortColumns = [
        'id',
        'name',
        'size',
        'seeders',
        'leechers',
        'times_completed',
        'created_at',
        'bumped_at',
    ];

    private static array $allowedDirections = [
        'asc',
        'desc',
    ];

    /**
     * Validate sorting input.
     */
    private static function sanitizeSort(
        ?string $column,
        ?string $direction
    ): array {
        $column = in_array(
            $column,
            self::$allowedSortColumns,
            true
        )
            ? $column
            : 'created_at';

        $direction = strtolower($direction ?? 'desc');

        $direction = in_array(
            $direction,
            self::$allowedDirections,
            true
        )
            ? $direction
            : 'desc';

        return [$column, $direction];
    }

    /**
     * Relations required by the torrent browse cards.
     */
    private static function applyBrowseRelations(Builder $query): void
    {
        $query
            ->with([
                'genres:id,name',
                'category:id,name,icon',
                'uploader:id,name,user_class',
                'bumper:id,name',
            ])
            ->withExists('subtitles');
    }

    /**
     * Normal torrent browse.
     */
    public static function buildTorrentQuery(
        Request $request,
        ?string $sortColumn = null,
        ?string $sortDirection = null
    ) {
        [$sortColumn, $sortDirection] = self::sanitizeSort(
            $sortColumn,
            $sortDirection
        );

        $query = Torrent::query()
            ->whereNotIn(
                'category_id',
                self::ADULT_CATEGORY_IDS
            );

        self::applyBrowseRelations($query);

        self::applyKeywordFilter(
            $query,
            $request
        );

        self::applyCategoryFilter(
            $query,
            $request
        );

        self::applyTmdbFilter(
            $query,
            $request
        );

        self::applyGenreFilter(
            $query,
            $request
        );

        self::applyStatusFilter(
            $query,
            $request
        );

        self::applySorting(
            $query,
            $request,
            $sortColumn,
            $sortDirection
        );

        return $query
            ->paginate(self::PER_PAGE)
            ->appends($request->query());
    }

    /**
     * Adult torrent browse.
     */
    public static function buildAdultTorrentQuery(
        Request $request,
        ?string $sortColumn = null,
        ?string $sortDirection = null
    ) {
        [$sortColumn, $sortDirection] = self::sanitizeSort(
            $sortColumn,
            $sortDirection
        );

        $query = Torrent::query()
            ->whereIn(
                'category_id',
                self::ADULT_CATEGORY_IDS
            );

        self::applyBrowseRelations($query);

        self::applyKeywordFilter(
            $query,
            $request
        );

        self::applyAdultCategoryFilter(
            $query,
            $request
        );

        self::applyTmdbFilter(
            $query,
            $request
        );

        self::applyGenreFilter(
            $query,
            $request
        );

        self::applyStatusFilter(
            $query,
            $request
        );

        self::applySorting(
            $query,
            $request,
            $sortColumn,
            $sortDirection
        );

        return $query
            ->paginate(self::PER_PAGE)
            ->appends($request->query());
    }

    /**
     * Keyword search.
     */
    private static function applyKeywordFilter(
        Builder $query,
        Request $request
    ): void {
        if (!$request->filled('keyword')) {
            return;
        }

        $keyword = trim(
            (string) $request->input('keyword')
        );

        if ($keyword === '') {
            return;
        }

        $query->where(
            function (Builder $q) use ($keyword) {
                $q
                    ->where(
                        'name',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'imdb_url',
                        'like',
                        '%' . $keyword . '%'
                    );
            }
        );
    }

    /**
     * Normal browse category filter.
     */
    private static function applyCategoryFilter(
        Builder $query,
        Request $request
    ): void {
        $categories = $request->input(
            'categories',
            []
        );

        if (!is_array($categories)) {
            return;
        }

        $categories = array_values(
            array_unique(
                array_map(
                    'intval',
                    array_filter(
                        $categories,
                        static fn ($category) =>
                            is_numeric($category)
                    )
                )
            )
        );

        /*
         * Prevent adult categories from appearing
         * in the normal torrent browser.
         */
        $categories = array_values(
            array_diff(
                $categories,
                self::ADULT_CATEGORY_IDS
            )
        );

        if (empty($categories)) {
            return;
        }

        $query->whereIn(
            'category_id',
            $categories
        );
    }

    /**
     * Adult category filter.
     */
    private static function applyAdultCategoryFilter(
        Builder $query,
        Request $request
    ): void {
        if (!$request->filled('category')) {
            return;
        }

        $category = $request->input('category');

        if (!is_numeric($category)) {
            return;
        }

        $category = (int) $category;

        if (!in_array(
            $category,
            self::ADULT_CATEGORY_IDS,
            true
        )) {
            return;
        }

        $query->where(
            'category_id',
            $category
        );
    }

    /**
     * TMDB filter.
     */
    private static function applyTmdbFilter(
        Builder $query,
        Request $request
    ): void {
        if (!$request->filled('tmdbid')) {
            return;
        }

        $tmdbId = $request->input('tmdbid');

        if (!is_numeric($tmdbId)) {
            return;
        }

        $query->where(
            'tmdbid',
            (int) $tmdbId
        );
    }

    /**
     * Genre filter.
     */
    private static function applyGenreFilter(
        Builder $query,
        Request $request
    ): void {
        if (!$request->filled('genre')) {
            return;
        }

        $genre = $request->input('genre');

        if (!is_numeric($genre)) {
            return;
        }

        $genreId = (int) $genre;

        $query->whereHas(
            'genres',
            function (Builder $q) use ($genreId) {
                $q->where(
                    'genres.id',
                    $genreId
                );
            }
        );
    }

    /**
     * Torrent status filters.
     */
    private static function applyStatusFilter(
        Builder $query,
        Request $request
    ): void {
        $status = $request->input(
            'torrent_status',
            'active'
        );

        switch ($status) {
            case 'dead':
                $query->where(
                    'seeders',
                    0
                );
                break;

            case 'free':
                $query
                    ->where('free', true)
                    ->where('seeders', '>', 0);
                break;

            case 'double':
                $query
                    ->where('double', true)
                    ->where('seeders', '>', 0);
                break;

            case 'seedbox':
                $query
                    ->where('seedbox', true)
                    ->where('seeders', '>', 0);
                break;

            case 'active':
            default:
                $query->where(
                    'seeders',
                    '>',
                    0
                );
                break;
        }
    }

    /**
     * Safe torrent sorting.
     */
    private static function applySorting(
        Builder $query,
        Request $request,
        string $sortColumn,
        string $sortDirection
    ): void {
        /*
         * Sticky torrents always appear first.
         */
        $query->orderByDesc('sticky');

        /*
         * User-selected sorting.
         */
        if ($request->filled('sort')) {
            $query->orderBy(
                $sortColumn,
                $sortDirection
            );

            /*
             * ID provides deterministic ordering
             * when created_at values are identical.
             */
            if ($sortColumn === 'created_at') {
                $query->orderByDesc('id');

                return;
            }

            $query
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            return;
        }

        /*
         * Default browse order.
         */
        $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Build the torrent file tree.
     */
    public static function buildFileTree($files): array
    {
        if ($files->isEmpty()) {
            return [];
        }

        /*
         * Build a deterministic cache signature.
         */
        $cacheData = $files
            ->map(
                static fn ($file) => [
                    'filename' => (string) $file->filename,
                    'size' => (int) $file->size,
                ]
            )
            ->sortBy('filename')
            ->values()
            ->toArray();

        $cacheKey = 'file_tree_' .
            md5(serialize($cacheData));

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($files) {
                $tree = [];

                foreach ($files as $file) {
                    $filename = trim(
                        (string) $file->filename
                    );

                    if ($filename === '') {
                        continue;
                    }

                    /*
                     * Normalise path separators.
                     */
                    $filename = str_replace(
                        '\\',
                        '/',
                        $filename
                    );

                    $parts = array_values(
                        array_filter(
                            explode(
                                '/',
                                $filename
                            ),
                            static fn ($part) =>
                                $part !== ''
                        )
                    );

                    if (empty($parts)) {
                        continue;
                    }

                    $current = &$tree;

                    $lastIndex =
                        count($parts) - 1;

                    foreach (
                        $parts as $index => $part
                    ) {
                        if (!isset(
                            $current[$part]
                        )) {
                            $current[$part] = [];
                        }

                        if (
                            $index === $lastIndex
                        ) {
                            $current[$part]['_size'] =
                                FormatHelper::formatSize(
                                    (int) $file->size
                                );
                        }

                        $current =
                            &$current[$part];
                    }

                    /*
                     * Break PHP reference after each file.
                     */
                    unset($current);
                }

                return $tree;
            }
        );
    }
}