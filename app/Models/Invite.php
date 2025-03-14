<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    // Define the table name (optional, Laravel will guess this based on the model name)
    protected $table = 'invites';

    // Define the fillable attributes
    protected $fillable = [
        'inviter_id', 'invite_code', 'is_used', 'is_expired', 
    ];

    // If you're using timestamps
    public $timestamps = true;

    // Define the relationship with the User model   // Define the inverse of the relationship
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id'); // The person who created the invite
    }

    public function invites()
    {
        return $this->hasMany(Invite::class, 'inviter_id');  // 'inviter_id' is the foreign key
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
public function usedBy()
{
    return $this->hasOne(User::class, 'invite_code', 'invite_code');
}


}
