<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    use HasFactory;

    protected $fillable = ['poll_id', 'option_id', 'user_id'];

    // Relationship to Poll
    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    // Relationship to PollOption
    public function option()
    {
        return $this->belongsTo(PollOption::class, 'option_id');
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
