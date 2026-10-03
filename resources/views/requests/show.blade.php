@extends('layouts.app')
@section('content')
@include('requests.partials.styles')
<div class="rq">
    <a class="d-inline-block mb-3 rq-muted" href="{{ route('requests.index') }}">← All requests</a>
    @include('requests.partials.feedback')
    <header class="rq-panel rq-hero">
        <div><div class="rq-kicker">{{ $request->category?->name ?? 'Community request' }} · Request #{{ $request->id }}</div><h1>{{ $request->name }}</h1>@include('requests.partials.status')</div>
        @if($request->canBeManagedBy(auth()->user()))<a class="btn rq-secondary" href="{{ route('requests.edit', $request->id) }}">Edit request</a>@endif
    </header>
    @include('requests.partials.metadata')
    <div class="rq-detail">
        <section class="rq-panel">
            <div class="d-flex gap-4 mb-4">
                @include('requests.partials.poster')
                <div><h2>Requested by {{ $request->requester?->name ?? 'Former member' }}</h2><p class="rq-muted">{{ $request->created_at?->format('M j, Y') }}</p><p class="rq-muted small mb-0">Updated {{ $request->updated_at?->diffForHumans() }}</p></div>
            </div>
            <h2>Release details</h2>
            <p class="rq-description">{{ $request->description ?: 'No additional details provided.' }}</p>
            <div class="rq-actions mt-4">
                @foreach(['imdb_url' => 'IMDb', 'tmdb_url' => 'TMDB', 'steam_url' => 'Steam'] as $field => $label)
                    @if($url = $request->safeUrl($request->$field))<a class="btn rq-secondary" href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }} ↗</a>@endif
                @endforeach
            </div>
        </section>
        <aside>
            <section class="rq-panel rq-demand mb-3">
                <div><div class="rq-kicker">Community interest</div><h2>Want this release too?</h2><p class="rq-muted small mb-3">Vote to help uploaders see what the community wants.</p></div>
                @include('requests.partials.vote')
                @include('requests.partials.voters')
            </section>
            <section class="rq-panel">
                @if($request->filled === 'yes')
                    <div class="rq-kicker">Request completed</div><h2>Your next watch, play or discovery</h2><p class="rq-muted">Filled by {{ $request->filledBy?->name ?? 'Former member' }}.</p>
                    @if($url = $request->safeUrl($request->link))<a class="btn rq-btn" href="{{ $url }}">View torrent <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@else<p class="rq-muted">The torrent link is unavailable.</p>@endif
                    @if($request->canBeManagedBy(auth()->user()))
                        <hr><p class="rq-muted small">Wrong release or a missing torrent? Reopen the request so members can help again.</p>
                        <form method="POST" action="{{ route('requests.reopen', $request->id) }}" onsubmit="return confirm('Reopen this request and remove its current fill link?')">@csrf<button class="btn rq-secondary" type="submit">Reopen request</button></form>
                    @endif
                @elseif(\App\Models\TorrentRequest::canBeFilledBy(auth()->user()))
                    <div class="rq-kicker">Help the community</div><h2>Have the right release?</h2><p class="rq-muted">Paste the torrent details link from this site. The requester will receive a message when you fill it.</p>
                    <form method="POST" action="{{ route('requests.fill', $request->id) }}">@csrf
                        <label for="link">Torrent link</label><input class="form-control mb-3" id="link" name="link" type="url" maxlength="255" value="{{ old('link') }}" placeholder="{{ url('/torrents/123') }}" required>
                        <button class="btn rq-btn" type="submit">Mark as filled</button>
                    </form>
                @else
                    <div class="rq-kicker">Waiting for an uploader</div><h2>Help this request get noticed</h2>
                    <p class="rq-muted mb-0">Vote or add useful details below. Only uploaders and members with upload permission can mark a request as filled.</p>
                @endif
            </section>
            @if($request->canBeDeletedBy(auth()->user()))
                <section class="rq-panel mt-3"><h2>Moderation</h2><p class="rq-muted small">Delete duplicate or inappropriate requests.</p><form method="POST" action="{{ route('requests.destroy', $request->id) }}" onsubmit="return confirm('Permanently delete this request?')">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Delete request</button></form></section>
            @endif
        </aside>
    </div>
    @include('comments.discussion', ['commentTarget' => $request, 'commentType' => \App\Models\TorrentRequest::class])
</div>
@endsection
