<?php

namespace App\Http\Controllers;

use App\Models\Category; // Assuming you have a Category model
use App\Models\Torrent;
use App\Models\User;
use App\Helpers\Bencode;
use Illuminate\Http\Request;

class RssFeedController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all(); // Retrieve all categories
        $user = $request->user(); // Get the authenticated user from the request
        $passkey = $user ? $user->passkey : ''; // Get the user's passkey, or use an empty string if not authenticated

        return view('rss.index', compact('categories', 'passkey'));
    }

    public function generateFeed(Request $request)
{
    // Retrieve the passkey from the request
    $passkey = $request->input('passkey');

    // Retrieve selected categories and ensure it's an array
    $selectedCategories = $request->input('cats', []); // Default to empty array

    // Redirect to 404 if 'cats' is empty or 'passkey' is missing
    if (empty($selectedCategories) || !$passkey) {
        abort(404);
    }

    // If categories are passed as a string (e.g., "1,2,3"), convert to an array
    if (!is_array($selectedCategories)) {
        $selectedCategories = explode(',', $selectedCategories);
    }

    // Fetch torrents based on the selected categories
    $query = Torrent::query();

    // Apply filtering only if categories are selected
    if (!empty($selectedCategories)) {
        $query->whereIn('category_id', $selectedCategories);
    }

    // Filter torrents where seeders > 0
    $query->where('seeders', '>', 0);

    // Get the most recent 20 torrents
    // $torrents = $query->orderBy('created_at', 'desc')->limit(20)->get();
    $torrents = $query->with('category')->orderBy('created_at', 'desc')->limit(15)->get();

    // Debugging: Uncomment to check the selected categories and torrents fetched
    // dd([
    //     'selected_categories' => $selectedCategories,
    //     'torrents_fetched' => $torrents->pluck('category_id')->toArray(),
    // ]);

    // Generate RSS feed content
    $content = view('rss.feed', compact('torrents', 'passkey'))->render();

    // Return RSS feed with appropriate headers
    return response($content, 200)
    ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
}



public function downloadrss(Request $request, $fileName, $passkey)
{
    // Find the user by passkey
    $user = User::where('passkey', $passkey)->first();

    if (!$user) {
        return response('Invalid passkey', 403);
    }

    // Find the torrent by file_name
    $torrent = Torrent::where('file_name', $fileName)->firstOrFail();

    $path = public_path('files/torrents/' . $torrent->file_name);

    if (!file_exists($path)) {
        return response('Torrent file not found', 404)
            ->header('Content-Type', 'text/plain');
    }

    // Decode the torrent file
    $dict = Bencode::bdecode(file_get_contents($path));

    // Update the announce URL with the user's passkey
    $dict['announce'] = route('announce', ['passkey' => $user->passkey]);
    $dict['comment'] = 'Using this torrent binds you to MyTorrents Confidentiality Agreement';

    // Remove additional announce lists
    unset($dict['announce-list']);

    // Re-encode the torrent file
    $fileToDownload = Bencode::bencode($dict);

    // Generate the download filename
    $downloadFileName = 'Last-Torrents_' . $torrent->name . '.torrent';

    return response($fileToDownload)
        ->header('Content-Type', 'application/x-bittorrent')
        ->header('Content-Disposition', 'attachment; filename="' . $downloadFileName . '"')
        ->header('Content-Length', strlen($fileToDownload));
}

}
