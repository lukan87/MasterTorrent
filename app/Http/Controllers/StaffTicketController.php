<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Message;
use App\Models\TicketEvent;
use App\Services\SystemMessageService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffTicketController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | Claim Ticket
    |--------------------------------------------------------------------------
    */

    public function claim($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $ticket->update([
            'claimed_by' => Auth::id()
        ]);

        TicketEvent::create([
    'ticket_id' => $ticket->id,
    'user_id' => Auth::id(),
    'event' => 'claimed the ticket'
]);

        return back()->with('success','Ticket claimed.');
    }

    /*
    |--------------------------------------------------------------------------
    | Assign Ticket
    |--------------------------------------------------------------------------
    */

public function assign(Request $request,$ticketId)
{
    $request->validate([
        'staff_id' => 'required|exists:users,id'
    ]);

    $ticket = Ticket::findOrFail($ticketId);

    $ticket->update([
        'assigned_to' => $request->staff_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Send notification via messaging service
    |--------------------------------------------------------------------------
    */

SystemMessageService::send(
    2,
    $request->staff_id,
    'Ticket assigned to you',
    $body
);

    /*
    |--------------------------------------------------------------------------
    | Ticket Event
    |--------------------------------------------------------------------------
    */

    TicketEvent::create([
        'ticket_id' => $ticket->id,
        'user_id' => Auth::id(),
        'event' => 'assigned the ticket'
    ]);

    return back()->with('success','Ticket assigned.');
}

    /*
    |--------------------------------------------------------------------------
    | Change Status
    |--------------------------------------------------------------------------
    */

    public function changeStatus(Request $request,$ticketId)
    {

        $request->validate([
            'status' => 'required'
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        $ticket->update([
            'status' => $request->status
        ]);

        return back()->with('success','Ticket status updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Lock Ticket
    |--------------------------------------------------------------------------
    */

    public function lock($ticketId)
    {

        $ticket = Ticket::findOrFail($ticketId);

        $ticket->update([
            'is_locked' => true
        ]);

        return back()->with('success','Ticket locked.');
    }

    /*
    |--------------------------------------------------------------------------
    | Staff Tickets
    |--------------------------------------------------------------------------
    */

public function myTickets()
{

    $tickets = Ticket::where('claimed_by',Auth::id())
        ->with(['user','category'])
        ->latest()
        ->paginate(20);

    $categories = TicketCategory::orderBy('name')->get();

    return view('tickets.index', [
        'tickets' => $tickets,
        'categories' => $categories
    ]);
}

public function unassigned()
{

    $tickets = Ticket::whereNull('claimed_by')
        ->with(['user','category'])
        ->latest()
        ->paginate(20);

    $categories = TicketCategory::orderBy('name')->get();

    return view('tickets.index', [
        'tickets' => $tickets,
        'categories' => $categories
    ]);

}

}