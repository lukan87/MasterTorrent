<?php

namespace App\Jobs;

use App\Models\Seedbox;
use App\Models\SeedboxPublication;
use App\Models\User;
use App\Services\SeedboxPublishingClients;
use App\Services\SeedboxPublishingReadiness;
use App\Services\Torrent\UploadPermission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CheckSeedboxPublication implements ShouldQueue
{
    use Queueable;

    public int $timeout = 90;

    public int $tries = 12;

    public int $maxExceptions = 1;

    public bool $failOnTimeout = true;

    public function __construct(public int $publicationId, public int $checks = 0)
    {
        $this->afterCommit();
    }

    public function handle(SeedboxPublishingClients $clients): void
    {
        $lock = Cache::lock('seedbox-publish:'.$this->publicationId, 300);
        if (! $lock->get()) {
            // A timed-out worker can leave its lock until expiry. Keep the retry queued.
            $this->release(30);

            return;
        }
        $operation = null;
        try {
            $operation = SeedboxPublication::with('torrent')->findOrFail($this->publicationId);
            if ($operation->status !== 'checking' || ! $operation->register || ! $operation->torrent) {
                return;
            }
            if (! ($operation->metadata['client_registered'] ?? false)) {
                $operation->update(['status' => 'manual_seeding', 'error_code' => 'existing_client_hash']);

                return;
            }
            if (! app(SeedboxPublishingReadiness::class)->ready()) {
                $operation->update(['status' => $operation->torrent_id ? 'manual_seeding' : 'failed', 'error_code' => 'publishing_disabled']);

                return;
            }
            $user = User::findOrFail($operation->user_id);
            app(UploadPermission::class)->authorize($user);
            $box = Seedbox::where('user_id', $user->id)->findOrFail($operation->seedbox_id);
            $client = $clients->forSeedbox($box);
            $hash = $operation->torrent->info_hash;
            $candidates = $client->candidates();
            if (isset($candidates[$hash])) {
                if (! $candidates[$hash]['active']) {
                    $result = $client->startTorrent($hash);
                    if (isset($result['error'])) {
                        throw new \DomainException('Client start failed.');
                    }
                    // Re-read to confirm state; a successful command alone does not prove seeding.
                    $candidates = $client->candidates();
                }
                if ($candidates[$hash]['active']) {
                    $operation->update(['status' => 'seeding', 'error_code' => null]);

                    return;
                }
            }
            if ($this->checks >= 20) {
                $operation->update(['status' => 'manual_seeding', 'error_code' => 'verification_timeout']);
            } else {
                self::dispatch($operation->id, $this->checks + 1)->delay(now()->addSeconds(30));
            }
        } catch (\Throwable $e) {
            $operation?->update(['status' => 'registration_failed', 'error_code' => 'seedbox_status_failed']);
        } finally {
            $lock->release();
        }
    }

    public function failed(?\Throwable $exception): void
    {
        SeedboxPublication::whereKey($this->publicationId)->where('status', 'checking')
            ->update(['status' => 'registration_failed', 'error_code' => 'worker_failed']);
    }
}
