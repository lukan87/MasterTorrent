<?php

namespace App\Jobs;

use App\Models\Torrent;
use App\Services\TorrentSubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyTorrentUpload implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $torrentId) {}

    public function handle(TorrentSubscriptionService $service): void
    {
        if ($torrent = Torrent::find($this->torrentId)) {
            $service->notifyUpload($torrent);
        }
    }
}
