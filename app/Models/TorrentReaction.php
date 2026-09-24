<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentReaction extends Model
{
    use HasFactory;

    protected $fillable = ['torrent_id', 'user_id', 'reaction'];

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
