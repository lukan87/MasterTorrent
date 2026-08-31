<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Torrent;
use Illuminate\Http\Request;

class TorrentHistoryController extends Controller
{
    /**
     * Display History of a Torrent.
     *
     * @param int $id
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index(int $id, string $slug, Request $request)
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
            ->orderByRaw('user_id = ? DESC', [$request->user()->id])
            ->paginate(50);

        return view('torrents.history', [
            'torrent' => $torrent,
            'histories' => $histories,
        ]);
    }
}

