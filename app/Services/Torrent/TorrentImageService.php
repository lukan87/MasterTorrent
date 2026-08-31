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
            if (!$image->isValid()) {
                continue;
            }

            $img = $this->images
    ->read($image->getPathname())
    ->scaleDown(1920, 1080)
    ->sharpen(8);

            $uuid = (string) Str::uuid();

// ---------- WebP (primary) ----------
$webpPath = "torrent_images/{$uuid}.webp";
Storage::disk('public')->put(
    $webpPath,
    $img->toWebp(quality: 78) // 👈 lower = smaller (75 sweet spot)
        ->toString()
);

// ---------- JPEG fallback ----------
$jpegPath = "torrent_images/{$uuid}.jpg";
Storage::disk('public')->put(
    $jpegPath,
    $img->toJpeg(quality: 80, progressive: true) // 👈 progressive loading
        ->toString()
);

            TorrentImage::create([
                'torrent_id' => $torrent->id,
                'path'       => $webpPath,
                'fallback'   => $jpegPath,
            ]);
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