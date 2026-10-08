@extends('layouts.app')

@section('title', 'Browse Torrents')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/torrent-index.css') }}?v={{ filemtime(public_path('css/torrent-index.css')) }}">
<link rel="stylesheet" href="{{ asset('css/torrent-browser.css') }}?v={{ filemtime(public_path('css/torrent-browser.css')) }}">
<link rel="stylesheet" href="{{ asset('css/movie-of-the-day.css') }}?v={{ filemtime(public_path('css/movie-of-the-day.css')) }}">
@endpush

@section('content')


@include('torrents.partials.movieoftheday')
@include('torrents.partials.indexsearch')

<div class="torrent-browser">

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="tx-toolbar">
        <div class="tx-toolbar-left">
            <span class="tx-logo"><i class="bi bi-collection-play-fill"></i></span>
            <div>
                <span class="tx-eyebrow">Library</span>
                <h2 class="tx-heading">Browse Torrents</h2>
            </div>
            <span class="tx-count">{{ $torrents->total() }} title{{ $torrents->total() === 1 ? '' : 's' }}</span>
        </div>

        <div class="tx-sorts">
            @php
                $mx = function ($sort, $direction) {
                    return route('torrents.index', array_merge(request()->except('page'), [
                        'sort'      => $sort,
                        'direction' => $direction,
                    ]));
                };
                $activeSort = request('sort', 'created_at');
                $curDir     = request('direction') === 'asc' ? 'asc' : 'desc';
            @endphp

            @foreach ([
                'created_at'      => ['Age',       'bi-clock',                 '27'],
                'size'            => ['Size',      'bi-aspect-ratio',          '35'],
                'seeders'         => ['Seeders',   'bi-arrow-up-circle-fill',  '15'],
                'leechers'        => ['Leechers',  'bi-arrow-down-circle-fill','25'],
                'times_completed' => ['Downloads', 'bi-check2-circle',         '13'],
            ] as $sort => [$label, $icon])
                @php
                    $isActive = $activeSort === $sort;
                    $nextDir  = ($isActive && $curDir === 'desc') ? 'asc' : 'desc';
                @endphp
                <a href="{{ $mx($sort, $nextDir) }}"
                   class="tx-sort {{ $isActive ? 'active' : '' }}"
                   data-bs-toggle="tooltip" title="Sort by {{ $label }}">
                    <i class="bi {{ $icon }}"></i>
                    <span class="tx-sort-name">{{ $label }}</span>
                    <i class="bi {{ $isActive ? ($curDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : '' }} tx-sort-arrow"></i>
                </a>
            @endforeach
        </div>
    </div>

</div>



{{ $torrents->onEachSide(1)->links('torrents.partials.pagination', ['position' => 'top']) }}

<section class="tx-library" aria-label="Torrent library">
    <table class="tx-table">
        <caption class="visually-hidden">Browse torrents, activity, uploaders, and available actions.</caption>
        <colgroup>
            <col class="tx-col-title"><col class="tx-col-size">
            <col class="tx-col-peers"><col class="tx-col-completed"><col class="tx-col-uploader"><col class="tx-col-actions">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">Torrent</th>
                @foreach (['size' => 'Size', 'seeders' => 'Peers', 'times_completed' => 'Completed'] as $sort => $label)
                    <th scope="col" @if($activeSort === $sort) aria-sort="{{ $curDir === 'asc' ? 'ascending' : 'descending' }}" @endif>
                        <a href="{{ $mx($sort, $activeSort === $sort && $curDir === 'desc' ? 'asc' : 'desc') }}">
                            {{ $label }}
                            <i class="bi {{ $activeSort === $sort ? ($curDir === 'asc' ? 'bi-arrow-up-short' : 'bi-arrow-down-short') : 'bi-arrow-down-up' }}" aria-hidden="true"></i>
                        </a>
                    </th>
                @endforeach
                <th scope="col">Uploader</th>
                <th scope="col" class="tx-actions-heading">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $lastUploadDay = null;
                $browseTimezone = config('app.default_timezone', config('app.timezone'));
                $browseToday = now($browseTimezone)->startOfDay();
                $browseYesterday = $browseToday->copy()->subDay();
            @endphp
            @forelse($torrents as $torrent)
                @if(! $torrent->sticky)
                    @php
                        $uploadDate = $torrent->created_at->copy()->timezone($browseTimezone);
                        $uploadDay = $uploadDate->toDateString();
                        $uploadDayLabel = match ($uploadDay) {
                            $browseToday->toDateString() => 'Today',
                            $browseYesterday->toDateString() => 'Yesterday',
                            default => $uploadDate->format('j F Y'),
                        };
                    @endphp
                    @if($uploadDay !== $lastUploadDay)
                        @include('torrents.partials.index.date-separator')
                    @endif
                    @php($lastUploadDay = $uploadDay)
                @endif
                <tr class="tx-list-row {{ $torrent->sticky ? 'torrent-sticky' : '' }}">
                    <td class="tx-info-cell">
                        <div class="tx-info">@include('torrents.partials.index.namecat')</div>
                    </td>
                    <td class="tx-size-cell" data-label="Size" tabindex="0" data-bs-toggle="tooltip" data-bs-trigger="hover focus click" title="Total file size: {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}">{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</td>
                    <td class="tx-peers-cell" data-label="Peers">
                        <div class="tx-peer-stats">
                            <span class="tx-seeders" tabindex="0" data-bs-toggle="tooltip" data-bs-trigger="hover focus click" title="Seeders: {{ number_format($torrent->seeders) }} users sharing the complete torrent" aria-label="{{ number_format($torrent->seeders) }} seeders"><i class="bi bi-arrow-up" aria-hidden="true"></i>{{ number_format($torrent->seeders) }}</span>
                            <span class="tx-leechers" tabindex="0" data-bs-toggle="tooltip" data-bs-trigger="hover focus click" title="Leechers: {{ number_format($torrent->leechers) }} users downloading the torrent" aria-label="{{ number_format($torrent->leechers) }} leechers"><i class="bi bi-arrow-down" aria-hidden="true"></i>{{ number_format($torrent->leechers) }}</span>
                        </div>
                    </td>
                    <td class="tx-completed-cell" data-label="Completed">
                        <span tabindex="0" data-bs-toggle="tooltip" data-bs-trigger="hover focus click" title="Completed: {{ number_format($torrent->times_completed) }} finished downloads" aria-label="{{ number_format($torrent->times_completed) }} completed downloads"><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ number_format($torrent->times_completed) }}</span>
                    </td>
                    <td class="tx-uploader-cell" data-label="Uploader" tabindex="0" data-bs-toggle="tooltip" data-bs-trigger="hover focus click" title="Uploaded by {{ $torrent->uploader->name ?? 'an unknown user' }}">
                        <span class="tx-meta-icon" aria-hidden="true"><i class="bi bi-person"></i></span>
                        @include('torrents.partials.index.uploaders')
                    </td>
                    <td class="tx-actions-cell">@include('torrents.partials.index.actions')</td>
                </tr>
            @empty
                <tr><td colspan="6" class="tx-empty">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <strong>No torrents found</strong>
                    <span>Try another title or adjust your filters.</span>
                    <a href="{{ route('torrents.index') }}">Clear filters <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{ $torrents->onEachSide(1)->links('torrents.partials.pagination', ['position' => 'bottom']) }}


@include('torrents.partials.css.list-common-css')





<script>
 function swalSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 3500,
        showConfirmButton: true,
        timerProgressBar: true
    });
}

function swalError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}



document.addEventListener('click', function (e) {
    const btn = e.target.closest('.seedbox-send-btn');
    if (!btn) return;

    e.preventDefault();

    // Prevent double-click
    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';
    btn.classList.add('disabled');

    // Optional loading alert
    Swal.fire({
        title: 'Sending torrent…',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', btn.dataset.torrent), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            seedbox_id: btn.dataset.seedbox
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    })
    .then(data => {
        Swal.close();
        swalSuccess(data.message || 'Torrent sent to seedbox');

        // Optional: mark as sent
        btn.innerHTML = '✔ Sent';
        btn.classList.add('text-success');
    })
    .catch(error => {
        Swal.close();
        swalError(error.message || 'Failed to send torrent');

        btn.dataset.loading = '0';
        btn.classList.remove('disabled');
    });
});


</script>


@endsection
