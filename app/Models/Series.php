<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tmdb_id',
        'imdb_id',
        'poster_path',
        'overview',
        'backdrop_path',
        'first_air_date',
        'number_of_seasons',
        'number_of_episodes',
        'vote_average',
        'vote_count',
        'tagline',
        'status',
        'genres',
        'views',
        'trailer_key',
    ];

    protected $casts = [
        'genres'         => 'array',
        'first_air_date' => 'datetime',
        'vote_average'   => 'float',
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Torrents uploaded for this series (matched via TMDB id).
     */
    public function torrents()
    {
        return $this->hasMany(Torrent::class, 'tmdbid', 'tmdb_id');
    }

    /**
     * Year used for cards / headers, with a graceful fallback.
     */
    public function getYearAttribute()
    {
        return $this->first_air_date
            ? $this->first_air_date->format('Y')
            : \Carbon\Carbon::parse($this->created_at)->format('Y');
    }

    /**
     * Full TMDB poster URL (or a placeholder when missing).
     */
    public function getPosterUrlAttribute()
    {
        return $this->poster_path
            ? 'https://image.tmdb.org/t/p/w600_and_h900_bestv2' . $this->poster_path
            : '/images/noposter.jpg';
    }

    /**
     * Full TMDB backdrop URL (or a placeholder when missing).
     */
    public function getBackdropUrlAttribute()
    {
        return $this->backdrop_path
            ? 'https://image.tmdb.org/t/p/original' . $this->backdrop_path
            : '/images/noimage.jpg';
    }

    /**
     * Bump the popularity counter for this title.
     */
    public function recordView(): void
    {
        $this->increment('views');
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically generate slug on create or update
        static::creating(function ($series) {
            $series->slug = Str::slug($series->name);
        });

        static::updating(function ($series) {
            $series->slug = Str::slug($series->name);
        });
    }

}
