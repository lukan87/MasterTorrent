<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Torrent;
use App\Services\Torrent\TorrentUploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TorrentController extends Controller
{
    public function store(Request $request, TorrentUploadService $service)
    {
        // These protected properties are assigned by the tracker, never by API callers.
        foreach (['owner', 'info_hash', 'announce', 'passkey', 'file_name', 'approved', 'external', 'seeders', 'leechers', 'recommended'] as $field) {
            if ($request->exists($field)) {
                throw ValidationException::withMessages([$field => 'This field cannot be supplied.']);
            }
        }
        $result = $service->handle($request, $request->user());

        return response()->json(['data' => $this->resource($result['torrent']), 'meta' => ['warnings' => $result['warnings'] ?? []]], 201)
            ->header('Location', route('api.v1.torrents.show', $result['torrent']));
    }

    public function show(Torrent $torrent)
    {
        return response()->json(['data' => $this->resource($torrent)]);
    }

    public function categories()
    {
        return response()->json(['data' => Category::query()->orderBy('name')->get(['id', 'name'])]);
    }

    private function resource(Torrent $torrent): array
    {
        return [
            'id' => $torrent->id, 'info_hash' => $torrent->info_hash, 'name' => $torrent->name, 'category_id' => (int) $torrent->category_id,
            'anonymous' => (bool) $torrent->anon,
            'size' => (int) $torrent->size, 'num_files' => (int) $torrent->num_files,
            'tmdbid' => $torrent->tmdbid, 'tmdb_type' => $torrent->tmdb_type, 'imdbid' => $torrent->imdbid,
            'tvdbid' => $torrent->tvdbid, 'season' => $torrent->season, 'episode' => $torrent->episode,
            'created_at' => $torrent->created_at?->toIso8601String(),
            'url' => route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]),
        ];
    }
}
