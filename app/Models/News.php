<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class News extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('latest_news');
        });

        static::deleted(function () {
            Cache::forget('latest_news');
        });
    }

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

