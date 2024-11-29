<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Topic extends Model
{
    // Topic.php


    use HasFactory;

    // Fillable properties
    protected $fillable = [
        'title',
        'content',
        'user_id',  // Add user_id to the fillable array
        'forum_category_id',
    ];

public function user()
{
    return $this->belongsTo(User::class);
}

public function posts()
{
    return $this->hasMany(Post::class);
}

public function category()
{
    return $this->belongsTo(ForumCategory::class, 'forum_category_id');
}



}
