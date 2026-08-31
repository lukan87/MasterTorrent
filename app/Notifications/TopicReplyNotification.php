<?php

namespace App\Notifications;

use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TopicReplyNotification extends Notification
{
    use Queueable;

    public function __construct(public ForumPost $post) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
{
    return [
        'topic_id'    => $this->post->topic->id,
        'topic_title' => $this->post->topic->title,
        'post_id'     => $this->post->id,
        'author'      => $this->post->author->name,

        // ✅ direct link to the post
       'url' => route('topics.show', [
       'topic' => $this->post->topic->id,
]) . '#post-' . $this->post->id,

    ];
}

}
