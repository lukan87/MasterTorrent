<?php

namespace App\Notifications;

use App\Models\ForumPost;
use App\Models\User;
use App\Services\ForumAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ForumLikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ForumPost $post,
        public User $reactor
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        $topic = $this->post->topic()->with('category')->first();
        $this->post->setRelation('topic', $topic);

        return $topic?->category !== null && ForumAccess::canView($notifiable, $topic->category);
    }

    public function toDatabase(object $notifiable): array
    {
        $topic = $this->post->topic;

        return [
            'type' => 'forum_like',

            'author' => $this->reactor->name,

            'topic_title' => $topic?->title ?? 'Forum topic',

            'topic_id' => $topic?->id,

            'post_id' => $this->post->id,

            'url' => route('forum.topic', [
                'category' => $topic->category->slug,
                'topic' => $topic->slug,
                'post' => $this->post->id,
            ]).'#post-'.$this->post->id,
        ];
    }
}
