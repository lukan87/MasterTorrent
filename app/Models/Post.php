<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['topic_id', 'user_id', 'content'];

    // Define relationship to Topic
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    // Define relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }
    
}


