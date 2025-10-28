<?php

namespace App\Http\Controllers;

use App\Models\Seedbox;
use App\Services\SeedboxService;
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



}
