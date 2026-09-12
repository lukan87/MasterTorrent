<?php

namespace App\Http\Controllers;


use App\Helpers\Bencode;
use App\Models\Peer;
use App\Models\User;
use App\Models\Genre;
use App\Models\History;
use App\Models\Torrent;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Warning;
use App\Services\Torrent\TorrentBumpService;
use App\Services\Torrent\TorrentDestroyService;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Models\TorrentThank;
use App\Models\TorrentImage;
use App\Models\UserSlot;
use Carbon\Carbon;
use App\Models\HappyHour;
use App\Helpers\TorrentHelper;
use App\Services\TorrentDownloadService;
use App\Models\Seedbox;
use App\Services\SeedboxService;
use App\Services\Torrent\TorrentUploadService;
use App\Services\Torrent\TorrentUpdateService;
use App\Services\Torrent\MovieOfTheDayService;
use App\Services\Torrent\TorrentBrowseService;
use App\Services\Torrent\TorrentDisplayService;
use App\Services\Subtitle\SubsRoService;
use App\Services\TorrentSubscriptionService;







class TorrentController extends Controller
{

    private $apiKey;
    protected $service;

    public function __construct(TorrentDownloadService $service)
    {
        $this->apiKey = env('TMDB_API_KEY');
        $this->service = $service;
    }

// public function apiUpload(Request $request)
// {
//     $request->validate([
//         'name' => 'required|string',
//         'description' => 'required|string',
//         'torrent' => 'required|file',
//     ]);

//     $torrentPath = $request->file('torrent')->store('torrents');

//     // Example: Save torrent record
//     Torrent::create([
//         'user_id' => auth()->id(),
//         'name' => $request->name,
//         'description' => $request->description,
//         'torrent_file' => $torrentPath,
//     ]);

//     return response()->json([
//         'success' => true,
//         'message' => 'Torrent uploaded successfully'
//     ]);
// }

 public function index(Request $request)
{
    $categories = Category::all();
    $allGenres = Genre::all();

    $sortColumn = $request->get('sort', 'name');
    $sortDirection = $request->get('direction', 'desc');

  $user = $request->user();

$torrents = TorrentHelper::buildTorrentQuery($request, $sortColumn, $sortDirection);

if ($user) {
    $torrents->getCollection()->load([
        'histories' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }
    ]);
}
    $tz = $user->timezone ?? 'UTC';

    
 $torrents = $torrents->through(function ($torrent) use ($tz) {

    $torrent->created_at_local = $torrent->created_at->clone()->tz($tz);

    $history = $torrent->histories->first();

    /*
    |--------------------------------------------------------------------------
    | User Torrent Status
    |--------------------------------------------------------------------------
    */

    $torrent->has_downloaded = false;
    $torrent->is_seeding = false;

    if ($history) {

        // User downloaded torrent
        $torrent->has_downloaded = true;

        // User currently seeding
        $torrent->is_seeding =
            ($history->active ?? 0) == 1 ||
            ($history->seeder ?? 0) == 1;
    }

    return $torrent;
});
  
$newTorrents = app(TorrentBrowseService::class)
    ->getNewTorrents($user, $torrents, 'last_browse');


    $movieOfTheDay = app(MovieOfTheDayService::class)->get();


    $currentHappyHour = HappyHour::where('active', true)
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now())
        ->latest('start_at')
        ->first();

        $seedboxes = auth()->check()
    ? Seedbox::where('user_id', auth()->id())->get()
    : collect();

    return view('torrents.index', compact(
        'torrents',
        'categories',
        'allGenres',
        'sortColumn',
        'sortDirection',
        'newTorrents',
        'movieOfTheDay',
        'currentHappyHour',
        'seedboxes'
    ));
}




    
    
    

  public function adult(Request $request)
{
    $categories = Category::whereIn('id', [27, 34])->get();
    $allGenres = Genre::all();

    $sortColumn = $request->get('sort', 'name');
    $sortDirection = $request->get('direction', 'desc');

    $adult = TorrentHelper::buildAdultTorrentQuery($request, $sortColumn, $sortDirection);

    $user = $request->user();

    // Convert paginator to collection
    $adultCollection = $adult->getCollection();

    // New torrents based on last_browsex
   $newTorrents = app(TorrentBrowseService::class)
    ->getNewTorrents($user, $adult, 'last_browsex');


    $currentHappyHour = HappyHour::where('active', true)
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now())
        ->latest('start_at')
        ->first();

         $seedboxes = auth()->check()
    ? Seedbox::where('user_id', auth()->id())->get()
    : collect();

    return view('torrents.adult', compact(
        'adult',
        'categories',
        'allGenres',
        'sortColumn',
        'sortDirection',
        'newTorrents',
        'currentHappyHour',
        'seedboxes'
    ));
}


public function deleted(Request $request)
{
    if (auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403);
    }

    $sortColumn = $request->get('sort', 'deleted_at');
    $sortDirection = $request->get('direction', 'desc');

    $query = Torrent::onlyTrashed()
        ->with(['category', 'deletedBy', 'user']);

    // Search by name
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    // Filter by uploader
    if ($request->filled('uploader')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->uploader . '%');
        });
    }

    // Filter by category
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // Filter by deletion reason
    if ($request->filled('reason')) {
        $query->where('deletion_reason', 'like', '%' . $request->reason . '%');
    }

    $torrents = $query
        ->orderBy($sortColumn, $sortDirection)
        ->paginate(25)
        ->withQueryString();

    return view('torrents.deleted', compact('torrents', 'sortColumn', 'sortDirection'));
}

    
    public function create()
    {
        $user = Auth::user();
        $categories = Category::all(); 
        return view('torrents.upload', compact('user', 'categories')); // Pass categories to view
    }

   public function store(Request $request, TorrentUploadService $service)
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
        ->with('success', 'Your torrent has been uploaded successfully.');
}

    


    public function download(Request $request, $id, $slug)
    {
        try {
            return $this->service->handleDownload($request, $id, $slug);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

public function sendToSeedbox(Request $request, Torrent $torrent)
{
    try {
        $authUser = auth()->user();

        $request->validate([
            'seedbox_id' => 'required|exists:seedboxes,id',
        ]);

        $path = public_path('files/torrents/' . $torrent->file_name);

        if (!file_exists($path) || !is_readable($path)) {
            return redirect()->back()
                ->with('error', 'Torrent file not found.');
        }

        $dict = Bencode::bdecode(file_get_contents($path));

        if (!$dict) {
            return redirect()->back()
                ->with('error', 'Invalid torrent file.');
        }

        // FileIplay tracker
        $dict['announce'] =
            'https://tracker.fileiplay.org/announce/' . $authUser->passkey;

        $dict['comment'] =
            'Downloaded with lukan87\'s Seedbox Script For ' . config('app.name');

        $dict['created_by'] =
            config('app.name') . ' Seedbox Service';

        $fileToUpload = Bencode::bencode($dict);

        $tmpPath = storage_path(
            "app/tmp/seedbox__{$torrent->id}.torrent"
        );

        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }

        file_put_contents($tmpPath, $fileToUpload);

        $seedbox = Seedbox::where('user_id', $authUser->id)
            ->where('id', $request->seedbox_id)
            ->first();

        if (!$seedbox) {
            @unlink($tmpPath);

            return redirect()->back()
                ->with('error', 'Seedbox not found.');
        }

        $service = new SeedboxService(
            $seedbox->address,
            $seedbox->username,
            $seedbox->password,
            $seedbox->auth_type
        );

        $result = $service->addTorrentFileSeedBox($tmpPath);

        @unlink($tmpPath);

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error']);
        }

        return redirect()->back()
            ->with('success', "Torrent sent to {$seedbox->name}.");

    } catch (\Throwable $e) {

        return redirect()->back()
            ->with('error', $e->getMessage());
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

public function bump($id, TorrentBumpService $service)
{
    $torrent = Torrent::findOrFail($id);

    $service->bump(auth()->user(), $torrent);

    return redirect()
        ->route('torrents.index')
        ->with('success', 'Torrent bumped successfully.');
}


   
public function show(
    $id,
    $slug = null
)
    {
        try {
          
           $query = Torrent::with([
    'files',
    'images',
    'genres',
    'category',
    'subtitles',
    'deletedBy'
]);

// Allow moderators to see deleted torrents
if (auth()->check() && auth()->user()->user_class >= \App\Models\UserClass::MODERATOR) {
    $query = $query->withTrashed();
}

$torrent = $query->findOrFail($id);

$subsRoService = app(SubsRoService::class);
$externalSubtitles = [];

if ($torrent->imdbid) {

    $externalSubtitles = $subsRoService
        ->search($torrent->imdbid);
}

$fanartData = [];
$fanartBackground = null;
$fanartPoster = null;
$fanartLogo = null;
$fanartBanner = null;

if ($torrent->tmdbid) {
    $fanartData = app(\App\Services\FanartService::class)
        ->getMovieArt($torrent->tmdbid);

    $fanartBackground = $fanartData['moviebackground'][0]['url'] ?? null;
    $fanartPoster     = $fanartData['movieposter'][0]['url'] ?? null;
    $fanartLogo       = $fanartData['hdmovielogo'][0]['url'] ?? null;
    $fanartBanner     = $fanartData['moviebanner'][0]['url'] ?? null;
}

// Block normal users from viewing deleted torrents
if ($torrent->trashed() &&
    (!auth()->check() || auth()->user()->user_class < \App\Models\UserClass::MODERATOR)) {
    abort(404);
}
    
         
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

              
$displayData = app(TorrentDisplayService::class)
    ->getDisplayData($torrent);

extract($displayData);

          
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
    
           
$thankUsers = User::whereIn(
    'id',
    TorrentThank::where('torrent_id', $torrent->id)->pluck('user_id')
)->get(['id', 'name']);

$hasThanked = Auth::check()
    ? $thankUsers->contains('id', Auth::id())
    : false;

$thankCount = $thankUsers->count();

// Build tooltip text
$names = $thankUsers
    ->where('id', '!=', Auth::id())
    ->pluck('name')
    ->take(2)
    ->toArray();

if ($hasThanked) {
    array_unshift($names, 'You');
}

$remaining = $thankCount - count($names);

$thankTooltip = match (true) {
    $thankCount === 0 => 'No thanks yet',
    $remaining > 0   => implode(', ', $names) . " and {$remaining} others thanked",
    default          => implode(', ', $names) . ' thanked',
};

$subscriptionService = app(\App\Services\TorrentSubscriptionService::class);
$subscribeAvailable = $subscriptionService->canSubscribe($torrent);
$isSubscribed       = $subscribeAvailable && $subscriptionService->isSubscribed(Auth::user(), $torrent);



           
           
    
            return view('torrents.show', compact(
    'torrent',
    'comments',
    'display',
    'mediainfo',
    'steamData',
    'snatched',
    'similarTorrents',
    'hasThanked',
    'thankUsers',
    'thankTooltip',
    'thankCount',
    'fileTree',
    'fanartBackground',
    'fanartPoster',
    'fanartLogo',
    'fanartBanner',
    'externalSubtitles',
    'isSubscribed',
    'subscribeAvailable'

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

/*
    |--------------------------------------------------------------------------
    | Subscribe / Unsubscribe
    |--------------------------------------------------------------------------
    */

    public function subscribe($id, TorrentSubscriptionService $service)
    {
        $torrent = Torrent::findOrFail($id);
        $user = Auth::user();

        if (!$service->subscribe($user, $torrent)) {
            return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
                ->with('info', 'You are already subscribed to this title, or there is no IMDb/TMDB id to subscribe to.');
        }

        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('success', 'Subscribed! You will be notified when a new version of this title is uploaded.');
    }

    public function unsubscribe($id, TorrentSubscriptionService $service)
    {
        $torrent = Torrent::findOrFail($id);
        $user = Auth::user();

        if (!$service->unsubscribe($user, $torrent)) {
            return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
                ->with('info', 'You were not subscribed to this title.');
        }

        return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
            ->with('success', 'Subscription removed. You will no longer receive notifications for this title.');
    }


   
    public function edit($id, $slug)
    {
      

        $torrent = Torrent::where('id', $id)->where('slug', $slug)->firstOrFail();

        
        $categories = Category::all(); 

      
        return view('torrents.edit', compact('torrent', 'categories'));
    }

    
public function update(
    
    Request $request,
    string $slug,
    TorrentUpdateService $service
) {

     $torrent = $service->handle($request, $slug);

    return redirect()
        ->route('torrents.show', [$torrent->id, $torrent->slug])
        ->with('success', 'Your torrent has been updated successfully.');
}




   
public function destroy(Request $request, string $slug, TorrentDestroyService $destroyService)
{
    $torrent = Torrent::where('slug', $slug)->firstOrFail();

   
    if (auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
        abort(403);
    }

    $reason = $request->input('deletion_reason');

    if (!$reason) {
        $reason = '0 seeders and 0 leechers';
    } elseif ($reason === 'custom') {
        $reason = $request->input('custom_reason') ?: 'No reason provided';
    }

    $destroyService->handle(
        $torrent,
        auth()->id(),
        $reason
    );

    

    return redirect()
        ->route('torrents.index')
        ->with('success', 'Torrent deleted successfully.');
}


// public function restore($id)
// {
//     if (auth()->user()->user_class < \App\Models\UserClass::MODERATOR) {
//         abort(403);
//     }

//     $torrent = Torrent::onlyTrashed()->findOrFail($id);
//     $torrent->restore();

//     return redirect()
//         ->route('torrents.show', [$torrent->id, $torrent->slug])
//         ->with('success', 'Torrent restored successfully.');
// }

public function forceDelete($id)
{
    if (auth()->user()->user_class < \App\Models\UserClass::ADMIN) {
        abort(403);
    }

    $torrent = Torrent::onlyTrashed()->findOrFail($id);

    $torrent->purge(); // permanently removes from DB

    return redirect()
        ->route('torrents.index')
        ->with('success', 'Torrent and associated records permanently deleted.');
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
                'body' => 'Your torrent "' . $torrent->name . '" has been deleted  by ' . auth()->user()->name . '. Reason: ' . $deletionReason,
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
            if ($image instanceof TorrentImage) {
                $filePath = storage_path('app/public/' . $image->path);
                if (file_exists($filePath)) unlink($filePath);
                $image->delete();
            }
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
