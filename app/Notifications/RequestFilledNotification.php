<?php

namespace App\Notifications;

use App\Models\TorrentRequest;
use Illuminate\Notifications\Notification;

class RequestFilledNotification extends Notification
{
    public function __construct(public TorrentRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'request_filled',
            'request_id' => $this->request->id,
            'request_name' => $this->request->name,
            'message' => "The request you voted for, '{$this->request->name}', has been filled.",
            'url' => route('requests.show', $this->request->id),
        ];
    }
}
