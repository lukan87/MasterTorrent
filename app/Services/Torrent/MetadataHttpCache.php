<?php

namespace App\Services\Torrent;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Cache provider data only; user permissions and live torrent stats stay uncached. */
class MetadataHttpCache
{
    public function get(string $key, string $url, array $query, ?callable $valid = null): ?array
    {
        $key = "torrent_metadata_v3:{$key}";
        $cached = Cache::get($key);
        $fallback = $cached['data'] ?? null;

        if (($cached['fresh_until'] ?? 0) > now()->timestamp || Cache::has("{$key}:retry")) {
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
