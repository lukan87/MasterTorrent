<?php

namespace App\Jobs;

use App\Models\Peer;
use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class UpdateTorrentStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $torrentId) {}

public function handle(): void
{
    $torrent = Torrent::find($this->torrentId);

    if (!$torrent) return;

    $timeout = now()->subMinutes(60);

    $seeders = Peer::where('torrent_id', $this->torrentId)
        ->where('active', 1)
        ->where('seeder', 1)
        ->where('client_updated_at', '>', $timeout)
        ->count();

    $leechers = Peer::where('torrent_id', $this->torrentId)
        ->where('active', 1)
        ->where('seeder', 0)
        ->where('client_updated_at', '>', $timeout)
        ->count();

    $torrent->update([
        'seeders'  => $seeders,
        'leechers' => $leechers
    ]);
}
}