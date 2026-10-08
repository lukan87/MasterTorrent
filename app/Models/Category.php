<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    public const ADULT_IDS = [27, 34, 60];

    protected $fillable = ['name', 'image'];

    public function browseGroup(): string
    {
        if (in_array((int) $this->id, self::ADULT_IDS, true)) {
            return match ((int) $this->id) {
                34 => 'Packs',
                60 => 'Image sets',
                default => 'Videos',
            };
        }

        $name = strtolower(trim($this->name));

        return match (true) {
            str_starts_with($name, 'movies') => 'Movies',
            str_starts_with($name, 'tv'), str_starts_with($name, 'hdtv') => 'TV shows',
            str_contains($name, 'music') => 'Music',
            str_starts_with($name, 'games') => 'Games',
            str_contains($name, 'software'), str_contains($name, 'apps') => 'Software',
            default => 'Other',
        };
    }

    public function browseLabel(): string
    {
        return trim(preg_replace('/^(?:movies|games)\s*[:\/]\s*/i', '', $this->name));
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('categories');
        });

        static::deleted(function () {
            Cache::forget('categories');
        });
    }

    public function torrents()
    {
        return $this->hasMany(Torrent::class);
    }
}
