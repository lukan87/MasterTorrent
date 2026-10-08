<?php

namespace App\Models;

use App\Services\SystemMessageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Message $message) {
            $message->massDelivery()->update([
                'message_id' => null, 'status' => 'removed', 'was_read' => $message->is_read,
            ]);
        });
        static::deleted(function (Message $message) {
            if ($message->conversation_id) {
                SystemMessageService::refreshConversation($message->conversation_id);
            }
            SystemMessageService::forgetUserCache($message->sender_id);
            SystemMessageService::forgetUserCache($message->receiver_id);
        });
    }

    public function massDelivery()
    {
        return $this->hasOne(MassMessageDelivery::class);
    }

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    protected $fillable = ['conversation_id', 'sender_id', 'receiver_id', 'subject', 'body', 'is_read'];

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
