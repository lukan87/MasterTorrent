<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TorrentSlugService
{
    public function unique(string $name): string
    {
        $base = Str::slug($name) ?: 'torrent';
        $slug = substr($base, 0, 255);
        $index = 1;
        while (Torrent::withTrashed()->where('slug', $slug)->exists()) {
            $suffix = '-'.$index++;
            $slug = substr($base, 0, 255 - strlen($suffix)).$suffix;
        }

        return $slug;
    }

    public function transaction(string $name, string $infoHash, callable $create): Torrent
    {
        $slug = $this->unique($name);
        for ($retry = 0; ; $retry++) {
            try {
                return DB::transaction(fn () => $create($slug));
            } catch (UniqueConstraintViolationException $exception) {
                if ($retry >= 2 || Torrent::withTrashed()->where('info_hash', $infoHash)->exists()
                    || ! Torrent::withTrashed()->where('slug', $slug)->exists()) {
                    throw $exception;
                }
                // A different torrent won the same URL. Retry without provider or file I/O.
                $slug = $this->unique(substr(Str::slug($name) ?: 'torrent', 0, 214).'-'.$infoHash);
            }
        }
    }
}
