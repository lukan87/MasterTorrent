<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id','sender_id', 'receiver_id', 'subject', 'body', 'is_read'];

   public function sender()
{
    return $this->belongsTo(User::class, 'sender_id')->withTrashed();
}

public function receiver()
{
    return $this->belongsTo(User::class, 'receiver_id')->withTrashed();
}

public function conversation()
{
    return $this->belongsTo(Conversation::class);
}

     /* ==========================
     | Scopes
     ========================== */
    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', 1);
    }
}
