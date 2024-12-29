@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Upload Application Details</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Application #{{ $uploadApp->id }}</h5>
        </div>
        <div class="card-body">
            <!-- Applicant ID -->
            <p><strong>Applicant:</strong> {{ $uploadApp->applicant->name }}</p>

            <!-- Staff ID -->
            <p><strong>Reviewed By:</strong> 
                {{ $uploadApp->staff ? $uploadApp->staff->name : 'Not Assigned Yet' }}
            </p>

            <!-- Why Promoted -->
            <p><strong>Why Should You Be Promoted?</strong></p>
            <p>{{ $uploadApp->why_promoted }}</p>

            <!-- Internal Speed -->
            <p><strong>Internal Speed:</strong> 
                @if($uploadApp->internal_speed)
                    <a href="{{ $uploadApp->internal_speed }}" target="_blank">View Link</a>
                @else
                    Not Provided
                @endif
            </p>

            <!-- External Speed -->
            <p><strong>External Speed:</strong> 
                @if($uploadApp->external_speed)
                    <a href="{{ $uploadApp->external_speed }}" target="_blank">View Link</a>
                @else
                    Not Provided
                @endif
            </p>

            <!-- External Sites -->
            <p><strong>External Sites:</strong></p>
            <p>{{ $uploadApp->external_sites ?? 'Not Provided' }}</p>

            <!-- Scene Access -->
            <p><strong>Scene Access:</strong> {{ $uploadApp->scene_access ? 'Yes' : 'No' }}</p>

            <!-- Know Torrents -->
            <p><strong>Knows How to Create Torrents:</strong> {{ $uploadApp->know_torrents ? 'Yes' : 'No' }}</p>

            <!-- Understand Seeding -->
            <p><strong>Understands Seeding Requirements:</strong> {{ $uploadApp->understand_seeding ? 'Yes' : 'No' }}</p>

            <!-- Status -->
            <p><strong>Status:</strong> 
                @if($uploadApp->status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                @elseif($uploadApp->status == 'accepted')
                    <span class="badge bg-success">Accepted</span>
                @else
                    <span class="badge bg-danger">Rejected</span>
                @endif
            </p>

            <!-- Created At -->
            <p><strong>Submitted On:</strong> {{ $uploadApp->created_at->format('d-m-Y H:i') }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('uploadapps.index') }}" class="btn btn-secondary">Back to Applications</a>
        </div>
    </div>
</div>


<hr>

    <!-- Status Update Form (Only for staff) -->
    @if(auth()->user()->user_class > 7) <!-- You can replace this with appropriate staff check -->
    <div class="container card mt-4">
            <div class="card-header">
                <h5 class="card-title">Update Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('uploadapps.updateStatus', $uploadApp->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="status">Select New Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="pending" {{ $uploadApp->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="accepted" {{ $uploadApp->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ $uploadApp->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Update Status</button>
                </form>
            </div>
        </div>
    @endif
@endsection
