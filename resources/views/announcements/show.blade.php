@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 1200px;">

    <div class="card glass shadow-sm border-0">
        <div class="card-body p-4">

            {{-- Title --}}
            <h2 class="fw-bold mb-3">
                {{ $announcement->title }}
            </h2>

            {{-- Meta --}}
            <div class="d-flex align-items-center text-muted small mb-3">
                <span class="me-3">👤 {{ $announcement->author->name ?? 'System' }}</span>
                <span>⏱ {{ $announcement->created_at->toDayDateTimeString() }}</span>
            </div>

            {{-- Divider --}}
            <hr class="my-3">

            {{-- Body --}}
            <div class="announcement-body">
                {!! convertCustomTagsToHtml($announcement->body) !!}
            </div>

            <hr class="mt-5">

@auth
@if(auth()->user()->user_class >= 6)

<div class="mt-4">

    <h6 class="fw-bold">
        👁️ {{ $announcement->users->count() }} users saw this announcement
    </h6>

    @if($announcement->users->count() > 0)
        <div class="mt-2 text-muted">

            @foreach($announcement->users as $user)
                <span class="me-2">
                    {{ $user->name }}@if(!$loop->last) • @endif
                </span>
            @endforeach

        </div>
    @else
        <p class="text-muted">No one has seen this yet.</p>
    @endif

</div>

@endif
@endauth

            {{-- Back button --}}
            <div class="mt-4">
                <a href="{{ route('announcements.index') }}" class="btn btn-light">
                    ← Back to announcements
                </a>
            </div>

        </div>
    </div>

</div>

{{-- Styling --}}
<style>
.announcement-body {
    font-size: 1.30rem;
    line-height: 1.7;
}

.card {
    border-radius: 14px;
}

.btn-light {
    border-radius: 8px;
}



.announcement-body img {
    max-width: 100%;
    border-radius: 10px;
    margin: 10px 0;
}

.announcement-body blockquote {
    border-left: 4px solid #999;
    padding-left: 12px;
    color: #ccc;
    margin: 10px 0;
}

.announcement-body a {
    color: #0d6efd;
    text-decoration: underline;
}
</style>

@endsection