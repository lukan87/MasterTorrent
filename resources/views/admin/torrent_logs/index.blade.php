@extends('layouts.admin')

@section('admin-content')

@php
    $actions = [
        'uploaded' => ['color' => 'success', 'icon' => 'bi-upload'],
        'edited' => ['color' => 'warning', 'icon' => 'bi-pencil-square'],
        'deleted' => ['color' => 'danger', 'icon' => 'bi-trash'],
        'restored' => ['color' => 'info', 'icon' => 'bi-arrow-counterclockwise'],
        'force_deleted' => ['color' => 'dark', 'icon' => 'bi-x-octagon'],
    ];
@endphp

<div class="container-fluid px-3 px-md-4 py-3 torrent-logs-page">

    {{-- LOG HEADER --}}
    <div class="logs-page-header mb-2 admin-page-header">

        <div>
            <h4>
                <i class="bi bi-journal-text"></i>
                Torrent Logs
            </h4>
            <p>Track uploads, edits, deletions and restoration activity.</p>
        </div>

        <span class="logs-total">
            {{ $logs->total() }} entries
        </span>

    </div>


    {{-- FILTER PANEL --}}
    <div class="logs-filter-panel mb-3">

        <div class="logs-filter-header">
            <div class="logs-section-icon">
                <i class="bi bi-funnel"></i>
            </div>

            <div>
                <h5>Log Filters</h5>
                <p>Filter torrent activity by date, action, class or user.</p>
            </div>
        </div>

        <div class="logs-filter-body">

            <form method="GET" class="d-flex flex-column gap-3">

                @php
                    $currentAction = request('action');
                    $range = request('range');
                @endphp

                {{-- DATE FILTER --}}
                <div class="filter-section">

                    <div class="filter-label">
                        <i class="bi bi-calendar3"></i>
                        Date Range
                    </div>

                    <div class="filter-buttons">

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => 'today'])) }}"
                           class="filter-btn {{ $range === 'today' ? 'filter-active' : '' }}">
                            Today
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => '7'])) }}"
                           class="filter-btn {{ $range === '7' ? 'filter-active' : '' }}">
                            7 days
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => '30'])) }}"
                           class="filter-btn {{ $range === '30' ? 'filter-active' : '' }}">
                            30 days
                        </a>

                    </div>

                </div>


                {{-- ACTION FILTERS --}}
                <div class="filter-section">

                    <div class="filter-label">
                        <i class="bi bi-activity"></i>
                        Actions
                    </div>

                    <div class="filter-buttons">

                        <a href="{{ route('admin.torrent_logs.index', request()->except('action')) }}"
                           class="filter-btn {{ !$currentAction ? 'filter-active' : '' }}">
                            All <span>{{ $counts['all'] }}</span>
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'uploaded'])) }}"
                           class="filter-btn filter-success {{ $currentAction === 'uploaded' ? 'filter-active' : '' }}">
                            <i class="bi bi-upload"></i>
                            Uploaded <span>{{ $counts['uploaded'] }}</span>
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'edited'])) }}"
                           class="filter-btn filter-warning {{ $currentAction === 'edited' ? 'filter-active' : '' }}">
                            <i class="bi bi-pencil-square"></i>
                            Edited <span>{{ $counts['edited'] }}</span>
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'deleted'])) }}"
                           class="filter-btn filter-danger {{ $currentAction === 'deleted' ? 'filter-active' : '' }}">
                            <i class="bi bi-trash"></i>
                            Deleted <span>{{ $counts['deleted'] }}</span>
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'restored'])) }}"
                           class="filter-btn filter-info {{ $currentAction === 'restored' ? 'filter-active' : '' }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Restored <span>{{ $counts['restored'] }}</span>
                        </a>

                        <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'force_deleted'])) }}"
                           class="filter-btn filter-force {{ $currentAction === 'force_deleted' ? 'filter-active' : '' }}">
                            <i class="bi bi-x-octagon"></i>
                            Force Deleted <span>{{ $counts['force_deleted'] }}</span>
                        </a>

                    </div>

                </div>


                {{-- DROPDOWNS --}}
                <div class="filter-section">

                    <div class="filter-label">
                        <i class="bi bi-sliders"></i>
                        User Filters
                    </div>

                    <div class="filter-selects">

                        <select name="user_class"
                                class="form-select form-select-sm log-filter-select"
                                onchange="this.form.submit()">

                            <option value="">All Classes</option>

                            @foreach($classes as $id => $name)

                                <option value="{{ $id }}"
                                    {{ request('user_class') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>

                            @endforeach

                        </select>


                        <select name="user_id"
                                class="form-select form-select-sm log-filter-select"
                                onchange="this.form.submit()">

                            <option value="">All Users</option>

                            @foreach($users as $user)

                                <option value="{{ $user->id }}"
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>

                            @endforeach

                        </select>


                        @if(request('action'))
                            <input type="hidden" name="action" value="{{ request('action') }}">
                        @endif


                        <a href="{{ route('admin.torrent_logs.index') }}"
                           class="reset-filter-btn">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- LOG TABLE --}}
    <div class="logs-table-card">

        {{-- HEADER ROW --}}
        <div class="row g-0 logs-table-header align-items-center">

            <div class="d-none d-md-block col-md-2 text-center">
                User
            </div>

            <div class="col-12 col-md-5 px-2">
                Torrent
            </div>

            <div class="col-6 col-md-2 text-center">
                Action
            </div>

            <div class="col-6 col-md-2 text-center">
                Date
            </div>

            <div class="d-none d-md-block col-md-1 text-center">
                View
            </div>

        </div>


        {{-- ROWS --}}
        @forelse($logs as $log)

            @php
                $action = $actions[$log->action] ?? [
                    'color' => 'secondary',
                    'icon' => 'bi-question-circle'
                ];

                $rowClass = match($log->action) {
                    'deleted' => 'log-row-deleted',
                    'edited' => 'log-row-edited',
                    'restored' => 'log-row-restored',
                    'force_deleted' => 'log-row-force-deleted',
                    default => ''
                };
            @endphp


            <div class="row g-0 align-items-center logs-row {{ $rowClass }}"
                 onclick="window.location='{{ route('admin.torrent_logs.show', $log->id) }}'"
                 style="cursor:pointer;">

                {{-- USER --}}
                <div class="d-none d-md-block col-md-2 text-center log-user">
                    {{ $log->user->name ?? 'Unknown' }}
                </div>


                {{-- TORRENT + DESCRIPTION --}}
                <div class="col-12 col-md-5 px-2 log-torrent">

                    <div class="log-torrent-name">
                        {{ $log->torrent->name ?? 'Deleted Torrent' }}
                    </div>

                    @if($log->description)

                        <div class="log-description">
                            {{ $log->description }}
                        </div>

                    @endif


                    {{-- MOBILE USER --}}
                    <div class="log-mobile-user d-md-none">
                        <i class="bi bi-person"></i>
                        {{ $log->user->name ?? 'Unknown' }}
                    </div>

                </div>


                {{-- ACTION --}}
                <div class="col-6 col-md-2 text-center">

                    <span class="log-action-badge log-action-{{ $action['color'] }}">
                        <i class="bi {{ $action['icon'] }}"></i>
                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                    </span>

                </div>


                {{-- DATE --}}
                <div class="col-6 col-md-2 text-center log-date">

                    {{ $log->created_at->format('M d, Y') }}

                    <div>
                        {{ $log->created_at->format('H:i') }}
                    </div>

                </div>


                {{-- VIEW --}}
                <div class="d-none d-md-block col-md-1 text-center">

                    <a href="{{ route('admin.torrent_logs.show', $log->id) }}"
                       class="log-view-btn"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip"
                       title="View Log">
                        <i class="bi bi-eye"></i>
                    </a>

                </div>

            </div>

        @empty

            <div class="logs-empty">
                <i class="bi bi-inbox"></i>
                <span>No logs found.</span>
            </div>

        @endforelse

    </div>


    {{-- PAGINATION --}}
    <div class="logs-pagination mt-3">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>

</div>


<style>
/* =========================================
   FILEIPLAY TORRENT LOGS
   Dark glass + teal forum style
========================================= */

.torrent-logs-page {
    max-width: 1550px;
}

/* FILTER PANEL */

.logs-filter-panel,
.logs-table-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.95)),
        var(--theme-surface, rgba(10,15,27,.84))
    );
    border: 1px solid var(--ui-border, var(--theme-border, rgba(148,163,184,.16)));
    border-radius: .75rem;
    box-shadow: 0 9px 25px var(--theme-shadow, rgba(0,0,0,.2));
}

.logs-filter-panel::before,
.logs-table-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--theme-teal-action, var(--ui-accent, #22d3c5));
}

/* FILTER HEADER */

.logs-filter-header {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .8rem .9rem;
    background: var(--theme-surface, rgba(10,15,27,.4));
    border-bottom: 1px solid var(--theme-border, rgba(148,163,184,.12));
}

.logs-section-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, var(--theme-on-action, #22d3c5));
    background: var(--theme-teal-soft, rgba(34,211,197,.07));
    border: 1px solid var(--theme-teal-border, rgba(34,211,197,.16));
    border-radius: .55rem;
    font-size: 15px;
}

.logs-filter-header h5 {
    margin: 0 0 .15rem;
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.logs-filter-header p {
    margin: 0;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

.logs-filter-body {
    padding: .85rem;
}

.filter-section {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: .35rem;
    color: var(--theme-muted, #94a3b8);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .35px;
}

.filter-label i {
    color: var(--ui-accent, var(--theme-teal-text, #22d3c5));
}

.filter-buttons,
.filter-selects {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    align-items: center;
}

.filter-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    min-height: 30px;
    padding: .3rem .55rem;
    color: var(--theme-muted, #94a3b8);
    background: var(--theme-surface, rgba(10,15,27,.5));
    border: 1px solid var(--theme-border, rgba(148,163,184,.15));
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    text-decoration: none;
    transition: .15s ease;
}

.filter-btn span {
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

.filter-btn:hover {
    color:  var(--theme-text, #e2e8f0);
    border-color: var(--theme-teal-border, rgba(34,211,197,.25));
    background: var(--theme-teal-soft, rgba(34,211,197,.04));
}

.filter-btn.filter-active {
    color: var(--theme-on-action, #071315);
    background: var(--theme-teal-action, var(--ui-accent, #22d3c5));
    border-color: var(--ui-accent, var(--theme-teal-border, #22d3c5));
}

.filter-btn.filter-active span {
    color: var(--theme-muted, #164e4a);
}

.filter-success.filter-active {
    color: var(--theme-text, #071315);
    background: var(--theme-green-soft, #4ade80);
    border-color: var(--theme-green-border, #4ade80);
}

.filter-warning.filter-active {
    color: var(--theme-text, #1c1917);
    background: var(--theme-amber-soft, #fbbf24);
    border-color: var(--theme-amber-border, #fbbf24);
}

.filter-danger.filter-active {
    color: var(--theme-text, #fff);
    background: var(--theme-red-soft, #dc2626);
    border-color: var(--theme-red-border, #dc2626);
}

.filter-info.filter-active {
    color:  var(--theme-text, #071315);
    background: var(--theme-teal-soft, #22d3ee);
    border-color: var(--theme-teal-border, #22d3ee);
}

.filter-force.filter-active {
    color: var(--theme-text, #fff);
    background: var(--theme-surface, #212a37);
    border-color: var(--theme-border, #475569);
}

.log-filter-select {
    width: auto;
    min-width: 180px;
    color: var(--theme-text, #cbd5e1);
    background-color: var(--theme-control, rgba(10,15,27,.72));
    border: 1px solid var(--theme-border, rgba(148,163,184,.18));
    border-radius: .45rem;
    font-size: var(--site-font-small, 13px);
}

.log-filter-select:focus {
    color: var(--theme-text, #f1f5f9);
    background-color: var(--theme-control, rgba(10,15,27,.9));
    border-color: var(--theme-teal-border, rgba(34,211,197,.4));
    box-shadow: 0 0 0 .15rem var(--theme-shadow, rgba(34,211,197,.07));
}

.log-filter-select option {
    background: var(--theme-control, #0b1019);
    color: var(--theme-text, #e2e8f0);
}

.reset-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    min-height: 31px;
    padding: .3rem .55rem;
    color: var(--theme-muted, #94a3b8);
    background: var(--theme-surface, rgba(33,42,55,.3));
    border: 1px solid var(--theme-border, rgba(148,163,184,.16));
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    text-decoration: none;
}

.reset-filter-btn:hover {
    color: var(--theme-text, #e2e8f0);
    background: var(--theme-surface-alt, rgba(148,163,184,0.056));
    border-color: var(--theme-border, rgba(148,163,184,.28));
}

/* PAGE HEADER */

.logs-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.logs-page-header h4 {
    margin: 0;
    color: var(--theme-text, #f1f5f9);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.logs-page-header h4 i {
    margin-right: .35rem;
    color: var(--ui-accent, var(--theme-teal-text, #22d3c5));
}

.logs-page-header p {
    margin: .15rem 0 0;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

.logs-total {
    display: inline-flex;
    align-items: center;
    padding: .35rem .55rem;
    color: var(--theme-teal-text, #67e8f9);
    background: var(--theme-teal-soft, rgba(14,116,144,.18));
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.2));
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    white-space: nowrap;
}

/* TABLE */

.logs-table-card {
    min-width: 0;
}

.logs-table-header {
    min-height: 44px;
    color: var(--theme-muted, #64748b);
    background: var(--theme-surface, rgba(10,15,27,.55));
    border-bottom: 1px solid var(--theme-border, rgba(148,163,184,.13));
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.logs-row {
    position: relative;
    min-height: 64px;
    color: var(--theme-text, #cbd5e1);
    border-bottom: 1px solid var(--theme-border, rgba(148,163,184,.08));
    transition: background .15s ease;
}

.logs-row:last-child {
    border-bottom: 0;
}

.logs-row:hover {
    background: var(--theme-teal-soft, rgba(34,211,197,.035));
}

/* ACTION ROW HIGHLIGHTS */

.log-row-deleted {
    background: var(--theme-red-soft, rgba(127,29,29,.06));
}

.log-row-edited {
    background: var(--theme-amber-soft, rgba(120,53,15,.055));
}

.log-row-restored {
    background: var(--theme-teal-soft, rgba(14,116,144,.05));
}

.log-row-force-deleted {
    background: var(--theme-surface, rgba(10,15,27,.5));
}

.log-row-deleted::before,
.log-row-edited::before,
.log-row-restored::before,
.log-row-force-deleted::before {
    content: "";
    position: absolute;
    left: 3px;
    top: 0;
    bottom: 0;
    width: 2px;
}

.log-row-deleted::before {
    background: var(--theme-red-soft, rgba(248,113,113,.65));
}

.log-row-edited::before {
    background: var(--theme-amber-soft, rgba(251,191,36,.65));
}

.log-row-restored::before {
    background: var(--theme-teal-soft, rgba(34,211,238,.65));
}

.log-row-force-deleted::before {
    background: var(--theme-surface, #64748b);
}

/* LOG CONTENT */

.log-user {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    overflow-wrap: anywhere;
}

.log-torrent {
    min-width: 0;
}

.log-torrent-name {
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.log-description {
    margin-top: .2rem;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.log-mobile-user {
    margin-top: .25rem;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

.log-mobile-user i {
    color: var(--ui-accent, var(--theme-teal-text, #22d3c5));
}

/* ACTION BADGES */

.log-action-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .3rem;
    min-height: 27px;
    padding: .3rem .5rem;
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
    white-space: nowrap;
}

.log-action-success {
    color: var(--theme-text, #bbf7d0);
    background: var(--theme-surface, rgba(20,83,45,.35));
    border: 1px solid var(--theme-green-border, rgba(74,222,128,.17));
}

.log-action-warning {
    color: var(--theme-amber-text, #fde68a);
    background: var(--theme-amber-soft, rgba(120,53,15,.35));
    border: 1px solid var(--theme-amber-border, rgba(251,191,36,.17));
}

.log-action-danger {
    color: var(--theme-text, #fecaca);
    background: var(--theme-red-soft, rgba(127,29,29,.38));
    border: 1px solid var(--theme-red-border, rgba(248,113,113,.18));
}

.log-action-info {
    color: var(--theme-teal-text, #a5f3fc);
    background: var(--theme-teal-soft, rgba(14,116,144,.2));
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.18));
}

.log-action-dark {
    color: var(--theme-text, #cbd5e1);
    background: var(--theme-surface, rgba(33,42,55,.45));
    border: 1px solid var(--theme-border, rgba(148,163,184,.16));
}

.log-action-secondary {
    color: var(--theme-text, #cbd5e1);
    background: var(--theme-surface, rgba(33,42,55,.4));
    border: 1px solid var(--theme-border, rgba(148,163,184,.14));
}

/* DATE */

.log-date {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    line-height: 1.45;
}

.log-date div {
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

/* VIEW */

.log-view-btn {
    width: 29px;
    height: 29px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-muted, #94a3b8);
    background: var(--theme-surface, rgba(10,15,27,.5));
    border: 1px solid var(--theme-border, rgba(148,163,184,.16));
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    text-decoration: none;
    transition: .15s ease;
}

.log-view-btn:hover {
    color: var(--ui-accent, var(--theme-on-action, #22d3c5));
    background: var(--theme-teal-soft, rgba(34,211,197,.05));
    border-color: var(--theme-teal-border, rgba(34,211,197,.25));
}

/* EMPTY */

.logs-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: .4rem;
    min-height: 150px;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-small, 13px);
}

.logs-empty i {
    color: var(--ui-accent, var(--theme-teal-text, #22d3c5));
    font-size: 24px;
}

/* PAGINATION */

.logs-pagination {
    display: flex;
    justify-content: center;
}

.logs-pagination .pagination {
    margin-bottom: 0;
}

.logs-pagination .page-link {
    color: var(--theme-muted, #94a3b8);
    background: var(--theme-surface, rgba(10,15,27,.75));
    border-color: var(--theme-border, rgba(148,163,184,.13));
    font-size: var(--site-font-small, 13px);
}

.logs-pagination .page-link:hover {
    color: var(--ui-accent, var(--theme-on-action, #22d3c5));
    background: var(--theme-teal-soft, rgba(34,211,197,.05));
    border-color: var(--theme-teal-border, rgba(34,211,197,.2));
}

.logs-pagination .page-item.active .page-link {
    color: var(--theme-on-action, #071315);
    background: var(--theme-teal-action, var(--ui-accent, #22d3c5));
    border-color: var(--ui-accent, var(--theme-teal-border, #22d3c5));
}

/* MOBILE */

@media (max-width: 767.98px) {

    .torrent-logs-page {
        padding-top: .75rem !important;
        padding-bottom: .75rem !important;
    }

    .logs-page-header {
        align-items: flex-start;
    }

    .filter-selects {
        align-items: stretch;
    }

    .log-filter-select,
    .reset-filter-btn {
        width: 100%;
    }

    .logs-table-card {
        overflow-x: auto;
    }

    .logs-table-header,
    .logs-row {
        min-width: 850px;
    }

    .logs-table-header {
        position: sticky;
        left: 0;
    }
}

@media (max-width: 480px) {

    .logs-filter-header {
        padding: .7rem;
    }

    .logs-filter-body {
        padding: .7rem;
    }

    .logs-page-header {
        flex-direction: column;
        gap: .5rem;
    }

    .logs-total {
        align-self: flex-start;
    }

    .filter-buttons {
        gap: .3rem;
    }

    .filter-btn {
        font-size: var(--site-font-small, 13px);
    }
}
</style>

@endsection
