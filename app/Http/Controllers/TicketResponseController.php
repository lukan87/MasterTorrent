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

         // Check if the ticket status is "Resolved" and if the user who created it is responding
    if ($ticket->status == 'Resolved' && $ticket->user_id == Auth::id()) {
        // Change the ticket status back to "Open"
        $ticket->status = 'Open';
        $ticket->save();
    }

        TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Notify the user here (optional)

      // Check if the user responding is a staff member (user_class > 5)
      if (Auth::user()->user_class > 5) {
        // Generate the URL for the ticket
        $ticketUrl = route('tickets.show', $ticket->id);

        // Send a notification to the user who created the ticket
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
