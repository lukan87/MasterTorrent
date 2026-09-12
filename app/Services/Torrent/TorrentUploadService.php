<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\User;
use App\Models\TorrentLog;
use App\Helpers\Bencode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\TorrentMovie;
use App\Services\TorrentSubscriptionService;
use Illuminate\Support\Facades\Http;


class TorrentUploadService
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
    $torrentData = $this->fileService->parseAndValidate($request);

    $decoded = $torrentData['decoded'];
    $raw     = $torrentData['raw'];

    $meta     = Bencode::get_meta($decoded);
    $infoHash = Bencode::get_infohash_raw($raw);
    // $infoHash = Bencode::get_infohash($decoded);
    $fileSignature = $this->buildFileSignature($decoded);

    // ✅ DUPLICATE CHECK
if (Torrent::where('info_hash', $infoHash)->exists()) {
    throw ValidationException::withMessages([
        'torrent' => ['This torrent already exists on the tracker.']
    ]);
}

    $name = $this->cleanName($request->name);
    $slug = $this->uniqueSlug($name);

    $metadata = $this->metadataService->resolve($request);

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

    $torrent = Torrent::create([
        'info_hash'   => $infoHash,
        'file_signature' => $fileSignature,
        'name'        => $name,
        'slug'        => $slug,
        'file_name'   => $this->fileService->storeTorrentFile($raw),
        'description' => $metadata['description'],
        'poster'      => $metadata['poster'],
        'background'  => $metadata['background'],
        'tmdbid'      => $metadata['tmdbid'],
        'tmdb_type'   => $metadata['tmdb_type'],
        'imdbid'      => $metadata['imdbid'],
        'imdb_url'   => $request->imdb_url,
        'steamid'     => $request->steamid,
        'category_id' => $request->category_id,
        'owner'       => $user->id,
        'size'        => $meta['size'],
        'num_files'   => $meta['count'],
        'announce'    => $torrentData['announce'],
        'mediainfo'   => $request->mediainfo,
        'free'        => $request->has('free') || $meta['size'] > 5368709120,
        'double'      => $request->has('double'),
        'sticky'      => $request->has('sticky'),
        'seedbox'     => $request->has('seedbox'),
        'external'    => $request->has('external'),
        'seeders'     => $request->has('external') ? 1 : 0,
    ]);

    $this->syncTorrentMovie($torrent);

    // 🔒 Hybrid torrents cannot be free or double
    if ($torrent->external) {
        $torrent->updateQuietly([
            'free'   => false,
            'double' => false,
        ]);
    }

    // ⚠ FIXED: pass $decoded, NOT $torrentData
    $this->genreService->sync($torrent, $request, $metadata);
    $this->fileService->storeFileList($torrent, $decoded);
    $this->imageService->upload($torrent, $request);

    $user->increment('seedbonus', 10);
    $user->update(['last_upload' => now()]);

    TorrentLog::create([
    'user_id'    => $user->id,
    'torrent_id' => $torrent->id,
    'action'     => 'uploaded',
    'description' => 'Uploaded torrent "' . $torrent->name . '" (ID: ' . $torrent->id . ')',
]);

    // 🔔 Notify subscribers whenever the torrent carries an IMDb/TMDB id
    app(\App\Services\TorrentSubscriptionService::class)->notifyUpload($torrent);

    return [
        'torrent' => $torrent,
        'created' => true,
    ];
}

    private function cleanName(string $name): string
    {
        $name = str_replace(['{', '}'], '.', $name);
        $name = preg_replace('/[^A-Za-z0-9\.\-\s]/', '.', $name);
        $name = preg_replace('/[\.]{2,}/', '.', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $i = 1;

        while (Torrent::where('slug', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $i++;
        }

        return $slug;
    }

    private function buildFileSignature(array $decoded): string
{
    $files = [];

    if (isset($decoded['info']['files'])) {

        foreach ($decoded['info']['files'] as $file) {
            $path = implode('/', $file['path']);
            $files[] = strtolower($path) . ':' . $file['length'];
        }

    } else {

        $files[] =
            strtolower($decoded['info']['name']) .
            ':' .
            $decoded['info']['length'];

    }

    sort($files);

    return sha1(implode('|', $files));
}

private function syncTorrentMovie(Torrent $torrent): void
{
    // 🚫 skip if not a movie
    if (!$torrent->tmdbid || $torrent->tmdb_type !== 'movie') {
        return;
    }

    // 🚫 skip if already exists
    if (TorrentMovie::where('tmdbid', $torrent->tmdbid)->exists()) {
        return;
    }

    $response = Http::get("https://api.themoviedb.org/3/movie/{$torrent->tmdbid}", [
        'api_key' => config('services.tmdb.key'),
    ]);

    if (!$response->successful()) {
        return;
    }

    $tmdb = $response->json();

    // extra safety
    if (!isset($tmdb['title'])) {
        return;
    }

    $title = $tmdb['title'];

    TorrentMovie::create([
        'tmdbid'        => $torrent->tmdbid,
        'title'         => $title,
        'slug'          => \Str::slug($title),
        'poster_path'   => $tmdb['poster_path'] ?? null,
        'backdrop_path' => $tmdb['backdrop_path'] ?? null,
        'rating'        => $tmdb['vote_average'] ?? null,
        'year'          => isset($tmdb['release_date'])
            ? substr($tmdb['release_date'], 0, 4)
            : null,
    ]);
}

}
