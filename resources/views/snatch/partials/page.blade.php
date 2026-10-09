@php
    $tabs = [
        ['snatchlist', 'snatch.snatchlist', 'bi-collection', 'History'],
        ['seeding', 'snatch.seeding', 'bi-cloud-upload', 'Seeding'],
        ['leeching', 'snatch.leeching', 'bi-arrow-down-circle', 'Downloading'],
        ['need', 'snatch.needToSeed', 'bi-hourglass-split', 'Needs seeding'],
        ['hnr', 'snatch.hitAndRun', 'bi-exclamation-triangle', 'Hit & Run'],
        ['fixer', 'snatch.hnrFixer', 'bi-tools', 'H&R fixer'],
    ];
@endphp
<div class="snatch-hub container-fluid py-4 py-md-5">
    <header class="snatch-overview">
    <div class="snatch-heading">
        <div>
            <div class="snatch-eyebrow">Torrent activity · {{ $user->name ?? 'Unknown user' }}</div>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
        <div class="snatch-count"><strong>{{ number_format($records->total()) }}</strong><span>{{ in_array($section, ['seeding', 'leeching']) ? 'sessions' : 'records' }}</span></div>
    </div>
    <div class="snatch-list-heading">
        <span class="snatch-summary-count"><i class="bi bi-list-ul" aria-hidden="true"></i> {{ $records->total() ? 'Showing '.$records->firstItem().'–'.$records->lastItem().' of '.number_format($records->total()) : 'No activity in this section' }}</span>
        <span class="snatch-summary-target"><i class="bi bi-clock" aria-hidden="true"></i> Seed target: {{ \App\Helpers\FormatHelper::formatTime(config('hitrun.seedtime', 43200)) }} · Ratio target: 1.00</span>
    </div>
    </header>
    <nav class="snatch-tabs" aria-label="Torrent activity sections">
        @foreach($tabs as [$key, $route, $icon, $label])
            <a href="{{ route($route, ['userId' => $userId]) }}" class="{{ $section === $key ? 'is-current' : '' }}" @if($section === $key) aria-current="page" @endif>
                <i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}
            </a>
        @endforeach
    </nav>
        <form action="{{ url()->current() }}" method="GET" class="snatch-search" role="search">
            <div class="snatch-search-field">
                <label for="snatch-search">Search torrents</label>
                <input id="snatch-search" type="search" name="q" value="{{ request('q', $search ?? '') }}" maxlength="200" placeholder="Torrent name" class="form-control">
            </div>
            <label>Sort <select name="sort" class="form-select"><option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest first</option><option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option></select></label>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            @if(request('q', $search ?? '') !== '')
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Clear search</a>
            @endif
            @if($section === 'snatchlist')<p>Search includes older entries with requirements met. History is retained when entries leave the default list.</p>@endif
        </form>
        @error('q')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
    <div class="snatch-list">
        @forelse($records as $history)
            <x-snatch-card :history="$history" :type="$section" />
        @empty
            <div class="snatch-empty">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <h2>{{ $emptyMessage }}</h2>
                <p>Switch sections to review other torrent activity.</p>
            </div>
        @endforelse
    </div>
    @if($records->hasPages())
        <div class="snatch-pagination">{{ $records->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
