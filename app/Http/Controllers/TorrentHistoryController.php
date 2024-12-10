<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Torrent;

class TorrentHistoryController extends Controller
{
    /**
     * Display History of a Torrent.
     */
    public function index(int $id, string $slug)
    {
        // Find the torrent by ID
        $torrent = Torrent::findOrFail($id);

        // Validate the slug
        if ($torrent->slug !== $slug) {
            abort(404, 'Torrent not found');
        }

        // Fetch history data
        return view('torrents.history', [
            'torrent' => $torrent,
            'histories' => History::with('user')
                ->where('torrent_id', $id)
                ->whereNotNull('completed_at') // Exclude histories with NULL completed_at
                ->latest()
                ->paginate(30),
        ]);
    }
}
