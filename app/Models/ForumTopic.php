<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumTopic extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'views',
        'is_pinned',
        'is_locked',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
    ];

    /**
     * Topic belongs to a forum
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Topic has many posts
     */
    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }

    // The last post in this topic
    public function lastPost(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ForumPost::class, 'forum_topic_id')->latestOfMany();
    }

    /**
     * Topic author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Only topics in forums visible to a given user class
     */
    public function scopeVisibleTo($query, ?int $userClass = null)
    {
        $userClass = $userClass ?? \App\Models\UserClass::USER;

        return $query->whereHas('forum', function ($f) use ($userClass) {
            $f->where('min_class_required', '<=', $userClass)
              ->whereHas('category', function ($c) use ($userClass) {
                  $c->where('min_class_required', '<=', $userClass);
              });
        });
    }

    public function subscriptions()
{
    return $this->hasMany(TopicSubscription::class);
}

public function isSubscribedBy($user): bool
{
    if (!$user) return false;

    return $this->subscriptions()
        ->where('user_id', $user->id)
        ->exists();
}

}
