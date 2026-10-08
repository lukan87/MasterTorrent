<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TvShowFollow extends Model
{
    protected $fillable = ['user_id', 'tvmaze_id', 'title', 'imdbid', 'tmdbid', 'notify_upload'];

    protected $casts = ['notify_upload' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
