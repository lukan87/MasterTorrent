@extends('layouts.app')

@section('content')
    <h1>Polls</h1>

    <!-- Check if there are any polls -->
    @if($polls->isEmpty())
        <p>No polls available.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Number of Votes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($polls as $poll)
                    <tr>
                        <td>{{ $poll->title }}</td>
                        <td>{{ Str::limit($poll->description, 50) }}</td>
                        <td>{{ $poll->votes_count }}</td>
                        <td>
                            <!-- View Poll or Vote link -->
                            <a href="{{ route('polls.show', $poll->id) }}" class="btn btn-info">View</a>

                            <!-- Only show edit and delete buttons for admins -->
                            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                <!-- Edit Button -->
                                <a href="{{ route('polls.edit', $poll->id) }}" class="btn btn-warning">Edit</a>

                                <!-- Delete Button -->
                                <form action="{{ route('polls.destroy', $poll->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this poll?')">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Create Poll button for admins -->
    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('polls.create') }}" class="btn btn-success">Create New Poll</a>
    @endif
@endsection
