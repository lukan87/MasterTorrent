<?php


namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\TorrentLog;
use Illuminate\Http\Request;

class TorrentUpdateService
{
    public function __construct(
        protected TorrentMetadataService $metadataService,
        protected TorrentGenreService $genreService,
        protected TorrentImageService $imageService,
        protected TorrentSteamService $steamService
    ) {}

    public function handle(Request $request, string $slug): Torrent
    {
        $torrent = Torrent::where('slug', $slug)->firstOrFail();
        $original = $torrent->getOriginal();

        /* ✅ Name is ALWAYS explicit */
        $name = $request->name;

        // ✅ Preserve original values BEFORE services touch the request
       $imdbUrl = $request->imdb_url ?: $torrent->imdb_url;

        /* 🎬 TMDB / IMDb metadata */
        $metadata = $this->metadataService->resolve($request);

        /* 🎮 Steam enrichment (fallback only) */
        if ($request->steamid) {
            $steam = $this->steamService->fetch($request->steamid);

            if ($steam) {
                $steamData = $this->steamService->normalize($steam);

                $metadata['description'] ??= $steamData['description'];
                $metadata['poster']      ??= $steamData['poster'];
                $metadata['background']  ??= $steamData['background'];
            }
        }

        /* 🔁 Update torrent */
        $torrent->update([
            'name'        => $name,
            'description' => $metadata['description'] ?? $torrent->description,
            'category_id' => $request->category_id,
           'poster' => $request->filled('poster')
    ? $request->poster
    : ($metadata['poster'] ?? $torrent->poster),

'background' => $request->filled('background')
    ? $request->background
    : ($metadata['background'] ?? $torrent->background),
            'imdb_url'    => $request->filled('imdb_url')
        ? $request->imdb_url
        : $torrent->imdb_url,
            'imdbid'      => $metadata['imdbid'],
            'tmdbid'      => $metadata['tmdbid'],
            'tmdb_type'   => $metadata['tmdb_type'],
            'steamid'     => $request->steamid,
            'trailer'     => $request->trailer,
            'mediainfo'   => $request->mediainfo,
            'free'        => $request->has('free'),
            'double'      => $request->has('double'),
            'sticky'      => $request->has('sticky'),
            'seedbox'     => $request->has('seedbox'),
        ]);

        /* 🎭 Genres (same logic as upload) */
        $this->genreService->sync($torrent, $request, $metadata);

        /* 🖼️ Delete selected images */
        if ($request->has('delete_images')) {
            $this->imageService->deleteMany(
                $torrent,
                $request->delete_images
            );
        }

        /* 🖼️ Upload new images */
        $this->imageService->upload($torrent, $request);


// refresh to get FINAL DB state (after all services)
$torrent->refresh();

$fields = [];

$trackFields = [
    'free',
    'double',
    'sticky',
    'seedbox',
    'poster',
    'background',
    'name',
    'description',
];

foreach ($trackFields as $field) {

    $oldValue = $original[$field] ?? null;
    $newValue = $torrent->$field ?? null;

    // normalize values (fix boolean / int mismatch)
    $oldNormalized = (string)(int)$oldValue;
    $newNormalized = (string)(int)$newValue;

    if ($oldNormalized !== $newNormalized) {

        $oldDisplay = $oldNormalized == '1' ? 'Yes' : 'No';
        $newDisplay = $newNormalized == '1' ? 'Yes' : 'No';

        $fields[] = "{$field}: '{$oldDisplay}' → '{$newDisplay}'";
    }
}

if (!empty($fields)) {
    TorrentLog::create([
        'user_id'    => auth()->id(),
        'torrent_id' => $torrent->id,
        'action'     => 'edited',
        'description'=> implode(', ', $fields),
    ]);
}

        return $torrent;
    }
}
