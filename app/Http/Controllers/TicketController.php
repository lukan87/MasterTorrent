<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Create a new ticket
    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'Open',
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Notify all staff members (user_class > 5)
    $staffMembers = User::where('user_class', '>', 5)->get();

    foreach ($staffMembers as $staff) {
        // Generate the URL for the ticket
        $ticketUrl = route('tickets.show', $ticket->id);

        // Send a notification to the staff member
        Message::create([
            'receiver_id' => $staff->id,  // The staff member receiving the message
            'subject' => 'New Ticket Created: ' . $ticket->title,
            'sender_id' => Auth::id(),  // The user who created the ticket
            'body' => 'A new ticket has been created: "' . $ticket->title . '". ' .
                      'Please review the ticket. You can view it by clicking the link below:<br>' .
                      '<a href="' . $ticketUrl . '" target="_blank">View the Ticket</a>',
            'is_read' => false,  // Mark as unread initially
        ]);
    }

        return redirect()->route('tickets.show', $ticket->id);
    }

    // View a single ticket
    public function show($id)
    {
        $ticket = Ticket::with('responses')->findOrFail($id);
        return view('tickets.show', compact('ticket'));
    }

    // List tickets with filtering
    public function index(Request $request)
    {
        if (Auth::user()->user_class > 5) {
            $tickets = Ticket::when($request->status, function($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->priority, function($query, $priority) {
                    return $query->where('priority', $priority);
                })
                ->when($request->category, function($query, $category) {
                    return $query->where('category', $category);
                })
                ->get();
        } else {
            // For regular users, show only their own tickets
            $tickets = Ticket::where('user_id', Auth::id())
                ->when($request->status, function($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->priority, function($query, $priority) {
                    return $query->where('priority', $priority);
                })
                ->when($request->category, function($query, $category) {
                    return $query->where('category', $category);
                })
                ->get();
        }
    
        return view('tickets.index', compact('tickets'));
    }
    
    

    // Change ticket status (only for staff)
    public function updateStatus(Request $request, $ticketId)
{
    $ticket = Ticket::find($ticketId);

    if (!$ticket) {
        return redirect()->back()->with('error', 'Ticket not found.');
    }

    // Valid status values matching the ENUM case in the database
    $validStatuses = ['Open', 'In Progress', 'Resolved'];

    // Check if the provided status is valid
    if (!in_array($request->status, $validStatuses)) {
        return redirect()->back()->with('error', 'Invalid status selected.');
    }

    // Update the status and save the ticket
    $ticket->status = $request->status;
    $ticket->save();

    // Send message to the creator of the ticket
    Message::create([
        'receiver_id' => $ticket->user_id, // The user who created the ticket
        'subject' => 'Ticket Response: ' . $ticket->title,
        'sender_id' => Auth::id(), // The user who changed the status (staff)
        'body' => "The status of your ticket \"{$ticket->title}\" has been changed to '{$ticket->status}'."
    ]);

    return redirect()->route('tickets.show', $ticketId)->with('success', 'Ticket status updated successfully.');
}

    

    public function closeTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Check if the authenticated user has the required user_class (staff user_class > 5)
        if (Auth::user()->user_class <= 5) {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('error', 'You do not have permission to close this ticket.');
        }

        // Logic to close the ticket
        $ticket->status = 'closed';
        $ticket->save();

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket closed successfully!');
    }

    // Destroy the ticket (delete a ticket)
    public function destroy($ticketId)
    {
        // Find the ticket by ID
        $ticket = Ticket::findOrFail($ticketId);

        // Check if the user is a staff member (user_class > 5)
        if (Auth::user()->user_class <= 5) {
            return redirect()->route('tickets.index')->with('error', 'You do not have permission to delete tickets.');
        }

        // Delete the ticket
        $ticket->delete();

        // Redirect back with a success message
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    public function editResponse($ticketId, $responseId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $response = $ticket->responses()->findOrFail($responseId);
    
        return view('tickets.editResponse', compact('ticket', 'response'));
    }

    public function updateResponse(Request $request, $ticketId, $responseId)
{
    $ticket = Ticket::findOrFail($ticketId);
    $response = $ticket->responses()->findOrFail($responseId);

    // Validate and update the response
    $request->validate([
        'message' => 'required|string|max:255',
    ]);

    $response->update([
        'message' => $request->message,
    ]);

    return redirect()->route('tickets.show', $ticket->id)->with('success', 'Response updated successfully!');
}

    

public function deleteResponse($ticketId, $responseId)
{
    // Logic to delete a response
    $response = TicketResponse::findOrFail($responseId);
    $response->delete();

    return back()->with('success', 'Response deleted successfully.');
}


}
