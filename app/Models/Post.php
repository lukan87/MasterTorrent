<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
   // Post.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function topic()
{
    return $this->belongsTo(Topic::class);
}

// This defines the relationship for replies to a post
public function replies()
{
    return $this->hasMany(Post::class, 'parent_id');
}

// This defines the relationship for the parent post of a reply
public function parent()
{
    return $this->belongsTo(Post::class, 'parent_id');
}


}
