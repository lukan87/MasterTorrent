<?php

namespace App\Jobs;

use App\Models\Torrent;
use App\Services\Torrent\TorrentImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUploadScreenshot implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(public int $torrentId, public string $path)
    {
        $this->afterCommit();
    }

    public function handle(TorrentImageService $images): void
    {
        if (! preg_match('~^upload_images/[a-f0-9-]+\.(?:jpg|jpeg|png|webp)$~D', $this->path)) {
            return;
        }
        $lock = Cache::lock('upload-screenshot:'.hash('sha256', $this->path), 300);
        if (! $lock->get()) {
            return;
        }
        try {
            if (! Storage::disk('local')->exists($this->path)) {
                return;
            }
            if ($torrent = Torrent::find($this->torrentId)) {
                $images->storeWebp($torrent, Storage::disk('local')->path($this->path));
            }
        } catch (\Throwable $e) {
            Log::warning('Upload screenshot processing failed', ['torrent_id' => $this->torrentId, 'exception_type' => get_class($e)]);
        } finally {
            Storage::disk('local')->delete($this->path);
            $lock->release();
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if (preg_match('~^upload_images/[a-f0-9-]+\.(?:jpg|jpeg|png|webp)$~D', $this->path)) {
            Storage::disk('local')->delete($this->path);
        }
    }
}
