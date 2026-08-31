<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicSubscription extends Model
{
    protected $fillable = ['user_id', 'forum_topic_id'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }
}
