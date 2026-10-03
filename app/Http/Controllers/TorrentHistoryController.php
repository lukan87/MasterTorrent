<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Torrent;
use Illuminate\Http\Request;

class TorrentHistoryController extends Controller
{
    public function snatched(Request $request, int $id)
    {
        abort_unless($request->user() && $request->user()->user_class >= \App\Models\UserClass::MODERATOR, 403);

        $completedOnly = $request->routeIs('torrents.completed');
        $torrent = Torrent::withTrashed()->findOrFail($id);
        $histories = History::with('user:id,name')
            ->where('torrent_id', $torrent->id)
            ->when($completedOnly, fn ($query) => $query->whereNotNull('completed_at'))
            ->orderByDesc($completedOnly ? 'completed_at' : 'created_at')
            ->orderByDesc('id')
            ->paginate(30);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('torrents.partials.snatched-table', compact('torrent', 'histories', 'completedOnly'))->render(),
            ]);
        }

        return view('torrents.snatched', compact('torrent', 'histories', 'completedOnly'));
    }

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

