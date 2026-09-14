@extends('layouts.app')

@section('title', 'Adult Torrents')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 mt-5">
    
</div>

{{-- 🔍 SEARCH --}}
@include('torrents.partials.adultsearch')

<div class="torrent-browser">

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="tx-toolbar">
        <div class="tx-toolbar-left">
            <span class="tx-logo"><i class="bi bi-heart-fill"></i></span>
            <div>
                <span class="tx-eyebrow">Adult</span>
                <h2 class="tx-heading">Adult Torrents</h2>
            </div>
            <span class="tx-count">{{ $adult->total() }} title{{ $adult->total() === 1 ? '' : 's' }}</span>
        </div>

        <div class="tx-sorts">
            @php
                $mx = function ($sort, $direction) {
                    return route('torrents.adult', array_merge(request()->all(), [
                        'sort'      => $sort,
                        'direction' => $direction,
                    ]));
                };
                $activeSort = request('sort') ?? '';
                $curDir     = request('direction') === 'asc' ? 'asc' : 'desc';
            @endphp

            @foreach ([
                'created_at'      => ['Age',       'bi-clock',                '27'],
                'size'            => ['Size',      'bi-aspect-ratio',         '35'],
                'seeders'         => ['Seeders',   'bi-arrow-up-circle-fill', '15'],
                'leechers'        => ['Leechers',  'bi-arrow-down-circle-fill','25'],
                'times_completed' => ['Downloads', 'bi-check2-circle',        '13'],
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

    {{-- ===================== LIST ===================== --}}
    <div class="torrent-list">

        {{-- ROWS --}}
        @forelse($adult as $torrent)
        <div class="torrent-card {{ $torrent->sticky ? 'torrent-sticky' : '' }}">

            <div class="torrent-card-head">
                <div class="torrent-info d-flex align-items-start">

                    <a href="{{ route('torrents.index', [
                        'keyword' => '',
                        'categories' => [$torrent->category->id],
                        'genre' => '',
                        'torrent_status' => 'active'
                    ]) }}"
                       class="me-3 d-inline-block"
                       data-bs-toggle="tooltip"
                       title="{{ $torrent->category->name }}">

                        <img src="{{ asset($torrent->category->image) }}?v={{ filemtime(public_path($torrent->category->image)) }}"
                             class="rounded shadow-sm"
                             style="width:74px;height:40px"
                             alt="{{ $torrent->category->name }}">
                    </a>

                    <div class="overflow-hidden">
                        <a href="{{ route('torrents.show', [$torrent->id, urlencode($torrent->slug)]) }}"
                           class="d-block text-decoration-none"
                           data-bs-toggle="tooltip"
                           data-bs-html="true"
                           data-bs-title="<img src='{{ $torrent->poster }}' class='img-fluid rounded' style='max-width:180px'>">

                            <small class="torrent-title text-truncate d-block">
                                {{ $torrent->name }}
                            </small>
                        </a>

                        <div class="mt-1 flex-wrap gap-2">
                            @foreach($torrent->genres as $genre)
                                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
                                   class="badge bg-secondary text-decoration-none">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                            @include('torrents.partials.tags')
                        </div>
                    </div>

                </div>
<div class="tx-actions">

                    {{-- DOWNLOAD / SEEDBOX --}}
                    @if(Auth::check() && Auth::user()->hit_and_run_count <= 20)
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
                               class="btn btn-secondary">
                                <i class="bi bi-cloud-arrow-down-fill"></i>
                            </a>

                            @if($seedboxes->isNotEmpty())
                                <button class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown"></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach($seedboxes as $seedbox)
                                        <li>
                                            <button type="button"
                                                    class="dropdown-item seedbox-send-btn"
                                                    data-torrent="{{ $torrent->id }}"
                                                    data-seedbox="{{ $seedbox->id }}">
                                                <i class="bi bi-cloud-upload-fill me-2"></i>
                                                {{ $seedbox->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    {{-- MODERATOR --}}
                    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                        <a href="{{ route('torrents.edit', [$torrent->id, $torrent->slug]) }}"
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    @endif

                    {{-- ADMIN --}}
                    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                        <form method="POST" action="{{ route('torrents.destroy', $torrent->slug) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif

                </div>
            </div>

            <div class="torrent-chips">
                <span class="stat-chip chip-muted" title="Uploaded {{ $torrent->created_at->format('M d, Y') }}">
                    <i class="bi bi-clock"></i>{{ $torrent->created_at->format('d M, Y') }}
                </span>
                <span class="stat-chip chip-info" title="Size">
                    <i class="bi bi-hdd-stack"></i>{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
                </span>
                <span class="stat-chip chip-seed" title="Seeders">
                    <i class="bi bi-arrow-up"></i>{{ $torrent->seeders }}
                </span>
                <span class="stat-chip chip-leech" title="Leechers">
                    <i class="bi bi-arrow-down"></i>{{ $torrent->leechers }}
                </span>
                <span class="stat-chip chip-ok" title="Times completed">
                    <i class="bi bi-check2-circle"></i>{{ $torrent->times_completed }}
                </span>

                <span class="torrent-uploader">
                    @if($torrent->uploader)
                        @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                            <i class="bi bi-arrow-90deg-up"></i>
                            <a href="{{ route('profile.show', $torrent->uploader->id) }}"
                               class="fw-semibold text-decoration-none"
                               style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}">
                                {{ $torrent->uploader->name }}
                            </a>
                        @else
                            <span style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}">
                                <i class="bi bi-arrow-90deg-up"></i> {{ $torrent->uploader->name }}
                            </span>
                        @endif
                    @else
                        <span class="text-muted">Unknown</span>
                    @endif
                </span>
            </div>

        </div>
        @empty
        <div class="torrent-empty">
            <i class="bi bi-inbox"></i>
            <p class="mb-0">No torrents found.</p>
        </div>
        @endforelse

    </div>

</div>
<div class="d-flex justify-content-center mt-4">
    {{ $adult->links('pagination::bootstrap-5') }}
</div>

<style>


    .torrent-title {
    position: relative;
    display: inline-block;
    color: #fff;
    font-size: 1rem;
    letter-spacing: .3px;
    line-height: 1.15;
    padding-bottom: 2px; /* space for the line */
    transition: color .15s ease;
}

/* animated line INSIDE the element */
.torrent-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0; /* <-- inside, not outside */
    width: 100%;
    height: 1px;
    opacity: .9;
    background: linear-gradient(90deg, #aca9a9, #f0527f);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform .25s ease;
}

/* hover */
a:hover .torrent-title {
    color: #aca9a9;
}

a:hover .torrent-title::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Sticky torrent highlight */
.torrent-sticky {
    position: relative;
    background: linear-gradient(
        90deg,
        rgba(120, 140, 255, 0.08),
        transparent 35%
    );
}

/* Dark accent line on the left */
.torrent-sticky::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(
        180deg,
        #9aa4ff,
        #d41a6b
    );
    border-radius: 0 4px 4px 0;
}

.torrent-sticky {
    background: rgba(134, 151, 252, 0.1);
}

/* ---------- Toolbar ---------- */
.tx-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;

    margin-bottom: 18px;
    padding: 16px 18px;

    border-radius: 16px;
    background: var(--ui-surface);
    border: 1px solid var(--ui-border);
    box-shadow: var(--ui-shadow);
}

.tx-toolbar-left { display: flex; align-items: center; gap: 12px; }

.tx-logo {
    width: 44px;
    height: 44px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;
    font-size: 20px;

    color: #08213a;
    background: linear-gradient(135deg, var(--ui-accent), var(--ui-accent-strong));
    box-shadow: 0 8px 20px rgba(99, 210, 198, .35);
}

.tx-eyebrow {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ui-accent);
    margin-bottom: 1px;
}

.tx-heading {
    font-family: 'Poppins', 'Inter', 'Segoe UI', sans-serif;
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin: 0;
    line-height: 1.1;
}

.tx-count {
    margin-left: 6px;
    padding: 4px 10px;

    border-radius: 999px;

    background: rgba(255, 255, 255, .06);
    border: 1px solid var(--ui-border);

    color: var(--ui-text-muted);
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.tx-sorts { display: flex; flex-wrap: wrap; gap: 6px; }

.tx-sort {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 7px 11px;
    border-radius: 999px;

    color: var(--ui-text-muted);
    text-decoration: none;

    font-size: 12px;
    font-weight: 600;

    background: rgba(255, 255, 255, .03);
    border: 1px solid var(--ui-border);

    transition: color .15s ease, background .15s ease, border-color .15s ease, transform .15s ease;
}

.tx-sort:hover {
    color: #fff;
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .18);
    transform: translateY(-1px);
}

.tx-sort.active {
    color: #0b2338;
    background: linear-gradient(135deg, var(--ui-accent), var(--ui-accent-strong));
    border-color: transparent;
    box-shadow: 0 6px 16px rgba(99, 210, 198, .28);
}

.tx-sort-arrow { font-size: 10px; }

/* ---------- Torrent cards ---------- */
.torrent-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 4px;
}

.torrent-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;

    padding: 16px 18px;

    border-radius: 16px;

    background: var(--ui-surface);
    border: 1px solid var(--ui-border);
    box-shadow: 0 4px 18px rgba(0, 0, 0, .16);

    transition: border-color .15s ease, transform .15s ease;
}

.torrent-card:hover {
    border-color: rgba(99, 210, 198, .35);
    transform: translateY(-1px);
}

.torrent-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
}

.torrent-info {
    flex: 1 1 auto;
    min-width: 0;
}

.torrent-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, .06);
}

/* ---------- Stat chips (horizontal pills) ---------- */
.stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 6px 12px;

    border-radius: 999px;

    background: rgba(255, 255, 255, .05);
    border: 1px solid var(--ui-border);

    font-size: 12px;
    font-weight: 600;

    color: var(--ui-text-muted);
    white-space: nowrap;
}

.stat-chip i {
    font-size: 13px;
    line-height: 1;
}

.chip-muted { color: var(--ui-text-muted); }
.chip-info  { color: #7dd3fc; border-color: rgba(125, 211, 252, .2); }
.chip-seed  { color: #34d399; border-color: rgba(52, 211, 153, .22); }
.chip-leech { color: #f87171; border-color: rgba(248, 113, 113, .22); }
.chip-ok    { color: #a5b4fc; border-color: rgba(165, 180, 252, .22); }

.torrent-uploader {
    margin-left: auto;
    font-size: 12px;
    color: var(--ui-text-muted);
}

.torrent-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 48px 16px;
    color: var(--ui-text-muted);
    background: var(--ui-surface);
    border: 1px dashed var(--ui-border);
    border-radius: 16px;
}

.torrent-empty i {
    font-size: 32px;
    opacity: .5;
}

/* ---------- Action buttons ---------- */
.tx-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    flex-wrap: nowrap;
}

.tx-actions .btn-group .btn {
    background: var(--ui-surface-raised);
    border-color: var(--ui-border);
    color: var(--ui-text-muted);
}

.tx-actions .btn-group {
    box-shadow: 0 3px 10px rgba(0, 0, 0, .14);
}

.tx-actions .btn-warning {
    background: rgba(245, 158, 11, .16);
    border-color: rgba(245, 158, 11, .4);
    color: #fcd34d;
}

.tx-actions .btn-warning:hover {
    background: rgba(245, 158, 11, .3);
    color: #fff;
}

.tx-actions .btn-danger {
    background: rgba(239, 68, 68, .12);
    border: 1px solid rgba(239, 68, 68, .34);
    color: #fca5a5;
}

.tx-actions .btn-danger:hover {
    background: rgba(239, 68, 68, .24);
    color: #fff;
}

@media (max-width: 767.98px) {
    .tx-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .torrent-card-head {
        flex-direction: column;
        align-items: stretch;
    }

    .tx-actions {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .torrent-uploader {
        margin-left: 0;
    }
}

</style>

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
