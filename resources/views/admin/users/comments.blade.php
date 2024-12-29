@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">User Comments</h1>

    <!-- Display Comments Section -->
    @if($comments->isEmpty())
        <div class="alert alert-info" role="alert">
            No comments found.
        </div>
    @else
        <div class="list-group">
            @foreach($comments as $comment)
                <div class="list-group-item d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                        @if ($comment->user)
    <a href="{{ route('profile.show', ['id' => $comment->user->id, 'name' => $comment->user->name]) }}"
       style="color: {{ \App\Models\UserClass::getClassColor($comment->user->user_class) }}" 
       data-bs-toggle="tooltip" 
       title="{{ \App\Models\UserClass::getClassName($comment->user->user_class) }}">
        {{ $comment->user->name }}
    </a>
@else
    <span class="text-muted">[Deleted User]</span>
@endif @  <small class="text-info">
            Commented on:
            @if($comment->torrent)
                <strong><a href="{{ route('torrents.show', $comment->torrent->id) }}">
                        Torrent: {{ $comment->torrent->name }}
                    </a></strong>
            @else
                <strong>Unknown Torrent</strong>
            @endif
        </small>

                        </h5>
                        <p class="mb-1">{{ $comment->comment }}</p>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Pagination links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $comments->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection


