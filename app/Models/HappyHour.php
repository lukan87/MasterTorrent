<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HappyHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'active',
        'automatic',
        'free_download',
        'upload_multiplier',
        'start_at',
        'end_at',
        'activated_by',
        'theme',
    ];

    protected $casts = [
        'active' => 'boolean',
        'automatic' => 'boolean',
        'free_download' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function isActive(): bool
    {
        return $this->active && now()->between($this->start_at, $this->end_at);
    }
}
