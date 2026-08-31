<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'forum_topic_id',
        'user_id',
        'content',
    ];

    /**
     * Post belongs to a topic
     */
    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /**
     * Post author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Only posts in visible topics
     */
    public function scopeVisibleTo($query, ?int $userClass = null)
    {
        $userClass = $userClass ?? \App\Models\UserClass::USER;

        return $query->whereHas('topic', fn($t) => $t->scopeVisibleTo($userClass));
    }
}
