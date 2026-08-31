<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSlot extends Model
{
    protected $fillable = ['user_id', 'torrent_id', 'free', 'double', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }
}
