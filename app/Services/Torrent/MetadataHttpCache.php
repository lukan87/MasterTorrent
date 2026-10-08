<?php

namespace App\Services\Torrent;

use App\Jobs\RefreshTorrentMetadata;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Cache provider data only; user permissions and live torrent stats stay uncached. */
class MetadataHttpCache
{
    private bool $deferred = false;

    private array $pendingRefreshes = [];

    public function hasPendingRefreshes(): bool
    {
        foreach ($this->pendingRefreshes as $key) {
            if (Cache::has("{$key}:pending")) {
                return true;
            }
        }

        return false;
    }

    /** Return saved metadata during rendering, and refresh outside the response. */
    public function defer(callable $render): mixed
    {
        $previous = $this->deferred;
        $this->deferred = true;
        try {
            return $render();
        } finally {
            $this->deferred = $previous;
        }
    }

    public function get(string $key, string $url, array $query, ?callable $valid = null): ?array
    {
        $providerKey = $key;
        $key = "torrent_metadata_v3:{$key}";
        $cached = Cache::get($key);
        $fallback = $cached['data'] ?? null;

        if (($cached['fresh_until'] ?? 0) > now()->timestamp || Cache::has("{$key}:retry")) {
            return $fallback;
        }

        if ($this->deferred) {
            $this->pendingRefreshes[$key] = $key;
            if (Cache::add("{$key}:pending", true, 120)) {
                try {
                    $job = RefreshTorrentMetadata::dispatch($providerKey);
                    // A sync development queue must also wait until the response is sent.
                    if (config('queue.default') === 'sync') {
                        $job->afterResponse();
                    }
                    unset($job);
                } catch (\Throwable $exception) {
                    Cache::forget("{$key}:pending");
                    report($exception);
                }
            }

            return $fallback;
        }

        // Never queue page requests behind another visitor's provider refresh.
        $lock = Cache::lock("{$key}:lock", 10);
        if (! $lock->get()) {
            return $fallback;
        }

        try {
            $response = Http::connectTimeout(1)->timeout(3)->get($url, $query);
            $data = $response->json();
            if ($response->successful() && is_array($data) && $data !== [] && (! $valid || $valid($data))) {
                Cache::put($key, ['data' => $data, 'fresh_until' => now()->addDay()->timestamp], now()->addDays(30));

                return $data;
            }
        } catch (ConnectionException $e) {
            // Optional metadata must not prevent downloading or viewing a torrent.
        } finally {
            $lock->release();
        }

        // Null cannot be negative-cached with Cache::remember. Back off separately.
        Cache::put("{$key}:retry", true, 60);

        return $fallback;
    }
}
