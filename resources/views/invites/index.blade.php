@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 flex-grow-1">Your Invites</h4>
                <span class="badge bg-light text-dark ms-auto">Available: {{ $inviteCount }}</span>
            </div>
            
            <div class="card-body">
                <!-- Create Invite Code Button -->
                <form action="{{ route('invites.create') }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Create New Invite Code
                    </button>
                </form>

                @if($invites->isEmpty())
                    <div class="alert alert-info text-center">
                        You have not created any invites yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Invite Code</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Used By</th>
                                    <th>Is Expired</th>
                                    @if($invites->contains(function($invite) { return !$invite->is_used && !$invite->is_expired; }))
                                      <th>Actions</th> 
                                    @else
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invites as $invite)
                                    <tr>
                                        <td><code>{{ $invite->invite_code }}</code></td>
                                        <td>
                                            @if($invite->is_used)
                                                <span class="badge bg-success">Used</span>
                                            @else
                                                <span class="badge bg-secondary">Not Used</span>
                                            @endif
                                        </td>
                                        <td>{{ $invite->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($invite->is_used && $invite->usedBy) 
                                                <a href="{{ route('profile.show', $invite->usedBy->id) }}" class="text-decoration-none">
                                                    <i class="fa fa-user text-primary"></i> {{ $invite->usedBy->name }} ({{ $invite->usedBy->email }})
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Not Used</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($invite->is_expired)
                                                <span class="badge bg-danger">Yes</span>
                                            @else
                                                <span class="badge bg-success">No</span>
                                            @endif
                                        </td>
                                        <td>
                                           @if(!$invite->is_used && !$invite->is_expired) 
                                             <form action="{{ route('invites.delete', $invite->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this invite?');">
                                              @csrf
                                              @method('DELETE')
                                               <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete Invite">
                                                 <i class="bi bi-trash3-fill"></i>
                                               </button>
                                             </form>
                                           @else
                                          @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
