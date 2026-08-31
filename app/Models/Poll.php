<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Poll extends Model
{
    use HasFactory, SoftDeletes;

    /* =========================
     *  Mass Assignment
     * ========================= */
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'expires_at',
        'is_active',
    ];

    /* =========================
     *  Casting
     * ========================= */
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    /* =========================
     *  Model Events (Cache)
     * ========================= */
    protected static function booted()
    {
        static::saved(function (self $poll) {
            Cache::forget("poll.{$poll->id}");
            Cache::forget('polls.index');
        });

        static::deleted(function (self $poll) {
            Cache::forget("poll.{$poll->id}");
            Cache::forget('polls.index');
        });

        static::restored(function (self $poll) {
            Cache::forget("poll.{$poll->id}");
            Cache::forget('polls.index');
        });
    }

    /* =========================
     *  Relationships
     * ========================= */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    /* =========================
     *  Local Scopes
     * ========================= */

    /**
     * Only polls that are open for voting
     */
    public function scopeOpen($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Closed (manually closed)
     */
    public function scopeClosed($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Expired polls
     */
    public function scopeExpired($query)
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /* =========================
     *  Helpers
     * ========================= */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isOpen(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function close(): void
    {
        $this->update(['is_active' => false]);
    }

    public function reopen(): void
    {
        if (! $this->isExpired()) {
            $this->update(['is_active' => true]);
        }
    }
}
