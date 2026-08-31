<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'torrent_id',
        'action',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

  public function torrent()
{
    return $this->belongsTo(Torrent::class)->withTrashed();
}
}