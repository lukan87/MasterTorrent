@extends('layouts.app')

@section('content')
<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-6">
            <h2>Ticket: {{ $ticket->title }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('tickets.index') }}" class="btn btn-primary">Back to Tickets</a>
        </div>
    </div>
  
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title">Ticket Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Category:</strong> {{ $ticket->category }}</p>
                    <p><strong>Priority:</strong> {{ $ticket->priority }}</p>
                    <p><strong>Status:</strong> {{ $ticket->status }}</p>
                    <p><strong>Description:</strong> {{ $ticket->description }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title">Responses</h5>
                </div>
                <div class="card-body">
                    @foreach($ticket->responses->reverse() as $response)
                    <div class="card mb-2">
                        <div class="card-body">
                            <p><strong>{{ $response->user->name }} ({{ $response->created_at->diffForHumans() }})</strong></p>
                            <p>{{ $response->message }}</p>
                
                            @if(Auth::id() == $response->user_id || Auth::user()->user_class > 5)
                                <div class="btn-group" role="group">
                                    <!-- Edit Button (GET request) -->
                                    <a href="{{ route('tickets.editResponse', ['ticket' => $ticket->id, 'response' => $response->id]) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                
                                    <!-- Delete Button (POST request) -->
                                    <form action="{{ route('tickets.deleteResponse', ['ticket' => $ticket->id, 'response' => $response->id]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this response?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                
                
        
                    @if(Auth::user()->user_class > 5 || $ticket->user_id == Auth::id())
                        <div class="mt-3">
                            <form method="POST" action="{{ route('tickets.storeResponse', $ticket->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="message">Add a Response</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-secondary mt-3">Submit Response</button>
                            </form>
                        </div>
                    @endif
        
                    @if(Auth::user()->user_class > 5)  <!-- Only show buttons if staff -->
                        <div class="mt-3">
                            <!-- Update Status Form -->
                            <form action="{{ route('tickets.updateStatus', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="status">Update Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                                        <option value="In Progress" {{ $ticket->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Update Status</button>
                            </form>
        
                            <!-- Close Ticket Form -->
                            {{-- <form action="{{ route('tickets.closeTicket', $ticket->id) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-danger">Close Ticket</button>
                            </form> --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
   
</div>
@endsection
