<?php

namespace App\Http\Controllers;

use App\Models\Seedbox;
use App\Services\SeedboxService;
use App\Services\TorrentMetadataService;
use App\Services\TorrentRebuildService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\MediaInfoParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Storage;

class SeedboxController extends Controller
{
    private function seedboxService(Seedbox $seedbox): SeedboxService
    {
        return new SeedboxService(
            $seedbox->address,
            $seedbox->username,
            $seedbox->password,
            $seedbox->auth_type
        );
    }

    public function index()
    {
        $seedboxes = Seedbox::with('user')->get();
        return view('seedboxes.index', compact('seedboxes'));
    }

    public function create()
    {
        return view('seedboxes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'auth_type' => 'required|in:basic,digest',
        ]);

        Seedbox::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'address' => $request->address,
            'username' => $request->username,
            'password' => $request->password,
            'auth_type' => $request->auth_type,
        ]);

        return redirect()->route('seedboxes.index')->with('success', 'Seedbox created successfully!');
    }

    public function edit(Seedbox $seedbox)
    {
        return view('seedboxes.edit', compact('seedbox'));
    }

    public function update(Request $request, Seedbox $seedbox)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'auth_type' => 'required|in:basic,digest',
        ]);

        $seedbox->update($request->only(['name', 'address', 'username', 'password', 'auth_type']));

        return redirect()->route('seedboxes.index')->with('success', 'Seedbox updated successfully!');
    }

    public function destroy(Seedbox $seedbox)
    {
        try {
            $seedbox->delete();
            return redirect()->route('seedboxes.index')->with('success', 'Seedbox deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('seedboxes.index')->with('error', 'Failed to delete seedbox: ' . $e->getMessage());
        }
    }

  public function testConnection(Seedbox $seedbox)
{
    $service = $this->seedboxService($seedbox);
    $torrents = $service->getTorrents();

    $success = !isset($torrents['error']);

    // If request is AJAX / fetch
    if (request()->expectsJson()) {
        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Connection successful'
                : 'Connection failed: ' . $torrents['error']
        ]);
    }

    // Normal browser redirect
    if ($success) {
        return redirect()->back()->with('success', 'Connection successful');
    }

    return redirect()->back()->with('error', 'Connection failed: ' . $torrents['error']);
}

   public function showTorrents(Seedbox $seedbox, Request $request)
{
    $service = $this->seedboxService($seedbox);
    $allTorrents = $service->getTorrents()['t'] ?? [];

    // Sorting
    uasort($allTorrents, fn($a, $b) => ($b[21] ?? 0) <=> ($a[21] ?? 0));

    // Filtering by search
    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $allTorrents = array_filter($allTorrents, fn($torrent) => str_contains(strtolower($torrent[4] ?? ''), $search));
    }

    // Pagination
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $perPage = 50;
    $items = collect($allTorrents);
    $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage);

    $torrents = new LengthAwarePaginator(
        $currentItems,
        $items->count(),
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // --- Add summary stats ---
    $stats = [
        'total' => count($allTorrents),
        'totalSize' => array_sum(array_column($allTorrents, 5)), // total size
        'totalDownloaded' => array_sum(array_column($allTorrents, 8)),
        'totalUploaded' => array_sum(array_column($allTorrents, 9)),
        'seeding' => count(array_filter($allTorrents, fn($t) => ($t[28] ?? 0) == 1)),
        'downloading' => count(array_filter($allTorrents, fn($t) => ($t[28] ?? 0) == 2)),
        'paused' => count(array_filter($allTorrents, fn($t) => ($t[28] ?? 0) == 0)),
    ];

    return view('seedboxes.torrents', compact('seedbox', 'torrents', 'service', 'stats'));
}


    public function importTorrent(Seedbox $seedbox, string $hash)
    {
        try {
            $service = $this->seedboxService($seedbox);
            $torrentContent = $service->downloadTorrentFile($hash);
            $tmpFile = tempnam(sys_get_temp_dir(), 'seedbox_') . '.torrent';
            file_put_contents($tmpFile, $torrentContent);

            $torrentDownloadService = new \App\Services\TorrentDownloadService();
            $torrentDownloadService->handleUploadFromSeedbox($tmpFile, auth()->user());

            unlink($tmpFile);

            return back()->with('success', 'Torrent imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function start(Seedbox $seedbox, string $hash)
    {
        $service = $this->seedboxService($seedbox);
        $result = $service->startTorrent($hash);
        return redirect()->back()->with(!isset($result['error']) ? 'success' : 'error', !isset($result['error']) ? 'Torrent started' : 'Failed to start torrent');
    }

    public function pause(Seedbox $seedbox, string $hash)
    {
        $service = $this->seedboxService($seedbox);
        $result = $service->pauseTorrent($hash);
        return redirect()->back()->with(!isset($result['error']) ? 'success' : 'error', !isset($result['error']) ? 'Torrent paused' : 'Failed to pause torrent');
    }

    public function delete(Seedbox $seedbox, string $hash, Request $request)
    {
        $service = $this->seedboxService($seedbox);
        $deleteData = $request->input('delete_data', false);
        $result = $service->deleteTorrent($hash, $deleteData);

        $success = !isset($result['error']);
        $message = $success ? ($deleteData ? 'Torrent and files deleted' : 'Torrent deleted') : 'Failed to delete torrent';

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }

    public function addTorrent(Request $request, Seedbox $seedbox)
    {
        $request->validate(['torrent_file' => 'required|file|mimes:torrent|max:10240']);
        $service = $this->seedboxService($seedbox);
        $result = $service->addTorrentFile($request->file('torrent_file')->getRealPath());

        return back()->with(isset($result['error']) ? 'error' : 'success', $result['error'] ?? 'Torrent added successfully!');
    }

    protected function addTorrentToSeedbox(Seedbox $seedbox, string $torrentBinary): array
{
    $tmpPath = tempnam(sys_get_temp_dir(), 'torrent_') . '.torrent';
    file_put_contents($tmpPath, $torrentBinary);

    try {
        $service = $this->seedboxService($seedbox);
        $result = $service->addTorrentFile($tmpPath);
    } finally {
        @unlink($tmpPath);
    }

    return $result;
}


    public function getTorrentTrackers(Seedbox $seedbox, string $hash)
    {
        $service = $this->seedboxService($seedbox);
        $trackers = $service->torrentTrackers($hash);

        $trackerUrls = [];
        foreach ($trackers as $t) {
            if (is_array($t) && isset($t[0][0])) $trackerUrls[] = $t[0][0];
        }

        $hasFileIplay = collect($trackerUrls)->contains(fn($url) => str_contains($url, 'fileiplay.org'));

        return response()->json(['trackers' => $trackerUrls, 'has_fileiplay' => $hasFileIplay]);
    }

public function downloadRebuiltTorrent($seedboxId, $hash)
{
    $seedbox = Seedbox::findOrFail($seedboxId);
    $user = Auth::user();

    if (!$user || !$user->passkey) {
        return back()->with('error', 'You do not have a passkey.');
    }

    $service = new SeedboxService(
        $seedbox->address,
        $seedbox->username,
        $seedbox->password,
        'basic',
        true
    );

    /* =========================================================
       1️⃣ GET TORRENT FROM SEEDBOX
    ========================================================= */

    $torrentData = $service->testRpcSessionPath($hash);
    $torrentContent = $torrentData['torrentContent'] ?? null;

    if (!$torrentContent) {
        return back()->with('error', 'Failed to retrieve torrent.');
    }

    $basePath = rtrim($torrentData['basePath'], '/');

   // dd($basePath);
//     dd(
//     $service->executeRemoteCommand(
//         $service->getRpcClient(),
//         'ls -R "' . str_replace('"', '\\"', $basePath) . '"'
//     )
// );

    $decoded = \App\Helpers\Bencode::bdecode($torrentContent);
    $torrentName = $decoded['info']['name'] ?? strtoupper($hash);

    /* =========================================================
       2️⃣ METADATA SERVICE (CLEAN NAME + TYPE + TMDB)
    ========================================================= */

    $metaService = app(TorrentMetadataService::class);

    $type = $metaService->detectTypeFromName($torrentName);

    $parsed = $metaService->cleanNameAndExtractData($torrentName);

    $category_id = $metaService->detectCategory($torrentName, $type);

    [$imdbId, $imdbLink, $imdbDescription] =
        $metaService->fetchTmdb(
            $parsed['clean'],
            $parsed['year'],
            $type
        );

       // dd($parsed, $imdbId, $imdbLink);

    /* =========================================================
       3️⃣ FIND REAL FILE + MEDIAINFO
    ========================================================= */

   if ($type === 'tv') {
    $tvData = $service->findTvEpisodeFile($basePath);
    $filePath = $tvData['file'] ?? null;
} else {
    $filePath = $service->findPrimaryVideoFile($basePath)
        ?: $service->guessPrimaryVideoFile($basePath);
}

    $mediainfo = $filePath
        ? $service->getMediaInfo($filePath)
        : null;

    /* =========================================================
       4️⃣ REBUILD TORRENT WITH USER PASSKEY
    ========================================================= */

     //$announceUrl = config('app.seedbox_url') . "/announce/{$user->passkey}";
     $announceUrl = 'https://tracker.fileiplay.org/announce/' . $user->passkey;
    //$announceUrl = env('APP_URL') . "/announce/{$user->passkey}";

    $rebuiltTorrent = (new TorrentRebuildService($announceUrl))
        ->rebuildTorrent($torrentContent);

       // dd($rebuiltTorrent);

    /* =========================================================
       5️⃣ BUILD DESCRIPTION
    ========================================================= */

    $description = "[Upload From Seedbox]!!!";

if ($type === 'tv' && isset($tvData)) {

    if ($tvData['is_pack']) {

        $description .= "\n\nSeason {$tvData['season']} Pack";
        $description .= "\nEpisodes: {$tvData['episode_range']} ({$tvData['episode_count']} total)";

        if ($tvData['is_complete']) {
            $description .= "\nStatus: COMPLETE Season";
        } else {
            $missing = implode(', ', $tvData['missing_episodes']);
            $description .= "\nStatus: INCOMPLETE (Missing: E{$missing})";
        }

    } else {
        $description .= "\n\nSeason {$tvData['season']} - Single Episode";
    }
}

    if ($imdbDescription) {
        $description .= "\n\n{$imdbDescription}";
    }

    if ($mediainfo) {
        $parsedMedia = MediaInfoParser::parse($mediainfo);
        $description .= "\n\n" .
            MediaInfoParser::toDescription($parsedMedia);
    }

    /* =========================================================
       6️⃣ UPLOAD TO TRACKER
    ========================================================= */

    $uploadData = [
        'name'        => $torrentName,
        'category_id' => $category_id,
        'description' => $description,
        'mediainfo'   => $mediainfo,
        'imdb_url'    => $imdbLink,
        'imdbid'      => $imdbId,
    ];

    //dd($uploadData);
  
    $auto = new \App\Services\AutoUploadService();
    $uploadResult = $auto->upload(
        $uploadData,
        $rebuiltTorrent,
        $torrentName
    );

    if (!$uploadResult || !isset($uploadResult['torrent'])) {
        return back()->with('error', 'Failed to upload torrent.');
    }

    if (!$uploadResult['created']) {
        return redirect()
            ->route('seedboxes.torrents', $seedbox)
            ->with('error', 'This torrent already exists on the tracker.');
    }

    $torrentModel = $uploadResult['torrent'];

    /* =========================================================
       7️⃣ ADD BACK TO SEEDBOX
    ========================================================= */

    $this->addTorrentToSeedbox($seedbox, $rebuiltTorrent);

/* =========================================================
   8️⃣ GENERATE SCREENSHOTS (MOVIES + TV)
========================================================= */

if ($filePath && in_array($type, ['movie', 'tv'])) {

    foreach ($service->generateScreenshots($filePath) as $i => $base64) {

        $binary = base64_decode(trim($base64), true);
        if (!$binary) continue;

        $path = 'torrent_images/' . md5($filePath . $i) . '.jpg';

        Storage::disk('public')->put($path, $binary);

        \App\Models\TorrentImage::create([
            'torrent_id' => $torrentModel->id,
            'path'       => $path,
        ]);
    }

}

    /* =========================================================
       9️⃣ REDIRECT
    ========================================================= */

    return redirect()
        ->route('torrents.show', $torrentModel->id)
        ->with(
            'success',
            "Torrent '{$torrentName}' uploaded successfully!"
        );
}

    protected function screenshotsToUploadedFiles(array $paths): array
{
    $files = [];

    foreach ($paths as $path) {
        if (!File::exists($path)) continue;

        $files[] = new UploadedFile(
            $path,
            basename($path),
            mime_content_type($path),
            null,
            true
        );
    }

    return $files;
}



}
