<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AnnouncementService;
use App\Models\Announcement;
use Illuminate\Support\Facades\Cache;


class AnnouncementController extends Controller
{
    protected $service;

    public function __construct(AnnouncementService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

public function index()
{
    if (auth()->user()->user_class > 8) {
        // Staff: see everything (including trashed)
        $announcements = Announcement::withTrashed()
            ->with('author')
            ->orderByDesc('priority')
            ->latest()
            ->get();
    } else {
        // Normal users: only active (cached)
        $announcements = $this->service->getActive();
    }

    return view('announcements.index', compact('announcements'));
}

    public function create()
{
    if (auth()->user()->user_class <= 8) {
        abort(403);
    }

    return view('announcements.create');
}

public function show($id)
{
    $announcement = Announcement::with(['users']) // 👈 important
    ->with(['users' => function ($q) {
    $q->whereNotNull('announcement_user.seen_at');
}])
        ->findOrFail($id);

    $this->service->markAsSeen(auth()->user(), $id);

    return view('announcements.show', compact('announcement'));
}

public function unreadCount()
{
    if (!auth()->check()) {
        return response()->json(['count' => 0]);
    }

    $count = $this->service->getUnreadCount(auth()->user());

    return response()->json([
        'count' => $count > 9 ? '9+' : $count
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {

       abort_if(auth()->user()->user_class <= 8, 403);


        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string',
            'priority' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $this->service->create($data, auth()->user());

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created');
    }

    
public function edit($id)
{
    abort_if(auth()->user()->user_class <= 8, 403);

    $announcement = Announcement::withTrashed()->findOrFail($id);

    return view('announcements.edit', compact('announcement'));
}

public function update(Request $request, $id)
{
    abort_if(auth()->user()->user_class <= 8, 403);

    $announcement = Announcement::withTrashed()->findOrFail($id);

    $data = $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'type' => 'nullable|string',
        'priority' => 'nullable|integer',
    ]);

    $announcement->update($data);

    \Cache::forget('announcements.active');

    return response()->json([
        'success' => true
    ]);
}



public function destroy($id)
{
    abort_if(auth()->user()->user_class <= 8, 403);

    $announcement = Announcement::findOrFail($id);
    $announcement->delete(); // soft delete

    Cache::forget('announcements.active');

    return response()->json([
        'success' => true
    ]);
}

public function restore($id)
{
    abort_if(auth()->user()->user_class <= 8, 403);

    $announcement = Announcement::withTrashed()->findOrFail($id);
    $announcement->restore();

    Cache::forget('announcements.active');

    return response()->json([
        'success' => true
    ]);
}

public function forceDelete($id)
{
    abort_if(auth()->user()->user_class <= 8, 403);

    $announcement = Announcement::withTrashed()->findOrFail($id);
    $announcement->forceDelete();

    Cache::forget('announcements.active');

    return redirect()->back()->with('success', 'Permanently deleted');
}

}