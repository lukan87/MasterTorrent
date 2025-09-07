<?php

namespace App\Http\Controllers;

use App\Models\Peer;
use App\Models\User;
use App\Models\Genre;
use App\Models\History;
use App\Models\Torrent;
use App\Models\Category;
use App\Models\Comment;
use App\Helpers\MediaInfo;
use App\Models\Warning;
use Illuminate\Support\Str;
use App\Models\TorrentFiles;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Helpers\Bencode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TorrentTools;
use App\Services\TMDBService;
use App\Models\TorrentThank;
use App\Models\TorrentImage;
use App\Models\UserSlot;
use Carbon\Carbon;
use App\Helpers\TorrentHelper;



class TorrentController extends Controller
{

    // TMDB API key
    private $apiKey;

    public function __construct()
    {
       
        $this->apiKey = env('TMDB_API_KEY');
    }

   
    

    public function index(Request $request)
    {
        // Cache categories and genres forever (they don’t change often)
        $categories = Cache::rememberForever('categories', fn() => Category::all());
        $allGenres = Cache::rememberForever('genres', fn() => Genre::all());
    
        $sortColumn = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'desc');
        $currentPage = $request->get('page', 1);
    
        // Generate a cache key using filters and current page
        $cacheKey = "torrents_page_{$currentPage}_sort_{$sortColumn}_dir_{$sortDirection}_" . 
                    "keyword_{$request->get('keyword', 'null')}_" .
                    "categories_" . implode('_', (array)$request->categories) . "_" .
                    "genre_{$request->get('genre', 'null')}_torrent_status_{$request->get('torrent_status', 'null')}";
    
        // Check if we can use cache (only when no filters are set)
        $useCache = !$request->filled(['keyword', 'categories', 'genre', 'torrent_status', 'sort', 'direction']);
        
        if ($useCache) {
            // Use cached data if available
            $torrents = Cache::remember($cacheKey, 60, function () use ($request, $sortColumn, $sortDirection) {
                return TorrentHelper::buildTorrentQuery($request, $sortColumn, $sortDirection);
            });
        } else {
            // If cache cannot be used, run the query directly
            $torrents = TorrentHelper::buildTorrentQuery($request, $sortColumn, $sortDirection);
        }
    
        // Check if torrents were found (for debugging purposes)
        if ($torrents->isEmpty()) {
          
        }
    
        // Mark new torrents for the user
        $user = $request->user();
       $newTorrents = $user
    ? $torrents->filter(fn($torrent) => $torrent->created_at->gt($user->last_browse))
    : collect();

        
if ($user) {
    $user->last_browse = now(); // stays UTC
    $user->save();
}

    $movieOfTheDay = Cache::remember('movie_of_the_day', now()->endOfDay(), function () {
    $categoryIds = [11,12,24,25,31,32,54,55,81,82];
    $twoDaysAgo = now()->subDays(2);
    $oneWeekAgo = now()->subWeek();

    // Try to get top 5 torrents from the last 2 days
    $topTorrents = Torrent::whereIn('category_id', $categoryIds)
                          ->where('created_at', '>=', $twoDaysAgo)
                          ->orderByDesc('seeders')
                          ->orderByDesc('times_completed')
                          ->take(5)
                          ->get();

    // If none, fallback to last week's uploads
    if ($topTorrents->isEmpty()) {
        $topTorrents = Torrent::whereIn('category_id', $categoryIds)
                              ->where('created_at', '>=', $oneWeekAgo)
                              ->orderByDesc('seeders')
                              ->orderByDesc('times_completed')
                              ->take(5)
                              ->get();
    }

    // Pick one randomly from available torrents
    return $topTorrents->isNotEmpty() ? $topTorrents->random() : null;
});


    
        return view('torrents.index', compact('torrents', 'categories', 'allGenres', 'sortColumn', 'sortDirection', 'newTorrents','movieOfTheDay',));
    }
    
    
    

    public function adult(Request $request)
{
    // Cache categories and genres
    $categories = Cache::remember('categories', now()->addMinutes(30), fn() => Category::all());
    $allGenres = Cache::remember('allGenres', now()->addMinutes(30), fn() => Genre::all());

    // Default sort column and direction
    $sortColumn = $request->get('sort', 'name'); // Default to 'name' column
    $sortDirection = $request->get('direction', 'desc'); // Default to 'desc'

    // Current page for pagination
    $currentPage = $request->get('page', 1); // Default to page 1

    // Generate a unique cache key for the current page and filters
    $cacheKey = "cached_adult_torrents_page_{$currentPage}_sort_{$sortColumn}_dir_{$sortDirection}_" .
    "keyword_" . ($request->get('keyword', '') ?: 'null') . "_" .
    "category_" . ($request->get('category', '') ?: 'null') . "_" .
    "genre_" . ($request->get('genre', '') ?: 'null') . "_" .
    "torrent_status_" . ($request->get('torrent_status', '') ?: 'null') . "_" .
    "sticky_" . ($request->get('sticky', '0') ?: 'null') . "_" .
    "free_" . ($request->get('free', '0') ?: 'null') . "_" .
    "double_" . ($request->get('double', '0') ?: 'null') . "_" .
    "recommended_" . ($request->get('recommended', '0') ?: 'null');

    // Fetch torrents from the cache
    $adult = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $sortColumn, $sortDirection) {
        return TorrentHelper::buildAdultTorrentQuery($request, $sortColumn, $sortDirection);
    });
    

    // Mark new torrents for the user
    $user = $request->user();
    $newTorrents = $user
        ? $adult->filter(fn($adult) => $adult->created_at > $user->last_browsex)
        : collect();

    if ($user) {
        $user->last_browsex = now();
        $user->save(); 
    }

    return view('torrents.adult', compact('adult', 'categories', 'allGenres', 'sortColumn', 'sortDirection', 'newTorrents'));
}

    
    public function create()
    {
        $user = Auth::user();
        $categories = Category::all(); 
        if (session('success')) {
            return view('torrents.upload', compact('user', 'categories'));
        }
        return view('torrents.upload', compact('user', 'categories')); // Pass categories to view
    }

    public function store(Request $request, TMDBService $tmdbService)
    {
        $user = $request->user();
        $request->validate([
            'torrent' => 'required|file|mimes:torrent',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mediainfo' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'poster' => 'nullable|url',
            'background' => 'nullable|url',
            'trailer' => 'nullable|url',
            'genre' => 'nullable|string', 
            'images.*' => 'nullable|image',
        ]);

        // Store the torrent file in a directory within public/files/torrents
        $directory = public_path('files/torrents');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the uploaded torrent file
        $fileName = uniqid('', true) . '.torrent';
        $torrentContent = file_get_contents($request->file('torrent')->path());

        // Decode the torrent content
        $torrentData = Bencode::bdecode($torrentContent);
        // Check if the torrent is external
if ($request->has('external') && $request->external == 1) {
    $torrentData['info']['private'] = 0; // Allow DHT for external torrents
} else {
    $torrentData['info']['private'] = 1; // Private mode for internal torrents
}
        $infoHash = Bencode::get_infohash($torrentData);
         // Check if a torrent with the same infohash already exists
        if (Torrent::where('info_hash', $infoHash)->exists()) {
            return back()->with('error', 'A torrent with the same infohash exists on the site!');
        }

        $meta = Bencode::get_meta($torrentData);

        $announce = $torrentData['announce'];
        $name = str_replace('.', '-', $request->name); 
        $slug = Str::slug($name, '-'); 

       
        $originalSlug = $slug;
        $count = 1;
        while (Torrent::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

       
        $tmdbData = null;
        $tmdbId = null;
        $tmdbtype = null;
        $imdbId = null;
        $imdbUrl = $request->input('imdb_url');

        if ($imdbUrl && preg_match('/^https?:\/\/(?:www\.)?imdb\.com\/title\/(tt\d{7,8})/', $imdbUrl, $matches)) {
            $imdbId = $matches[1];
            $tmdbDetails = $tmdbService->getTMDBIdAndTypeByIMDbId($imdbId);

            if ($tmdbDetails) {
                $tmdbId = $tmdbDetails['tmdb_id'];
                $tmdbtype = $tmdbDetails['type'];
                $tmdbData = $tmdbService->fetchTMDBData($tmdbId, $tmdbtype);
            }
        }


        
    $steamData = null;
    if ($request->steamid) {
        $steamResponse = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $request->steamid,
            'lang' => 'en'
        ]);

        $steamData = $steamResponse->json();

        // Check if the Steam data is valid
        if (isset($steamData[$request->steamid]['success']) && $steamData[$request->steamid]['success']) {
            $gameDetails = $steamData[$request->steamid]['data'];

            // Extract necessary game details from the response
            $name = $gameDetails['name'] ?? $name; // Use the Steam game name if available
            $sdescription = $gameDetails['short_description'] ?? $request->description; // Use the Steam description if available
            $poster = $gameDetails['header_image'] ?? $request->poster; // Use the Steam poster if available
            $background = $gameDetails['background'] ?? $request->background; // Use Steam background if available
        }
    }

        // Use $tmdbData to retrieve and set poster, background, and genres if it's a movie or TV
        if ($tmdbData) {
            $posterBaseUrl = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2';
            $backgroundBaseUrl = 'https://image.tmdb.org/t/p/original';
            $poster = $tmdbData['poster_path'] ? $posterBaseUrl . $tmdbData['poster_path'] : null;
            $background = $tmdbData['backdrop_path'] ? $backgroundBaseUrl . $tmdbData['backdrop_path'] : null;

            // Set genre information for movies/TV shows
            $genres = $tmdbData['genres'] ?? [];
            $genreIds = [];
            foreach ($genres as $genre) {
                $genreRecord = Genre::firstOrCreate(['name' => $genre['name']]);
                $genreIds[] = $genreRecord->id;
            }
        } else {
            // If it's not a movie or TV show, use the manually entered genres
            $poster = $request->poster;
            $background = null;

            // Handle manually entered genres
            $manualGenres = $request->input('genre', ''); // Get manually entered genres as a string
            if (!empty($manualGenres)) {
                // Split the string by both ',' and '/'
                $manualGenresArray = preg_split('/[\/,]/', $manualGenres); // Split by either ',' or '/'

                // Loop through each genre, clean it, create if necessary, and collect the genre IDs
                foreach ($manualGenresArray as $genreName) {
                    $genreName = trim($genreName); // Clean up any extra spaces
                    if (!empty($genreName)) {
                        // Save the genre to the database or retrieve it if it already exists
                        $genreRecord = Genre::firstOrCreate(['name' => $genreName]);
                        $genreIds[] = $genreRecord->id;
                    }
                }
            }

        }
        
         
        // Save the torrent file to the server
        file_put_contents(public_path('files/torrents') . '/' . $fileName, Bencode::bencode($torrentData));

       
$cleanName = str_replace(['{', '}'], '.', $request->name);
$cleanName = preg_replace('/[^A-Za-z0-9\.\-\s]/', '.', $cleanName);
$cleanName = preg_replace('/[\.]{2,}/', '.', $cleanName);
$cleanName = preg_replace('/\s+/', ' ', $cleanName);
$cleanName = trim($cleanName);
$slug = Str::slug($cleanName, '-');

        // Save the torrent details to the database
        $torrent = Torrent::create([
            'info_hash' => $infoHash,
            'name' => $cleanName,
            'slug' => $slug,
            'file_name' => $fileName,
            'description' => $sdescription ?? $request->description,
            'genre' => $request->genre, // Save the manual genres string
            'steamid' => $request->steamid,
            'announce' => $announce,
            'size' => $meta['size'],
            'num_files' => $meta['count'],
            'owner' => $user->id,
            'category_id' => $request->category_id,
            'imdb_url' => $request->input('imdb_url'),
            'poster' => $poster,
            'imdbid' => $imdbId,
            'tmdbid' => $tmdbId,
            'background' => $background ?? $request->background,
            'tmdb_type' => $tmdbtype,
            'trailer' => $request->input('trailer'),
            'mediainfo' => $request->mediainfo,
            'free' => $request->has('free') || $meta['size'] > 5368709120 ? 1 : 0,
            'double' => $request->has('double') ? 1 : 0,
            'sticky' => $request->has('sticky') ? 1 : 0,
            'recommended' => $request->has('recommended') ? 1 : 0,
            'seedbox' => $request->has('seedbox') ? 1 : 0,
            'external' => $request->has('external') ? 1 : 0,
        ]);
      

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store the image in the 'torrent_images' directory, using the 'public' disk
                $imagePath = $image->store('torrent_images', 'public');
        
                // Create a new record in the TorrentImage table
                TorrentImage::create([
                    'torrent_id' => $torrent->id,
                    'path' => $imagePath,
                ]);
            }
        }

       

    

        // Attach genres to the torrent
        if (!empty($genreIds)) {
            $torrent->genres()->sync($genreIds); // Sync the genres with the torrent
        }

        // Backup the files contained in the torrent
        $fileList = TorrentTools::getTorrentFiles($torrentData);
        foreach ($fileList as $file) {
            $f = new TorrentFiles();
            $f->filename = $file['name'];
            $f->size = $file['size'];
            $f->torrent_id = $torrent->id;
            $f->save();
            unset($f);
        }

       
        $user->increment('seedbonus', 10);

       
        $user->update(['last_upload' => now()]);

		// Clear all cache to ensure fresh data is loaded
            Cache::flush();

        
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $slug])->with('success', 'Your torrent has been uploaded successfully.');
    }


    public function download(Request $request, $id, $slug, $rsskey = null)
    {

       
        // Find the torrent by ID and slug
        $torrent = Torrent::where('id', $id)->where('slug', $slug)->firstOrFail();
    
        // Get the user from the request or the RSS key
        $user = $request->user();
        if (!$user && $rsskey) {
            $user = User::where('passkey', $rsskey)->first();
        }
    
        // Ensure we have a valid user
        if (!$user) {
            return redirect('/login')->with('error', 'Authentication required.');
        }
       
        // Get the slot type (if any) from the query string
        $slotType = $request->query('free') ? 'free' : ($request->query('double') ? 'double' : '');
    
        // Check if the user has available slots
        // if ($user->slots <= 0) {
        //     return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
        //                      ->with('error', 'No available slots.');
        // }


        $existingSlot = UserSlot::where('user_id', $user->id)
        ->where('torrent_id', $torrent->id)
        ->where(function ($query) {
            $query->where('free', 1)
                  ->orWhere('double', 1);
        })
        ->first();

// If the user has already downloaded the torrent as free or double, prevent further downloads of that type
if ($existingSlot && ($slotType === 'free' || $slotType === 'double')) {
    return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
                     ->with('error', 'You have already downloaded this torrent as ' . ucfirst($existingSlot->free ? 'Free' : 'Double') . '. You cannot download it again as ' . ucfirst($slotType) . '.');
}
    
        // Process slot type and apply logic
        if ($slotType === 'free') {
            // Handle free download logic
            // Assign the slot (create the entry in user_slots table)
            $userSlot = UserSlot::create([
                'user_id' => $user->id,
                'torrent_id' => $torrent->id,
                'free' => 1,
                'double' => 0,
                'expires_at' => now()->addDays(28),
            ]);
          
            // Decrease available slots in the user table
            $user->decrement('slots');
        } elseif ($slotType === 'double') {
            // Handle double upload logic
            // Assign the slot (create the entry in user_slots table)
            $userSlot = UserSlot::create([
                'user_id' => $user->id,
                'torrent_id' => $torrent->id,
                'free' => 0,
                'double' => 1,
                'expires_at' => now()->addDays(28),
            ]);
            // Decrease available slots in the user table
            $user->decrement('slots');
        }
    
        // Get the path of the torrent file
        $path = public_path('files/torrents/' . $torrent->file_name);
    
        // Check if the file exists
        if (!file_exists($path)) {
            return response('Torrent file not found', 404)
                ->header('Content-Type', 'text/plain');
        }
    
        // Decode the torrent file
        $dict = Bencode::bdecode(file_get_contents($path));
    
        // Modify the announce URL and add a comment
        $dict['announce'] = route('announce', ['passkey' => $user->passkey], false);
        $dict['comment'] = 'Using this torrent binds you to LastFiles Confidentiality Agreement';
        // Add the label to the torrent's metadata
        $dict['custom']['label'] = 'LastFiles';
    
        // Add the announce-list for multiple trackers
        // $dict['announce-list'] = [
        // [route('announce', ['passkey' => $user->passkey])]
        // ];

        // Add the announce-list for one tracker
    $dict['announce-list'] = [
        // [route('announce', ['passkey' => $user->passkey])],
        ["http://last-torrents.org/announce/{$user->passkey}"] // Secondary announce URL
    ];

            // Add the announce-list for multiple trackers
// $dict['announce-list'] = [
//     // [route('announce', ['passkey' => $user->passkey])],
//     ["http://last-torrents.org/announce/{$user->passkey}"], // Secondary announce URL
//     ["http://lastfiles.ro/announce/{$user->passkey}"]      // Additional announce URL
// ];
    
        // Re-encode the torrent file
        $fileToDownload = Bencode::bencode($dict);
    
        // Generate a custom filename
        $prefix = 'LastFiles_';
        $fileName = $prefix . preg_replace('/[^a-zA-Z0-9-_]/', '_', $torrent->name) . '.torrent';

       
    
        // Clear all cache to ensure fresh data is loaded
        Cache::flush();
    
        // Return the torrent file as a download
        return response($fileToDownload)
            ->header('Content-Type', 'application/x-bittorrent')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($fileToDownload));
    }
    
    
// Renew Slot
public function renewSlot($slotId)
{
    $slot = UserSlot::findOrFail($slotId);

    // Extend the expiration date of the slot by 1 day
    $slot->expires_at = now()->addDays(28);
    $slot->save();

    // Add one more slot back to the user
    $user = $slot->user;
    $user->decrement('slots');

    return redirect()->back()->with('success', 'Slot renewed successfully.');
}

// Remove Slot
public function removeSlot($slotId)
{
    $slot = UserSlot::findOrFail($slotId);

    $slot->delete();

    return redirect()->back()->with('success', 'Slot removed successfully.');
}

public function bump($id)
{
    $user = auth()->user();
    $torrent = Torrent::findOrFail($id);

    // Allow unlimited bumps for user ID 3
    if ($user->id !== 3) {
        // Count total bumps made today (across any torrents)
        $bumpCount = Torrent::where('bumped', true)
            ->whereDate('created_at', today()) // Check bumps made today
            ->count();

        if ($bumpCount > 10) {
            return redirect()->back()->with('error', 'Ai atins limita de 10 bump-uri pentru astăzi.');
        }
    }
   

    // Perform the bump
    $torrent->created_at = now();
    $torrent->bumped = true;
    $torrent->save();

    Cache::flush();

    return redirect()->route('torrents.index')->with('success', 'Torrent bumped to actual date successfully.');
}

    // Show details of a specific torrent
    public function show($id, $slug = null)
    {
        try {
            // Fetch the torrent by ID first to ensure it exists, eager load related models
            $torrent = Torrent::with(['files', 'images', 'genres'])
                ->findOrFail($id);
    
            // Check if the slug matches; if not, redirect to the correct URL
            if ($slug !== $torrent->slug) {
                return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]);
            }
    
            // Fetch comments directly by torrent_id (limit and paginate in one call)
            $comments = Comment::with('user')
                ->where('torrent_id', $torrent->id)
                ->orderByDesc('created_at')
                ->paginate(5);
    
            // Fetch snatched history with user details using joins
            $snatched = History::select('history.*', 'users.name as user_name', 'users.id as user_id')
                ->join('users', 'users.id', '=', 'history.user_id')
                ->where('history.torrent_id', $torrent->id)
                ->get();

                // Parse mediainfo only if it's not null
            $mediainfo = $torrent->mediainfo !== null ? (new MediaInfo())->parse($torrent->mediainfo) : null;
    
            // Use caching for TMDB, OMDB, Steam data to reduce external calls
            $tmdbData = Cache::remember("tmdb_{$torrent->tmdbid}", 3600, function () use ($torrent) {
                return $torrent->tmdbid ? (new TMDBService())->fetchTMDBData($torrent->tmdbid, $torrent->tmdb_type) : null;
            });
    
            $omdbData = Cache::remember("omdb_{$torrent->imdbid}", 3600, function () use ($torrent) {
                return $torrent->imdbid ? (new TMDBService())->fetchOMDBData($torrent->imdbid) : null;
            });
    
            $steamData = Cache::remember("steam_{$torrent->steamid}", 3600, function () use ($torrent) {
                return $torrent->steamid ? (new TMDBService())->fetchSteamData($torrent->steamid) : null;
            });
    
            // Fetch similar torrents based on category or TMDB ID
            $similarTorrents = $torrent->tmdbid
                ? Torrent::where('id', '!=', $torrent->id)
                    ->where('tmdbid', $torrent->tmdbid)
                    ->where('seeders', '>', 0)
                    ->limit(5)
                    ->get()
                : collect(); // Default to empty collection if no TMDB ID
    
            // Fetch recommended torrents based on category and genres
            $recommendedTorrents = Torrent::where('category_id', $torrent->category_id)
                ->where('seeders', '>', 0)
                ->whereHas('genres', function ($query) use ($torrent) {
                    $query->whereIn('genres.id', $torrent->genres->pluck('id'));
                })
                ->orWhere('name', 'like', '%' . preg_replace('/[^\w]+/', '', $torrent->name) . '%')
                ->select('id', 'slug', 'name', 'size', 'seeders', 'leechers', 'times_completed', 'poster')
                ->inRandomOrder()
                ->limit(6)
                ->get();
    
            // Check if the current user has already thanked this torrent
            $hasThanked = TorrentThank::where('torrent_id', $torrent->id)
                ->where('user_id', Auth::id())
                ->exists();
    
            // Get names of users who thanked the torrent (only fetch names, not entire user records)
            $thankUserNames = TorrentThank::where('torrent_id', $torrent->id)
                ->pluck('user_id')
                ->toArray();
    
            $thankUserNames = User::whereIn('id', $thankUserNames)
                ->pluck('name')
                ->toArray();
    
            // Prepare file tree
            $fileTree = TorrentHelper::buildFileTree($torrent->files);
    
            return view('torrents.show', compact(
                'torrent', 'comments', 'tmdbData', 'omdbData', 'mediainfo', 'steamData', 'snatched',
                'similarTorrents', 'recommendedTorrents', 'hasThanked', 'thankUserNames', 'fileTree'
            ));
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the torrent is not found
            return view('errors.torrent-not-found');
        }
    }
    


public function thank($id)
{
    $torrent = Torrent::findOrFail($id);
    $userId = Auth::id();

    // Check if the authenticated user is the owner of the torrent
    if ($torrent->owner == Auth::id()) {
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('error', 'You cannot thank your own torrent.');
    }

    // Check if the user has already thanked the torrent
    if (TorrentThank::where('torrent_id', $torrent->id)->where('user_id', Auth::id())->exists()) {
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('error', 'You have already thanked this torrent.');
    }

    // Check the number of thanks given today
$todayThanksCount = TorrentThank::where('user_id', $userId)
->whereDate('created_at', Carbon::today())
->count();

if ($todayThanksCount >= 5) {
return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
    ->with('error', 'You have reached your daily thank limit (5 per day).');
}

    // Create a new "thank" record
    TorrentThank::create([
        'torrent_id' => $torrent->id,
        'user_id' => Auth::id(),
    ]);

   
$user = Auth::user();
$user->seedbonus += 0.5; 
$user->save();

    // Redirect back to the torrent page with a success message
    return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
        ->with('success', 'Thank you for thanking this torrent!');
}



    // Show the form for editing a specific torrent
    public function edit($id, $slug)
    {
        // Fetch the torrent by slug

        $torrent = Torrent::where('id', $id)->where('slug', $slug)->firstOrFail();

        // Fetch all categories to populate the dropdown in the form
        $categories = Category::all(); // Assuming you have a Category model

        // Pass the torrent and categories to the edit view
        return view('torrents.edit', compact('torrent', 'categories'));
    }

    // Update a torrent's details
    public function update(Request $request, $slug, TMDBService $tmdbService)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:categories,id',
                'description' => 'nullable|string',
                'poster' => 'nullable|url',
                'background' => 'nullable|url',
                'trailer' => 'nullable|url',
                'mediainfo' => 'nullable|string',
                'imdb_url' => 'nullable|url',
                'genre' => 'nullable|string',
                'steamid' => 'nullable|string',

            ]);

            // Fetch the torrent by slug
            $torrent = Torrent::where('slug', $slug)->firstOrFail();

            // Initialize variables
            $poster = $background = $tmdbId = $tmdbType = $imdbId = $steamData = null;
            $genreIds = [];

           // Fetch data from Steam API if steamid is provided

    if ($request->steamid) {
        $steamResponse = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $request->steamid,
            'lang' => 'en'
        ]);

        $steamData = $steamResponse->json();

        // Check if the Steam data is valid
        if (isset($steamData[$request->steamid]['success']) && $steamData[$request->steamid]['success']) {
            $gameDetails = $steamData[$request->steamid]['data'];

            $poster = $gameDetails['header_image'] ?? $request->poster; // Use the Steam poster if available
            $background = $gameDetails['background_raw'] ?? $request->background; // Use Steam background if available
        }
    }


            // Check if it's a movie or TV torrent (IMDb URL provided)
            $imdbUrl = $request->input('imdb_url');
            if ($imdbUrl && preg_match('/^https?:\/\/(?:www\.)?imdb\.com\/title\/(tt\d{7,8})/', $imdbUrl, $matches)) {
                $imdbId = $matches[1];
                $tmdbDetails = $tmdbService->getTMDBIdAndTypeByIMDbId($imdbId);

                if ($tmdbDetails) {
                    $tmdbId = $tmdbDetails['tmdb_id'];
                    $tmdbType = $tmdbDetails['type'];
                    //$background = $tmdbDetails['backdrop_path'];
                    $tmdbData = $tmdbService->fetchTMDBData($tmdbId, $tmdbType);

                    if ($tmdbData) {


                        // Set genres from TMDB data
                        $genres = $tmdbData['genres'] ?? [];
                        foreach ($genres as $genre) {
                            $genreRecord = Genre::firstOrCreate(['name' => $genre['name']]);
                            $genreIds[] = $genreRecord->id;
                        }

                        // Update the genre-torrent relationship
                        $torrent->genres()->sync($genreIds);
                    }
                }
            }


           // Handle manually entered genres
           $manualGenres = $request->input('genre', ''); // Get manually entered genres as a string
           if (!empty($manualGenres)) {
               // Split the string by both ',' and '/'
               $manualGenresArray = preg_split('/[\/,]/', $manualGenres); // Split by either ',' or '/'

               // Loop through each genre, clean it, create if necessary, and collect the genre IDs
               foreach ($manualGenresArray as $genreName) {
                   $genreName = trim($genreName); // Clean up any extra spaces
                   if (!empty($genreName)) {
                       // Save the genre to the database or retrieve it if it already exists
                       $genreRecord = Genre::firstOrCreate(['name' => $genreName]);
                       $genreIds[] = $genreRecord->id;
                   }
               }
           }

    

if ($request->has('recommended') && !$torrent->recommended) {
    $recommendedCount = Torrent::where('recommended', 1)->count();

    if ($recommendedCount >= 15) {
            return redirect()->route('torrents.index')->with('error', 'You cannot have more than 15 recommended torrents at a time.');
    }
}


            // Update the torrent's details, including genres and TMDB data
            $torrent->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'poster' => $poster ?? $request->poster,
                'background' => $background ?? $request->background,
                'genre' => $request->genre, // Save the manual genres string
                'steamid' => $request->steamid,
                'imdb_url' => $imdbUrl,
                'tmdbid' => $tmdbId,
                'imdbid' => $imdbId,
                'tmdb_type' => $tmdbType,
                'trailer' => $request->trailer,
                'mediainfo' => $request->mediainfo,
                'free' => $request->has('free') ? 1 : 0,
                'double' => $request->has('double') ? 1 : 0,
                'sticky' => $request->has('sticky') ? 1 : 0,
                'recommended' => $request->has('recommended') ? 1 : 0,
                'seedbox' => $request->has('seedbox') ? 1 : 0,
            ]);

// Delete selected images
if ($request->has('delete_images')) {
    foreach ($request->delete_images as $imageId) {
        // Find the image record by ID and torrent ID
        $image = TorrentImage::where('id', $imageId)
            ->where('torrent_id', $torrent->id)
            ->first();

        // Log the image record for debugging
        \Log::info('Image record to delete:', ['image' => $image]);

        if ($image) {
            // Get the relative path from the database
            $relativePath = $image->path;  // e.g., 'torrent_images/filename.jpg'
            $filePath = storage_path('app/public/' . $relativePath);  // Full path with storage_path()

            // Log the full file path for debugging
            \Log::info('Attempting to delete file from storage:', ['filePath' => $filePath]);

            // Check if file exists in storage and delete it
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file from the disk using unlink
                \Log::info('Deleted file from storage:', ['filePath' => $filePath]);
            } else {
                \Log::warning('File not found in storage:', ['filePath' => $filePath]);
            }

            // Attempt to delete the image record from the database
            try {
                $image->delete(); // Delete from DB
                \Log::info('Deleted image record from database:', ['imageId' => $imageId]);
            } catch (\Exception $e) {
                \Log::error('Error deleting image from database:', ['error' => $e->getMessage()]);
            }
        } else {
            \Log::warning('Image not found or does not belong to this torrent:', ['imageId' => $imageId, 'torrentId' => $torrent->id]);
        }
    }
}



// Handle newly uploaded images
if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $path = $image->store('torrent_images', 'public'); // store in storage/app/public/torrent_images

        TorrentImage::create([
            'torrent_id' => $torrent->id,
            'path' => $path,
        ]);
    }
}

            // Clear all cache to ensure fresh data is loaded
            Cache::flush();

            // Sync the manually entered genres with the torrent
            if (!empty($genreIds)) {
                $torrent->genres()->sync($genreIds); // Sync the genres with the torrent
            }

            return redirect()->route('torrents.show', $torrent->id)->with('success', 'Your torrent has been updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('torrents.show', $torrent->id)->with('error', 'There was an error updating the torrent. Please try again.');
        }
    }




    // Delete a torrent
    public function destroy(Request $request, $slug)
    {
        // Find the torrent using the slug
        $torrent = Torrent::where('slug', $slug)->firstOrFail();

        // Get the owner of the torrent
        $owner = $torrent->owner; // Assuming the owner is a User model related to the Torrent model



       // Get the deletion reason (from the request, either predefined or custom)
$deletionReason = $request->input('deletion_reason');

// If the deletion reason is not set or is null, set it to "0 seeders and 0 leechers"
if (empty($deletionReason)) {
    $deletionReason = '0 seeders and 0 leechers';
} elseif ($deletionReason === 'custom') {
    $deletionReason = $request->input('custom_reason');
}

         // Ensure the owner exists before sending the message
if ($owner && User::where('id', $owner)->exists()) {
    // Send a message to the owner about the deletion
    Message::create([
        'receiver_id' => $owner,  // The owner receives the message
        'subject' => 'Torrent Deletion',
        'sender_id' => 2,  // The user deleting the torrent (usually admin or system)
        'body' => 'Your torrent "' . $torrent->name . '" has been deleted. Reason: ' . $deletionReason,
        'is_read' => false,  // Mark as unread initially
    ]);
}


        // Delete associated peers
        Peer::where('torrent_id', $torrent->id)->delete();

        // Delete associated history records using the correct column name
        History::where('torrent_id', $torrent->id)->delete();

          // Delete associated comments
        Comment::where('torrent_id', $torrent->id)->delete();

        //Delete associated warning records
        if (Warning::where('torrent', $torrent->id)->exists()) {
            Warning::where('torrent', $torrent->id)->delete();
        }

        // Delete associated slots
        if (UserSlot::where('torrent_id', $torrent->id)->exists()) {
            UserSlot::where('torrent_id', $torrent->id)->delete();
        }

        // Detach associated genres
        $torrent->genres()->detach();

        // Delete associated images from the `torrent_images` table
    $images = TorrentImage::where('torrent_id', $torrent->id)->get();

    foreach ($images as $image) {
        // Get the file path for the image stored in storage
        $filePath = storage_path('app/public/' . $image->path); // Full file path

        // Check if the file exists and delete it from storage
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the image from storage
        }

        // Delete the image record from the database
        $image->delete();
    }

        // Define the path to the torrent file
        $filePath = public_path('files/torrents/' . $torrent->file_name);

        // Check if the file exists and delete it
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file
        }

        // Delete the torrent itself
        $torrent->delete();

        return redirect()->route('torrents.index')->with('success', 'Torrent and associated records deleted successfully.');
    }


public function bulkDelete(Request $request)
{
    $torrentIds = $request->input('torrent_ids', []);
    $deletionReason = $request->input('deletion_reason');

    if (empty($deletionReason)) {
        $deletionReason = '0 seeders and 0 leechers';
    } elseif ($deletionReason === 'custom') {
        $deletionReason = $request->input('custom_reason');
    }

    foreach ($torrentIds as $id) {
        $torrent = Torrent::find($id);

        if (!$torrent) continue;

        $owner = $torrent->owner;

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
        History::where('torrent_id', $torrent->id)->delete();
        Comment::where('torrent_id', $torrent->id)->delete();
        Warning::where('torrent', $torrent->id)->delete();
        UserSlot::where('torrent_id', $torrent->id)->delete();
        $torrent->genres()->detach();

        $images = TorrentImage::where('torrent_id', $torrent->id)->get();
        foreach ($images as $image) {
            $filePath = storage_path('app/public/' . $image->path);
            if (file_exists($filePath)) unlink($filePath);
            $image->delete();
        }

        $filePath = public_path('files/torrents/' . $torrent->file_name);
        if (file_exists($filePath)) unlink($filePath);

        $torrent->delete();
    }

    return redirect()->route('torrents.index')->with('success', 'Selected torrents deleted successfully.');
}



public function peers($torrentId, Request $request)
{
    // Fetch the torrent by ID
    $torrent = Torrent::findOrFail($torrentId);

    // Check if 'seeders' or 'leechers' is present in the query string
    $seeders = null;
    $leechers = null;

    // Fetch seeders if the query string contains 'seeders'
    if (request()->has('seeders')) {
        $seeders = Peer::where('torrent_id', $torrentId)->where('seeder', 1)->orderByRaw('user_id = ? DESC', [$request->user()->id])->paginate(50);
        // Append 'seeders' query parameter to pagination links
        $seeders->withPath(url()->current())->appends(['seeders' => '1']);
    }

    // Fetch leechers if the query string contains 'leechers'
    if (request()->has('leechers')) {
        $leechers = Peer::where('torrent_id', $torrentId)->where('seeder', 0)->where('active', 1)->paginate(25);
        // Append 'leechers' query parameter to pagination links
        $leechers->withPath(url()->current())->appends(['leechers' => '1']);
    }

    // Return the view with the seeders or leechers based on the query parameters
    return view('torrents.peers', compact('torrent', 'seeders', 'leechers'));
}


public function checkImdbUrl(Request $request)
{
    $url = $request->query('url');

    $torrents = Torrent::where('imdb_url', $url)
        ->latest()
        ->take(10)
        ->get(['id', 'name', 'seeders', 'leechers', 'times_completed', 'size']);

    if ($torrents->isNotEmpty()) {
        
        return response()->json([
            'exists' => true,
            'torrents' => $torrents
        ]);
    }

    return response()->json(['exists' => false]);
}








}
