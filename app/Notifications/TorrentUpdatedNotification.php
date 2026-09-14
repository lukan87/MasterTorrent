<?php

namespace App\Notifications;

use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TorrentUpdatedNotification extends Notification
{
    use Queueable;

    protected $torrent;

    public function __construct(Torrent $torrent)
    {
        $this->torrent = $torrent;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'torrent_id'   => $this->torrent->id,
            'torrent_name' => $this->torrent->name,
            'updated_by'   => 'System',
            'url'          => route('torrents.show', [$this->torrent->id, $this->torrent->slug]),
            'type'         => 'torrent_updated',
        ];
    }
}