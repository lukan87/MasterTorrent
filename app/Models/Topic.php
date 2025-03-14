<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_id',
        'title',
        'user_id',
        'body',
    ];
    

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

// Define relationship to Post
public function posts()
{
    return $this->hasMany(Post::class)->whereNull('parent_id');
}


    public function user()
{
    return $this->belongsTo(User::class);
}
}
