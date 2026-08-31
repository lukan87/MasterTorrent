<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorrentThank extends Model
{
    protected $fillable = ['torrent_id', 'user_id'];

    // The user who gave thanks
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // assumes torrent_thanks table has a user_id column
    }

    // The related torrent
    public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent_id');
    }
}
