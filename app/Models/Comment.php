<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'commentable_id', 'commentable_type', 'comment', 'parent_id', 'torrent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Polymorphic relationship
    public function commentable()
    {
        return $this->morphTo();
    }
}

