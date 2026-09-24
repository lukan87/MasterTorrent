@extends('layouts.app')

@section('content')

<div class="container-fluid px-3 px-md-4 py-3 admin-dashboard-page">

    <!-- HEADER -->
    <div class="admin-dashboard-header mb-4">

        <div>
            <h2 class="admin-dashboard-title">
                <i class="bi bi-speedometer2"></i>
                Admin Dashboard
            </h2>

            <p class="admin-dashboard-subtitle">
                System overview and management tools
            </p>
        </div>

        <div class="admin-mode-badge">
            <i class="bi bi-shield-lock"></i>
            Admin Mode
        </div>

    </div>


    <!-- STATS -->
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card stat-torrents h-100">

                <div class="admin-stat-icon">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                </div>

                <div class="admin-stat-content">
                    <div class="admin-stat-value">
                        {{ number_format($totalTorrents) }}
                    </div>

                    <div class="admin-stat-label">
                        Total Torrents
                    </div>

                    <a href="{{ route('admin.torrents.index') }}" class="admin-action-btn">
                        <i class="bi bi-gear"></i>
                        Manage
                    </a>
                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card stat-users h-100">

                <div class="admin-stat-icon">
                    <i class="bi bi-people"></i>
                </div>

                <div class="admin-stat-content">
                    <div class="admin-stat-value">
                        {{ number_format($totalUsers) }}
                    </div>

                    <div class="admin-stat-label">
                        Registered Users
                    </div>

                    <a href="{{ route('admin.users.index') }}" class="admin-action-btn">
                        <i class="bi bi-gear"></i>
                        Manage
                    </a>
                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card stat-movies h-100">

                <div class="admin-stat-icon">
                    <i class="bi bi-film"></i>
                </div>

                <div class="admin-stat-content">
                    <div class="admin-stat-value">
                        {{ number_format($totalMovies) }}
                    </div>

                    <div class="admin-stat-label">
                        Movies
                    </div>

                    <a href="{{ route('admin.movies.index') }}" class="admin-action-btn">
                        <i class="bi bi-gear"></i>
                        Manage
                    </a>
                </div>

            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card stat-series h-100">

                <div class="admin-stat-icon">
                    <i class="bi bi-collection-play"></i>
                </div>

                <div class="admin-stat-content">
                    <div class="admin-stat-value">
                        {{ number_format($totalSeries) }}
                    </div>

                    <div class="admin-stat-label">
                        TV Series
                    </div>

                    <a href="{{ route('admin.series.index') }}" class="admin-action-btn">
                        <i class="bi bi-gear"></i>
                        Manage
                    </a>
                </div>

            </div>
        </div>


        {{-- <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card h-100">

                <div class="admin-stat-icon">
                    <i class="bi bi-clipboard-data"></i>
                </div>

                <div class="admin-stat-content">
                    <div class="admin-stat-value">
                        {{ number_format($totalLogs ?? 0) }}
                    </div>

                    <div class="admin-stat-label">
                        Torrent Logs
                    </div>

                    <a href="{{ route('admin.torrent_logs.index') }}" class="admin-action-btn">
                        <i class="bi bi-eye"></i>
                        View
                    </a>
                </div>

            </div>
        </div> --}}

    </div>


    <!-- QUICK ACTIONS -->
    <div class="row g-3">

        @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

            <div class="col-md-6">

                <div class="admin-action-card h-100">

                    <div class="admin-action-card-header">
                        <div class="admin-section-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>

                        <div>
                            <h5>Happy Hour</h5>
                            <p>Manage manual and automatic happy hours.</p>
                        </div>
                    </div>

                    <div class="admin-card-actions">

                        <a href="{{ route('happyhour.index') }}" class="admin-primary-btn">
                            <i class="bi bi-grid"></i>
                            Open Panel
                        </a>

                        <a href="{{ route('happyhour.create') }}" class="admin-secondary-btn">
                            <i class="bi bi-plus-lg"></i>
                            Start New
                        </a>

                    </div>

                </div>

            </div>

        @endif


        @if(Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)

            <div class="col-md-6">

                <div class="admin-action-card h-100">

                    <div class="admin-action-card-header">
                        <div class="admin-section-icon">
                            <i class="bi bi-terminal"></i>
                        </div>

                        <div>
                            <h5>Developer Tools</h5>
                            <p>System diagnostics and developer utilities.</p>
                        </div>
                    </div>

                    <div class="admin-card-actions">

                        <a href="{{ route('admin.systemInfo.index') }}" class="admin-primary-btn">
                            <i class="bi bi-info-circle"></i>
                            System Info
                        </a>

                        <button type="button" class="admin-secondary-btn">
                            <i class="bi bi-tools"></i>
                            Maintenance
                        </button>

                    </div>

                </div>

            </div>

        @endif


        @auth

            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                <div class="col-md-6">

                    <div class="admin-action-card h-100">

                        <div class="admin-action-card-header">
                            <div class="admin-section-icon">
                                <i class="bi bi-megaphone"></i>
                            </div>

                            <div>
                                <h5>Announcements</h5>
                                <p>Create and manage system announcements.</p>
                            </div>
                        </div>

                        <div class="admin-card-actions">

                            <a href="{{ route('announcements.index') }}" class="admin-primary-btn">
                                <i class="bi bi-list-ul"></i>
                                View All
                            </a>

                            <a href="{{ route('announcements.create') }}" class="admin-secondary-btn">
                                <i class="bi bi-plus-lg"></i>
                                Create New
                            </a>

                        </div>

                    </div>

                </div>


                <!-- <div class="col-md-6">

                    <div class="admin-action-card h-100">

                        <div class="admin-action-card-header">
                            <div class="admin-section-icon">
                                <i class="bi bi-envelope-check"></i>
                            </div>

                            <div>
                                <h5>Remainder Email Sent to users</h5>
                                <p>View emails sent to users.</p>
                            </div>
                        </div>

                        <div class="admin-card-actions">

                            <a href="{{ route('admin.emails.index') }}" class="admin-primary-btn">
                                <i class="bi bi-envelope-open"></i>
                                View All
                            </a>

                        </div>

                    </div>

                </div> -->

            @endif


            @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                <div class="col-md-6">

                    <div class="admin-action-card h-100">

                        <div class="admin-action-card-header">
                            <div class="admin-section-icon">
                                <i class="bi bi-clipboard-data"></i>
                            </div>

                            <div>
                                <h5>Torrent Logs</h5>
                                <p>Track uploads, edits, deletions, and moderation actions.</p>
                            </div>
                        </div>

                        <div class="admin-card-actions">

                            <a href="{{ route('admin.torrent_logs.index') }}" class="admin-primary-btn">
                                <i class="bi bi-journal-text"></i>
                                View Logs
                            </a>

                        </div>

                    </div>

                </div>

            @endif

            @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                <div class="col-md-6">

                    <div class="admin-action-card h-100">

                        <div class="admin-action-card-header">
                            <div class="admin-section-icon">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>

                            <div>
                                <h5>Hit&run's</h5>
                                <p>Track All Hit&run's on site</p>
                            </div>
                        </div>

                        <div class="admin-card-actions">

                            <a href="{{ route('admin.hitrun_amnesty.index') }}" class="admin-primary-btn">
                                <i class="bi bi-tools"></i>
                                Hit & Run Amnesty
                            </a>

                        </div>

                    </div>

                </div>

            @endif

        @endauth

    </div>

</div>


<style>
/* =========================================
   FILEIPLAY ADMIN DASHBOARD
   Dark glass + teal forum style
========================================= */

.admin-dashboard-page {
    max-width: 1550px;
}

.admin-dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.admin-dashboard-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 700;
}

.admin-dashboard-title i {
    margin-right: .4rem;
    color: var(--ui-accent, #22d3c5);
}

.admin-dashboard-subtitle {
    margin: .2rem 0 0;
    color: #64748b;
    font-size: 12px;
}

.admin-mode-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .4rem .65rem;
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border: 1px solid rgba(34,211,238,.2);
    border-radius: .45rem;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.admin-mode-badge i {
    font-size: 12px;
}

/* STAT CARDS */

.admin-stat-card {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    overflow: hidden;
    padding: .85rem;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .75rem;
    box-shadow: 0 8px 22px rgba(0,0,0,.2);
    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
}

.admin-stat-card::before,
.admin-action-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.admin-stat-card:hover,
.admin-action-card:hover {
    transform: translateY(-2px);
    border-color: rgba(34,211,197,.25);
    box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

.admin-stat-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .6rem;
    font-size: 17px;
}

.stat-users .admin-stat-icon {
    color: #67e8f9;
}

.stat-movies .admin-stat-icon {
    color: #5eead4;
}

.stat-series .admin-stat-icon {
    color: #a5f3fc;
}

.admin-stat-content {
    min-width: 0;
}

.admin-stat-value {
    color: #f8fafc;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.1;
}

.admin-stat-label {
    margin-top: .2rem;
    margin-bottom: .55rem;
    color: #64748b;
    font-size: 12px;
}

/* STAT ACTION */

.admin-action-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .3rem .5rem;
    color: #94a3b8;
    background: rgba(15,23,42,.45);
    border: 1px solid rgba(148,163,184,.16);
    border-radius: .4rem;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: .15s ease;
}

.admin-action-btn:hover {
    color: var(--ui-accent, #22d3c5);
    border-color: rgba(34,211,197,.3);
    background: rgba(34,211,197,.05);
}

/* ACTION CARDS */

.admin-action-card {
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: .9rem;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .75rem;
    box-shadow: 0 8px 22px rgba(0,0,0,.2);
    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
}

.admin-action-card-header {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
}

.admin-section-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .55rem;
    font-size: 16px;
}

.admin-action-card h5 {
    margin: .05rem 0 .2rem;
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 700;
}

.admin-action-card p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.45;
}

.admin-card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .85rem;
}

.admin-primary-btn,
.admin-secondary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    min-height: 32px;
    padding: .35rem .6rem;
    border-radius: .45rem;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: .15s ease;
}

.admin-primary-btn {
    color: #071315;
    background: var(--ui-accent, #22d3c5);
    border: 1px solid var(--ui-accent, #22d3c5);
}

.admin-primary-btn:hover {
    color: #071315;
    background: var(--ui-accent-strong, #14b8a6);
    border-color: var(--ui-accent-strong, #14b8a6);
    transform: translateY(-1px);
}

.admin-secondary-btn {
    color: #94a3b8;
    background: rgba(15,23,42,.4);
    border: 1px solid rgba(148,163,184,.18);
}

.admin-secondary-btn:hover {
    color: #e2e8f0;
    background: rgba(148,163,184,.08);
    border-color: rgba(148,163,184,.3);
}

/* MOBILE */

@media (max-width: 767.98px) {
    .admin-dashboard-page {
        padding-top: .75rem !important;
        padding-bottom: .75rem !important;
    }

    .admin-dashboard-header {
        align-items: flex-start;
    }

    .admin-dashboard-title {
        font-size: 14px;
    }

    .admin-mode-badge {
        padding: .35rem .5rem;
    }

    .admin-stat-card {
        min-height: 80px;
    }

    .admin-action-card {
        padding: .8rem;
    }
}

@media (max-width: 480px) {
    .admin-dashboard-header {
        flex-direction: column;
        gap: .65rem;
    }

    .admin-mode-badge {
        align-self: flex-start;
    }

    .admin-stat-value {
        font-size: 16px;
    }

    .admin-card-actions {
        flex-direction: column;
    }

    .admin-primary-btn,
    .admin-secondary-btn {
        width: 100%;
    }
}
</style>

@endsection
