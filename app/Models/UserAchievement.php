<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'earned_at' => 'datetime',
        'balance_before' => 'decimal:2',
        'bonus_awarded' => 'decimal:2',
        'tokens_awarded' => 'integer',
        'vip_months_awarded' => 'integer',
    ];
}
