
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Upload Applications</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(auth()->user()->user_class > 5)
        <p class="text-success">You have administrative privileges to view all applications.</p>
    @else
        <p class="text-info">You are viewing your own applications.</p>
    @endif

    @if($uploadApps->isEmpty())
        <p>No applications found.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Applicant</th>
                    <th>Internal Speed</th>
                    <th>External Speed</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uploadApps as $app)
                    <tr>
                        <td>{{ $app->id }}</td>
                        <td>{{ $app->applicant->name ?? 'Unknown' }}</td>
                        <td>
                            @if($app->internal_speed)
                                <a href="{{ $app->internal_speed }}" target="_blank">Link</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($app->external_speed)
                                <a href="{{ $app->external_speed }}" target="_blank">Link</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($app->status === 'pending') bg-warning 
                                @elseif($app->status === 'accepted') bg-success 
                                @else bg-danger 
                                @endif">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('uploadapps.show', $app->id) }}" class="btn btn-info btn-sm">View</a>
                            @if(auth()->user()->user_class > 6)
                                <!-- <a href="{{ route('uploadapps.edit', $app->id) }}" class="btn btn-warning btn-sm">Edit</a> -->
                                <!-- Delete Button -->
                                <form action="{{ route('uploadapps.destroy', $app->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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

