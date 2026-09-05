<?php

namespace App\Notifications;

use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ForumReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ForumPost $post
    ) {
    }

    /**
     * Notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Database notification data.
     */
    public function toDatabase(object $notifiable): array
    {
        $topic = $this->post->topic;
        $author = $this->post->user;

        return [
            'type' => 'forum_reply',

            'author' => $author?->name ?? 'Someone',

            'topic_title' => $topic?->title ?? 'Forum topic',

            'topic_id' => $topic?->id,

            'post_id' => $this->post->id,

            'url' => route('forum.topic', [
                'category' => $topic->category->slug,
                'topic' => $topic->slug,
            ]) . '#post-' . $this->post->id,
        ];
    }
}