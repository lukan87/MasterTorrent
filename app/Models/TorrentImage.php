<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentImage extends Model
{
    use HasFactory;

    protected $fillable = ['torrent_id', 'path'];

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }
}
