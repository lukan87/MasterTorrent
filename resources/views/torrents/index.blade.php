@extends('layouts.app')

@section('title', 'Browse Torrents')

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
                    return route('torrents.index', array_merge(request()->all(), [
                        'sort'      => $sort,
                        'direction' => $direction,
                    ]));
                };
                $activeSort = request('sort') ?? '';
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



<div class="card rounded-lg shadow-sm">
<div class="card-body p-0">

{{-- ROWS --}}
@forelse($torrents as $torrent)
<div class="row g-0 align-items-center border-bottom py-3 tx-list-row {{ $torrent->sticky ? 'torrent-sticky' : '' }}">

    {{-- TORRENT INFO --}}
    <div class="col-12 col-md-6 d-flex px-3">
   @include('torrents.partials.index.namecat')
    </div>

    {{-- AGE --}}
    <div class="d-none d-md-block col-md-1 text-center small fw-bold">
        <span class="stat-chip chip-muted" data-bs-toggle="tooltip" title="Uploaded">
        {{ $torrent->created_at->format('M d, Y') }}
        </span>
    </div>

    {{-- SIZE --}}
    <div class="d-none d-md-block col-md-1 text-center small text-info fw-bold">
        <span class="stat-chip chip-info" data-bs-toggle="tooltip" title="Size">
        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
        </span>
    </div>

    {{-- SEED / LEECH --}}
    <div class="col-6 col-md-1 text-center">
        <span class="stat-chip chip-seed" data-bs-toggle="tooltip" title="Seeders">
            <i class="bi bi-arrow-up"></i>{{ $torrent->seeders }}
        </span>
        <span class="stat-chip chip-leech" data-bs-toggle="tooltip" title="Leechers">
            <i class="bi bi-arrow-down"></i>{{ $torrent->leechers }}
        </span>
    </div>

      {{-- COMPLETED --}}
    <div class="d-none d-md-block col-md-1 text-center text-success fw-bold">
        
        <span class="stat-chip chip-ok" data-bs-toggle="tooltip" title="Times completed">
            <i class="bi bi-check2-circle"></i>{{ $torrent->times_completed }}
        </span>
    </div>

  {{-- UPLOADER --}}
<div class="col-6 col-md-1 text-center small">
@include('torrents.partials.index.uploaders')
</div>

    {{-- ACTIONS --}}
     
    <div class="col-12 col-md-1 px-1 tx-actions">
   @include('torrents.partials.index.actions')
    </div>

</div>
@empty
<div class="text-center py-5 text-muted">No torrents found.</div>
@endforelse

</div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $torrents->links('pagination::bootstrap-5') }}
</div>

<style>
    /* Sticky must NOT be blocked */
.card,
.card-body,
.torrent-list {
    overflow: visible !important;
}

/* Sticky header */
.torrent-header {
    position: sticky;
    top: 56px; /* navbar height */
    z-index: 50;
    background: rgba(20,20,25,.96);
    backdrop-filter: blur(6px);
}
.action-group .btn {
    min-width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-group .dropdown-toggle-split {
    padding-left: .5rem;
    padding-right: .5rem;
}

.btn-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #000;
}

.movie-highlight {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    max-width: 900px;
    margin: 50px auto;
    background: linear-gradient(145deg, #1c1c1c30, #2a2a2a);
    backdrop-filter: blur(3px);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: glow 1.5s ease-in-out infinite alternate;
}
@keyframes glow {
    0% {
        box-shadow: 0 0 5px #c0b7b4ff, 0 0 10px #5f5755ff, 0 0 15px #aaa7a6ff;
    }
    50% {
        box-shadow: 0 0 10px #5a5959ff, 0 0 20px #2b2a29ff, 0 0 30px #636261ff;
    }
    100% {
        box-shadow: 0 0 5px #5070ffff, 0 0 10px #5350ffff, 0 0 15px #5065c4ff;
    }
}

.movie-highlight:hover {
    transform: translateY(-5px);
}

.poster {
    flex: 0 0 200px;
    position: relative;
}

.poster img {
    width: 100%;
    height: auto;
    border-radius: 20px 0 0 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    transition: transform 0.3s ease;
}

.poster img:hover {
    transform: scale(1.05);
}

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
    background: linear-gradient(90deg, #aca9a9, #9aa4ff);
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



.details {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 20px;
}

.info-card {
    color: #fff;
}

.tagline {
    color: #ff7f50;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.title {
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 10px;
    line-height: 1.2;
}

.category {
    font-size: 1rem;
    margin-bottom: 20px;
    color: #bbb;
}

.motd-stats {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: .85rem;
    font-weight: 600;
    backdrop-filter: blur(6px);
}

.stat-pill.seeders {
    background: rgba(40,167,69,.15);
    color: #6dff9c;
}

.stat-pill.leechers {
    background: rgba(220,53,69,.15);
    color: #ff9c9c;
}

.stat-pill.completed {
    background: rgba(0,123,255,.15);
    color: #9cc7ff;
}

/* Sticky Movie of the Day */
.motd-sticky {
    position: sticky;
    top: 14px; /* navbar height */
    bottom: 20px;
    z-index: 40;
}

/* 📱 Mobile = ONE LINE */
@media (max-width: 991.98px) {

    .movie-highlight {
        flex-direction: row;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        margin: 12px auto;
        max-width: 100%;
        border-radius: 12px;
        animation: none;
    }

    .poster {
        flex: 0 0 auto;
    }

    .poster img {
        width: 42px;
        border-radius: 6px;
    }

    .details {
        padding: 0;
        flex: 1;
    }

    .info-card {
        padding: 0;
    }

    .tagline {
        font-size: 11px;
        margin-bottom: 2px;
    }

    .title {
        font-size: 13px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .category {
        display: none;
    }
}

.action-group .btn {
    width: 34px;
    height: 34px;
    padding: 0;
}

.torrent-name-tags {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

.torrent-name-tags .tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

@media (max-width: 768px) {
    .torrent-name-tags {
        flex-direction: column;
        align-items: flex-start;
    }
}

.torrent-header {
    position: sticky;
    top: 56px; /* adjust if navbar height differs */
    z-index: 20;
    background: #1d1c1c;
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
        #9aa4ffbe,
        #5065c477
    );
    backdrop-filter: blur(1px);
    border-radius: 0 4px 4px 0;
}

.torrent-sticky {
    background: rgba(53, 47, 99, 0.231);
}


/* =====================================
   Base & Layout
   ===================================== */

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Everything visible sits above background */
.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

/* Base background image */
html::before {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background-image:
        linear-gradient(
            to bottom,
            rgba(0,0,0,0.35),
            rgba(0,0,0,0.9)
        ),
      url('{{ optional($movieOfTheDay)->background ?? optional($torrent ?? null)->background  ?? asset("images/default-bg.jpg") }}?v={{ optional($movieOfTheDay)->id ?? time() }}');


    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;

    opacity: 0.75;
    z-index: -2;
}


/* Darkening as you scroll */
html::after {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.05) 0%,
        rgba(0,0,0,0.25) 25%,
        rgba(0,0,0,0.55) 55%,
        rgba(0,0,0,0.85) 80%,
        rgba(0,0,0,1) 100%
    );

    pointer-events: none;
    z-index: -1;
}

.torrent-user-status {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}

.torrent-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    padding: 4px 10px;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: .3px;

    backdrop-filter: blur(8px);

    transition: all .2s ease;
}

/* Downloaded */
.torrent-badge.downloaded {
    background: rgba(40, 167, 69, .12);
    color: #7dffb0;

    border: 1px solid rgba(125,255,176,.2);

    box-shadow:
        inset 0 0 10px rgba(125,255,176,.05),
        0 0 12px rgba(125,255,176,.08);
}

/* Seeding */
.torrent-badge.seeding {
    background: rgba(80,120,255,.14);
    color: #9ab0ff;

    border: 1px solid rgba(154,176,255,.25);

    box-shadow:
        inset 0 0 10px rgba(154,176,255,.05),
        0 0 12px rgba(154,176,255,.08);

    animation: seedPulse 2s infinite;
}

@keyframes seedPulse {

    0% {
        box-shadow:
            0 0 0 rgba(154,176,255,0);
    }

    50% {
        box-shadow:
            0 0 14px rgba(154,176,255,.25);
    }

    100% {
        box-shadow:
            0 0 0 rgba(154,176,255,0);
    }
}

.torrent-badge {
    width: 24px;
    height: 24px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    font-size: 12px;

    cursor: help;
}





/* =====================================================================
   FILEIPLAY - TORRENT BROWSER IMPROVEMENTS
   (appended - keeps original column look, adds toolbar + nicer actions)
   ===================================================================== */

.torrent-browser { margin-bottom: 6px; }

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
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
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

/* ---------- Row polish (original column layout kept) ---------- */
.tx-list-row:hover {
    background: rgba(99, 210, 198, .045);
}

/* ---------- Action buttons ---------- */
.tx-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 4px;
    flex-wrap: nowrap;
}

.tx-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.tx-actions .btn-group .btn {
    background: var(--ui-surface-raised);
    border-color: var(--ui-border);
    color: var(--ui-text-muted);
}

.tx-actions .btn-group {
    box-shadow: 0 3px 10px rgba(0, 0, 0, .14);
}

.tx-actions .btn-group .btn:hover,
.tx-actions .btn-group .btn:focus {
    color: #fff;
    border-color: var(--ui-accent);
    background: rgba(99, 210, 198, .10);
    z-index: 2;
}

.tx-actions .btn-group .tx-btn-download {
    color: var(--ui-accent);
    border-color: rgba(99, 210, 198, .35);
}

.tx-actions .btn-warning {
    background: rgba(245, 158, 11, .16);
    border-color: rgba(245, 158, 11, .4);
    color: #fcd34d;
}

.tx-actions .btn-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #000;
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

/* Seedbox dropdown */
.tx-actions .dropdown-menu {
    min-width: 210px;
    padding: .35rem;
    border-radius: 12px;
}

.tx-actions .dropdown-header {
    color: var(--ui-text-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: .4rem .6rem;
}

.tx-actions .dropdown-item {
    border-radius: .45rem;
    color: var(--ui-text-muted);
    padding: .45rem .6rem;
}

.tx-actions .dropdown-item:hover {
    color: #fff;
    background: rgba(99, 210, 198, .12);
}

/* ---------- Responsive toolbar ---------- */
@media (max-width: 767.98px) {
    .tx-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* ---------- Stat chips (horizontal pills) ---------- */
.stat-chip {
   display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: .55rem;

    background: rgba(45,212,191,.06);
    border: 1px solid rgba(45,212,191,.18);

    color: var(--ui-accent);
    font-size: 11px;
    font-weight: 700;
}

.stat-chip i {
    font-size: 10px;
    line-height: 1;
}

.chip-muted { color: var(--ui-text-muted); }
.chip-info  { color: #7dd3fc; border-color: rgba(125, 211, 252, .2); }
.chip-seed  { color: #34d399; border-color: rgba(52, 211, 153, .22); }
.chip-leech { color: #f87171; border-color: rgba(248, 113, 113, .22); }
.chip-ok    { color: #a5b4fc; border-color: rgba(165, 180, 252, .22); }

</style>

<script>
document.querySelectorAll('.seedbox-send-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', this.dataset.torrent), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                seedbox_id: this.dataset.seedbox
            })
        })
        .then(r => r.json())
        .then(data => {
            showToast(data.message || 'Sent to seedbox');
        })
        .catch(() => showToast('Seedbox error', 'danger'));
    });
});

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
