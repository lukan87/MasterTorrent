@extends('layouts.app')

@section('content')

@error('torrent')
    <div class="modern-alert modern-alert-danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $message }}
    </div>
@enderror

<div class="seedbox-page">

    <div class="container-fluid py-5">

        {{-- =========================================
            HERO HEADER
        ========================================= --}}
        <div class="seedbox-hero mb-4">

            <div class="hero-content">

                <div class="hero-kicker">
                    REMOTE CLIENT • TORRENT CONTROL
                </div>

                <h1 class="hero-title">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    {{ $seedbox->name }}

                </h1>

                <div class="hero-subtitle">

                    Manage torrents, monitor activity and control downloads.

                </div>

            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                @php
                    $isConnected = isset($torrents) && $torrents->count() > 0;
                @endphp

                <span class="connection-badge {{ $isConnected ? 'online' : 'offline' }}">

                    <i class="bi {{ $isConnected
                        ? 'bi-check-circle-fill'
                        : 'bi-x-circle-fill' }}"></i>

                    {{ $isConnected ? 'Connected' : 'Offline' }}

                </span>

                <a href="{{ route('seedboxes.index') }}"
                   class="btn modern-back-btn">

                    <i class="bi bi-arrow-left-circle me-1"></i>

                    Back

                </a>

            </div>

        </div>

        {{-- =========================================
            SESSION ALERTS
        ========================================= --}}
        @if(session('success'))

            <div class="modern-alert modern-alert-success">

                <div>
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {!! session('success') !!}
                </div>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="alert"></button>

            </div>

        @endif

        @if(session('error'))

            <div class="modern-alert modern-alert-danger">

                <div>
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {!! session('error') !!}
                </div>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="alert"></button>

            </div>

        @endif

        {{-- =========================================
            STATS
        ========================================= --}}
        <div class="stats-grid mb-4">

            <div class="modern-stat-card">

                <i class="bi bi-hdd-network text-info"></i>

                <div class="stat-value">
                    {{ $stats['total'] }}
                </div>

                <div class="stat-label">
                    Total Torrents
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-arrow-up-circle text-success"></i>

                <div class="stat-value">
                    {{ $stats['seeding'] }}
                </div>

                <div class="stat-label">
                    Seeding
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-arrow-down-circle text-primary"></i>

                <div class="stat-value">
                    {{ $stats['downloading'] }}
                </div>

                <div class="stat-label">
                    Downloading
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-pause-circle text-secondary"></i>

                <div class="stat-value">
                    {{ $stats['paused'] }}
                </div>

                <div class="stat-label">
                    Paused
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-hdd-stack text-warning"></i>

                <div class="stat-value">
                    {{ App\Helpers\FormatHelper::formatSize($stats['totalSize']) }}
                </div>

                <div class="stat-label">
                    Total Size
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-cloud-arrow-up text-info"></i>

                <div class="stat-value">
                    {{ App\Helpers\FormatHelper::formatSize($stats['totalUploaded']) }}
                </div>

                <div class="stat-label">
                    Uploaded
                </div>

            </div>

            <div class="modern-stat-card">

                <i class="bi bi-cloud-arrow-down text-light"></i>

                <div class="stat-value">
                    {{ App\Helpers\FormatHelper::formatSize($stats['totalDownloaded']) }}
                </div>

                <div class="stat-label">
                    Downloaded
                </div>

            </div>

        </div>

        {{-- =========================================
            ADD TORRENT
        ========================================= --}}
        <div class="modern-card mb-4">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-cloud-arrow-up-fill text-info me-2"></i>

                    Add Torrent

                </h5>

            </div>

            <div class="modern-card-body">

                <form action="{{ route('seedboxes.addTorrent', $seedbox) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="row g-3 align-items-end">

                        <div class="col-md-9">

                            <label class="modern-label mb-2">

                                Select .torrent file

                            </label>

                            <input class="form-control modern-input"
                                   type="file"
                                   name="torrent_file"
                                   id="torrent_file"
                                   accept=".torrent"
                                   required>

                        </div>

                        <div class="col-md-3">

                            <button type="submit"
                                    class="btn modern-upload-btn w-100">

                                <i class="bi bi-upload me-1"></i>

                                Upload

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- =========================================
            SEARCH
        ========================================= --}}
        <div class="modern-card mb-4">

            <div class="modern-card-body">

                <form method="GET"
                      action="{{ route('seedboxes.torrents', $seedbox) }}">

                    <div class="modern-search-wrap">

                        <i class="bi bi-search search-icon"></i>

                        <input type="text"
                               name="search"
                               class="modern-search-input"
                               placeholder="Search torrents by name..."
                               value="{{ request('search') }}">

                        <button class="btn modern-search-btn"
                                type="submit">

                            Search

                        </button>

                    </div>

                </form>

            </div>

        </div>

        @php
            $torrentItems = $torrents->items();
        @endphp

        {{-- =========================================
            TORRENT LIST
        ========================================= --}}
        <div class="modern-card">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-list-ul text-info me-2"></i>

                    Torrents

                </h5>

                <span class="torrent-count">

                    {{ count($torrentItems) }}

                </span>

            </div>

            <div class="modern-card-body p-0">

                <div class="table-responsive">

                    <table class="table modern-table align-middle mb-0">

                        <tbody>

                        @foreach($torrentItems as $hash => $torrent)

                            @php
                                $name = $torrent[4] ?? 'Unknown';
                                $size = $torrent[5] ?? 0;
                                $path = $torrent[25] ?? 'Unknown';
                                $downloaded = $torrent[8] ?? 0;
                                $uploaded = $torrent[9] ?? 0;
                                $progress = $size > 0 ? round(($downloaded / $size) * 100, 2) : 0;
                                $state = $torrent[28] ?? 0;
                                $ratio = $downloaded > 0 ? round($uploaded / $downloaded, 2) : 0;

                                if ($progress >= 80) $color = 'bg-success-gradient';
                                elseif ($progress >= 50) $color = 'bg-info-gradient';
                                elseif ($progress >= 30) $color = 'bg-warning-gradient';
                                else $color = 'bg-danger-gradient';

                                if ($state == 1) {
                                    $statusText = 'Seeding';
                                    $statusIcon = 'bi-arrow-up-circle text-success';
                                    $actionIcon = ['type'=>'pause','icon'=>'bi-pause-circle-fill','title'=>'Pause','class'=>'text-warning'];
                                } elseif ($state == 2) {
                                    $statusText = 'Downloading';
                                    $statusIcon = 'bi-arrow-down-circle text-info';
                                    $actionIcon = ['type'=>'pause','icon'=>'bi-pause-circle-fill','title'=>'Pause','class'=>'text-warning'];
                                } elseif ($state == 0) {
                                    $statusText = 'Paused';
                                    $statusIcon = 'bi-stop-circle text-secondary';
                                    $actionIcon = ['type'=>'start','icon'=>'bi-play-circle-fill','title'=>'Start','class'=>'text-success'];
                                } else {
                                    $statusText = 'Unknown';
                                    $statusIcon = 'bi-question-circle text-secondary';
                                    $actionIcon = ['type'=>'start','icon'=>'bi-play-circle-fill','title'=>'Start','class'=>'text-secondary'];
                                }

                                $addedDate = isset($torrent[21])
                                    ? \Carbon\Carbon::createFromTimestamp($torrent[21])->format('Y-m-d H:i')
                                    : 'N/A';

                                $canUpload = Auth::check() && (
                                    Auth::user()->user_class >= \App\Models\UserClass::UPLOADER ||
                                    Auth::user()->uploadpos === 'yes'
                                );
                            @endphp

                            <tr data-name="{{ $name }}">

                                {{-- PROGRESS --}}
                                <td class="progress-column">

                                    <div class="modern-progress">

                                        <div class="progress-bar {{ $color }}"
                                             style="width: {{ $progress }}%"></div>

                                        <span class="progress-text">

                                            {{ $progress }}%

                                        </span>

                                    </div>

                                </td>

                                {{-- INFO --}}
                                <td>

                                    <div class="torrent-name">

                                        <i class="bi bi-file-earmark-text text-info me-2"></i>

                                        {{ $name }}

                                    </div>

                                    <div class="torrent-meta">

                                        <span class="torrent-badge status-badge">

                                            <i class="bi {{ $statusIcon }}"></i>

                                            {{ $statusText }}

                                        </span>

                                        <span class="torrent-badge ratio-badge">

                                            <i class="bi bi-arrow-up-down"></i>

                                            Ratio {{ $ratio }}

                                        </span>

                                        <span class="torrent-badge upload-badge">

                                            <i class="bi bi-arrow-up-circle"></i>

                                            {{ App\Helpers\FormatHelper::formatSize($uploaded) }}

                                        </span>

                                        <span class="torrent-badge download-badge">

                                            <i class="bi bi-arrow-down-circle"></i>

                                            {{ App\Helpers\FormatHelper::formatSize($downloaded) }}

                                        </span>

                                    </div>

                                    <div class="torrent-extra">

                                        <div>

                                            <i class="bi bi-calendar3 me-1"></i>

                                            Added: {{ $addedDate }}

                                        </div>

                                        <div>

                                            <span class="tracker-badge">

                                                <b class="torrent-trackers"
                                                   data-hash="{{ $hash }}">

                                                    Loading trackers...

                                                </b>

                                            </span>

                                        </div>

                                    </div>

                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end">

                                    <div class="torrent-actions">

                                        <form method="POST"
                                              action="{{ route('seedboxes.'.$actionIcon['type'], [$seedbox, $hash]) }}">

                                            @csrf

                                            <button class="action-btn action-btn-dark"
                                                    title="{{ $actionIcon['title'] }}">

                                                <i class="bi {{ $actionIcon['icon'] }} {{ $actionIcon['class'] }}"></i>

                                            </button>

                                        </form>

                                        <form method="POST"
                                              action="{{ route('seedboxes.delete', [$seedbox, $hash]) }}">

                                            @csrf
                                            @method('DELETE')

                                            <button class="action-btn action-btn-danger"
                                                    title="Delete torrent"
                                                    onclick="return confirm('Are you sure you want to delete this torrent? Only the torrent will be removed, not the data.');">

                                                <i class="bi bi-trash-fill"></i>

                                            </button>

                                        </form>

                                        @if($canUpload)

                                            <span class="upload-button"
                                                  data-hash="{{ $hash }}"
                                                  style="display:none;">

                                                <a href="{{ route('seedboxes.downloadRebuiltTorrent', [$seedbox, $hash]) }}"
                                                   class="action-btn action-btn-info"
                                                   title="Upload to {{ config('app.name') }}">

                                                    <i class="bi bi-cloud-arrow-up-fill"></i>

                                                </a>

                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="mt-4">

            {{ $torrents->links('pagination::bootstrap-5') }}

        </div>

    </div>

</div>

{{-- =========================================
    AUTO REFRESH
========================================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const refreshInterval = 20000;

    function refreshTorrents() {

        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        .then(response => response.text())

        .then(html => {

            const parser = new DOMParser();

            const doc = parser.parseFromString(html, 'text/html');

            const newTable = doc.querySelector('.table-responsive');

            const oldTable = document.querySelector('.table-responsive');

            if (newTable && oldTable) {
                oldTable.innerHTML = newTable.innerHTML;
            }

            document.querySelectorAll('.torrent-trackers').forEach(td => {

                const hash = td.dataset.hash;

                fetch(`/seedboxes/{{ $seedbox->id }}/torrent/${hash}/trackers`)

                .then(res => res.json())

                .then(data => {

                    let hosts = [];

                    if (data.trackers && data.trackers.length) {

                        hosts = data.trackers.map(url => {

                            try {

                                let host = new URL(url).hostname;

                                host = host.replace(/^(tracker\.|www\.)/i, '');

                                return host.toLowerCase();

                            } catch (e) {

                                return null;
                            }

                        }).filter(Boolean);

                        td.textContent = [...new Set(hosts)].join(', ');

                    } else {

                        td.textContent = 'N/A';
                    }

                    const uploadBtn = document.querySelector(`.upload-button[data-hash="${hash}"]`);

                    if(uploadBtn){

                        const hasInternalTracker = hosts.some(host =>
                            host.includes('last-torrents.org') ||
                            host.includes('lastfiles.ro')
                        );

                        uploadBtn.style.display = hasInternalTracker
                            ? 'none'
                            : 'inline-block';
                    }

                })

                .catch(() => td.textContent = 'Error');

            });

        })

        .catch(err => console.error('Failed to refresh torrents:', err));
    }

    refreshTorrents();

    setInterval(refreshTorrents, refreshInterval);
});
</script>

<style>

/* =========================================
   BACKGROUND
========================================= */

body{

    background:
        radial-gradient(
            circle at top,
            #1e293b,
            #0f172a 45%,
            #020617
        );

    min-height:100vh;
}

/* =========================================
   HERO
========================================= */

.seedbox-hero{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding:32px;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.07),
            rgba(255,255,255,.03)
        );

    backdrop-filter:blur(18px);

    border:
        1px solid rgba(255,255,255,.08);

    box-shadow:
        0 20px 60px rgba(0,0,0,.45);
}

.hero-kicker{

    color:#60a5fa;

    font-size:.75rem;

    letter-spacing:2px;

    font-weight:800;

    margin-bottom:10px;
}

.hero-title{

    color:white;

    font-size:2.3rem;

    font-weight:900;

    margin:0;
}

.hero-subtitle{

    color:rgba(255,255,255,.65);

    margin-top:8px;
}

/* =========================================
   BUTTONS
========================================= */

.modern-back-btn,
.modern-upload-btn,
.modern-search-btn{

    border:none;

    border-radius:16px;

    font-weight:700;

    color:white;
}

.modern-back-btn{

    background:
        rgba(255,255,255,.08);

    padding:12px 18px;
}

.modern-upload-btn{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    padding:13px 18px;
}

.modern-search-btn{

    background:
        linear-gradient(135deg,#0ea5e9,#2563eb);

    padding:0 24px;
}

.modern-back-btn:hover,
.modern-upload-btn:hover,
.modern-search-btn:hover{

    color:white;

    transform:translateY(-2px);
}

/* =========================================
   CARDS
========================================= */

.modern-card{

    border-radius:24px;

    overflow:hidden;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.03)
        );

    backdrop-filter:blur(16px);

    border:
        1px solid rgba(255,255,255,.06);

    box-shadow:
        0 18px 45px rgba(0,0,0,.35);
}

.modern-card-header{

    padding:20px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    display:flex;

    justify-content:space-between;

    align-items:center;

    color:white;
}

.modern-card-body{

    padding:24px;
}

/* =========================================
   ALERTS
========================================= */

.modern-alert{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:16px 18px;

    border-radius:18px;

    margin-bottom:18px;

    color:white;

    backdrop-filter:blur(10px);
}

.modern-alert-success{

    background:
        rgba(34,197,94,.15);

    border:
        1px solid rgba(34,197,94,.25);
}

.modern-alert-danger{

    background:
        rgba(239,68,68,.15);

    border:
        1px solid rgba(239,68,68,.25);
}

/* =========================================
   STATS
========================================= */

.stats-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(180px,1fr));

    gap:18px;
}

.modern-stat-card{

    padding:22px;

    border-radius:22px;

    text-align:center;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(10px);

    transition:.25s ease;
}

.modern-stat-card:hover{

    transform:translateY(-4px);
}

.modern-stat-card i{

    font-size:1.6rem;

    margin-bottom:10px;
}

.stat-value{

    color:white;

    font-size:1.3rem;

    font-weight:800;
}

.stat-label{

    color:rgba(255,255,255,.55);

    font-size:.82rem;

    margin-top:4px;
}

/* =========================================
   CONNECTION
========================================= */

.connection-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:10px 16px;

    border-radius:999px;

    font-weight:700;

    font-size:.9rem;
}

.connection-badge.online{

    background:
        rgba(34,197,94,.15);

    color:#4ade80;
}

.connection-badge.offline{

    background:
        rgba(239,68,68,.15);

    color:#f87171;
}

/* =========================================
   INPUTS
========================================= */

.modern-label{

    color:#cbd5e1;

    font-size:.8rem;

    font-weight:700;

    letter-spacing:1px;

    text-transform:uppercase;
}

.modern-input,
.modern-search-input{

    background:
        rgba(255,255,255,.04) !important;

    border:
        1px solid rgba(255,255,255,.08) !important;

    color:white !important;

    border-radius:16px !important;

    padding:14px 18px !important;
}

.modern-input:focus,
.modern-search-input:focus{

    box-shadow:
        0 0 0 4px rgba(59,130,246,.15) !important;

    border-color:
        rgba(59,130,246,.35) !important;
}

.modern-search-wrap{

    display:flex;

    align-items:center;

    gap:12px;

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:20px;

    padding:10px 12px;
}

.search-icon{

    color:#60a5fa;

    font-size:1.1rem;

    padding-left:4px;
}

.modern-search-input{

    border:none !important;

    background:transparent !important;

    flex:1;
}

/* =========================================
   TABLE
========================================= */

.modern-table{

    color:white;

    margin-bottom:0;
}

.modern-table tr{

    border-color:
        rgba(255,255,255,.05);
}

.modern-table td{

    padding:20px;
}

.modern-table tbody tr:hover{

    background:
        rgba(255,255,255,.03);
}

/* =========================================
   PROGRESS
========================================= */

.progress-column{

    width:180px;
}

.modern-progress{

    position:relative;

    height:28px;

    border-radius:999px;

    overflow:hidden;

    background:
        rgba(255,255,255,.06);
}

.progress-bar{

    height:100%;

    border-radius:999px;

    transition:width .4s ease;
}

.progress-text{

    position:absolute;

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);

    font-size:.82rem;

    font-weight:800;

    color:white;
}

.bg-success-gradient{
    background:linear-gradient(90deg,#22c55e,#4ade80);
}

.bg-info-gradient{
    background:linear-gradient(90deg,#0ea5e9,#38bdf8);
}

.bg-warning-gradient{
    background:linear-gradient(90deg,#f59e0b,#facc15);
}

.bg-danger-gradient{
    background:linear-gradient(90deg,#ef4444,#f87171);
}

/* =========================================
   TORRENT INFO
========================================= */

.torrent-name{

    color:white;

    font-size:1rem;

    font-weight:700;

    margin-bottom:10px;
}

.torrent-meta{

    display:flex;

    flex-wrap:wrap;

    gap:8px;

    margin-bottom:10px;
}

.torrent-badge{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:6px 12px;

    border-radius:999px;

    font-size:.75rem;

    font-weight:700;
}

.status-badge{
    background:rgba(59,130,246,.15);
    color:#93c5fd;
}

.ratio-badge{
    background:rgba(6,182,212,.15);
    color:#67e8f9;
}

.upload-badge{
    background:rgba(34,197,94,.15);
    color:#4ade80;
}

.download-badge{
    background:rgba(239,68,68,.15);
    color:#f87171;
}

.torrent-extra{

    display:flex;

    flex-wrap:wrap;

    gap:12px;

    color:rgba(255,255,255,.55);

    font-size:.82rem;
}

.tracker-badge{

    display:inline-flex;

    align-items:center;

    padding:4px 10px;

    border-radius:999px;

    background:
        rgba(255,255,255,.05);
}

/* =========================================
   ACTIONS
========================================= */

.torrent-actions{

    display:flex;

    justify-content:flex-end;

    gap:10px;
}

.action-btn{

    width:42px;
    height:42px;

    border:none;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    text-decoration:none;

    transition:.2s ease;

    background:
        rgba(255,255,255,.06);

    color:white;
}

.action-btn:hover{

    transform:translateY(-2px);

    color:white;
}

.action-btn-danger{
    color:#f87171;
}

.action-btn-info{
    color:#67e8f9;
}

/* =========================================
   PAGINATION
========================================= */

.pagination .page-link{

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.06);

    color:white;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .seedbox-hero{

        padding:24px;
    }

    .hero-title{

        font-size:1.7rem;
    }

    .progress-column{

        width:120px;
    }

    .modern-table td{

        padding:16px 12px;
    }

    .torrent-actions{

        flex-wrap:wrap;
    }

    .modern-search-wrap{

        flex-wrap:wrap;
    }

    .modern-search-btn{

        width:100%;
        height:46px;
    }
}

</style>

@endsection