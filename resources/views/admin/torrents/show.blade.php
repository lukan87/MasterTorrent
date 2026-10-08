@extends('layouts.admin')

@section('admin-content')

<div class="container-fluid py-3 admin-torrent-view">
    <div class="row g-3">

        {{-- MAIN COLUMN --}}
        <div class="col-xl-9">

            {{-- TORRENT HEADER --}}
            <div class="torrent-header-card admin-page-header">
                <div class="torrent-header-content">
                    <h1 class="torrent-title">
                        <i class="bi bi-download me-2"></i>
                        {{ $torrent->name }}
                    </h1>

                    <div class="torrent-hash">
                        {{ strtoupper($torrent->info_hash) }}
                    </div>
                </div>

                <div class="torrent-stats">
                    <div class="stat-pill seeders">
                        <i class="bi bi-arrow-up-circle"></i>
                        {{ $seedersCount }}
                    </div>

                    <div class="stat-pill leechers">
                        <i class="bi bi-arrow-down-circle"></i>
                        {{ $leechersCount }}
                    </div>

                    <div class="stat-pill completed">
                        <i class="bi bi-check-circle"></i>
                        {{ $timesCompleted }}
                    </div>
                </div>
            </div>

            {{-- PEERS --}}
            <div class="modern-card mt-3">
                <div class="card-body">

                    <div class="row g-3">

                        {{-- SEEDERS --}}
                        <div class="col-md-6">
                            <h5 class="section-title seed-title">
                                <i class="bi bi-arrow-up-circle me-2"></i>
                                Seeders
                            </h5>

                            @if($seeders->isEmpty())
                                <p class="empty-message">No active seeders</p>
                            @else
                                <div class="peer-list">
                                    @foreach($seeders as $event)
                                        <div class="peer-row">
                                            <div class="peer-user">
                                                <strong>{{ $event->user->name ?? 'Unknown' }}</strong>

                                                <div class="peer-time">
                                                    {{ $event->created_at->diffForHumans() }}
                                                </div>
                                            </div>

                                            <div class="peer-stats">
                                                <div class="upload">
                                                    ↑ {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}
                                                </div>

                                                <div class="download">
                                                    ↓ {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- LEECHERS --}}
                        <div class="col-md-6">
                            <h5 class="section-title leech-title">
                                <i class="bi bi-arrow-down-circle me-2"></i>
                                Leechers
                            </h5>

                            @if($leechers->isEmpty())
                                <p class="empty-message">No active leechers</p>
                            @else
                                <div class="peer-list">
                                    @foreach($leechers as $peer)
                                        <div class="peer-row">
                                            <div class="peer-user">
                                                <strong>{{ $peer->user->name ?? 'Unknown' }}</strong>
                                            </div>

                                            <div class="peer-stats">
                                                <div class="download">
                                                    ↓ {{ \App\Helpers\FormatHelper::formatSize($peer->downloaded) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- SNATCH HISTORY --}}
            <div class="modern-card mt-3">
                <div class="card-body">

                    <div class="section-heading">
                        <div>
                            <h4 class="section-title mb-1">
                                <i class="bi bi-clock-history me-2"></i>
                                Snatched History
                            </h4>

                            <div class="section-subtitle">
                                Download and seeding activity for this torrent
                            </div>
                        </div>

                        <span class="history-count">
                            {{ $history->total() }}
                        </span>
                    </div>

                    @if($history->isEmpty())
                        <div class="empty-history">
                            <i class="bi bi-clock-history"></i>
                            <span>No download history available.</span>
                        </div>
                    @else
                        <div class="history-container">
                            @foreach($history as $event)
                                <div class="history-row">

                                    <div class="history-user">
                                        <strong>{{ $event->user->name ?? 'Unknown User' }}</strong>

                                        <div class="history-time">
                                            {{ $event->created_at->format('Y-m-d H:i') }}
                                        </div>
                                    </div>

                                    <div class="history-badges">
                                        <span class="status-badge {{ $event->seeder ? 'status-seeded' : 'status-not-seeded' }}">
                                            <i class="bi {{ $event->seeder ? 'bi-arrow-up-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                            {{ $event->seeder ? 'Seeder' : 'Not Seeding' }}
                                        </span>

                                        <span class="status-badge {{ $event->completed_at ? 'status-completed' : 'status-pending' }}">
                                            <i class="bi {{ $event->completed_at ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                                            {{ $event->completed_at ? 'Completed' : 'Incomplete' }}
                                        </span>
                                    </div>

                                    <div class="history-stats">
                                        <span>
                                            <i class="bi bi-arrow-up"></i>
                                            {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}
                                        </span>

                                        <span>
                                            <i class="bi bi-arrow-down"></i>
                                            {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}
                                        </span>

                                        <span>
                                            <i class="bi bi-stopwatch"></i>
                                            {{ \App\Helpers\FormatHelper::formatTime($event->seedtime) }}
                                        </span>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="pagination-wrap">
                            {{ $history->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                </div>
            </div>

        </div>

        {{-- SIDEBAR --}}
        <div class="col-xl-3">
            <div class="modern-card sticky-info">
                <div class="card-body">

                    <h5 class="section-title mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Torrent Info
                    </h5>

                    <ul class="info-list">
                        <li>
                            <span>ID</span>
                            <strong>{{ $torrent->id }}</strong>
                        </li>

                        <li>
                            <span>Slug</span>
                            <strong title="{{ $torrent->slug }}">{{ $torrent->slug }}</strong>
                        </li>

                        <li>
                            <span>File</span>
                            <strong title="{{ $torrent->file_name }}">{{ $torrent->file_name }}</strong>
                        </li>

                        <li>
                            <span>Files</span>
                            <strong>{{ $torrent->num_files }}</strong>
                        </li>
                    </ul>

                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /*
     * FileIplay Admin Torrent View
     * Dark glass / teal forum style
     */

    .admin-torrent-view {
        color: var(--theme-text, #dbe7ef);
        font-size: var(--site-font-body, 13px);
    }

    .torrent-header-card,
    .modern-card {
        position: relative;
        background: linear-gradient(
            135deg,
            var(--theme-surface, rgba(14,21,33,.96)),
            var(--theme-surface, rgba(10,15,27,.88))
        );
        border: 1px solid var(--ui-border, var(--theme-border, rgba(255, 255, 255, .08)));
        border-radius: .65rem;
        box-shadow: 0 6px 18px var(--theme-shadow, rgba(0, 0, 0, .16));
        overflow: hidden;
    }

    .torrent-header-card::before,
    .modern-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--theme-teal-action, var(--ui-accent, #20c997));
        opacity: .75;
    }

    .torrent-header-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
    }

    .torrent-header-content {
        min-width: 0;
        flex: 1 1 auto;
    }

    .torrent-title {
        margin: 0 0 .3rem;
        color: var(--theme-text, #f3f8fb);
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .torrent-title i {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
    }

    .torrent-hash {
        display: inline-block;
        max-width: 100%;
        padding: .2rem .45rem;
        background: var(--theme-surface, rgba(5,10,18,.5));
        border: 1px solid var(--theme-border, rgba(255, 255, 255, .07));
        border-radius: .35rem;
        color: var(--theme-muted, #7f929f);
        font-family: monospace;
        font-size: var(--site-font-small, 13px);
        overflow-wrap: anywhere;
    }

    .torrent-stats {
        display: flex;
        align-items: center;
        gap: .35rem;
        flex: 0 0 auto;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        min-width: 42px;
        justify-content: center;
        padding: .3rem .5rem;
        border: 1px solid var(--theme-border, rgba(255, 255, 255, .07));
        border-radius: .4rem;
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
    }

    .seeders {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        color: var(--theme-green-text, #72e0a9);
    }

    .leechers {
        background: var(--theme-red-soft, rgba(220, 53, 69, .08));
        color: var(--theme-red-text, #ff8e98);
    }

    .completed {
        background: var(--theme-amber-soft, rgba(255, 193, 7, .07));
        color: var(--theme-amber-text, #e8c66d);
    }

    .modern-card .card-body {
        padding: .8rem .9rem;
    }

    .section-title {
        margin: 0;
        color: var(--theme-text, #eaf2f5);
        font-size: var(--site-font-body, 13px);
        font-weight: 700;
    }

    .section-title i {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
    }

    .seed-title i {
        color: var(--theme-green-text, #72e0a9);
    }

    .leech-title i {
        color: var(--theme-red-text, #ff8e98);
    }

    .section-subtitle {
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .65rem;
        padding-bottom: .55rem;
        border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .065));
    }

    .history-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 25px;
        padding: 0 .45rem;
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border: 1px solid var(--theme-teal-border, rgba(32, 201, 151, .2));
        border-radius: .35rem;
        color: var(--theme-teal-text, #72e3bb);
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
    }

    .peer-list {
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .peer-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .45rem .15rem;
        border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .055));
    }

    .peer-row:last-child {
        border-bottom: 0;
    }

    .peer-user {
        min-width: 0;
    }

    .peer-user strong {
        display: block;
        color: var(--theme-text, #dce8ed);
        font-size: var(--site-font-body, 13px);
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .peer-time,
    .history-time {
        margin-top: .1rem;
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .peer-stats {
        flex: 0 0 auto;
        text-align: right;
        font-size: var(--site-font-small, 13px);
        line-height: 1.5;
    }

    .upload {
        color: var(--theme-green-text, #72e0a9);
    }

    .download {
        color: var(--theme-red-text, #ff9a9f);
    }

    .empty-message {
        margin: .65rem 0 0;
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .history-container {
        max-height: 540px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .history-row {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) auto minmax(230px, auto);
        align-items: center;
        gap: .7rem;
        padding: .55rem .15rem;
        border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .055));
    }

    .history-row:last-child {
        border-bottom: 0;
    }

    .history-user strong {
        display: block;
        color: var(--theme-text, #dce8ed);
        font-size: var(--site-font-small, 13px);
        font-weight: 600;
    }

    .history-badges {
        display: flex;
        align-items: center;
        gap: .3rem;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border: 1px solid transparent;
        border-radius: .3rem;
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
        white-space: nowrap;
    }

    .status-seeded {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .18));
        color: var(--theme-green-text, #72e0a9);
    }

    .status-not-seeded {
        background: var(--theme-red-soft, rgba(220, 53, 69, .08));
        border-color: var(--theme-red-border, rgba(220, 53, 69, .18));
        color: var(--theme-red-text, #ff8e98);
    }

    .status-completed {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .18));
        color: var(--theme-green-text, #72e0a9);
    }

    .status-pending {
        background: var(--theme-surface-alt, rgba(255,255,255,0.0245));
        border-color: var(--theme-border, rgba(255, 255, 255, .08));
        color: var(--theme-muted, #899ba7);
    }

    .history-stats {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: .55rem;
        color: var(--theme-muted, #8fa2ae);
        font-size: var(--site-font-small, 13px);
        white-space: nowrap;
    }

    .history-stats span {
        display: inline-flex;
        align-items: center;
        gap: .2rem;
    }

    .history-stats i {
        color: var(--theme-muted, #6f8490);
    }

    .empty-history {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 70px;
        padding: .8rem;
        background: var(--theme-amber-soft, rgba(255, 193, 7, .035));
        border: 1px solid var(--theme-amber-border, rgba(255, 193, 7, .1));
        border-radius: .45rem;
        color: var(--theme-muted, #a99b70);
        font-size: var(--site-font-small, 13px);
    }

    .empty-history i {
        color: var(--theme-amber-text, #d2ad45);
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: .65rem;
    }

    .pagination {
        gap: 2px;
        margin-bottom: 0;
    }

    .pagination .page-link {
        background: var(--theme-surface, rgba(14,21,33,.9));
        border-color: var(--theme-border, rgba(255, 255, 255, .075));
        color: var(--theme-muted, #aabcc7);
        font-size: var(--site-font-small, 13px);
        padding: .3rem .55rem;
        border-radius: .35rem !important;
    }

    .pagination .page-item.active .page-link {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .13));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .28));
        color: var(--theme-teal-text, #73e2bb);
    }

    .pagination .page-link:hover {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .07));
        color:  var(--theme-text, #fff);
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .22));
    }

    .sticky-info {
        position: sticky;
        top: 85px;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        padding: .45rem 0;
        border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .055));
    }

    .info-list li:last-child {
        border-bottom: 0;
    }

    .info-list span {
        flex: 0 0 auto;
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .info-list strong {
        min-width: 0;
        color: var(--theme-text, #dbe7ef);
        font-size: var(--site-font-small, 13px);
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .peer-list::-webkit-scrollbar,
    .history-container::-webkit-scrollbar {
        width: 5px;
    }

    .peer-list::-webkit-scrollbar-track,
    .history-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .peer-list::-webkit-scrollbar-thumb,
    .history-container::-webkit-scrollbar-thumb {
        background: var(--theme-surface-alt, rgba(255,255,255,0.091));
        border-radius: 5px;
    }

    @media (max-width: 1199.98px) {
        .sticky-info {
            position: static;
        }

        .history-row {
            grid-template-columns: 1fr auto;
        }

        .history-stats {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .admin-torrent-view {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .torrent-header-card {
            align-items: flex-start;
            flex-direction: column;
            padding: .75rem .85rem;
        }

        .torrent-title {
            font-size: var(--site-font-body, 13px);
        }

        .torrent-stats {
            width: 100%;
        }

        .stat-pill {
            flex: 1 1 0;
        }

        .modern-card .card-body {
            padding: .7rem .75rem;
        }

        .history-row {
            grid-template-columns: 1fr;
            gap: .45rem;
        }

        .history-stats {
            font-size: var(--site-font-small, 13px);
        }
    }
</style>

@endsection
