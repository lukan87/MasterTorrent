<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Torrent;
use Illuminate\Support\Facades\Cache;

class TorrentHelper
{
    public static function buildTorrentQuery(Request $request, $sortColumn, $sortDirection)
    {
        $query = Torrent::query()->with('genres')->whereNotIn('category_id', [27, 34]);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $normalizedKeyword = preg_replace('/[^a-zA-Z0-9]/', '', $keyword);

            $query->where(function ($q) use ($normalizedKeyword) {
                $q->whereRaw('REPLACE(REPLACE(name, ".", ""), " ", "") LIKE ?', ['%' . $normalizedKeyword . '%'])
                    ->orWhere('imdb_url', 'like', '%' . $normalizedKeyword . '%');
            });
        }

        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('category_id', $request->categories);
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }

        if ($request->filled('torrent_status')) {
            $status = $request->torrent_status;

            switch ($status) {
                case 'active':
                    $query->where('seeders', '>', 0);
                    break;
                case 'dead':
                    $query->where('seeders', '=', 0);
                    break;
                case 'free':
                    $query->where('free', '=', true)->where('seeders', '>', 0);
                    break;
                case 'double':
                    $query->where('double', '=', true)->where('seeders', '>', 0);
                    break;
                case 'seedbox':
                    $query->where('seedbox', '=', true)->where('seeders', '>', 0);
                    break;
            }
        } else {
            $query->where('seeders', '>', 0);
        }

        return $query
            ->orderByRaw('sticky DESC')
            ->when(
                $request->has('sort') || $request->has('direction'),
                fn($q) => $q->orderBy($sortColumn, $sortDirection),
                fn($q) => $q->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            )
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($request->query());
    }

    public static function buildAdultTorrentQuery(Request $request, $sortColumn, $sortDirection)
{
    $query = Torrent::query()->with('genres')->whereIn('category_id', [27, 34, 60]);

    if ($request->filled('keyword')) {
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%')
              ->orWhere('imdb_url', 'like', '%' . $keyword . '%');
        });
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($request->filled('genre')) {
        $query->whereHas('genres', function ($q) use ($request) {
            $q->where('genres.id', $request->genre);
        });
    }

    if ($request->filled('torrent_status')) {
        $status = $request->torrent_status;
        switch ($status) {
            case 'active':
                $query->where('seeders', '>', 0); break;
            case 'dead':
                $query->where('seeders', '=', 0); break;
            case 'free':
                $query->where('free', '=', true)->where('seeders', '>', 0); break;
            case 'double':
                $query->where('double', '=', true)->where('seeders', '>', 0); break;
            case 'seedbox':
                $query->where('seedbox', '=', true)->where('seeders', '>', 0); break;
        }
    } else {
        $query->where('seeders', '>', 0);
    }

    return $query
        ->orderByRaw('sticky DESC')
        ->when(
            $request->has('sort') || $request->has('direction'),
            fn($q) => $q->orderBy($sortColumn, $sortDirection),
            fn($q) => $q->orderBy('created_at', 'desc')->orderBy('id', 'desc')
        )
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
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
