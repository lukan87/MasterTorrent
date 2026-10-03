@extends('layouts.app')
@section('content')
@include('requests.partials.styles')
<div class="rq">
    <header class="rq-panel rq-hero">
        <div><div class="rq-kicker">Discover • Request • Share</div><h1>Community requests</h1><p class="rq-muted mb-0">Find your next upload. Help someone complete their collection.</p></div>
        @if(\App\Models\TorrentRequest::canBeCreatedBy(auth()->user()))
            <a class="btn rq-btn" href="{{ route('requests.create') }}"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New request</a>
        @else
            <span class="rq-muted small">Elite User rank unlocks new requests.</span>
        @endif
    </header>
    @include('requests.partials.feedback')
    <div class="rq-stats">
        <div class="rq-panel rq-stat"><strong>{{ number_format($counts->sum()) }}</strong>Total requests</div>
        <div class="rq-panel rq-stat"><strong>{{ number_format($counts['no'] ?? 0) }}</strong>Waiting to be filled</div>
        <div class="rq-panel rq-stat"><strong>{{ number_format($counts['yes'] ?? 0) }}</strong>Filled by the community</div>
    </div>
    <form method="GET" action="{{ route('requests.index') }}" class="rq-panel mb-4">
        <div class="row g-3">
            <div class="col-lg-4"><label for="q">Search requests</label><input type="search" class="form-control" id="q" name="q" maxlength="255" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title…"></div>
            <div class="col-lg-3 col-md-4"><label for="category_id">Category</label><select class="form-select" id="category_id" name="category_id"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-4"><label for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option><option value="open" @selected(($filters['status'] ?? '') === 'open')>Open</option><option value="filled" @selected(($filters['status'] ?? '') === 'filled')>Filled</option></select></div>
            <div class="col-lg-3 col-md-4"><label for="sort">Sort by</label><select class="form-select" id="sort" name="sort">@foreach(['popular' => 'Most voted', 'newest' => 'Newest first', 'oldest' => 'Oldest first', 'updated' => 'Recently updated'] as $value => $label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>@endforeach</select></div>
        </div>
        <div class="rq-actions mt-3"><button class="btn rq-btn" type="submit">Apply filters</button><a class="btn rq-secondary" href="{{ route('requests.index') }}">Reset</a><div class="form-check ms-md-auto"><input class="form-check-input" type="checkbox" id="mine" name="mine" value="1" @checked($filters['mine'] ?? false)><label class="form-check-label" for="mine">My requests only</label></div></div>
    </form>
    <p class="rq-muted small" role="status">{{ number_format($requests->total()) }} matching {{ Str::plural('request', $requests->total()) }}</p>
    <div class="rq-grid">
        @forelse($requests as $request)
            <article class="rq-panel rq-card">
                @include('requests.partials.poster')
                <div class="rq-card-body">
                    @include('requests.partials.status')
                    <span class="rq-muted small ms-1">{{ $request->category?->name ?? 'Uncategorized' }}</span>
                    <h2><a href="{{ route('requests.show', $request->id) }}">{{ $request->name }}</a></h2>
                    <p class="rq-muted small mb-2">Requested by {{ $request->requester?->name ?? 'Former member' }} · {{ $request->created_at?->diffForHumans() }}</p>
                    <div class="rq-actions mt-3 mb-2">@include('requests.partials.vote')<a class="rq-muted small" href="{{ route('requests.show', $request->id) }}#discussion"><i class="bi bi-chat-dots" aria-hidden="true"></i> {{ $request->comments_count }} {{ Str::plural('comment', $request->comments_count) }}</a></div>
                    @include('requests.partials.voters')
                    @if($request->filled === 'yes')<p class="small mb-0">Filled by {{ $request->filledBy?->name ?? 'Former member' }}</p>@endif
                </div>
            </article>
        @empty
            <div class="rq-panel rq-empty" style="grid-column:1/-1"><i class="bi bi-search fs-1 rq-muted" aria-hidden="true"></i><h2 class="mt-3">No requests found</h2><p class="rq-muted">Try another title or clear your filters.</p><a href="{{ route('requests.index') }}" class="btn rq-secondary">Clear filters</a></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</div>
@endsection
