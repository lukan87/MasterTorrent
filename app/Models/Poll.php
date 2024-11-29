<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Poll extends Model
{

    use HasFactory;

    protected $fillable = ['title', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('polls');
        });

        static::deleted(function () {
            Cache::forget('polls');
        });
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

public function votes()
{
    return $this->hasMany(PollVote::class);
}

}
