<?php

namespace App\Notifications;

use App\Models\Torrent;
use App\Models\User;
use Illuminate\Notifications\Notification;

class TorrentActivityNotification extends Notification
{
    public function __construct(
        public Torrent $torrent,
        public User $actor,
        public ?int $commentId = null,
        public ?string $reaction = null,
        public bool $downloaded = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->commentId ? 'torrent_comment' : 'torrent_reaction',
            'audience' => $this->downloaded ? 'downloader' : 'uploader',
            'author' => $this->actor->name,
            'author_id' => $this->actor->id,
            'torrent_id' => $this->torrent->id,
            'torrent_name' => $this->torrent->name,
            'comment_id' => $this->commentId,
            'reaction' => $this->reaction,
            'url' => route('torrents.show', [$this->torrent->id, $this->torrent->slug])
                . ($this->commentId ? '#comment-'.$this->commentId : '#reaction-tooltip-wrap'),
        ];
    }
}
