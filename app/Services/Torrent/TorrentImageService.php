<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\TorrentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TorrentImageService
{
    protected ImageManager $images;

    public function __construct()
    {
        $this->images = new ImageManager(new Driver);
    }

    public function upload(Torrent $torrent, Request $request): void
    {
        $this->attach($torrent, $this->prepare($request));
    }

    /** Upload before creating the torrent, so storage failures can be retried safely. */
    public function prepare(Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $request->validate([
            'images' => 'array|max:10',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);
        $paths = [];
        try {
            foreach ($request->file('images') as $image) {
                $paths[] = $this->storeEncoded($this->encode($image->getPathname()));
            }
        } catch (\Throwable $exception) {
            TorrentImage::deleteLocalFiles($paths);
            throw $exception;
        }

        return $paths;
    }

    /** Stage API screenshots privately so encoding does not block the upload response. */
    public function stageForBackground(Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }
        $request->validate([
            'images' => 'array|max:10',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:max_width=4096,max_height=2160',
        ]);
        $paths = [];
        try {
            foreach ($request->file('images') as $image) {
                $path = 'upload_images/'.Str::uuid().'.'.$image->extension();
                $paths[] = $path;
                if (! Storage::disk('local')->putFileAs('upload_images', $image, basename($path))) {
                    throw new \RuntimeException('Screenshot staging failed.');
                }
            }
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        return $paths;
    }

    public function attach(Torrent $torrent, array $paths): void
    {
        try {
            DB::transaction(function () use ($torrent, $paths) {
                foreach ($paths as $path) {
                    TorrentImage::create(['torrent_id' => $torrent->id, 'path' => $path]);
                }
            });
        } catch (\Throwable $exception) {
            TorrentImage::deleteLocalFiles($paths);
            throw $exception;
        }
    }

    /** Store an uploaded file or generated screenshot as a single WebP. */
    public function storeWebp(Torrent $torrent, string $source): TorrentImage
    {
        $path = $this->storeEncoded($this->encode($source));
        try {
            return TorrentImage::create(['torrent_id' => $torrent->id, 'path' => $path]);
        } catch (\Throwable $exception) {
            TorrentImage::deleteLocalFiles([$path]);
            throw $exception;
        }
    }

    private function encode(string $source): string
    {
        return $this->images->read($source)->scaleDown(1920, 1080)
            ->sharpen(8)->toWebp(quality: 78)->toString();
    }

    private function storeEncoded(string $encoded): string
    {
        $path = 'torrent_images/'.Str::uuid().'.webp';
        if (! Storage::disk('public')->put($path, $encoded)) {
            throw new \RuntimeException('Could not save the screenshot.');
        }

        return $path;
    }

    public function deleteMany(Torrent $torrent, array $imageIds): void
    {
        foreach ($imageIds as $id) {
            $image = $torrent->images()->where('id', $id)->first();
            if (! $image) {
                continue;
            }
            TorrentImage::deleteLocalFiles([$image->path, $image->fallback]);
            $image->delete();
        }
    }
}
