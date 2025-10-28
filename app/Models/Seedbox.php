<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Seedbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'auth_type',
        'username',
        'password',
    ];

    // Relationship: a seedbox belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mutator: encrypt password before saving
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    // Accessor: decrypt password when retrieving
    public function getPasswordAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}
