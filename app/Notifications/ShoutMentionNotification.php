<?php

namespace App\Notifications;

use App\Models\Shoutbox;
use Illuminate\Notifications\Notification;

class ShoutMentionNotification extends Notification
{
    public function __construct(public Shoutbox $shout, public string $author) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'shout_mention',
            'shout_id' => $this->shout->id,
            'author' => $this->author,
            'message' => $this->author . ' tagged you in a shout in chat.',
            'url' => route('home', ['shout' => $this->shout->id]) . '#shout-' . $this->shout->id,
        ];
    }
}
