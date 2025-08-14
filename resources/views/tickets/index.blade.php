@extends('layouts.app')

@section('content')
<div class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            @if(Auth::user()->user_class > 5)
                All Tickets
            @else
                My Tickets
            @endif
        </h2>
        <a href="{{ route('tickets.create') }}" class="btn btn-success">
            <i class="fa fa-plus"></i> Create a Ticket
        </a>
    </div>

    @if($tickets->isEmpty())
    @if(Auth::user()->user_class > 5)
    <div class="alert alert-warning text-center">No tickets found.</div>
            @else
            <div class="alert alert-warning text-center">You didn't submit any support ticket</div>
            @endif
       
    @else
        @if(Auth::user()->user_class > 5)  <!-- Staff Filters -->
            <form method="GET" action="{{ route('tickets.index') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Filter by Status</option>
                            <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="priority">
                            <option value="">Filter by Priority</option>
                            <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                            <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">Filter by Category</option>
                            <option value="Technical Issue" {{ request('category') == 'Technical Issue' ? 'selected' : '' }}>Technical Issue</option>
                            <option value="Account Problem" {{ request('category') == 'Account Problem' ? 'selected' : '' }}>Account Problem</option>
                            <option value="Download/Upload Problem" {{ request('category') == 'Download/Upload Problem' ? 'selected' : '' }}>Download/Upload Problem</option>
                            <option value="Torrents" {{ request('category') == 'Torrents' ? 'selected' : '' }}>Torrents</option>
                            <option value="Users" {{ request('category') == 'Users' ? 'selected' : '' }}>Users</option>
                            <option value="Other" {{ request('category') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Category</th>
                            @if(Auth::user()->user_class > 5)
                                <th>Creator</th>
                                <th>Last Reply</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td class="fw-bold">{{ $ticket->title }}</td>

                            <!-- Status Badge -->
                            <td>
                                <span class="badge 
                                    @if($ticket->status == 'Open') bg-info 
                                    @elseif($ticket->status == 'In Progress') bg-warning text-dark 
                                    @elseif($ticket->status == 'Resolved') bg-success 
                                    @endif
                                ">
                                    {{ $ticket->status }}
                                </span>
                            </td>

                            <!-- Priority Badge -->
                            <td>
                                <span class="badge 
                                    @if($ticket->priority == 'Low') bg-light text-dark 
                                    @elseif($ticket->priority == 'Medium') bg-warning text-dark 
                                    @elseif($ticket->priority == 'High') bg-danger 
                                    @endif
                                ">
                                    {{ $ticket->priority }}
                                </span>
                            </td>

                            <td>{{ $ticket->category }}</td>

                            @if(Auth::user()->user_class > 5)
                                <td><a href="{{ route('profile.show', ['id' => $ticket->user->id]) }}"
                                    class="fw-bold" style="color: {{ \App\Models\UserClass::getClassColor($ticket->user_class ?? '') }}">
                                     {{ $ticket->user->name ?? 'Unknown' }}
                                 </a></td>
                                <td>
                                    @if($ticket->last_replied_at)
                                        <span class="badge bg-success">
                                            Last reply by <strong>{{ $ticket->lastReplier->name }}</strong> 
                                            ({{ $ticket->last_replied_at->diffForHumans() }})
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">No replies yet</span>
                                    @endif
                                </td>
                            @endif

                            

                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View Ticket">
                                    <i class="fa fa-eye"></i>
                                </a>

                                @if(Auth::user()->user_class > 5)
                                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" data-bs-toggle="tooltip" title="Delete Ticket">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
