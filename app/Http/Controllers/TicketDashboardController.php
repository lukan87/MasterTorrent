<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()->user_class > 5, 403);

            return $next($request);
        });
    }

    public function index()
    {

        $stats = [

            'open' => Ticket::where('status', 'Open')->count(),

            'waiting_staff' => Ticket::where('status', 'Waiting Staff')->count(),

            'waiting_user' => Ticket::where('status', 'Waiting User')->count(),

            'resolved' => Ticket::where('status', 'Resolved')->count(),

            'unassigned' => Ticket::unassigned()->count(),

            'my_tickets' => Ticket::ownedBy(Auth::id())->count(),

        ];

        $recentTickets = Ticket::with(['user', 'category', 'assignedStaff', 'claimedBy'])
            ->withCount('responses')->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        return view('tickets.dashboard', compact('stats', 'recentTickets'));

    }
}
