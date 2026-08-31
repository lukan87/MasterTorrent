<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'staff_reply',
        'answered_by',
        'answered_at',
        'is_answered',
        'ip'
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function messages()
{
    return $this->hasMany(ContactMessage::class);
}

}