<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketEvent;
use App\Models\Torrent;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | List tickets
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'ticket_id' => 'nullable|integer|min:1',
            'status' => ['nullable', Rule::in(Ticket::STATUSES)],
            'priority' => ['nullable', Rule::in(Ticket::PRIORITIES)],
            'category' => 'nullable|integer',
            'user' => 'nullable|string|max:255',
            'my' => 'nullable|boolean',
            'unassigned' => 'nullable|boolean',
        ]);
        $query = Ticket::query()->when(Auth::user()->user_class <= 5,
            fn ($query) => $query->where('user_id', Auth::id()));
        $counts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $stats = [
            'total' => $counts->sum(),
            'active' => $counts->only(['Open', 'Waiting Staff', 'Waiting User'])->sum(),
            'waiting' => $counts->get(Auth::user()->user_class > 5 ? 'Waiting Staff' : 'Waiting User', 0),
            'resolved' => $counts->only(['Resolved', 'Closed'])->sum(),
        ];
        foreach (['ticket_id' => 'id', 'status' => 'status', 'priority' => 'priority', 'category' => 'category_id'] as $input => $column) {
            if (! empty($filters[$input])) {
                $query->where($column, $filters[$input]);
            }
        }
        if (! empty($filters['keyword'])) {
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('description', 'like', '%'.$filters['keyword'].'%');
            });
        }
        if (Auth::user()->user_class > 5) {
            if (! empty($filters['user'])) {
                $query->whereHas('user', fn ($query) => $query->where('name', 'like', '%'.$filters['user'].'%'));
            }
            if ($request->boolean('unassigned')) {
                $query->unassigned();
            }
            if ($request->boolean('my')) {
                $query->ownedBy(Auth::id());
            }
        }
        $tickets = $query->with(['user', 'category', 'assignedStaff', 'claimedBy'])
            ->withCount(['responses' => fn ($query) => $query->visibleTo(Auth::user())])
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = TicketCategory::orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'categories', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | Show ticket
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $ticket = Ticket::with(['user', 'category', 'assignedStaff', 'claimedBy', 'events.user'])->findOrFail($id);
        abort_unless($ticket->canBeViewedBy(Auth::user()), 403);
        $ticket->load(['responses' => fn ($query) => $query->visibleTo(Auth::user())->with(['user', 'attachments'])->orderBy('id')]);
        $staffMembers = Auth::user()->user_class > 5
            ? User::where('user_class', '>', 5)->orderBy('name')->get(['id', 'name']) : collect();

        return view('tickets.show', compact('ticket', 'staffMembers'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create ticket
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $categories = TicketCategory::orderBy('name')->get();

        $context = $request->validate([
            'torrent_id' => 'nullable|integer|exists:torrents,id',
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::notIn([Auth::id()])],
        ]);
        $torrentId = $context['torrent_id'] ?? $request->old('linked_torrent_id');
        $userId = $context['user_id'] ?? $request->old('linked_user_id');
        $torrent = $torrentId ? Torrent::findOrFail($torrentId) : null;
        $reportedUser = ! $torrent && $userId ? User::findOrFail($userId) : null;
        $selectedCategoryId = $categories->firstWhere('name', $torrent ? 'Torrent Problem' : ($reportedUser ? 'User Report' : ''))?->id;

        return view('tickets.create', compact('categories', 'torrent', 'reportedUser', 'selectedCategoryId'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store ticket
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|integer|exists:ticket_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            'linked_torrent_id' => 'nullable|integer|exists:torrents,id',
            'linked_user_id' => ['nullable', 'integer', 'exists:users,id', Rule::notIn([Auth::id()])],
            'attachment' => 'nullable|array|max:5',
            'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,zip,rar,txt,log|max:10240',
        ]);
        $paths = [];
        try {
            $ticket = DB::transaction(function () use ($request, $data, &$paths) {
                $description = $data['description'];
                if (! empty($data['linked_torrent_id'])) {
                    $torrent = Torrent::findOrFail($data['linked_torrent_id']);
                    $description .= "\n\nReported torrent #{$torrent->id}: {$torrent->name}";
                }
                if (! empty($data['linked_user_id'])) {
                    $reportedUser = User::findOrFail($data['linked_user_id']);
                    $profileUrl = route('profile.show', ['id' => $reportedUser->id, 'name' => $reportedUser->name]);
                    $description .= "\n\nReported user #{$reportedUser->id}: {$reportedUser->name}\nProfile: {$profileUrl}";
                }
                $ticket = Ticket::create([
                    'user_id' => Auth::id(), 'category_id' => $data['category_id'],
                    'title' => $data['title'], 'description' => $description,
                    'priority' => $data['priority'], 'status' => 'Open',
                ]);
                $response = $ticket->responses()->create(['user_id' => Auth::id(), 'message' => $description]);
                foreach ($request->file('attachment', []) as $file) {
                    $path = $file->store('ticket_attachments/'.$ticket->id, 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to save the attachment.');
                    }
                    $paths[] = $path;
                    $response->attachments()->create(['file_path' => $path, 'file_name' => $file->getClientOriginalName()]);
                }
                TicketEvent::create(['ticket_id' => $ticket->id, 'user_id' => Auth::id(), 'event' => 'created the ticket']);
                foreach (User::where('user_class', '>', 5)->where('id', '!=', Auth::id())->pluck('id') as $staffId) {
                    SystemMessageService::send(Auth::id(), $staffId, 'New support ticket',
                        'A new support ticket has been created: '.$ticket->notificationLink());
                }

                return $ticket;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        return redirect()->route('tickets.show', ['id' => $ticket->id, 'slug' => $ticket->slug])
            ->with('success', 'Your ticket has been submitted. You can follow its progress here.');
    }

    public function lock($id)
    {
        return $this->setLocked($id, true);
    }

    public function unlock($id)
    {
        return $this->setLocked($id, false);
    }

    private function setLocked($id, bool $locked)
    {
        DB::transaction(function () use ($id, $locked) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            abort_unless($ticket->canBeViewedBy(Auth::user()), 403);
            abort_if(! $locked && in_array($ticket->status, ['Resolved', 'Closed'], true) && Auth::user()->user_class <= 5, 403, 'This ticket is resolved or closed. Please create a new ticket for further help.');
            if ($ticket->is_locked === $locked) {
                return;
            }
            $ticket->update(['is_locked' => $locked]);
            TicketEvent::create([
                'ticket_id' => $ticket->id, 'user_id' => Auth::id(),
                'event' => $locked ? 'locked the conversation' : 'unlocked the conversation',
            ]);
        });

        return back()->with('success', $locked ? 'Conversation locked.' : 'Conversation unlocked.');
    }
}
