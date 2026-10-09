<?php

namespace App\Services\Torrent;

use App\Helpers\Bencode;
use App\Jobs\NotifyTorrentUpload;
use App\Models\Torrent;
use App\Models\TorrentImage;
use App\Models\TorrentLog;
use App\Models\UploadAttempt;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExternalTorrentUploadService
{
    public function __construct(
        protected TorrentFileService $fileService,
        protected TorrentMetadataService $metadataService,
        protected TorrentGenreService $genreService,
        protected TorrentImageService $imageService,
        protected TorrentSteamService $steamService
    ) {}

    public function handle(Request $request, User $user): array
    {
        $attempt = UploadAttempt::create(['user_id' => $user->id, 'method' => 'website', 'status' => 'processing']);
        try {
            app(UploadValidator::class)->validate($request, $user);
            $result = $this->publish($request, $user, $attempt);
            $attempt->update(['torrent_id' => $result['torrent']->id, 'torrent_name' => $result['torrent']->name,
                'status' => $result['created'] ? 'completed' : 'duplicate',
                'error_code' => $result['created'] ? null : 'duplicate_torrent', 'completed_at' => now()]);

            return $result;
        } catch (\Throwable $exception) {
            $attempt->update(['status' => 'failed', 'error_code' => $exception instanceof ValidationException ? 'validation_failed' : 'upload_failed', 'completed_at' => now()]);
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface) {
                throw $exception;
            }
            Log::error('External torrent upload failed', ['attempt_id' => $attempt->id, 'exception_type' => get_class($exception)]);
            throw new \RuntimeException('The upload could not be completed. Please retry.');
        }
    }

    private function publish(Request $request, User $user, UploadAttempt $attempt): array
    {
        $file = $request->file('torrent');
        $rawTorrent = file_get_contents($file->getRealPath());
        $decoded = app(TorrentParser::class)->decode($rawTorrent === false ? '' : $rawTorrent);

        // 1. Set our announce URL
        $announceUrl = 'https://tracker.fileiplay.org/announce/'.$user->passkey;
        $decoded['announce'] = $announceUrl;

        // Ensure no other trackers are present if we want to force private
        unset($decoded['announce-list']);

        // Remove private flag to make it DHT/public-friendly
        if (isset($decoded['info']['private'])) {
            unset($decoded['info']['private']);
        }

        // 2. Re-bencode
        $modifiedRawTorrent = Bencode::bencode($decoded);

        // 3. Get new infohash
        $infoHash = sha1(Bencode::bencode($decoded['info']));

        // 4. Duplicate Check
        if (Torrent::withTrashed()->where('info_hash', $infoHash)->exists()) {
            $torrent = Torrent::withTrashed()->where('info_hash', $infoHash)->first();

            return ['torrent' => $torrent, 'created' => false];
        }

        $name = $this->cleanName($request->name);

        $meta = Bencode::get_meta($decoded);

        $metadata = $this->metadataService->resolve($request);

        $imagePaths = $this->imageService->prepare($request);
        $fileName = null;
        try {
            $fileName = $this->fileService->storeTorrentFile($modifiedRawTorrent);
            $torrent = app(TorrentSlugService::class)->transaction($name, $infoHash, function (string $slug) use ($request, $user, $infoHash, $decoded, $name, $fileName, $meta, $announceUrl, $metadata, $imagePaths, $attempt) {

                $torrent = Torrent::create([
                    'info_hash' => $infoHash,
                    'file_signature' => $this->buildFileSignature($decoded),
                    'name' => $name,
                    'slug' => $slug,
                    'file_name' => $fileName,
                    'description' => $request->description,
                    'poster' => $request->poster,
                    'category_id' => $request->category_id,
                    'owner' => $user->id,
                    'anon' => app(TorrentAnonymity::class)->resolve($request, $user),
                    'size' => $meta['size'],
                    'num_files' => $meta['count'],
                    'announce' => $announceUrl,
                    'external' => true,
                    'imdbid' => $metadata['imdbid'],
                    'tmdbid' => $metadata['tmdbid'],
                    'tmdb_type' => $metadata['tmdb_type'],
                    'imdb_url' => $request->imdb_url,
                    'seeders' => 1, // External torrents have seeders
                ]);

                $this->imageService->attach($torrent, $imagePaths);

                TorrentLog::create([
                    'user_id' => $user->id,
                    'torrent_id' => $torrent->id,
                    'action' => 'uploaded',
                    'description' => 'Uploaded external torrent "'.$torrent->name.'" (ID: '.$torrent->id.')',
                ]);

                $attempt->update(['torrent_id' => $torrent->id, 'torrent_name' => $torrent->name, 'status' => 'completed', 'completed_at' => now()]);

                return $torrent;
            });
        } catch (\Throwable $exception) {
            if ($fileName) {
                $this->fileService->delete($fileName);
            }
            TorrentImage::deleteLocalFiles($imagePaths);
            if ($exception instanceof UniqueConstraintViolationException &&
                $existing = Torrent::withTrashed()->where('info_hash', $infoHash)->first()) {
                return ['torrent' => $existing, 'created' => false];
            }
            throw $exception;
        }
        if ($torrent->imdbid || $torrent->tmdbid) {
            try {
                $pending = NotifyTorrentUpload::dispatch($torrent->id);
                if (config('queue.default') === 'sync') {
                    $pending->afterResponse();
                }
                unset($pending);
            } catch (\Throwable $exception) {
                Log::warning('External upload notification scheduling failed', ['torrent_id' => $torrent->id, 'exception_type' => get_class($exception)]);
            }
        }

        return ['torrent' => $torrent, 'created' => true];
    }

    private function cleanName(string $name): string
    {
        $name = str_replace(['{', '}'], '.', $name);
        $name = preg_replace('/[^A-Za-z0-9\.\-\s]/', '.', $name);
        $name = preg_replace('/\s+/', '.', $name);
        $name = preg_replace('/[\.]{2,}/', '.', $name);
        $name = trim($name, '.');

        return preg_replace('/(?:\.(?:torrent|mkv|mp4|avi|mov|m2ts|ts|webm|wmv|mpg|mpeg|m4v|vob))+$/i', '', $name);
    }

    private function buildFileSignature(array $decoded): string
    {
        $files = [];
        if (isset($decoded['info']['files'])) {
            foreach ($decoded['info']['files'] as $file) {
                $path = implode('/', $file['path']);
                $files[] = strtolower($path).':'.$file['length'];
            }
        } else {
            $files[] = strtolower($decoded['info']['name']).':'.$decoded['info']['length'];
        }
        sort($files);

        return sha1(implode('|', $files));
    }
}
