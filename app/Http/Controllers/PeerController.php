<?php

namespace App\Http\Controllers;

use App\Models\Peer;
use Illuminate\Http\Request;

class PeerController extends Controller
{
    // Store a new peer
    public function store(Request $request)
    {
        $request->validate([
            'torrent' => 'required|integer',
            'peer_id' => 'required|string|max:20',
            'ip' => 'required|ip',
            'port' => 'required|integer',
            'userid' => 'required|integer',
            // Validate other fields as necessary
        ]);

        // Create a new peer
        $peer = Peer::create([
            'torrent' => $request->torrent,
            'peer_id' => $request->peer_id,
            'ip' => $request->ip,
            'port' => $request->port,
            'userid' => $request->userid,
            'started' => now(),
            'last_action' => now(),
            // Set other fields as necessary
        ]);

        return response()->json($peer, 201);
    }

    // Other methods can be added as needed
}
