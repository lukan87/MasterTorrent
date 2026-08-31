<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tmdb_id',
        'imdb_id',
        'poster_path',
        'collection_id',
        'collection_name',
        'overview',
        'backdrop_path',
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically generate slug on create or update
        static::creating(function ($movie) {
            $movie->slug = Str::slug($movie->name);
        });

        static::updating(function ($movie) {
            $movie->slug = Str::slug($movie->name);
        });
    }

}
