<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Torrent\ExternalTorrentUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalUploadController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $categories = Category::all();
        return view('torrents.external_upload', compact('user', 'categories'));
    }

    public function store(Request $request, ExternalTorrentUploadService $service)
    {
        $result = $service->handle($request, $request->user());

        $torrent = $result['torrent'];

        if (!$result['created']) {
            return redirect()
                ->route('torrents.show', $torrent->id)
                ->with('info', 'This torrent already exists on the tracker.');
        }

        return redirect()
            ->route('torrents.show', $torrent->id)
            ->with('success', 'Your external torrent has been uploaded successfully.');
    }
}
