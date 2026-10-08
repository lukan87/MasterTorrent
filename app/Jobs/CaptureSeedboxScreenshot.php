<?php

namespace App\Jobs;

use App\Models\Seedbox;
use App\Models\Torrent;
use App\Services\SeedboxService;
use App\Services\Torrent\TorrentImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaptureSeedboxScreenshot implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 75;
    public int $uniqueFor = 300;

    public function __construct(
        public int $seedboxId,
        public int $torrentId,
        public string $filePath,
        public int $index,
    ) {}

    public function uniqueId(): string
    {
        return $this->torrentId.':'.$this->index;
    }

    public function handle(TorrentImageService $images): void
    {
        if ($this->index < 0 || $this->index >= 6) return;
        $torrent = Torrent::find($this->torrentId);
        $seedbox = Seedbox::find($this->seedboxId);
        if (!$torrent || !$seedbox) return;

        $service = app()->makeWith(SeedboxService::class, [
            'url' => $seedbox->address,
            'username' => $seedbox->username,
            'password' => $seedbox->password,
            'authType' => $seedbox->auth_type,
            'useRpc' => true,
        ]);
        $base64 = $service->generateScreenshot($this->filePath, $this->index);
        $binary = $base64 === null ? false : base64_decode(trim($base64), true);
        if (!$binary) {
            throw new \RuntimeException('The seedbox did not return a screenshot for frame '.($this->index + 1).'.');
        }
        $images->storeWebp($torrent, $binary);
    }
}
