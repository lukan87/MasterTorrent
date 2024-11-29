@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">News</h1>
        <a href="{{ route('news.create') }}" class="btn btn-primary">+ Create News</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="list-group">
        @forelse($newsItems as $news)
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <a href="{{ route('news.show', $news) }}" class="text-decoration-none">{{ $news->title }}</a>
                    </h5>
                    <small class="text-muted">
                      Posted on {{ $news->created_at->format('F j, Y') }} by {{ $news->user->name }}
                    </small>

                </div>
                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <div class="btn-group" role="group" aria-label="Action Buttons">
                    <a href="{{ route('news.edit', $news) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('news.destroy', $news) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="alert alert-info">
                No news articles available. Be the first to <a href="{{ route('news.create') }}" class="alert-link">create one</a>.
            </div>
        @endforelse
    </div>
</div>
@endsection
