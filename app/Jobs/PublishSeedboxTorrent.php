<?php

namespace App\Jobs;

use App\Exceptions\DuplicateTorrentException;
use App\Models\Seedbox;
use App\Models\SeedboxPublication;
use App\Models\Torrent;
use App\Models\User;
use App\Services\SeedboxPublishingClients;
use App\Services\SeedboxPublishingReadiness;
use App\Services\Torrent\TorrentFileService;
use App\Services\Torrent\TorrentUploadService;
use App\Services\Torrent\UploadPermission;
use App\Services\TorrentRebuildService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class PublishSeedboxTorrent implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 12;

    public int $maxExceptions = 1;

    public bool $failOnTimeout = true;

    public function __construct(public int $publicationId)
    {
        $this->onConnection(config('upload-api.publishing_queue_connection') ?: config('queue.default'));
        $this->onQueue(config('upload-api.publishing_queue', 'seedbox-publishing'));
        $this->afterCommit();
    }

    public function handle(SeedboxPublishingClients $clients, TorrentUploadService $uploads, TorrentFileService $files): void
    {
        $lock = Cache::lock('seedbox-publish:'.$this->publicationId, 300);
        if (! $lock->get()) {
            // A timed-out worker can leave its lock until expiry. Keep the retry queued.
            $this->release(30);

            return;
        }
        $operation = null;
        $tmp = null;
        try {
            $operation = SeedboxPublication::findOrFail($this->publicationId);
            if (! in_array($operation->status, ['queued', 'processing'], true)) {
                return;
            }
            if (! app(SeedboxPublishingReadiness::class)->ready()) {
                $operation->update(['status' => $operation->torrent_id ? 'manual_seeding' : 'failed', 'error_code' => 'publishing_disabled']);

                return;
            }
            $user = User::findOrFail($operation->user_id);
            app(UploadPermission::class)->authorize($user);
            $seedbox = Seedbox::where('user_id', $user->id)->findOrFail($operation->seedbox_id);
            $operation->update(['status' => 'processing', 'error_code' => null, 'attempts' => $operation->attempts + 1]);
            $client = $clients->forSeedbox($seedbox);
            if (! isset($client->candidates()[$operation->source_hash])) {
                throw new \DomainException('Source is no longer complete.');
            }
            if (! $operation->torrent_id) {
                $raw = $client->source($operation->source_hash);
                // Reuse the normal seedbox upload's source marker to create a distinct client hash.
                $raw = (new TorrentRebuildService(config('upload-api.announce_base').$user->passkey))->rebuildTorrent($raw);
                $tmp = tempnam(sys_get_temp_dir(), 'fileiplay_publish_');
                if ($tmp === false || file_put_contents($tmp, $raw) !== strlen($raw)) {
                    throw new \RuntimeException('Temporary storage failed.');
                }
                $request = Request::create('/seedbox-publish', 'POST', $operation->metadata);
                $request->files->set('torrent', new UploadedFile($tmp, 'source.torrent', 'application/x-bittorrent', null, true));
                $request->setUserResolver(fn () => $user);
                $request->attributes->set('upload_method', 'seedbox');
                $uploads->handle($request, $user, $operation);
                $operation->refresh();
            }
            $torrent = Torrent::findOrFail($operation->torrent_id);
            $operation->update(['name' => $torrent->name, 'status' => 'published']);
            if (! $operation->register) {
                return;
            }
            // An identical source hash would overwrite or alter an existing client torrent.
            if ($torrent->info_hash === $operation->source_hash) {
                $operation->update(['status' => 'manual_seeding', 'error_code' => 'existing_client_hash']);

                return;
            }
            $operation->update(['status' => 'registering']);
            $candidates = $client->candidates();
            $registeredHere = (bool) ($operation->metadata['client_registered'] ?? false);
            if (isset($candidates[$torrent->info_hash])) {
                if ($candidates[$torrent->info_hash]['active']) {
                    $operation->update(['status' => 'seeding', 'error_code' => null]);

                    return;
                }
                if (! $registeredHere) {
                    $operation->update(['status' => 'manual_seeding', 'error_code' => 'existing_client_hash']);

                    return;
                }
            }
            if (! $registeredHere) {
                $created = $client->registerPublished($files->path($torrent->file_name), $operation->source_hash);
                if (! $created) {
                    // A concurrent/manual addition is not owned by this publishing operation.
                    $operation->update(['status' => 'manual_seeding', 'error_code' => 'existing_client_hash']);

                    return;
                }
                $operation->update(['metadata' => array_replace($operation->metadata, ['client_registered' => true])]);
            }
            $client->verifyPublished($torrent->info_hash);
            $operation->update(['status' => 'checking']);
            CheckSeedboxPublication::dispatch($operation->id)->delay(now()->addSeconds(30));
            // Only a torrent registered by this operation can be started after verification.
        } catch (DuplicateTorrentException $exception) {
            $operation?->update(['status' => 'duplicate', 'error_code' => 'duplicate_torrent']);
        } catch (\Throwable $exception) {
            $operation?->update(['status' => $operation->torrent_id ? 'registration_failed' : 'failed',
                'error_code' => $operation->torrent_id ? 'seedbox_registration_failed' : 'seedbox_publishing_failed']);
            // No raw provider diagnostics, URLs, request values or credentials are logged.
        } finally {
            if (is_string($tmp) && is_file($tmp)) {
                unlink($tmp);
            }
            $lock->release();
        }
    }

    public function failed(?\Throwable $exception): void
    {
        SeedboxPublication::whereKey($this->publicationId)->whereIn('status', ['queued', 'processing', 'published', 'registering'])
            ->update(['status' => 'failed', 'error_code' => 'worker_failed']);
    }
}
