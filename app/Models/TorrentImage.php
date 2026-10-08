<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TorrentImage extends Model
{
    use HasFactory;

    protected $fillable = ['torrent_id', 'path'];

    protected $table = 'torrent_images'; // Explicitly specifying the table name

    public function getUrlAttribute(): string
    {
        return self::urlFor($this->path);
    }

    public function getFallbackUrlAttribute(): string
    {
        return self::urlFor($this->fallback);
    }

    private static function urlFor(?string $path): string
    {
        if (! $path) return '';

        return filter_var($path, FILTER_VALIDATE_URL) ? $path : asset('storage/'.$path);
    }

    /** Hosted images have no file on our public disk. */
    public static function deleteLocalFiles(array $paths): void
    {
        $local = array_values(array_filter($paths, fn ($path) =>
            is_string($path) && $path !== '' && ! parse_url($path, PHP_URL_SCHEME) && ! str_starts_with($path, '//')
        ));
        if ($local) Storage::disk('public')->delete($local);
    }

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }
}
