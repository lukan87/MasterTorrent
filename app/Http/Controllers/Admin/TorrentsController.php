<?php

namespace App\Http\Controllers\Admin;

use App\Models\Peer;
use App\Models\History;
use App\Models\Torrent;
use App\Models\Warning;
use App\Models\Comment;
use App\Models\TorrentImage;
use App\Models\TorrentThank;
use App\Models\UserSlot;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;



class TorrentsController extends Controller
{
public function index(Request $request)
{
    $query = Torrent::query();

    // Search by name
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $query->where('name', 'like', "%{$searchTerm}%");
    }

    // Filter by Active/Dead
    $status = $request->input('status', 'active'); // Default: active

    $query->withCount(['peers as seeders_count' => function ($q) {
        $q->where('seeder', true);
    }]);

    if ($status === 'active') {
        $query->having('seeders_count', '>', 0);
    } elseif ($status === 'dead') {
        $query->having('seeders_count', '=', 0);
    }

    $torrents = $query->orderBy('id', 'desc')->paginate(50);
    $torrents->appends($request->all());

    return view('admin.torrents.index', compact('torrents', 'status'));
}



public function show($id)
{
    $torrent = Torrent::findOrFail($id);

    // Fetch paginated history
    $history = History::with(['user', 'peer'])
        ->where('info_hash', $torrent->info_hash)
        ->latest()
        ->paginate(10);

    // Seeders (from history)
    $seeders = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->where('seeder', true)
        ->latest()
        ->get();

    // Leechers (optional)
    $leechers = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->where('seeder', false)
        ->where('active', true)
        ->latest()
        ->get();

    // Completed users
    $completedUsers = History::with('user')
        ->where('info_hash', $torrent->info_hash)
        ->whereNotNull('completed_at')
        ->select('user_id', DB::raw('MAX(completed_at) as last_completed'))
        ->groupBy('user_id')
        ->get();

    return view('admin.torrents.show', [
        'torrent'         => $torrent,
        'history'         => $history,
        'seeders'         => $seeders,
        'leechers'        => $leechers,
        'completedUsers'  => $completedUsers,
        'seedersCount'    => $seeders->count(),
        'leechersCount'   => $leechers->count(),
        'timesCompleted'  => $torrent->times_completed,
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



    public function destroy(Request $request, $id)
    {
        
        // Find the torrent
    $torrent = Torrent::findOrFail($id);

     
 $owner = $torrent->owner; 



      
$deletionReason = $request->input('deletion_reason');


if (empty($deletionReason)) {
    $deletionReason = '0 seeders and 0 leechers';
} elseif ($deletionReason === 'custom') {
    $deletionReason = $request->input('custom_reason');
}

      
if ($owner && User::where('id', $owner)->exists()) {
  
    Message::create([
        'receiver_id' => $owner,  
        'subject' => 'Torrent Deletion',
        'sender_id' => 2,  
        'body' => 'Your torrent "' . $torrent->name . '" has been deleted. Reason: ' . $deletionReason,
        'is_read' => false,  
    ]);
}


      
        Peer::where('torrent_id', $torrent->id)->delete();
        TorrentThank::where('torrent_id', $torrent->id)->delete();
      
        History::where('torrent_id', $torrent->id)->delete();

        
        Comment::where('torrent_id', $torrent->id)->delete();

        
        if (Warning::where('torrent', $torrent->id)->exists()) {
            Warning::where('torrent', $torrent->id)->delete();
        }

        
        if (UserSlot::where('torrent_id', $torrent->id)->exists()) {
            UserSlot::where('torrent_id', $torrent->id)->delete();
        }

    
        $torrent->genres()->detach();

        
    $images = TorrentImage::where('torrent_id', $torrent->id)->get();

    foreach ($images as $image) {
    
        $filePath = storage_path('app/public/' . $image->path); // Full file path

        
        if (file_exists($filePath)) {
            unlink($filePath); 
        }

    
        $image->delete();
    }

      
        $filePath = public_path('files/torrents/' . $torrent->file_name);

       
        if (file_exists($filePath)) {
            unlink($filePath); 
        }

       
        $torrent->delete();

        
        // Redirect with success message
    return redirect()->route('admin.torrents.index')->with('success', 'Torrent and associated records deleted successfully.');
    }

}
