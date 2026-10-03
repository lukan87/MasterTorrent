<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    public const TYPES = ['like' => '👍', 'dislike' => '👎', 'love' => '❤️', 'laugh' => '😄', 'thanks' => '🙏'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = ['comment_id', 'user_id', 'reaction'];
}
