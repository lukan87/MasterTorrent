<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'priority',
        'status',
        'title',
        'description',
        'last_replied_at',  
        'last_replier_id',  
    ];

    protected $casts = [
        'last_replied_at' => 'datetime', // This ensures it is treated as a Carbon instance
    ];

    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastReplier()
    {
        return $this->belongsTo(User::class, 'last_replier_id');
    }
}

