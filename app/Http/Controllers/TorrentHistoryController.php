<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Torrent;

class TorrentHistoryController extends Controller
{
    /**
     * Display History of a Torrent.
     *
     * @param int $id
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
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
        $histories = History::with('user')
            ->where('torrent_id', $id)
            ->where(function ($query) {
                $query->whereNotNull('completed_at') // Include completed torrents
                      ->orWhere('seeder', true); // Or users who are actively seeding
            })
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->paginate(30);

        return view('torrents.history', [
            'torrent' => $torrent,
            'histories' => $histories,
        ]);
    }
}

