<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'body',
        'sent_at'
    ];

    protected $casts = [
    'sent_at' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
