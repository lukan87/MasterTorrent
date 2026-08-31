<?php

namespace App\Services\Torrent;

use App\Models\Genre;
use App\Models\Torrent;
use Illuminate\Http\Request;

class TorrentGenreService
{
    public function sync(Torrent $torrent, Request $request, array $metadata): void
    {
        $genreIds = [];

        foreach ($metadata['genres'] ?? [] as $genre) {
            $genreIds[] = Genre::firstOrCreate(['name' => $genre['name']])->id;
        }

        if ($request->genre) {
            foreach (preg_split('/[\/,]/', $request->genre) as $name) {
                $genreIds[] = Genre::firstOrCreate(['name' => trim($name)])->id;
            }
        }

        if ($genreIds) {
            $torrent->genres()->sync($genreIds);
        }
    }
}
