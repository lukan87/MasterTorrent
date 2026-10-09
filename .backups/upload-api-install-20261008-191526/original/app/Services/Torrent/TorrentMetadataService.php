<?php


namespace App\Services\Torrent;

use App\Services\TMDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TorrentMetadataService
{
    public function resolve(Request $request): array
    {
        $data = [
            'poster'      => $request->poster,
            'background'  => $request->background,
            'description' => $request->description,
            'tmdbid'      => null,
            'tmdb_type'   => null,
            'imdbid'      => null,
            'genres'      => [],
        ];

        if ($request->imdb_url && preg_match('/tt\d+/', $request->imdb_url, $m)) {
            $tmdb = app(TMDBService::class);
            $data['imdbid'] = $m[0];

            if ($info = $tmdb->getTMDBIdAndTypeByIMDbId($m[0])) {
                $data['tmdbid']    = $info['tmdb_id'];
                $data['tmdb_type'] = $info['type'];

                $tmdbData = $tmdb->fetchTMDBData($info['tmdb_id'], $info['type']);
                $data['poster']     = $tmdbData['poster_path']
                    ? 'https://image.tmdb.org/t/p/w342' . $tmdbData['poster_path']
                    : null;
                $data['background'] = $tmdbData['backdrop_path']
                    ? 'https://image.tmdb.org/t/p/w1280' . $tmdbData['backdrop_path']
                    : null;
                $data['genres']     = $tmdbData['genres'] ?? [];
            }
        }

        return $data;
    }
}
