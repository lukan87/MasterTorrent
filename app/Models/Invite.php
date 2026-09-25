<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    public const VALID_DAYS = 14;

    protected $fillable = ['inviter_id', 'user_id', 'invite_code', 'is_used', 'is_expired'];

    protected $casts = ['is_used' => 'boolean', 'is_expired' => 'boolean'];

    public function getExpiresAtAttribute()
    {
        return $this->created_at->copy()->addDays(self::VALID_DAYS);
    }

    public function getExpiredAttribute(): bool
    {
        return $this->is_expired || $this->expires_at->lte(now());
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    // Retain code-based lookup for invitations redeemed before user_id was populated.
    public function usedBy()
    {
        return $this->hasOne(User::class, 'invite_code', 'invite_code')->withTrashed();
    }
}
