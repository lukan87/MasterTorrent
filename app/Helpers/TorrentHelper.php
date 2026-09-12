<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Torrent;
use Illuminate\Support\Facades\Cache;

class TorrentHelper
{


private static array $allowedSortColumns = [
        'id',
        'name',
        'size',
        'seeders',
        'leechers',
        'times_completed',
        'created_at',
        'bumped_at'
    ];

    private static array $allowedDirections = ['asc', 'desc'];

private static function sanitizeSort(?string $column, ?string $direction): array
    {
        $column = in_array($column, self::$allowedSortColumns)
            ? $column
            : 'created_at';

        $direction = strtolower($direction ?? 'desc');
        $direction = in_array($direction, self::$allowedDirections)
            ? $direction
            : 'desc';

        return [$column, $direction];
    }

  public static function buildTorrentQuery(Request $request, $sortColumn, $sortDirection)
    {
        [$sortColumn, $sortDirection] = self::sanitizeSort($sortColumn, $sortDirection);

        $query = Torrent::query()
            ->with(['genres', 'bumper'])
            ->whereNotIn('category_id', [27, 34]);

        // Keyword
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $keyword);

            $query->where(function ($q) use ($normalized) {
                $q->whereRaw(
                    'REPLACE(REPLACE(name, ".", ""), " ", "") LIKE ?',
                    ['%' . $normalized . '%']
                )->orWhere('imdb_url', 'like', '%' . $normalized . '%');
            });
        }

        // Categories (numeric only)
        if ($request->has('categories') && is_array($request->categories)) {
            $categories = array_filter($request->categories, 'is_numeric');
            if (!empty($categories)) {
                $query->whereIn('category_id', $categories);
            }
        }

        // TMDB link filter (from movie/series "View all torrents" links)
        if ($request->filled('tmdbid') && is_numeric($request->tmdbid)) {
            $query->where('tmdbid', (int) $request->tmdbid);
        }

        // Genre
        if ($request->filled('genre') && is_numeric($request->genre)) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }

        // Status
        if ($request->filled('torrent_status')) {
            match ($request->torrent_status) {
                'active'  => $query->where('seeders', '>', 0),
                'dead'    => $query->where('seeders', 0),
                'free'    => $query->where('free', true)->where('seeders', '>', 0),
                'double'  => $query->where('double', true)->where('seeders', '>', 0),
                'seedbox' => $query->where('seedbox', true)->where('seeders', '>', 0),
                default   => $query->where('seeders', '>', 0)
            };
        } else {
            $query->where('seeders', '>', 0);
        }

        // Sorting (safe)
        $query->orderByDesc('sticky');

        if ($request->filled('sort')) {
            $query->orderBy($sortColumn, $sortDirection);

            if ($sortColumn === 'created_at') {
                $query->orderByDesc('id');
            } else {
                $query->orderByDesc('created_at')
                      ->orderByDesc('id');
            }
        } else {
            $query->orderByDesc('created_at')
                  ->orderByDesc('id');
        }

        return $query
            ->paginate(50)
            ->appends($request->query());
    }


 public static function buildAdultTorrentQuery(Request $request, $sortColumn, $sortDirection)
    {
        [$sortColumn, $sortDirection] = self::sanitizeSort($sortColumn, $sortDirection);

        $query = Torrent::query()
            ->with(['genres', 'bumper'])
            ->whereIn('category_id', [27, 34]);

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('imdb_url', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('category') && is_numeric($request->category)) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('genre') && is_numeric($request->genre)) {
            $query->whereHas('genres', fn ($q) =>
                $q->where('genres.id', $request->genre)
            );
        }

        if ($request->filled('torrent_status')) {
            match ($request->torrent_status) {
                'active'  => $query->where('seeders', '>', 0),
                'dead'    => $query->where('seeders', 0),
                'free'    => $query->where('free', true)->where('seeders', '>', 0),
                'double'  => $query->where('double', true)->where('seeders', '>', 0),
                'seedbox' => $query->where('seedbox', true)->where('seeders', '>', 0),
                default   => $query->where('seeders', '>', 0)
            };
        } else {
            $query->where('seeders', '>', 0);
        }

        $query->orderByDesc('sticky');

        if ($request->filled('sort')) {
            $query->orderBy($sortColumn, $sortDirection)
                  ->orderByDesc('bumped_at')
                  ->orderByDesc('id');
        } else {
            $query->orderByDesc('created_at')
                  ->orderByDesc('id');
        }

        return $query
            ->paginate(50)
            ->appends($request->query());
    }



public static function buildFileTree($files)
{
    $cacheKey = 'file_tree_' . md5(serialize($files->pluck('filename', 'size')->toArray()));

    return Cache::remember($cacheKey, 300, function () use ($files) {
        $tree = [];

        foreach ($files as $file) {
            $parts = str_contains($file->filename, '/') 
                ? explode('/', $file->filename) 
                : [$file->filename];

            $current = &$tree;
            $lastIndex = count($parts) - 1;

            foreach ($parts as $index => $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }

                if ($index === $lastIndex) {
                    $current[$part]['_size'] = FormatHelper::formatSize($file->size);
                }

                $current = &$current[$part];
            }
        }

        return $tree;
    });
}


}
