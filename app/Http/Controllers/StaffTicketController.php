<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()->user_class > 5, 403);

            return $next($request);
        });
    }

    public function claim($ticketId)
    {
        DB::transaction(function () use ($ticketId) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticketId);
            abort_if(($ticket->claimed_by && $ticket->claimed_by != Auth::id()) ||
                ($ticket->assigned_to && $ticket->assigned_to != Auth::id()), 409, 'This ticket already has an owner. Use assignment to transfer it.');
            if ($ticket->claimed_by == Auth::id()) {
                return;
            }
            $ticket->update(['claimed_by' => Auth::id()]);
            $this->record($ticket, 'claimed the ticket');
        });

        return back()->with('success', 'Ticket claimed.');
    }

    public function assign(Request $request, $ticketId)
    {
        $request->validate(['staff_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('user_class', '>', 5)->whereNull('deleted_at'))]]);
        $staff = User::findOrFail($request->integer('staff_id'));
        DB::transaction(function () use ($staff, $ticketId) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticketId);
            $ticket->update(['assigned_to' => $staff->id, 'claimed_by' => null]);
            $this->record($ticket, 'assigned the ticket to '.$staff->name);
            SystemMessageService::send(Auth::id(), $staff->id, 'Ticket assigned to you',
                'A support ticket has been assigned to you: '.$ticket->notificationLink());
        });

        return back()->with('success', 'Ticket assigned.');
    }

    public function changeStatus(Request $request, $ticketId)
    {
        $request->validate(['status' => ['required', Rule::in(Ticket::STATUSES)]]);
        DB::transaction(function () use ($request, $ticketId) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticketId);
            if ($ticket->status === $request->status) {
                return;
            }
            $previous = $ticket->status;
            $ticket->update(['status' => $request->status]);
            $this->record($ticket, 'changed status from '.$previous.' to '.$ticket->status);
        });

        return back()->with('success', 'Ticket status updated.');
    }

    public function myTickets()
    {
        return redirect()->route('tickets.index', ['my' => 1]);
    }

    public function unassigned()
    {
        return redirect()->route('tickets.index', ['unassigned' => 1]);
    }

    private function record(Ticket $ticket, string $event): void
    {
        TicketEvent::create(['ticket_id' => $ticket->id, 'user_id' => Auth::id(), 'event' => $event]);
    }
}
