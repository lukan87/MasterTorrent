<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketDashboardController extends Controller
{

    public function index()
    {

        $stats = [

            'open' => Ticket::where('status','Open')->count(),

            'waiting_staff' => Ticket::where('status','Waiting Staff')->count(),

            'waiting_user' => Ticket::where('status','Waiting User')->count(),

            'resolved' => Ticket::where('status','Resolved')->count(),

            'unassigned' => Ticket::whereNull('claimed_by')->count(),

            'my_tickets' => Ticket::where('claimed_by',Auth::id())->count()

        ];

        $recentTickets = Ticket::with(['user','category'])
            ->latest()
            ->limit(15)
            ->get();

        return view('tickets.dashboard',compact('stats','recentTickets'));

    }

}