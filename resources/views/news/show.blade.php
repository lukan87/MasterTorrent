@extends('layouts.app')

@section('content')
<div class="container my-5">
    <!-- News Title -->
    <h1 class="display-4 mb-4">{{ $news->title }}</h1>

    <!-- News Content -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p>{!! convertCustomTagsToHtml($news->content ) !!}</p>
        </div>
    </div>

    <!-- Posted Info -->
    <p class="text-muted">
        <small>
            <strong>Posted by:</strong> {{ $news->user->name }} <br>
            <strong>On:</strong> {{ $news->created_at->format('F d, Y') }}
        </small>
    </p>

    <!-- Back to News Button -->
    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
    <a href="{{ route('news.index') }}" class="btn btn-primary mt-4">
        <i class="bi bi-arrow-left-circle"></i> Back to News
    </a>
    @endif
    <a href="{{ route('news.index') }}" class="btn btn-secondary mt-4">
        <i class="bi bi-arrow-left-circle"></i> Home
    </a>
</div>
@endsection
