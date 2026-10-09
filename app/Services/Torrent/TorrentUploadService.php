<?php

namespace App\Services\Torrent;

use App\Exceptions\DuplicateTorrentException;
use App\Helpers\Bencode;
use App\Jobs\NotifyTorrentUpload;
use App\Jobs\ProcessUploadScreenshot;
use App\Models\SeedboxPublication;
use App\Models\Torrent;
use App\Models\TorrentImage;
use App\Models\TorrentLog;
use App\Models\TorrentMovie;
use App\Models\TorrentSeries;
use App\Models\UploadAttempt;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class TorrentUploadService
{
    public function __construct(
        protected TorrentFileService $fileService,
        protected TorrentMetadataService $metadataService,
        protected TorrentGenreService $genreService,
        protected TorrentImageService $imageService,
        protected TorrentSteamService $steamService
    ) {}

    public function handle(Request $request, User $user, ?SeedboxPublication $publication = null): array
    {
        $token = $user->currentAccessToken();
        $attempt = UploadAttempt::create([
            'user_id' => $user->id,
            'personal_access_token_id' => $token instanceof PersonalAccessToken ? $token->id : null,
            'token_name' => $token instanceof PersonalAccessToken ? mb_substr($token->name, 0, 80) : null,
            'method' => $publication ? 'seedbox' : ($request->is('api/v1/*') ? 'api' : 'website'),
            'status' => 'processing',
        ]);
        try {
            return $this->publish($request, $user, $publication, $attempt);
        } catch (\Throwable $exception) {
            $code = match (true) {
                $exception instanceof DuplicateTorrentException => 'duplicate_torrent',
                $exception instanceof ValidationException => 'validation_failed',
                $exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 403 => 'forbidden',
                default => 'upload_failed',
            };
            $attempt->update(['status' => $code === 'duplicate_torrent' ? 'duplicate' : 'failed', 'error_code' => $code, 'completed_at' => now()]);
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface) {
                throw $exception;
            }
            Log::error('Torrent upload failed', ['attempt_id' => $attempt->id, 'exception_type' => get_class($exception)]);
            throw new \RuntimeException('The upload could not be completed. Please retry.');
        }
    }

    private function publish(Request $request, User $user, ?SeedboxPublication $publication, UploadAttempt $attempt): array
    {
        app(UploadValidator::class)->validate($request, $user);
        $torrentData = $this->fileService->parseAndValidate($request, $user);

        $decoded = $torrentData['decoded'];
        $raw = $torrentData['raw'];

        $meta = Bencode::get_meta($decoded);
        $infoHash = $torrentData['info_hash'];
        // $infoHash = Bencode::get_infohash($decoded);
        $fileSignature = $this->buildFileSignature($decoded);

        if (Torrent::withTrashed()->where('info_hash', $infoHash)->exists()) {
            throw DuplicateTorrentException::forUpload();
        }

        $metadata = $this->metadataService->resolve($request);
        $name = $this->cleanName($metadata['name']);
        if ($name === '') {
            throw ValidationException::withMessages(['name' => 'Provide a valid torrent name.']);
        }

        /* 🎮 Steam enrichment */
        if ($request->steamid) {
            $steam = $this->steamService->fetch($request->steamid);

            if ($steam) {
                $steamData = $this->steamService->normalize($steam);

                $metadata['description'] = $metadata['description']
                    ?? $steamData['description']
                    ?? null;

                $metadata['poster'] = $metadata['poster']
                    ?? $steamData['poster']
                    ?? null;

                $metadata['background'] = $metadata['background']
                    ?? $steamData['background']
                    ?? null;
            }
        }

        // Provider I/O and file preparation happen before database locks are held.
        $libraryMetadata = $metadata['library_metadata'];
        $stagedImages = $request->is('api/v1/*') ? $this->imageService->stageForBackground($request) : [];
        $imagePaths = $request->is('api/v1/*') ? [] : $this->imageService->prepare($request);
        $fileName = null;
        try {
            $fileName = $this->fileService->storeTorrentFile($raw);
            $torrent = app(TorrentSlugService::class)->transaction($name, $infoHash, function (string $slug) use ($request, $user, $infoHash, $fileSignature, $name, $fileName, $metadata, $meta, $torrentData, $decoded, $imagePaths, $libraryMetadata, $publication, $attempt) {

                $torrent = Torrent::create([
                    'info_hash' => $infoHash,
                    'file_signature' => $fileSignature,
                    'name' => $name,
                    'slug' => $slug,
                    'file_name' => $fileName,
                    'description' => $metadata['description'],
                    'poster' => $metadata['poster'],
                    'background' => $metadata['background'],
                    'tmdbid' => $metadata['tmdbid'],
                    'tvdbid' => $metadata['tvdbid'],
                    'season' => $request->input('season'),
                    'episode' => $request->input('episode'),
                    'tmdb_type' => $metadata['tmdb_type'],
                    'imdbid' => $metadata['imdbid'],
                    'imdb_url' => $request->imdb_url,
                    'steamid' => $request->steamid,
                    'category_id' => $request->category_id,
                    'owner' => $user->id,
                    'anon' => app(TorrentAnonymity::class)->resolve($request, $user),
                    'size' => $meta['size'],
                    'num_files' => $meta['count'],
                    'announce' => $torrentData['announce'],
                    'mediainfo' => $request->mediainfo,
                    'free' => $request->has('free') || $meta['size'] > 5368709120,
                    'double' => $request->has('double'),
                    'sticky' => $request->has('sticky'),
                    'seedbox' => $request->has('seedbox'),
                    'external' => $request->has('external'),
                    'seeders' => $request->has('external') ? 1 : 0,
                ]);

                $this->syncTorrentMovie($torrent, $libraryMetadata);

                $this->syncTorrentSeries($torrent, $libraryMetadata);

                // 🔒 Hybrid torrents cannot be free or double
                if ($torrent->external) {
                    $torrent->updateQuietly([
                        'free' => false,
                        'double' => false,
                    ]);
                }

                // ⚠ FIXED: pass $decoded, NOT $torrentData
                $this->genreService->sync($torrent, $request, $metadata);
                $this->fileService->storeFileList($torrent, $decoded);
                $this->imageService->attach($torrent, $imagePaths);

                $user->increment('seedbonus', 10);
                $user->update(['last_upload' => now()]);

                TorrentLog::create([
                    'user_id' => $user->id,
                    'torrent_id' => $torrent->id,
                    'action' => 'uploaded',
                    'description' => 'Uploaded torrent "'.$torrent->name.'" (ID: '.$torrent->id.')',
                ]);

                $attempt->update(['torrent_id' => $torrent->id, 'torrent_name' => $torrent->name, 'status' => 'completed', 'completed_at' => now()]);
                $publication?->update(['torrent_id' => $torrent->id, 'name' => $torrent->name, 'status' => 'published']);

                return $torrent;
            });
        } catch (\Throwable $exception) {
            if ($fileName) {
                $this->fileService->delete($fileName);
            }
            TorrentImage::deleteLocalFiles($imagePaths);
            Storage::disk('local')->delete($stagedImages);
            if ($exception instanceof UniqueConstraintViolationException) {
                if (Torrent::withTrashed()->where('info_hash', $infoHash)->exists()) {
                    throw DuplicateTorrentException::forUpload();
                }
                throw ValidationException::withMessages(['name' => 'Another upload used this name. Please retry.']);
            }
            throw $exception;
        }

        foreach ($stagedImages as $path) {
            try {
                $pending = ProcessUploadScreenshot::dispatch($torrent->id, $path);
                if (config('queue.default') === 'sync') {
                    $pending->afterResponse();
                }
                unset($pending);
            } catch (\Throwable $exception) {
                Storage::disk('local')->delete($path);
                $metadata['warnings'][] = 'A screenshot could not be scheduled. Add it from Edit Torrent.';
                Log::warning('Upload screenshot scheduling failed', ['torrent_id' => $torrent->id, 'exception_type' => get_class($exception)]);
            }
        }

        // A committed upload must not become a failed response because notifications fail.
        if ($torrent->imdbid || $torrent->tmdbid) {
            try {
                $pending = NotifyTorrentUpload::dispatch($torrent->id);
                if (config('queue.default') === 'sync') {
                    $pending->afterResponse();
                }
                unset($pending);
            } catch (\Throwable $exception) {
                Log::warning('Upload notification scheduling failed', [
                    'torrent_id' => $torrent->id, 'exception_type' => get_class($exception),
                ]);
            }
        }

        return [
            'torrent' => $torrent,
            'created' => true,
            'warnings' => $metadata['warnings'],
        ];
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

            $files[] =
                strtolower($decoded['info']['name']).
                ':'.
                $decoded['info']['length'];

        }

        sort($files);

        return sha1(implode('|', $files));
    }

    private function syncTorrentMovie(Torrent $torrent, ?array $tmdb): void
    {
        // 🚫 skip if not a movie
        if (! $torrent->tmdbid || $torrent->tmdb_type !== 'movie') {
            return;
        }

        if (! is_string($tmdb['title'] ?? null)) {
            return;
        }
        TorrentMovie::firstOrCreate(['tmdbid' => $torrent->tmdbid], $this->libraryAttributes($tmdb, 'title', 'release_date'));
    }

    /**
     * Sync TV-series TMDB metadata into the torrent_series table whenever a TV
     * torrent is uploaded. This keeps the series library populated automatically.
     */
    private function syncTorrentSeries(Torrent $torrent, ?array $tmdb): void
    {
        // 🚫 skip if not a TV/series torrent
        if (! $torrent->tmdbid || $torrent->tmdb_type !== 'tv') {
            return;
        }

        if (! is_string($tmdb['name'] ?? null)) {
            return;
        }
        TorrentSeries::firstOrCreate(['tmdbid' => $torrent->tmdbid], $this->libraryAttributes($tmdb, 'name', 'first_air_date'));
    }

    private function libraryAttributes(array $data, string $titleKey, string $dateKey): array
    {
        $title = mb_substr($data[$titleKey], 0, 255);
        $date = $data[$dateKey] ?? null;
        $year = is_string($date) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date) ? (int) substr($date, 0, 4) : null;
        // MySQL YEAR cannot represent early films; retain those titles without an invalid year.
        if ($year !== null && ($year < 1901 || $year > 2155)) {
            $year = null;
        }
        $rating = $data['vote_average'] ?? null;

        return ['title' => $title, 'slug' => Str::slug($title),
            'poster_path' => is_string($data['poster_path'] ?? null) ? mb_substr($data['poster_path'], 0, 255) : null,
            'backdrop_path' => is_string($data['backdrop_path'] ?? null) ? mb_substr($data['backdrop_path'], 0, 255) : null,
            'rating' => is_numeric($rating) && $rating >= 0 && $rating <= 10 ? (float) $rating : null, 'year' => $year];
    }
}
