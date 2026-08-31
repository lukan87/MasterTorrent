<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorrentMovie extends Model
{
    protected $fillable = [
        'tmdbid',
        'title',
        'slug',
        'poster_path',
        'backdrop_path',
        'rating',
        'year',
    ];
}