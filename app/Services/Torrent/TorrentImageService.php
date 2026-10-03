<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\TorrentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class TorrentImageService
{
    protected ImageManager $images;

    public function __construct()
    {
        // ✅ Correct for Intervention v3.11+
        $this->images = new ImageManager(new Driver());
    }

    public function upload(Torrent $torrent, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ((array) $request->file('images') as $image) {
            if ($image->isValid()) {
                $this->storeWebp($torrent, $image->getPathname());
            }
        }
    }

    /** Store an uploaded file or generated image binary as a single WebP. */
    public function storeWebp(Torrent $torrent, string $source): TorrentImage
    {
        $encoded = $this->images->read($source)
            ->scaleDown(1920, 1080)
            ->sharpen(8)
            ->toWebp(quality: 78)
            ->toString();

        $path = 'torrent_images/' . Str::uuid() . '.webp';
        $disk = Storage::disk('public');
        if (!$disk->put($path, $encoded)) {
            throw new \RuntimeException('Could not save the screenshot.');
        }

        try {
            return TorrentImage::create([
                'torrent_id' => $torrent->id,
                'path' => $path,
            ]);
        } catch (\Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }
    }

    public function deleteMany(Torrent $torrent, array $imageIds): void
    {
        foreach ($imageIds as $id) {
            $image = $torrent->images()->where('id', $id)->first();

            if (!$image) {
                continue;
            }

            $paths = array_filter([
                $image->path,
                $image->fallback,
            ]);

            if ($paths) {
                Storage::disk('public')->delete($paths);
            }

            $image->delete();
        }
    }
}