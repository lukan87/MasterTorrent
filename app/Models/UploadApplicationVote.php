<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadApplicationVote extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'vote'
    ];

    public function application()
    {
        return $this->belongsTo(UploadApplication::class, 'application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}