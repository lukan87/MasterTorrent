@extends('layouts.app')

@section('content')
<div class="mt-4">
    <!-- Forum Header Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="display-6 mb-0">{{ $forum->name }}</h1>
        </div>
        <div class="card-body">
            <p class="lead mb-0">{{ $forum->description }}</p>
        </div>
    </div>

    <!-- Create New Topic Button -->
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('topics.create', $forum->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create New Topic
        </a>
    </div>
    @endif

    <!-- Topics Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">Topics</h2>
        </div>
        <div class="card-body">
            @if($forum->topics->count() > 0)
                <ul class="list-group">
                    @foreach($forum->topics as $topic)
                        @php
                            $lastPost = $topic->posts->sortByDesc('created_at')->first();
                        @endphp
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('topics.show', [$forum->id, $topic->id]) }}" class="text-decoration-none">
                                        <strong>{{ $topic->title }}</strong>
                                    </a>
                                    -
                                    <small class="text-muted">
                                        Created {{ $topic->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="badge bg-primary">{{ $topic->posts->count() }} Posts</span>
                            </div>
                            @if($lastPost)
                            <div class="mt-2">
                                <small class="text-muted">
                                    Last post by 
                                    <strong>{{ $lastPost->user->name }}</strong> 
                                    {{ $lastPost->created_at->diffForHumans() }}
                                </small>
                            </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted text-center">No topics available in this forum.</p>
            @endif
        </div>
    </div>
</div>
@endsection
