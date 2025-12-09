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
use App\Models\HappyHour;
use App\Helpers\TorrentHelper;
use App\Services\TorrentDownloadService;
use App\Models\Seedbox;



class TorrentController extends Controller
{

    private $apiKey;
    protected $service;

    public function __construct(TorrentDownloadService $service)
    {
        $this->apiKey = env('TMDB_API_KEY');
        $this->service = $service;
    }

    
    public function sendToSeedboxInternal(User $user, Torrent $torrent, int $seedboxId)
{
    $authUser = $user;

    $path = public_path('files/torrents/' . $torrent->file_name);
    if (!file_exists($path) || !is_readable($path)) {
        throw new \Exception('Torrent file not found or unreadable: ' . $path);
    }

    // Use the file as-is, no re-encode
    $tmpFileName = "seedbox__{$torrent->id}.torrent";
    $tmpPath     = storage_path("app/tmp/{$tmpFileName}");
    if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);

    copy($path, $tmpPath); // copy directly

    $seedbox = Seedbox::where('user_id', $authUser->id)
        ->where('id', $seedboxId)
        ->first();

    if (!$seedbox) {
        throw new \Exception('Selected seedbox not found or not yours.');
    }

    $service = new \App\Services\SeedboxService(
        $seedbox->address,
        $seedbox->username,
        $seedbox->password,
        $seedbox->auth_type
    );

    $result = $service->addTorrentFileSeedBox($tmpPath);

    @unlink($tmpPath);

    if (isset($result['error'])) {
        throw new \Exception($result['error']);
    }

    return true;
}



public function sendToSeedbox(User $user, Torrent $torrent, Request $request)
{
    try {
        $authUser = auth()->user();

        

        $path = public_path('files/torrents/' . $torrent->file_name);
        if (!file_exists($path) || !is_readable($path)) {
            return redirect()->back()->with('error', 'Torrent file not found or unreadable: ' . $path);
        }

       
        $dict = Bencode::bdecode(file_get_contents($path));
        if (!$dict) {
            return redirect()->back()->with('error', 'Invalid torrent file (cannot decode)');
        }

        
        $dict['announce'] = "http://last-torrents.org/announce/{$authUser->passkey}";
        $dict['comment']  = 'Using this torrent binds you to LastFiles Confidentiality Agreement';
       // $dict['custom']['label'] = 'LastFiles';
        // $dict['announce-list'] = [
        //     ["http://last-torrents.org/announce/{$authUser->passkey}"]
        // ];

        $fileToUpload = Bencode::bencode($dict);
        if (!$fileToUpload) {
            return redirect()->back()->with('error', 'Failed to encode torrent with user passkey');
        }

       
        $tmpFileName = "seedbox__{$torrent->id}.torrent";
        $tmpPath     = storage_path("app/tmp/{$tmpFileName}");
        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }
        file_put_contents($tmpPath, $fileToUpload);

       
        $seedbox = Seedbox::where('user_id', $authUser->id)
            ->where('id', $request->input('seedbox_id'))
            ->first();

        if (!$seedbox) {
            return redirect()->back()->with('error', 'Selected seedbox not found or not yours.');
        }

        
        $service = new \App\Services\SeedboxService(
            $seedbox->address,
            $seedbox->username,
            $seedbox->password,
            $seedbox->auth_type
        );

        $result = $service->addTorrentFileSeedBox($tmpPath);

       
        @unlink($tmpPath);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', "Torrent sent to {$seedbox->name} successfully!");
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}








 public function index(Request $request)
{
    $categories = Category::all();
    $allGenres = Genre::all();

    $sortColumn = $request->get('sort', 'name');
    $sortDirection = $request->get('direction', 'desc');

    $torrents = TorrentHelper::buildTorrentQuery($request, $sortColumn, $sortDirection);

    $user = $request->user();
    $tz = $user->timezone ?? 'UTC';

    
    $torrents = $torrents->through(function ($torrent) use ($tz) {
        $torrent->created_at_local = $torrent->created_at->clone()->tz($tz);
        return $torrent;
    });

  
    $newTorrents = $user
        ? $torrents->getCollection()->filter(fn($torrent) =>
            !$user->last_browse ||
            $torrent->created_at->gt($user->last_browse)
        )
        : collect();

   
    if ($user) {
        $user->last_browse = now(); 
        $user->save();
    }

    $categoryIds = [11, 12, 24, 25, 31, 32, 54, 55, 81, 82];
    $twoDaysAgo = now()->subDays(2);
    $oneWeekAgo = now()->subWeek();

    $topTorrents = Torrent::whereIn('category_id', $categoryIds)
        ->where('created_at', '>=', $twoDaysAgo)
        ->orderByDesc('seeders')
        ->orderByDesc('times_completed')
        ->take(5)
        ->get();

    if ($topTorrents->isEmpty()) {
        $topTorrents = Torrent::whereIn('category_id', $categoryIds)
            ->where('created_at', '>=', $oneWeekAgo)
            ->orderByDesc('seeders')
            ->orderByDesc('times_completed')
            ->take(5)
            ->get();
    }

    $movieOfTheDay = $topTorrents->isNotEmpty() ? $topTorrents->random() : null;

    $currentHappyHour = HappyHour::where('active', true)
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now())
        ->latest('start_at')
        ->first();

    return view('torrents.index', compact(
        'torrents',
        'categories',
        'allGenres',
        'sortColumn',
        'sortDirection',
        'newTorrents',
        'movieOfTheDay',
        'currentHappyHour'
    ));
}



    
    
    

  public function adult(Request $request)
{
    $categories = Category::whereIn('id', [27, 34, 60])->get();
    $allGenres = Genre::all();

    $sortColumn = $request->get('sort', 'name');
    $sortDirection = $request->get('direction', 'desc');

    $adult = TorrentHelper::buildAdultTorrentQuery($request, $sortColumn, $sortDirection);

    $user = $request->user();

    // Convert paginator to collection
    $adultCollection = $adult->getCollection();

    // New torrents based on last_browsex
    $newTorrents = $user
        ? $adultCollection->filter(fn($torrent) =>
            !$user->last_browsex || $torrent->created_at->gt($user->last_browsex)
        )
        : collect();

    // Update last_browsex
    if ($user) {
        $user->last_browsex = now();
        $user->save();
    }

    $currentHappyHour = HappyHour::where('active', true)
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now())
        ->latest('start_at')
        ->first();

    return view('torrents.adult', compact(
        'adult',
        'categories',
        'allGenres',
        'sortColumn',
        'sortDirection',
        'newTorrents',
        'currentHappyHour'
    ));
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

    public function store(Request $request)
    {


         $tmdbService = app(TMDBService::class);

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

       
        $directory = public_path('files/torrents');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

       
        $fileName = uniqid('', true) . '.torrent';
        $torrentContent = file_get_contents($request->file('torrent')->path());

       
        $torrentData = Bencode::bdecode($torrentContent);

        $torrentData['info']['private'] = ($request->has('external') && $request->external == 1) ? 0 : 1;
        $infoHash = Bencode::get_infohash($torrentData);

      
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

     
        if (isset($steamData[$request->steamid]['success']) && $steamData[$request->steamid]['success']) {
            $gameDetails = $steamData[$request->steamid]['data'];

           
            $name = $gameDetails['name'] ?? $name; // Use the Steam game name if available
            $sdescription = $gameDetails['short_description'] ?? $request->description; // Use the Steam description if available
            $poster = $gameDetails['header_image'] ?? $request->poster; // Use the Steam poster if available
            $background = $gameDetails['background'] ?? $request->background; // Use Steam background if available
        }
    }

        
        if ($tmdbData) {
            $posterBaseUrl = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2';
            $backgroundBaseUrl = 'https://image.tmdb.org/t/p/original';
            $poster = $tmdbData['poster_path'] ? $posterBaseUrl . $tmdbData['poster_path'] : null;
            $background = $tmdbData['backdrop_path'] ? $backgroundBaseUrl . $tmdbData['backdrop_path'] : null;

          
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

           
            $manualGenres = $request->input('genre', ''); // Get manually entered genres as a string
            if (!empty($manualGenres)) {
              
                $manualGenresArray = preg_split('/[\/,]/', $manualGenres); // Split by either ',' or '/'

              
                foreach ($manualGenresArray as $genreName) {
                    $genreName = trim($genreName); 
                    if (!empty($genreName)) {
                        
                        $genreRecord = Genre::firstOrCreate(['name' => $genreName]);
                        $genreIds[] = $genreRecord->id;
                    }
                }
            }

        }
        
         
        
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
            'genre' => $request->genre, 
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
            'seedbox' => $request->has('seedbox') ? 1 : 0,
            'external' => $request->has('external') ? 1 : 0,
        ]);
      

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
             
                $imagePath = $image->store('torrent_images', 'public');
        
               
                TorrentImage::create([
                    'torrent_id' => $torrent->id,
                    'path' => $imagePath,
                ]);
            }
        }

       

    

     
        if (!empty($genreIds)) {
            $torrent->genres()->sync($genreIds); 
        }

        
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

		
        //Cache::flush();

        
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $slug])->with('success', 'Your torrent has been uploaded successfully.');
    }


    public function download(Request $request, $id, $slug)
    {
        try {
            return $this->service->handleDownload($request, $id, $slug);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
// Renew Slot
public function renewSlot($slotId)
{
    $slot = UserSlot::findOrFail($slotId);

   
    $slot->expires_at = now()->addDays(28);
    $slot->save();

   
    $user = $slot->user;
    $user->decrement('slots');

    return redirect()->back()->with('success', 'Slot renewed successfully.');
}


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
     
        $bumpCount = Torrent::where('bumped', true)
            ->whereDate('created_at', today()) 
            ->count();

        if ($bumpCount > 10) {
            return redirect()->back()->with('error', 'Ai atins limita de 10 bump-uri pentru astăzi.');
        }
    }
   

   
    $torrent->created_at = now();
    $torrent->bumped = true;
    $torrent->save();

   

    return redirect()->route('torrents.index')->with('success', 'Torrent bumped to actual date successfully.');
}

   
    public function show($id, $slug = null)
    {
        try {
          
            $torrent = Torrent::with(['files', 'images', 'genres'])
                ->findOrFail($id);
    
         
            if ($slug !== $torrent->slug) {
                return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]);
            }
    
          
            $comments = Comment::with('user')
                ->where('torrent_id', $torrent->id)
                ->orderByDesc('created_at')
                ->paginate(5);
    
          
            $snatched = History::select('history.*', 'users.name as user_name', 'users.id as user_id')
                ->join('users', 'users.id', '=', 'history.user_id')
                ->where('history.torrent_id', $torrent->id)
                ->get();

              
            $mediainfo = $torrent->mediainfo !== null ? (new MediaInfo())->parse($torrent->mediainfo) : null;
    
           
            $tmdbData = Cache::remember("tmdb_{$torrent->tmdbid}", 86400, function () use ($torrent) {
                return $torrent->tmdbid ? (new TMDBService())->fetchTMDBData($torrent->tmdbid, $torrent->tmdb_type) : null;
            });
    
            $omdbData = Cache::remember("omdb_{$torrent->imdbid}", 86400, function () use ($torrent) {
                return $torrent->imdbid ? (new TMDBService())->fetchOMDBData($torrent->imdbid) : null;
            });
    
            $steamData = Cache::remember("steam_{$torrent->steamid}", 86400, function () use ($torrent) {
                return $torrent->steamid ? (new TMDBService())->fetchSteamData($torrent->steamid) : null;
            });
    
          
            $similarTorrents = $torrent->tmdbid
                ? Torrent::where('id', '!=', $torrent->id)
                    ->where('tmdbid', $torrent->tmdbid)
                    ->where('seeders', '>', 0)
                    ->limit(5)
                    ->get()
                : collect(); // Default to empty collection if no TMDB ID
    
         
            // $recommendedTorrents = Torrent::where('category_id', $torrent->category_id)
            //     ->where('seeders', '>', 0)
            //     ->whereHas('genres', function ($query) use ($torrent) {
            //         $query->whereIn('genres.id', $torrent->genres->pluck('id'));
            //     })
            //     ->orWhere('name', 'like', '%' . preg_replace('/[^\w]+/', '', $torrent->name) . '%')
            //     ->select('id', 'slug', 'name', 'size', 'seeders', 'leechers', 'times_completed', 'poster')
            //     ->inRandomOrder()
            //     ->limit(6)
            //     ->get();
    
           
            $hasThanked = TorrentThank::where('torrent_id', $torrent->id)
                ->where('user_id', Auth::id())
                ->exists();
    
            
            $thankUserNames = TorrentThank::where('torrent_id', $torrent->id)
                ->pluck('user_id')
                ->toArray();
    
            $thankUserNames = User::whereIn('id', $thankUserNames)
                ->pluck('name')
                ->toArray();
    
           
            $fileTree = TorrentHelper::buildFileTree($torrent->files);
    
            return view('torrents.show', compact(
                'torrent', 'comments', 'tmdbData', 'omdbData', 'mediainfo', 'steamData', 'snatched',
                'similarTorrents',  'hasThanked', 'thankUserNames', 'fileTree'
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

    
    if ($torrent->owner == Auth::id()) {
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('error', 'You cannot thank your own torrent.');
    }

   
    if (TorrentThank::where('torrent_id', $torrent->id)->where('user_id', Auth::id())->exists()) {
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('error', 'You have already thanked this torrent.');
    }

  
$todayThanksCount = TorrentThank::where('user_id', $userId)
->whereDate('created_at', Carbon::today())
->count();

if ($todayThanksCount >= 10) {
return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
    ->with('error', 'You have reached your daily thank limit (10 per day).');
}


    if (TorrentThank::where('torrent_id', $torrent->id)->where('user_id', $userId)->exists()) {
        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('error', 'You have already thanked this torrent.');
    }


  
    TorrentThank::create([
        'torrent_id' => $torrent->id,
        'user_id' => Auth::id(),
    ]);

   
$user = Auth::user();
$user->seedbonus += 0.5; 
$user->save();

    
    return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
        ->with('success', 'Your thanks has been registered! You received 0.5 seedbonus points!');
}



   
    public function edit($id, $slug)
    {
      

        $torrent = Torrent::where('id', $id)->where('slug', $slug)->firstOrFail();

        
        $categories = Category::all(); 

      
        return view('torrents.edit', compact('torrent', 'categories'));
    }

    
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

           
            $torrent = Torrent::where('slug', $slug)->firstOrFail();

          
            $poster = $background = $tmdbId = $tmdbType = $imdbId = $steamData = null;
            $genreIds = [];

        

    if ($request->steamid) {
        $steamResponse = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $request->steamid,
            'lang' => 'en'
        ]);

        $steamData = $steamResponse->json();

      
        if (isset($steamData[$request->steamid]['success']) && $steamData[$request->steamid]['success']) {
            $gameDetails = $steamData[$request->steamid]['data'];

            $poster = $gameDetails['header_image'] ?? $request->poster; 
            $background = $gameDetails['background_raw'] ?? $request->background; 
        }
    }


           
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
                        $tmdbBaseUrlPoster = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2';
                        $tmdbBaseUrlBackdrop = 'https://image.tmdb.org/t/p/original';

                    $poster = $tmdbBaseUrlPoster . $tmdbData['poster_path'] ?? $poster ?? $request->poster;
                    $background = $tmdbBaseUrlBackdrop . $tmdbData['backdrop_path'] ?? $background ?? $request->background;
                     
                        $genres = $tmdbData['genres'] ?? [];
                        foreach ($genres as $genre) {
                            $genreRecord = Genre::firstOrCreate(['name' => $genre['name']]);
                            $genreIds[] = $genreRecord->id;
                        }

                        
                        $torrent->genres()->sync($genreIds);
                    }
                }
            }


         
           $manualGenres = $request->input('genre', ''); // Get manually entered genres as a string
           if (!empty($manualGenres)) {
              
               $manualGenresArray = preg_split('/[\/,]/', $manualGenres); // Split by either ',' or '/'

              
               foreach ($manualGenresArray as $genreName) {
                   $genreName = trim($genreName); 
                   if (!empty($genreName)) {
                       
                       $genreRecord = Genre::firstOrCreate(['name' => $genreName]);
                       $genreIds[] = $genreRecord->id;
                   }
               }
           }


           
            $torrent->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'poster' => $poster ?? $request->poster,
                'background' => $background ?? $request->background,
                'genre' => $request->genre, 
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
                'seedbox' => $request->has('seedbox') ? 1 : 0,
            ]);


if ($request->has('delete_images')) {
    foreach ($request->delete_images as $imageId) {
        
        $image = TorrentImage::where('id', $imageId)
            ->where('torrent_id', $torrent->id)
            ->first();

       
        \Log::info('Image record to delete:', ['image' => $image]);

        if ($image) {
            
            $relativePath = $image->path;  
            $filePath = storage_path('app/public/' . $relativePath);  

           
           

           
            if (file_exists($filePath)) {
                unlink($filePath); 
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




if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $path = $image->store('torrent_images', 'public'); 

        TorrentImage::create([
            'torrent_id' => $torrent->id,
            'path' => $path,
        ]);
    }
}

         
          //  Cache::flush();

          
            if (!empty($genreIds)) {
                $torrent->genres()->sync($genreIds); 
            }

            return redirect()->route('torrents.show', $torrent->id)->with('success', 'Your torrent has been updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('torrents.show', $torrent->id)->with('error', 'There was an error updating the torrent. Please try again.');
        }
    }




   
    public function destroy(Request $request, $slug)
    {
        
        $torrent = Torrent::where('slug', $slug)->firstOrFail();

     
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

        TorrentThank::where('torrent_id', $torrent->id)->delete();
      
        Peer::where('torrent_id', $torrent->id)->delete();

      
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
   
    $torrent = Torrent::findOrFail($torrentId);

    
    $seeders = null;
    $leechers = null;

    
if ($request->has('seeders')) {
    $seeders = Peer::where('torrent_id', $torrentId)
        ->where('seeder', 1)
        ->orderByRaw('user_id = ? DESC', [$request->user()->id]) // logged in user first
        ->orderByRaw('user_id = ? DESC', [$torrent->owner])   // then torrent owner
        ->paginate(50);

    
    $seeders->withPath(url()->current())->appends(['seeders' => '1']);
}


   
    if (request()->has('leechers')) {
        $leechers = Peer::where('torrent_id', $torrentId)->where('seeder', 0)->where('active', 1)->paginate(25);
       
        $leechers->withPath(url()->current())->appends(['leechers' => '1']);
    }

  
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
