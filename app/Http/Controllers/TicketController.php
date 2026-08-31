<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Message;
use App\Models\Torrent;
use App\Models\User;
use App\Models\TicketResponse;
use App\Models\TicketAttachment;
use App\Models\TicketEvent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    $query = Ticket::with(['user','category','assignedStaff','claimedBy']);

    /*
    |--------------------------------------------------------------------------
    | Regular Users
    |--------------------------------------------------------------------------
    */

    if (Auth::user()->user_class <= 5) {

        $query->where('user_id', Auth::id());

    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    if ($request->ticket_id) {
        $query->where('id',$request->ticket_id);
    }

    if ($request->status) {
        $query->where('status',$request->status);
    }

    if ($request->priority) {
        $query->where('priority',$request->priority);
    }

    if ($request->category) {
        $query->where('category_id',$request->category);
    }

    if ($request->user) {
        $query->whereHas('user',function($q) use ($request){
            $q->where('name','LIKE','%'.$request->user.'%');
        });
    }

    if ($request->unassigned) {
        $query->whereNull('assigned_to');
    }

    if ($request->my) {
        $query->where('claimed_by',Auth::id());
    }

    $tickets = $query->latest()->paginate(20);

    $categories = TicketCategory::all();

    return view('tickets.index',compact('tickets','categories'));
}

    /*
    |--------------------------------------------------------------------------
    | Show ticket
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $ticket = Ticket::with([
    'user',
    'category',
    'responses.user',
    'responses.attachments',
    'events.user'
])->find($id);

    if (!$ticket) {
    return redirect()->route('tickets.index')->with('error','Ticket not found.');
}

if (Auth::user()->user_class <= 5 && $ticket->user_id != Auth::id()) {
    abort(403);
}

        return view('tickets.show', compact('ticket'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create ticket
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $categories = TicketCategory::all();

       $torrent = null;

    if ($request->torrent_id) {

        $torrent = Torrent::find($request->torrent_id);

    }

    

        return view('tickets.create', compact('categories', 'torrent'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store ticket
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
    'category_id' => 'required|exists:ticket_categories,id',
    'title' => 'required|max:255',
    'description' => 'required',
    'priority' => 'required',
    'attachment' => 'nullable|array',
    'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,zip,rar,txt,log|max:10240'
]);

$slug = Str::slug($request->title);

$ticket = Ticket::create([
    'user_id' => Auth::id(),
    'category_id' => $request->category_id,
    'title' => $request->title,
    'slug' => $slug,
    'description' => $request->description,
    'priority' => $request->priority,
    'status' => 'Open'
]);

/*
|--------------------------------------------------------------------------
| Create first ticket message
|--------------------------------------------------------------------------
*/

$response = TicketResponse::create([
    'ticket_id' => $ticket->id,
    'user_id' => Auth::id(),
    'message' => $request->description
]);

/*
|--------------------------------------------------------------------------
| Handle attachment
|--------------------------------------------------------------------------
*/

if ($request->hasFile('attachment')) {

    foreach ($request->file('attachment') as $file) {

       $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();

        $path = $file->storeAs(
            'ticket_attachments',
            $filename,
            'local'
        );

        TicketAttachment::create([
            'ticket_response_id' => $response->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName()
        ]);

    }

}

$staffMembers = User::where('user_class','>',5)->get();

foreach ($staffMembers as $staff) {

    /*
    |--------------------------------------------------------------------------
    | Find or create conversation
    |--------------------------------------------------------------------------
    */

    $conversation = Conversation::where(function ($q) use ($staff) {
        $q->where('user_one', Auth::id())
          ->where('user_two', $staff->id);
    })
    ->orWhere(function ($q) use ($staff) {
        $q->where('user_one', $staff->id)
          ->where('user_two', Auth::id());
    })
    ->first();

    if (!$conversation) {

        $conversation = Conversation::create([
            'user_one' => Auth::id(),
            'user_two' => $staff->id,
            'subject' => 'Support Tickets',
            'last_message_at' => now(),
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Create message
    |--------------------------------------------------------------------------
    */

    Message::create([
        'conversation_id' => $conversation->id,
        'receiver_id' => $staff->id,
        'sender_id' => Auth::id(),
        'subject' => 'New Support Ticket',
        'body' => 'A new ticket has been created:<br><a href="'.route('tickets.show', [
            'id' => $ticket->id,
            'slug' => $ticket->slug
        ]).'">'.$ticket->title.'</a>',
        'is_read' => false
    ]);

    /*
    |--------------------------------------------------------------------------
    | Update conversation timestamp
    |--------------------------------------------------------------------------
    */

    $conversation->update([
        'last_message_at' => now()
    ]);

}

TicketEvent::create([
    'ticket_id' => $ticket->id,
    'user_id' => Auth::id(),
    'event' => 'created the ticket'
]);

        return redirect()->route('tickets.show', [
    'id' => $ticket->id,
    'slug' => $ticket->slug
])
            ->with('success','Ticket created successfully.');
    }


public function lock($id)
{
    $ticket = Ticket::findOrFail($id);

    // Only staff OR ticket creator can unlock
    if(auth()->user()->user_class <= 5 && auth()->id() != $ticket->user_id){
        abort(403);
    }

    // toggle lock
    $ticket->is_locked = !$ticket->is_locked;
    $ticket->save();

    TicketEvent::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'event' => $ticket->is_locked ? 'locked the ticket' : 'unlocked the ticket'
    ]);

    return back();
}

    public function unlock($id)
{
    $ticket = Ticket::findOrFail($id);

    // allow creator or staff
    if(auth()->id() != $ticket->user_id && auth()->user()->user_class <= 5){
        abort(403);
    }

    $ticket->update([
        'is_locked' => 0
    ]);

    TicketEvent::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'event' => 'unlocked the ticket'
    ]);

    return back();
}

}