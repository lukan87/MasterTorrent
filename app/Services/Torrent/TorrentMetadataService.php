<?php

namespace App\Services\Torrent;

use App\Models\Category;
use App\Services\TMDBService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TorrentMetadataService
{
    public function resolve(Request $request): array
    {
        $data = ['poster' => $request->poster, 'background' => $request->background,
            'description' => $request->description, 'name' => $request->name,
            'tmdbid' => null, 'tmdb_type' => null, 'imdbid' => null, 'tvdbid' => null,
            'genres' => [], 'library_metadata' => null, 'warnings' => []];
        $tmdb = app(TMDBService::class);
        $id = $request->integer('tmdbid');
        $type = $request->input('tmdb_type');
        if ($request->imdb_url && preg_match('/tt[0-9]+/', $request->imdb_url, $matches)) {
            $data['imdbid'] = $matches[0];
            if (! $id && $found = $tmdb->getTMDBIdAndTypeByIMDbId($matches[0])) {
                $id = $found['tmdb_id'];
                $type = $found['type'];
            }
        }
        if ($request->filled('tvdbid')) {
            $found = $tmdb->getTMDBIdByTVDBId($request->integer('tvdbid'));
            if ($found) {
                if ($id && ($id !== $found['tmdb_id'] || $type !== 'tv')) {
                    throw ValidationException::withMessages(['tvdbid' => 'The metadata identifiers do not refer to the same TV series.']);
                }
                $id = $found['tmdb_id'];
                $type = 'tv';
                $data['tvdbid'] = $request->integer('tvdbid');
            }
        }
        $info = $id && in_array($type, ['movie', 'tv'], true) ? $tmdb->fetchTMDBData($id, $type) : null;
        if ($info && (int) ($info['id'] ?? 0) === $id) {
            $data['tmdbid'] = $id;
            $data['tmdb_type'] = $type;
            $data['library_metadata'] = $info;
            $providerImdb = $info['imdb_id'] ?? $info['external_ids']['imdb_id'] ?? null;
            if ($data['imdbid'] && $providerImdb && $data['imdbid'] !== $providerImdb) {
                throw ValidationException::withMessages(['imdb_url' => 'The metadata identifiers do not refer to the same title.']);
            }
            if (is_string($providerImdb) && preg_match('/^tt[0-9]+$/D', $providerImdb)) {
                $data['imdbid'] ??= $providerImdb;
            }
            $title = $info[$type === 'tv' ? 'name' : 'title'] ?? null;
            if (! $data['name'] && is_string($title)) {
                $data['name'] = mb_substr($title, 0, 200);
                $date = $info[$type === 'tv' ? 'first_air_date' : 'release_date'] ?? null;
                if (is_string($date) && preg_match('/^[0-9]{4}-/', $date)) {
                    $data['name'] .= '.'.substr($date, 0, 4);
                }
                if ($type === 'tv' && $request->filled('season')) {
                    $data['name'] .= '.S'.str_pad((string) $request->integer('season'), 2, '0', STR_PAD_LEFT);
                }
                if ($type === 'tv' && $request->filled('episode')) {
                    $data['name'] .= 'E'.str_pad((string) $request->integer('episode'), 2, '0', STR_PAD_LEFT);
                }
            }
            if (! $data['description'] && is_string($info['overview'] ?? null)) {
                $data['description'] = mb_substr($info['overview'], 0, 100000);
            }
            foreach (['poster' => ['poster_path', 'w342'], 'background' => ['backdrop_path', 'w1280']] as $field => [$key,$size]) {
                $path = $info[$key] ?? null;
                if (! $data[$field] && is_string($path) && preg_match('~^/[A-Za-z0-9_-]+\.(?:jpg|png|webp)$~D', $path)) {
                    $data[$field] = "https://image.tmdb.org/t/p/{$size}{$path}";
                }
            }
            foreach (is_array($info['genres'] ?? null) ? $info['genres'] : [] as $genre) {
                if (is_array($genre) && is_string($genre['name'] ?? null) && trim($genre['name']) !== '' && mb_strlen($genre['name']) <= 100) {
                    $data['genres'][] = ['name' => $genre['name']];
                }
            }
            $group = Category::find($request->integer('category_id'))?->browseGroup();
            if (($group === 'Movies' && $type === 'tv') || ($group === 'TV shows' && $type === 'movie')) {
                throw ValidationException::withMessages(['category_id' => 'The selected category does not match the metadata type.']);
            }
            if ($type !== 'tv' && ($request->filled('season') || $request->filled('episode'))) {
                throw ValidationException::withMessages(['season' => 'Season and episode fields are only supported for TV torrents.']);
            }
        } elseif ($request->filled('tmdbid') || $request->filled('tvdbid') || $request->filled('imdb_url')) {
            $data['warnings'][] = 'External metadata was unavailable. Supplied manual details were used.';
        }
        foreach (['name', 'description'] as $field) {
            if (! is_string($data[$field]) || trim($data[$field]) === '') {
                throw ValidationException::withMessages([$field => 'Provide this field manually when metadata cannot supply it.']);
            }
        }

        return $data;
    }
}
