<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TorrentDeletedNotification extends Notification
{
    use Queueable;

    protected $torrent;
    protected $reason;
    protected $deletedBy;

    public function __construct($torrent, $reason, $deletedBy)
    {
        $this->torrent = $torrent;
        $this->reason = $reason;
        $this->deletedBy = $deletedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'torrent_id' => $this->torrent->id,
            'torrent_name' => $this->torrent->name,
            'reason' => $this->reason,
            'deleted_by' => $this->deletedBy->name,
            'url' => route('torrents.show', [$this->torrent->id, $this->torrent->slug]),
            'type' => 'torrent_deleted'
        ];
    }
}