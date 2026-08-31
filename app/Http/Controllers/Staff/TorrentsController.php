<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Torrent;

class TorrentsController extends Controller
{

    public function index()
    {
        $torrents = Torrent::latest()->paginate(50);

        return view('staff.torrents.index', compact('torrents'));
    }

public function show(Torrent $torrent)
{
    $torrent->load([
        'uploader',
        'category',
        'files',
        'peers'
    ]);

    return view('staff.torrents.show', compact('torrent'));
}

public function destroy(Torrent $torrent)
{
    $torrent->delete();

    return redirect()
        ->route('staff.torrents')
        ->with('success', 'Torrent deleted.');
}

}