@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    @if(Auth::user()->user_class > 5)
        <h2>All Tickets</h2>
    @else
        <h2>My Tickets</h2>
    @endif

    <!-- Create Ticket Button visible to everyone -->
    <a href="{{ route('tickets.create') }}" class="btn btn-success mb-3">Create a Ticket</a>

    @if($tickets->isEmpty())
        <p>You have not created any tickets yet.</p>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create a Ticket</a>
    @else
        @if(Auth::user()->user_class > 5)  <!-- Only show filters for users with class > 5 (staff) -->
            <form method="GET" action="{{ route('tickets.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-control" name="status">
                            <option value="">Filter by Status</option>
                            <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-control" name="priority">
                            <option value="">Filter by Priority</option>
                            <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                            <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-control" name="category">
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
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        @endif

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    @if(Auth::user()->user_class > 5) <!-- Only show "Creator" for staff -->
                        <th>Creator</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->title }}</td>

                    <!-- Apply status color and bold/italic font based on the status -->
                    <td class="
                        @if($ticket->status == 'Open') 
                            bg-info text-white font-weight-bold 
                        @elseif($ticket->status == 'In Progress') 
                            bg-warning text-dark font-italic 
                        @elseif($ticket->status == 'Resolved') 
                            bg-success text-white font-weight-bold 
                        @endif
                    ">
                        {{ $ticket->status }}
                    </td>

                    <!-- Apply priority color -->
                    <td class="
                        @if($ticket->priority == 'Low') 
                            bg-light text-dark font-weight-bold 
                        @elseif($ticket->priority == 'Medium') 
                            bg-warning text-dark font-weight-bold 
                        @elseif($ticket->priority == 'High') 
                            bg-danger text-white font-weight-bold 
                        @endif
                    ">
                        {{ $ticket->priority }}
                    </td>

                    <td>{{ $ticket->category }}</td>

                    @if(Auth::user()->user_class > 5) <!-- Only show creator if user class > 5 -->
                        <td>{{ $ticket->user->name }}</td> <!-- Assuming 'user' relationship is set in Ticket model -->
                    @endif

                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View Ticket"><i class="fa fa-eye" aria-hidden="true"></i></a>

                        @if(Auth::user()->user_class > 5)  <!-- Only show delete button for staff members -->
                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this ticket?')" data-bs-toggle="tooltip" title="Delete Ticket"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
