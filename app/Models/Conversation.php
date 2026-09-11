<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    protected $fillable = [
        'user_one',
        'user_two',
        'subject',
        'last_message_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function userOne()
    {
        return $this->belongsTo(User::class,'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class,'user_two');
    }

    /*
    |--------------------------------------------------------------------------
    | Unread / read helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Number of unread messages inside this conversation that were addressed
     * to the given user (i.e. that user has not read yet).
     */
    public function unreadCountFor(int $userId): int
    {
        return (int) $this->messages()
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

    /**
     * Whether the given user has any unread messages in this conversation.
     */
    public function hasUnreadFor(int $userId): bool
    {
        return $this->unreadCountFor($userId) > 0;
    }

    /**
     * Mark every incoming message for the given user as read.
     * Returns the number of messages that were updated.
     */
    public function markReadFor(int $userId): int
    {
        return $this->messages()
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function otherUser()
    {
        return Auth::id() == $this->user_one
            ? $this->userTwo
            : $this->userOne;
    }
}