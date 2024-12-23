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
use Illuminate\Support\Facades\Response;



class TorrentController extends Controller
{

    // TMDB API key
    private $apiKey;

    public function __construct()
    {
        // Set the API key from the environment variable
        $this->apiKey = env('TMDB_API_KEY');
    }
    // Display a list of all torrentspublic function index(Request $request)
    public function index(Request $request)
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
        $cacheKey = "cached_torrents_page_{$currentPage}_sort_{$sortColumn}_dir_{$sortDirection}_" .
        "keyword_" . ($request->get('keyword', '') ?: 'null') . "_" .
        "category_" . ($request->get('category', '') ?: 'null') . "_" .
        "genre_" . ($request->get('genre', '') ?: 'null') . "_" .
        "torrent_status_" . ($request->get('torrent_status', '') ?: 'null') . "_" .
        "sticky_" . ($request->get('sticky', '0') ?: 'null') . "_" .
        "free_" . ($request->get('free', '0') ?: 'null') . "_" .
        "double_" . ($request->get('double', '0') ?: 'null') . "_" .
        "recommended_" . ($request->get('recommended', '0') ?: 'null');



        // Fetch torrents from the cache
        $torrents = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $sortColumn, $sortDirection) {
            $query = Torrent::query()->with('genres');

            // Exclude category 27
            $query->whereNotIn('category_id', [27, 34]);

            // Apply keyword filter if present
if ($request->filled('keyword')) {
    $keyword = $request->keyword;

    // Normalize the keyword: Remove periods, spaces, and other common delimiters
    $normalizedKeyword = preg_replace('/[^a-zA-Z0-9]/', '', $keyword);

    $query->where(function ($q) use ($normalizedKeyword) {
        // Normalize only the 'name' field in the database
        $q->whereRaw('REPLACE(REPLACE(name, ".", ""), " ", "") LIKE ?', ['%' . $normalizedKeyword . '%'])
          // Keep the imdb_url as it is, no changes to this field
          ->orWhere('imdb_url', 'like', '%' . $normalizedKeyword . '%');
    });
}


            // Apply multiple category filters if categories are selected
if ($request->has('categories') && is_array($request->categories)) {
    $query->whereIn('category_id', $request->categories);
}

            // Apply genre filter if genre is selected
            if ($request->filled('genre')) {
                $query->whereHas('genres', function ($q) use ($request) {
                    $q->where('genres.id', $request->genre);
                });
            }

            // Apply torrent status filter if present
            if ($request->filled('torrent_status')) {
                $status = $request->torrent_status;

                switch ($status) {
                    case 'active':
                        $query->where('seeders', '>', 0); // Active torrents with seeders
                        break;
                    case 'dead':
                        $query->where('seeders', '=', 0); // Dead torrents with no seeders
                        break;
                    case 'free':
                        $query->where('free', '=', true)->where('seeders', '>', 0); // Free torrents with seeders
                        break;
                    case 'double':
                        $query->where('double', '=', true)->where('seeders', '>', 0); // Double torrents with seeders
                        break;
                    case 'seedbox':
                        $query->where('seedbox', '=', true)->where('seeders', '>', 0); // Seedbox torrents with seeders
                        break;
                }
            } else {
                // Default behavior: show only torrents with seeders > 0
                $query->where('seeders', '>', 0);
            }

            // Order first by sticky, then by the selected sort column and direction, or fallback to id
            return $query
                ->orderByRaw('sticky DESC')            // Sticky torrents first
                ->when(
                    $request->has('sort') || $request->has('direction'),
                    function ($q) use ($sortColumn, $sortDirection) {
                        $q->orderBy($sortColumn, $sortDirection);  // Apply user-selected sorting
                    },
                    function ($q) {
                        $q->orderBy('id', 'desc');    // Default to id (descending) if no query
                    }
                )
                ->orderBy('id', 'desc')               // Then by id (descending)
                ->paginate(50)
                ->appends($request->query());         // Preserve query parameters
        });

        // Mark new torrents for the user
        $user = $request->user();
        $newTorrents = $user
            ? $torrents->filter(fn($torrent) => $torrent->created_at > $user->last_browse)
            : collect();

        if ($user) {
            $user->last_browse = now();
            $user->save(); // Explicitly save the model
        }

        return view('torrents.index', compact('torrents', 'categories', 'allGenres', 'sortColumn', 'sortDirection', 'newTorrents'));
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
        $query = Torrent::query()->with('genres');

        // Apply the filter for adult categories: 27, 34, 60
        $query->whereIn('category_id', [27, 34, 60]);

        // Apply other filters like in the index function (keyword, category, genre, etc.)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('imdb_url', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }

        if ($request->filled('torrent_status')) {
            $status = $request->torrent_status;

            switch ($status) {
                case 'active':
                    $query->where('seeders', '>', 0); // Active torrents with seeders
                    break;
                case 'dead':
                    $query->where('seeders', '=', 0); // Dead torrents with no seeders
                    break;
                case 'free':
                    $query->where('free', '=', true)->where('seeders', '>', 0); // Free torrents with seeders
                    break;
                case 'double':
                    $query->where('double', '=', true)->where('seeders', '>', 0); // Double torrents with seeders
                    break;
                case 'seedbox':
                    $query->where('seedbox', '=', true)->where('seeders', '>', 0); // Seedbox torrents with seeders
                    break;
            }
        } else {
            // Default behavior: show only torrents with seeders > 0
            $query->where('seeders', '>', 0);
        }

        // Order first by sticky, then by the selected sort column and direction, or fallback to id
        return $query
            ->orderByRaw('sticky DESC')            // Sticky torrents first
            ->when(
                $request->has('sort') || $request->has('direction'),
                function ($q) use ($sortColumn, $sortDirection) {
                    $q->orderBy($sortColumn, $sortDirection);  // Apply user-selected sorting
                },
                function ($q) {
                    $q->orderBy('id', 'desc');    // Default to id (descending) if no query
                }
            )
            ->orderBy('id', 'desc')               // Then by id (descending)
            ->paginate(50)
            ->appends($request->query());         // Preserve query parameters
    });

    // Mark new torrents for the user
    $user = $request->user();
    $newTorrents = $user
        ? $adult->filter(fn($adult) => $adult->created_at > $user->last_browsex)
        : collect();

    if ($user) {
        $user->last_browsex = now();
        $user->save(); // Explicitly save the model
    }

    return view('torrents.adult', compact('adult', 'categories', 'allGenres', 'sortColumn', 'sortDirection', 'newTorrents'));
}





    // Show the form to upload a new torrent
    public function create()
    {
        $user = Auth::user();
        $categories = Category::all(); // Get all categories
        if (session('success')) {
            return view('torrents.upload', compact('user', 'categories'));
        }
        return view('torrents.upload', compact('user', 'categories')); // Pass categories to view
    }

    // Handle the upload and storage of a new torrent
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
            'genre' => 'nullable|string', // Add validation for manually added genres
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
        $torrentData['info']['private'] = 1;
        $infoHash = Bencode::get_infohash($torrentData);
        $meta = Bencode::get_meta($torrentData);

        $announce = $torrentData['announce'];
        $name = str_replace('.', '-', $request->name); // Replace dots with hyphens
        $slug = Str::slug($name, '-'); // Generate the slug

        // Ensure the slug is unique by appending a number if it already exists
        $originalSlug = $slug;
        $count = 1;
        while (Torrent::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        // Use the TMDB service to determine TMDB ID and type based on IMDb ID
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


        // Fetch data from Steam API if steamid is provided
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

        // Check if a torrent with the same infohash already exists
        if (Torrent::where('info_hash', $infoHash)->exists()) {
            return back()->with('error', 'A torrent with the same infohash exists on the site!');
        }

        // Save the torrent file to the server
        file_put_contents(public_path('files/torrents') . '/' . $fileName, Bencode::bencode($torrentData));

        // Save the torrent details to the database
        $torrent = Torrent::create([
            'info_hash' => $infoHash,
            'name' => $request->name,
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
            'free' => $request->has('free') ? 1 : 0,
            'double' => $request->has('double') ? 1 : 0,
            'sticky' => $request->has('sticky') ? 1 : 0,
            'recommended' => $request->has('recommended') ? 1 : 0,
            'seedbox' => $request->has('seedbox') ? 1 : 0,
        ]);

        // Clear the torrents cache
        // Cache::forget('cached_torrents');

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

        // Add 5 seed bonus points to the user
        $user->increment('seedbonus', 5);

		// Clear all cache to ensure fresh data is loaded
            Cache::flush();

        // Redirect to the torrent details page using the slug
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
        $dict['comment'] = 'Using this torrent binds you to LastTorrents Confidentiality Agreement';
         // Add the label to the torrent's metadata
        $dict['label'] = 'LastFiles';

        // Remove other announce URLs
        // unset($dict['announce-list']);

        // Add the announce-list for multiple trackers
    $dict['announce-list'] = [
        // [route('announce', ['passkey' => $user->passkey])],
        ["http://last-torrents.org/announce/{$user->passkey}"] // Secondary announce URL
    ];

        // Re-encode the torrent file
        $fileToDownload = Bencode::bencode($dict);

        // Generate a custom filename
        $prefix = 'Last-Torrents_';
        $fileName = $prefix . preg_replace('/[^a-zA-Z0-9-_]/', '_', $torrent->name) . '.torrent';

        // Clear all cache to ensure fresh data is loaded
        Cache::flush();

        // Return the torrent file as a download
        return response($fileToDownload)
            ->header('Content-Type', 'application/x-bittorrent')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($fileToDownload));
    }






    // Show details of a specific torrent
    public function show($id, $slug = null)
    {
        try {
            // Fetch the torrent by ID first to ensure it exists
            $torrent = Torrent::with('files')->findOrFail($id);

            // Check if the slug matches; if not, redirect to the correct URL
            if ($slug !== $torrent->slug) {
                return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]);
            }

            // Fetch comments directly by torrent_id
            $comments = Comment::with('user')
                ->where('torrent_id', $torrent->id)
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            // Get the snatched history from the history table (assuming 'history' table is tracking snatched torrents)
            $snatched = History::select('history.*', 'users.name as user_name', 'users.id as user_id')
                ->join('users', 'users.id', '=', 'history.user_id')
                ->where('history.torrent_id', $torrent->id)
                ->get();

            $tmdbData = null; // Initialize variable to hold TMDB data
            $omdbData = null; // Initialize variable to hold OMDB data
            $steamData = null;

            // Parse mediainfo only if it's not null
            $mediainfo = $torrent->mediainfo !== null ? (new MediaInfo())->parse($torrent->mediainfo) : null;

            // Fetch TMDB data if available
            if ($torrent->tmdbid) {
                // Define a unique cache key for this torrent's TMDB data
                $cacheKey = "tmdb_{$torrent->tmdb_type}_{$torrent->tmdbid}";

                // Attempt to get TMDB data from cache
                $tmdbData = Cache::remember($cacheKey, now()->addDays(30), function () use ($torrent) {
                    $tmdbResponse = Http::get("https://api.themoviedb.org/3/{$torrent->tmdb_type}/{$torrent->tmdbid}", [
                        'api_key' => '325f0b42fccd356be82ede4d2be6312c',
                        'language' => 'en-US',
                        'append_to_response' => 'credits,videos,images,keywords'
                    ]);

                    return $tmdbResponse->successful() ? $tmdbResponse->json() : null;
                });
            }

            // Fetch OMDB data if a valid IMDB ID is available
            if ($torrent->imdbid) {
                // Define a unique cache key for this torrent's OMDB data
                $omdbCacheKey = "omdb_{$torrent->imdbid}";

                // Attempt to get OMDB data from cache
                $omdbData = Cache::remember($omdbCacheKey, now()->addDays(30), function () use ($torrent) {
                    $omdbResponse = Http::get("http://www.omdbapi.com/", [
                        'apikey' => 'd3eb5201',  // Replace with your OMDB API key
                        'i' => $torrent->imdbid,
                        'plot' => 'full', // or 'full' depending on your needs
                        'r' => 'json'  // Ensure the response is in JSON format
                    ]);

                    return $omdbResponse->successful() ? $omdbResponse->json() : null;
                });
            }

            // Fetch Steam data if a valid Steam ID is available
            if ($torrent->steamid) {
                // Define a unique cache key for this torrent's Steam data
                $steamCacheKey = "steam_{$torrent->steamid}";

                // Attempt to get Steam data from cache
                $steamData = Cache::remember($steamCacheKey, now()->addDays(30), function () use ($torrent) {
                    $steamResponse = Http::get('https://store.steampowered.com/api/appdetails', [
                        'appids' => $torrent->steamid,
                        'lang' => 'en'
                    ]);

                    return $steamResponse->successful() ? $steamResponse->json() : null;
                });
            }

            // Fetch similar torrents based on category or TMDB ID
            $similarTorrents = collect(); // Default to an empty collection

            if ($torrent->tmdbid !== null) {
                $similarTorrents = Torrent::where('id', '!=', $torrent->id)
                    ->where('tmdbid', $torrent->tmdbid)
                    ->where('seeders', '>', 0)
                    ->limit(5)
                    ->get();
            }

            $recommendedTorrents = Torrent::where('category_id', $torrent->category_id)
            ->where('seeders', '>', 0)
            ->whereHas('genres', function ($query) use ($torrent) {
                // Match torrents that have at least one genre in common with the current torrent
                $query->whereIn('genres.id', $torrent->genres->pluck('id'));
            })
            ->orWhere('name', 'like', '%' . preg_replace('/[^\w]+/', '', $torrent->name) . '%')
            ->select('id', 'slug', 'name', 'size', 'seeders', 'leechers', 'times_completed', 'poster')
            ->inRandomOrder()
            ->limit(5)
            ->get();

            // Return the data to the view
            return view('torrents.show', compact('torrent', 'comments', 'tmdbData', 'omdbData', 'mediainfo', 'steamData', 'snatched', 'similarTorrents', 'recommendedTorrents'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the torrent is not found
            return view('errors.torrent-not-found'); // Display custom "Torrent Not Found" page
        }
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

         // Send a message to the owner about the deletion
    Message::create([
        'receiver_id' => $owner,  // The owner receives the message
        'subject' => 'Torrent Deletion',
        'sender_id' => 2,  // The user deleting the torrent (usually admin or system)
        'body' => 'Your torrent " ' . $torrent->name . ' " has been deleted. Reason: ' . $deletionReason,
        'is_read' => false,  // Mark as unread initially
    ]);

        // Delete associated peers
        Peer::where('torrent_id', $torrent->id)->delete();

        // Delete associated history records using the correct column name
        History::where('torrent_id', $torrent->id)->delete();

        // Delete associated comments
        Peer::where('torrent_id', $torrent->id)->delete();

        // Detach associated genres
        $torrent->genres()->detach();

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




    // Example method in TorrentController to get peers for a specific torrent
public function getPeers($torrentId)
{
    $peers = Peer::where('torrent', $torrentId)->get();
    return response()->json($peers);
}


public function peers($torrentId)
{
    // Fetch the torrent by ID
    $torrent = Torrent::findOrFail($torrentId);

    // Check if 'seeders' or 'leechers' is present in the query string
    $seeders = null;
    $leechers = null;

    // Fetch seeders if the query string contains 'seeders'
    if (request()->has('seeders')) {
        $seeders = Peer::where('torrent_id', $torrentId)->where('seeder', 1)->paginate(25);
        // Append 'seeders' query parameter to pagination links
        $seeders->withPath(url()->current())->appends(['seeders' => '1']);
    }

    // Fetch leechers if the query string contains 'leechers'
    if (request()->has('leechers')) {
        $leechers = Peer::where('torrent_id', $torrentId)->where('seeder', 0)->paginate(25);
        // Append 'leechers' query parameter to pagination links
        $leechers->withPath(url()->current())->appends(['leechers' => '1']);
    }

    // Return the view with the seeders or leechers based on the query parameters
    return view('torrents.peers', compact('torrent', 'seeders', 'leechers'));
}



}
