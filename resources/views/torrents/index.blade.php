@extends('layouts.app')

@section('title', 'Browse Torrents')

@section('content')


@include('torrents.partials.movieoftheday')
@include('torrents.partials.indexsearch')



<div class="card rounded-lg shadow-sm">
<div class="card-body p-0">

{{-- HEADER --}}
<div class="row g-0 border-bottom small text-uppercase text-muted align-items-center">

    <div class="col-12 col-md-6 px-3 py-3"></div>

    {{-- AGE --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">

       @php
    $direction = request('sort') === 'created_at' && request('direction') === 'asc'
        ? 'desc'
        : 'asc';
@endphp

<a data-bs-toggle="tooltip"
   title="Sort by latest activity"
   href="{{ route('torrents.index', array_merge(request()->all(), [
       'sort' => 'created_at',
       'direction' => $direction
   ])) }}">
   <i class="bi bi-clock fs-5
   {{ request('sort') === 'created_at' ? 'text-danger' : 'text-warning' }}">
</i>
</a>
    </div>

    {{-- SIZE --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">
        <a data-bs-toggle="tooltip" title="Sort by size"
           href="{{ route('torrents.index', array_merge(request()->all(), [
               'sort' => 'size',
               'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
           ])) }}">
            <i class="bi bi-aspect-ratio text-info fs-5"></i>
        </a>
    </div>

    {{-- SEED / LEECH --}}
    <div class="col-6 col-md-1 text-center py-3">
        <span class="text-success">
            <a data-bs-toggle="tooltip" title="Sort by seeders"
               href="{{ route('torrents.index', array_merge(request()->all(), [
                   'sort' => 'seeders',
                   'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
               ])) }}">
                <i class="bi bi-arrow-up-circle text-success fs-5"></i>
            </a>
        </span>
        /
        <span class="text-danger">
            <a data-bs-toggle="tooltip" title="Sort by leechers"
               href="{{ route('torrents.index', array_merge(request()->all(), [
                   'sort' => 'leechers',
                   'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
               ])) }}">
                <i class="bi bi-arrow-down-circle text-danger fs-5"></i>
            </a>
        </span>
    </div>

    {{-- COMPLETED --}}
    <div class="d-none d-md-block col-md-1 text-center py-3">
        <a data-bs-toggle="tooltip" title="Sort by completed"
           href="{{ route('torrents.index', array_merge(request()->all(), [
               'sort' => 'times_completed',
               'direction' => request('direction') === 'asc' ? 'desc' : 'asc'
           ])) }}">
            <i class="bi bi-floppy text-success fs-5"></i>
        </a>
    </div>

    {{-- UPLOADER --}}
      <div class="col-6 col-md-1 text-center py-3" data-bs-toggle="tooltip" title="Uploader" ><i class="bi bi-person-up text-success fs-5"></i></div>

    {{-- ACTIONS --}}
    <div class="col-12 col-md-1 text-center px-3 py-3"></div>
</div>


{{-- ROWS --}}
@forelse($torrents as $torrent)
<div class="row g-0 align-items-center border-bottom py-3 {{ $torrent->sticky ? 'torrent-sticky' : '' }}">

    {{-- TORRENT INFO --}}
    <div class="col-12 col-md-6 d-flex px-3">
   @include('torrents.partials.index.namecat')
    </div>

    {{-- AGE --}}
    <div class="d-none d-md-block col-md-1 text-center small fw-bold">
        {{ $torrent->created_at->format('M d, Y') }}
    </div>

    {{-- SIZE --}}
    <div class="d-none d-md-block col-md-1 text-center small text-info fw-bold">
        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
    </div>

    {{-- SEED / LEECH --}}
    <div class="col-6 col-md-1 text-center">
        <span class="text-success fw-bold">{{ $torrent->seeders }}</span>
        <span class="text-muted fw-bold">/</span>
        <span class="text-danger fw-bold">{{ $torrent->leechers }}</span>
    </div>

      {{-- COMPLETED --}}
    <div class="d-none d-md-block col-md-1 text-center text-success fw-bold">
        {{ $torrent->times_completed }}
    </div>

  {{-- UPLOADER --}}
<div class="col-6 col-md-1 text-center small">
@include('torrents.partials.index.uploaders')
</div>

    {{-- ACTIONS --}}
     
    <div class="col-12 col-md-1 px-1">
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
