<?php

namespace App\Http\Controllers\Admin;

use App\Models\Peer;
use App\Models\History;
use App\Models\Torrent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TorrentsController extends Controller
{
    public function index()
{
    $torrents = Torrent::orderBy('id', 'desc')->paginate(50);
    return view('admin.torrents.index', compact('torrents'));
}

    public function show($id)
    {
        // Retrieve the torrent and related data
       // Retrieve the selected torrent and its associated history
    $torrent = Torrent::with('peers')->findOrFail($id);

    // Get the history associated with the selected torrent
    $paginatedHistory = History::with('user', 'peer')
        ->where('info_hash', $torrent->info_hash)  // Filter by the torrent's info_hash
        ->paginate(10);  // Pagination for download history



        // Count seeders and leechers
        $seeders = $torrent->peers->where('seeder', true)->count();
        $leechers = $torrent->peers->where('seeder', false)->count();
        // Assuming you're fetching peers related to the specific torrent
       $peers = Peer::where('torrent_id', $torrent->id)->get();


        return view('admin.torrents.show', [
            'torrent' => $torrent,
            'seeders' => $seeders,
            'leechers' => $leechers,
            'times_completed' => $torrent->times_completed,
            'history' => $paginatedHistory, // Pass paginated history
            'peers' => $peers,
        ]);
    }





    public function edit($id)
    {
        $torrent = Torrent::findOrFail($id);
        return view('admin.torrents.edit', compact('torrent'));
    }

    public function update(Request $request, $id)
{
    $torrent = Torrent::findOrFail($id);

    // Validate the incoming request data
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',

        // Add other fields for validation as needed
    ]);

    // Update movie details
    $torrent->name = $request->name;
    // Set other movie properties here
    $torrent->description = $request->description;


    $torrent->save();

    return redirect()->route('admin.torrents.index')->with('status', 'Movie updated successfully!');
}

public function destroy($id)
{
    // Find the torrent
    $torrent = Torrent::findOrFail($id);

    // Delete the associated records from the peers table
    Peer::where('torrent_id', $torrent->id)->delete();

    // Delete the associated records from the history table
    History::where('torrent_id', $torrent->id)->delete();

    // Finally, delete the torrent itself
    $torrent->delete();

    // Redirect with success message
    return redirect()->route('admin.torrents.index')->with('success', 'Movie deleted successfully, including associated peers and history.');
}

}
