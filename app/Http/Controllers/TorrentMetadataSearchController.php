<?php

namespace App\Http\Controllers;

use App\Services\PageBrowse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TorrentMetadataSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => 'required_without:id|nullable|string|min:2|max:160',
            'id' => 'nullable|integer|min:1',
            'type' => 'required|in:movie,tv',
        ]);
        $key = config('services.tmdb.key') ?: config('app.tmdb_api_key');
        abort_unless($key, 503, 'Title search is temporarily unavailable.');
        $type = $data['type'];
        $query = trim($data['q'] ?? '');
        $id = $data['id'] ?? null;
        $cacheKey = 'torrent_form_metadata_v1:'.hash('sha256', json_encode([$type, $query, $id]));
        try {
            $result = Cache::remember($cacheKey, now()->addHours(6), function () use ($key, $type, $query, $id) {
                if ($id) {
                    $title = $this->get($type.'/'.$id, $key, ['append_to_response' => 'external_ids,credits']);
                    return ['metadata' => [
                        'title' => $title['title'] ?? $title['name'] ?? '',
                        'imdb_url' => !empty($title['imdb_id'] ?? $title['external_ids']['imdb_id'] ?? null)
                            ? 'https://www.imdb.com/title/'.($title['imdb_id'] ?? $title['external_ids']['imdb_id']).'/' : '',
                        'poster' => !empty($title['poster_path']) ? 'https://image.tmdb.org/t/p/w342'.$title['poster_path'] : '',
                        'genre' => collect($title['genres'] ?? [])->pluck('name')->implode(', '),
                        'overview' => $title['overview'] ?? '',
                        'cast' => collect($title['credits']['cast'] ?? [])->take(6)->pluck('name')->implode(', '),
                    ]];
                }
                if (preg_match('/tt[0-9]+/', $query, $match)) {
                    $found = $this->get('find/'.$match[0], $key, ['external_source' => 'imdb_id']);
                    $titles = collect($found['movie_results'] ?? [])->map(fn ($item) => $item + ['type' => 'movie'])
                        ->concat(collect($found['tv_results'] ?? [])->map(fn ($item) => $item + ['type' => 'tv']));
                } else {
                    $found = $this->get('search/'.$type, $key, ['query' => $query, 'include_adult' => false]);
                    $titles = collect($found['results'] ?? [])->map(fn ($item) => $item + ['type' => $type]);
                }
                return ['results' => $titles->reject(fn ($item) => $item['adult'] ?? false)->take(8)->map(fn ($item) => [
                    'id' => $item['id'], 'type' => $item['type'],
                    'title' => $item['title'] ?? $item['name'] ?? '',
                    'year' => substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4),
                ])->values()->all()];
            });
        } catch (\Throwable $exception) {
            report($exception);
            return PageBrowse::json(['message' => 'Could not retrieve title information. Your form is retained; try again.'], 502);
        }
        return PageBrowse::json($result);
    }

    private function get(string $path, string $key, array $params): array
    {
        $response = Http::connectTimeout(3)->timeout(8)->get('https://api.themoviedb.org/3/'.$path, $params + ['api_key' => $key]);
        $response->throw();
        $data = $response->json();
        if (!is_array($data)) throw new \RuntimeException('Invalid title metadata response.');
        return $data;
    }
}
