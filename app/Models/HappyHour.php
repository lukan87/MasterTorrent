<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'upload_multiplier' => 'integer',
        'active' => 'boolean',
        'automatic' => 'boolean',
        'free_download' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('tracker:happy_hour:state'));
        static::deleted(fn () => Cache::forget('tracker:happy_hour:state'));
    }

    public function scopeCurrent(Builder $query): Builder
    {
        $now = now();

        return $query->where('active', true)->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)->orderByDesc('start_at')->orderByDesc('id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->isActive()) {
            return 'Live';
        }
        if ($this->active && $this->start_at?->isFuture()) {
            return 'Scheduled';
        }

        return ($this->end_at && now()->gte($this->end_at)) ? 'Ended' : 'Cancelled';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function isActive(): bool
    {
        return $this->active && $this->start_at && $this->end_at
            && now()->gte($this->start_at) && now()->lt($this->end_at);
    }
}
