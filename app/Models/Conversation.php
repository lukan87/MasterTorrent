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