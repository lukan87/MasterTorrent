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

                            host.includes('fileiplay.org') ||

                            host.includes('fileiplay.ro')

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
   FILEIPLAY SEEDBOX TORRENT CONTROL
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.seedbox-page {
    color: #e2e8f0;
}

.seedbox-hero,
.modern-card,
.modern-stat-card {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px rgba(0,0,0,.18);
}

.seedbox-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.05rem 1.2rem;
    border-left: 3px solid var(--ui-accent, #22d3ee);
    border-radius: .85rem;
}

.seedbox-hero::after {
    content: "";
    position: absolute;
    top: -100px;
    right: -90px;
    width: 210px;
    height: 210px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34,211,238,.09), transparent 70%);
    pointer-events: none;
}

.hero-content { min-width: 0; }

.hero-kicker {
    margin-bottom: .3rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.hero-title {
    margin: 0;
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.hero-title i { color: var(--ui-accent, #22d3ee); }

.hero-subtitle {
    margin-top: .35rem;
    color: rgba(226,232,240,.58);
    font-size: 13px;
}

.connection-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .65rem;
    border-radius: .55rem;
    font-size: 12px;
    font-weight: 700;
}

.connection-badge.online {
    border: 1px solid rgba(34,197,94,.22);
    background: rgba(34,197,94,.08);
    color: #86efac;
}

.connection-badge.offline {
    border: 1px solid rgba(239,68,68,.22);
    background: rgba(239,68,68,.08);
    color: #fca5a5;
}

.modern-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .45rem .7rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .55rem;
    background: rgba(255,255,255,.035);
    color: rgba(226,232,240,.72);
    font-size: 13px;
    font-weight: 700;
}

.modern-back-btn:hover {
    border-color: rgba(34,211,238,.28);
    background: rgba(34,211,238,.06);
    color: #fff;
}

/* Alerts */
.modern-alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .65rem .8rem;
    margin-bottom: .8rem;
    border-radius: .65rem;
    color: #e2e8f0;
    font-size: 13px;
}

.modern-alert-success {
    border: 1px solid rgba(34,197,94,.20);
    border-left: 3px solid rgba(34,197,94,.60);
    background: rgba(34,197,94,.07);
}

.modern-alert-danger {
    border: 1px solid rgba(239,68,68,.20);
    border-left: 3px solid rgba(239,68,68,.60);
    background: rgba(239,68,68,.07);
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: .65rem;
}

.modern-stat-card {
    padding: .8rem .55rem;
    border-radius: .7rem;
    text-align: center;
    background: rgba(255,255,255,.025);
    transition: transform .2s ease, border-color .2s ease;
}

.modern-stat-card:hover {
    transform: translateY(-1px);
    border-color: rgba(34,211,238,.20);
}

.modern-stat-card i {
    display: block;
    margin-bottom: .3rem;
    font-size: 16px;
}

.modern-stat-card .text-info,
.modern-stat-card .text-primary {
    color: var(--ui-accent, #22d3ee) !important;
}

.stat-value {
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.stat-label {
    margin-top: .2rem;
    color: rgba(226,232,240,.45);
    font-size: 11px;
}

/* Cards */
.modern-card {
    overflow: hidden;
    border-radius: .85rem;
}

.modern-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .95rem;
    border-bottom: 1px solid var(--ui-border, rgba(255,255,255,.08));
}

.modern-card-header h5 {
    margin: 0;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
}

.modern-card-header .text-info {
    color: var(--ui-accent, #22d3ee) !important;
}

.modern-card-body { padding: .95rem; }

.torrent-count {
    min-width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(34,211,238,.20);
    border-radius: .5rem;
    background: rgba(34,211,238,.07);
    color: var(--ui-accent, #22d3ee);
    font-size: 12px;
    font-weight: 700;
}

/* Inputs */
.modern-label {
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 700;
}

.modern-input {
    min-height: 40px;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
    border-radius: .6rem !important;
    background: rgba(255,255,255,.035) !important;
    color: #f8fafc !important;
    font-size: 13px;
    box-shadow: none !important;
}

.modern-input:focus {
    border-color: rgba(34,211,238,.45) !important;
    background: rgba(34,211,238,.035) !important;
    box-shadow: 0 0 0 3px rgba(34,211,238,.08) !important;
}

.modern-input::file-selector-button {
    margin: -0.375rem .75rem -0.375rem -0.75rem;
    padding: .375rem .7rem;
    border: 0;
    border-right: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: rgba(34,211,238,.06);
    color: var(--ui-accent, #22d3ee);
}

/* Search */
.modern-search-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .4rem .35rem .65rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .65rem;
    background: rgba(255,255,255,.025);
}

.search-icon {
    flex: 0 0 auto;
    color: var(--ui-accent, #22d3ee);
    font-size: 14px;
}

.modern-search-input {
    min-width: 0;
    flex: 1;
    border: 0 !important;
    outline: 0 !important;
    background: transparent !important;
    color: #f8fafc !important;
    font-size: 13px;
    box-shadow: none !important;
}

.modern-search-input::placeholder { color: rgba(226,232,240,.35); }

.modern-search-btn,
.modern-upload-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(34,211,238,.28);
    border-radius: .55rem;
    background: rgba(34,211,238,.10);
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
    transition: all .2s ease;
}

.modern-search-btn {
    min-height: 38px;
    padding: 0 .85rem;
}

.modern-upload-btn { min-height: 40px; }

.modern-search-btn:hover,
.modern-upload-btn:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.16);
    color: #fff;
    transform: translateY(-1px);
}

/* Table */
.table-responsive {
    border-radius: .65rem;
}

.modern-table {
    min-width: 850px;
    margin-bottom: 0;
    color: #e2e8f0;
    font-size: 13px;
}

.modern-table tbody tr {
    border-color: var(--ui-border, rgba(255,255,255,.06)) !important;
    transition: background .2s ease;
}

.modern-table tbody tr:hover {
    background: rgba(34,211,238,.025);
}

.modern-table td {
    padding: .75rem .7rem;
    border-color: var(--ui-border, rgba(255,255,255,.06)) !important;
    vertical-align: middle;
}

/* Progress */
.progress-column { width: 150px; }

.modern-progress {
    position: relative;
    height: 24px;
    overflow: hidden;
    border: 1px solid var(--ui-border, rgba(255,255,255,.07));
    border-radius: .45rem;
    background: rgba(255,255,255,.045);
}

.progress-bar {
    height: 100%;
    border-radius: .35rem;
    transition: width .4s ease;
}

.bg-success-gradient { background: linear-gradient(90deg, #16a34a, #22c55e); }
.bg-info-gradient { background: linear-gradient(90deg, #0891b2, #22d3ee); }
.bg-warning-gradient { background: linear-gradient(90deg, #d97706, #f59e0b); }
.bg-danger-gradient { background: linear-gradient(90deg, #dc2626, #ef4444); }

.progress-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f8fafc;
    font-size: 11px;
    font-weight: 700;
}

/* Torrent info */
.torrent-name {
    margin-bottom: .4rem;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.torrent-name .text-info { color: var(--ui-accent, #22d3ee) !important; }

.torrent-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem;
    margin-bottom: .4rem;
}

.torrent-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .45rem;
    border-radius: .4rem;
    font-size: 11px;
    font-weight: 700;
}

.status-badge {
    border: 1px solid rgba(34,211,238,.18);
    background: rgba(34,211,238,.06);
    color: var(--ui-accent, #22d3ee);
}

.ratio-badge {
    border: 1px solid rgba(34,211,238,.15);
    background: rgba(34,211,238,.045);
    color: #a5f3fc;
}

.upload-badge {
    border: 1px solid rgba(34,197,94,.16);
    background: rgba(34,197,94,.055);
    color: #86efac;
}

.download-badge {
    border: 1px solid rgba(239,68,68,.16);
    background: rgba(239,68,68,.055);
    color: #fca5a5;
}

.torrent-extra {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
    color: rgba(226,232,240,.45);
    font-size: 11px;
}

.tracker-badge {
    display: inline-flex;
    align-items: center;
    padding: .18rem .4rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.07));
    border-radius: .4rem;
    background: rgba(255,255,255,.025);
}

/* Actions */
.torrent-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .35rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .5rem;
    background: rgba(255,255,255,.035);
    color: rgba(226,232,240,.72);
    text-decoration: none;
    transition: all .2s ease;
}

.action-btn:hover {
    border-color: rgba(34,211,238,.25);
    background: rgba(34,211,238,.07);
    color: #fff;
    transform: translateY(-1px);
}

.action-btn-danger { color: #fca5a5; }

.action-btn-danger:hover {
    border-color: rgba(239,68,68,.25);
    background: rgba(239,68,68,.07);
    color: #fff;
}

.action-btn-info { color: var(--ui-accent, #22d3ee); }

/* Pagination */
.pagination { margin-bottom: 0; }

.pagination .page-link {
    border-color: var(--ui-border, rgba(255,255,255,.08));
    background: rgba(255,255,255,.035);
    color: rgba(226,232,240,.72);
    font-size: 12px;
}

.pagination .page-link:hover {
    border-color: rgba(34,211,238,.25);
    background: rgba(34,211,238,.07);
    color: #fff;
}

.pagination .active .page-link {
    border-color: rgba(34,211,238,.30);
    background: rgba(34,211,238,.12);
    color: var(--ui-accent, #22d3ee);
}

/* Mobile */
@media (max-width: 1100px) {
    .stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 767.98px) {
    .seedbox-page .container-fluid {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .seedbox-hero { padding: .9rem; }
    .hero-title { font-size: 18px; }
    .hero-subtitle { font-size: 12px; }

    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .modern-card-body { padding: .75rem; }

    .modern-search-wrap { flex-wrap: wrap; }
    .modern-search-btn { width: 100%; }

    .torrent-actions { flex-wrap: wrap; }
}
</style>

@endsection