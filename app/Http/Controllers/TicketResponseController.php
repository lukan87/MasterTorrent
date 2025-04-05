<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketResponseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Add a response to a ticket
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
    
        $ticket = Ticket::findOrFail($ticketId);
    
        // If the ticket status is "Resolved" and the user who created it is responding, reopen it
        if ($ticket->status == 'Resolved' && $ticket->user_id == Auth::id()) {
            $ticket->status = 'Open';
            $ticket->save();
        }
    
        // Create the ticket response
        TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);
    
        // Update last reply details in the ticket
        $ticket->update([
            'last_replied_at' => now(),
            'last_replier_id' => Auth::id(),
        ]);
    
        // Notify the ticket owner if a staff member responds
        if (Auth::user()->user_class > 5) {
            $ticketUrl = route('tickets.show', $ticket->id);
    
            Message::create([
                'receiver_id' => $ticket->user_id,  // The user who opened the ticket
                'subject' => 'Ticket Response: ' . $ticket->title,
                'sender_id' => Auth::id(),  // The staff member responding
                'body' => 'A staff member has responded to your ticket: "' . $ticket->title . '". ' .
                          'Please check your ticket for the response. You can view it by clicking the link below:<br>' .
                          '<a href="' . $ticketUrl . '" target="_blank">View Your Ticket</a>',
                'is_read' => false,  // Mark as unread initially
            ]);
        }
    
        return redirect()->route('tickets.show', $ticket->id);
    }
    
}
