<?php

namespace App\Http\Controllers;

use App\Models\Seedbox;
use App\Services\SeedboxService;
use App\Services\TorrentRebuildService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;


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

    // Show the form to create a new seedbox
public function create()
{
    return view('seedboxes.create');
}

// Store the new seedbox in the database
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


    // Show the edit form for a seedbox
public function edit(Seedbox $seedbox)
{
    return view('seedboxes.edit', compact('seedbox'));
}

// Handle the update request
public function update(Request $request, Seedbox $seedbox)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|url',
        'username' => 'required|string|max:255',
        'password' => 'required|string|max:255',
        'auth_type' => 'required|in:basic,digest',
    ]);

    $seedbox->update([
        'name' => $request->name,
        'address' => $request->address,
        'username' => $request->username,
        'password' => $request->password,
        'auth_type' => $request->auth_type,
    ]);

    return redirect()->route('seedboxes.index')
                     ->with('success', 'Seedbox updated successfully!');
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

        if (isset($torrents['error'])) {
            return redirect()->back()->with('error', 'Connection failed: ' . $torrents['error']);
        }

        return redirect()->back()->with('success', 'Connection successful');
    }
public function testRpc(Seedbox $seedbox, string $hash)
{
    $service = $this->seedboxService($seedbox);
    $result = $service->testRpcSessionPath($hash);

    dd($result); // dump result and die
}


  public function showTorrents(Seedbox $seedbox, Request $request)
{
    $service = $this->seedboxService($seedbox);

    $allTorrents = $service->getTorrents()['t'] ?? [];

    
    uasort($allTorrents, function ($a, $b) {
        $aDate = $a[21] ?? 0;
        $bDate = $b[21] ?? 0;
        return $bDate <=> $aDate;
    });

  
    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $allTorrents = array_filter($allTorrents, function ($torrent) use ($search) {
            $name = strtolower($torrent[4] ?? '');
            return str_contains($name, $search);
        });
    }

  
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
    

    return view('seedboxes.torrents', compact('seedbox', 'torrents', 'service'));
}


public function importTorrent(Seedbox $seedbox, string $hash)
{
    try {
       $service = $this->seedboxService($seedbox);


        // Download the torrent file from the seedbox
        $torrentContent = $service->downloadTorrentFile($hash);

        // Save temporarily
        $tmpFile = tempnam(sys_get_temp_dir(), 'seedbox_') . '.torrent';
        file_put_contents($tmpFile, $torrentContent);

        // Create a Torrent model entry if necessary
        // Or directly call TorrentDownloadService to process the file
        $torrentDownloadService = new \App\Services\TorrentDownloadService();
        $torrentDownloadService->handleUploadFromSeedbox($tmpFile, auth()->user());

        // Clean up temp file
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
        $success = !isset($result['error']);

        return redirect()->back()->with($success ? 'success' : 'error', $success ? 'Torrent started' : 'Failed to start torrent');
    }

    public function pause(Seedbox $seedbox, string $hash)
    {
       $service = $this->seedboxService($seedbox);


        $result = $service->pauseTorrent($hash);
        $success = !isset($result['error']);

        return redirect()->back()->with($success ? 'success' : 'error', $success ? 'Torrent paused' : 'Failed to pause torrent');
    }

   public function delete(Seedbox $seedbox, string $hash, Request $request)
{
    $service = $this->seedboxService($seedbox);


    $deleteData = $request->input('delete_data', false); // default false
    $result = $service->deleteTorrent($hash, $deleteData);

    $success = !isset($result['error']);
    $message = $success 
        ? ($deleteData ? 'Torrent and files deleted' : 'Torrent deleted') 
        : 'Failed to delete torrent';

    return redirect()->back()->with($success ? 'success' : 'error', $message);
}



public function addTorrent(Request $request, Seedbox $seedbox)
{
    $request->validate([
        'torrent_file' => 'required|file|mimes:torrent|max:10240',
    ]);

    $file = $request->file('torrent_file');

    $service = $this->seedboxService($seedbox);


    $result = $service->addTorrentFile($file->getRealPath());

    if (isset($result['error'])) {
        return back()->with('error', $result['error']);
    }

    return back()->with('success', 'Torrent added successfully!');
}


public function getTorrentTrackers(Seedbox $seedbox, string $hash)
{
    $service = $this->seedboxService($seedbox);

    $trackers = $service->torrentTrackers($hash); // array from API

    // Flatten to simple URLs if necessary
    $trackerUrls = [];
    foreach ($trackers as $t) {
        if (is_array($t) && isset($t[0][0])) {
            $trackerUrls[] = $t[0][0];
        }
    }

    return response()->json(['trackers' => $trackerUrls]);
}

// app/Http/Controllers/SeedboxController.php
public function downloadTorrentFile($seedboxId, $hash)
{
    $seedbox = Seedbox::findOrFail($seedboxId);
    
    $service = new SeedboxService(
        $seedbox->url,      // Make sure this is a valid string
        $seedbox->username,
        $seedbox->password,
        'basic',
        true                // using RPC
    );

    $result = $service->testRpcSessionPath($hash);

    if (isset($result['error']) || empty($result['torrentContent'])) {
        return redirect()->back()->with('error', 'Could not retrieve the torrent file.');
    }

    $torrentContent = $result['torrentContent'];

    $filename = $hash . '.torrent';

    return response($torrentContent)
        ->header('Content-Type', 'application/x-bittorrent')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
}


public function downloadTorrent($seedboxId, $hash)
{
    $seedbox = Seedbox::findOrFail($seedboxId);

    // dd($seedbox->address, $seedbox->username, $seedbox->password);

    $service = new SeedboxService(
        $seedbox->address,
        $seedbox->username,
        $seedbox->password,
        'basic', // or digest if needed
        true     // use RPC
    );

    $result = $service->testRpcSessionPath($hash);

    if (empty($result['torrentContent'])) {
        return redirect()->back()->with('error', 'Failed to retrieve torrent file.');
    }

    $filename = strtoupper($hash) . '.torrent';

    return response($result['torrentContent'])
        ->header('Content-Type', 'application/x-bittorrent')
        ->header('Content-Disposition', "attachment; filename=\"$filename\"");
}

public function downloadRebuiltTorrent($seedboxId, $hash)
{
    $seedbox = Seedbox::findOrFail($seedboxId);

    // Get logged-in user and passkey
    $user = Auth::user();
    if (!$user || !$user->passkey) {
        return back()->with('error', 'You do not have a passkey.');
    }
    $passkey = $user->passkey;

    // Get raw torrent from seedbox
    $service = new SeedboxService($seedbox->address, $seedbox->username, $seedbox->password, 'basic', true);
    $torrentContent = $service->testRpcSessionPath($hash)['torrentContent'] ?? null;

    if (!$torrentContent) {
        return back()->with('error', 'Failed to retrieve torrent');
    }

    // Decode torrent to extract name
    $decoded = \App\Helpers\Bencode::bdecode($torrentContent);
    $torrentName = $decoded['info']['name'] ?? strtoupper($hash);

    // Clean torrent name for metadata/search
    $cleanTitle = preg_replace([
        '/\bS\d+E\d+\b/i',
        '/\b\d{3,4}p\b/i',
        '/\b(BluRay|WEB-DL|WEBRip|HDRip|DVDRip|XviD|x264|x265|FiLELiST|YIFY|RARBG|Ganool|ETRG|REPACK|LIMITED)\b/i',
        '/[\[\]\(\)\-]/',
        '/\./'
    ], ' ', $torrentName);
    $cleanTitle = preg_replace('/\s+/', ' ', $cleanTitle);
    $cleanTitle = trim($cleanTitle);

    // Detect type: TV or Movie
    $type = preg_match('/S\d+E\d+/i', $torrentName) ? 'tv' : 'movie';

    // Rebuild torrent with your tracker
    $announceUrl = "http://last-torrents.org/announce/{$passkey}";
    $rebuildService = new TorrentRebuildService($announceUrl);
    $rebuiltTorrent = $rebuildService->rebuildTorrent($torrentContent);

    // Auto-generate description
    $description = "[Auto Upload From Seedbox]\n\nGenerated automatically.";

    // Auto-detect category
    $category_id = 49; // default
    if ($type === 'movie') {
        $tnLower = strtolower($torrentName);
        if (str_contains($tnLower, '4k')) $category_id = 31;
        elseif (str_contains($tnLower, 'x265')) $category_id = 82;
        elseif (str_contains($tnLower, 'bluray')) $category_id = 5;
        elseif (str_contains($tnLower, 'dvd')) $category_id = 9;
        elseif (str_contains($tnLower, 'xvid')) $category_id = 24;
        elseif (str_contains($tnLower, 'web-dl')) $category_id = 54;
    } elseif ($type === 'tv') {
        $category_id = 20;
    }

    // Prepare data for auto-upload
    $uploadData = [
        'name' => $torrentName,
        'genre' => null,
        'steamid' => null,
        'category_id' => $category_id,
        'description' => $description,
    ];

    // Auto-upload torrent to your site
    $auto = new \App\Services\AutoUploadService();
    $uploadedTorrent = $auto->upload($uploadData, $rebuiltTorrent, $torrentName);

    // Redirect to torrent list page
    return redirect()->to('https://last-torrents.org/torrents?keyword=&genre=&torrent_status=dead')
        ->with('success', "Torrent '$torrentName' uploaded successfully! You can now send it to seedbox.");
}












}
