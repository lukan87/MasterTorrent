<?php

namespace App\Jobs;

use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessTorrentImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected int $torrentId;

    public function __construct(string $path, int $torrentId)
    {
        $this->path = $path;
        $this->torrentId = $torrentId;
    }

    public function handle(): void
    {
        $torrent = Torrent::find($this->torrentId);
        if ($torrent) {
            app(\App\Services\Torrent\TorrentImageService::class)
                ->storeWebp($torrent, Storage::path($this->path));
        }
        Storage::delete($this->path);
    }
}
