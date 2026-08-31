<?php

namespace App\Jobs;

use App\Models\Torrent;
use App\Models\TorrentImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
        $manager = new ImageManager(new Driver());

        $img = $manager
            ->read(Storage::path($this->path))
            ->scaleDown(1920, 1080)
            ->strip();

        $uuid = (string) Str::uuid();

        $webpPath = "torrent_images/{$uuid}.webp";
        Storage::disk('public')->put(
            $webpPath,
            $img->toWebp(75)->toString()
        );

        $jpegPath = "torrent_images/{$uuid}.jpg";
        Storage::disk('public')->put(
            $jpegPath,
            $img->toJpeg(78, progressive: true)->toString()
        );

        TorrentImage::create([
            'torrent_id' => $this->torrentId,
            'path'       => $webpPath,
            'fallback'   => $jpegPath,
        ]);

        // delete temp upload
        Storage::delete($this->path);
    }
}