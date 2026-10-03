<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestVote extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function request()
    {
        return $this->belongsTo(TorrentRequest::class, 'request_id');
    }
}
