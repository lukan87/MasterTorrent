@extends('layouts.app')

@section('title', 'Manage Happy Hours')

@section('content')

<div class="container-fluid px-3 px-md-4 py-3 happyhour-page">

    {{-- PAGE HEADER --}}
    <div class="happyhour-page-header mb-3">

        <div>
            <h2 class="happyhour-page-title">
                <i class="bi bi-clock-history"></i>
                Happy Hours
            </h2>

            <p class="happyhour-page-subtitle">
                Manage automatic and manual events
            </p>
        </div>

        <div class="happyhour-header-actions">

            {{-- Automatic Toggle --}}
            <form action="{{ route('happyhour.toggleAutomatic') }}" method="POST">
                @csrf

                <button type="submit"
                        class="hh-btn {{ $automatic ? 'hh-btn-danger' : 'hh-btn-success' }}">
                    <i class="bi {{ $automatic ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                    {{ $automatic ? 'Disable Automatic' : 'Enable Automatic' }}
                </button>
            </form>

            {{-- Start New Happy Hour --}}
            @if($automatic)

                <span data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="Cannot start manual Happy Hour while Automatic mode is enabled">

                    <a href="#"
                       class="hh-btn hh-btn-primary disabled-btn">
                        <i class="bi bi-plus-lg"></i>
                        Start New Happy Hour
                    </a>

                </span>

            @else

                <a href="{{ route('happyhour.create') }}"
                   class="hh-btn hh-btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    Start New Happy Hour
                </a>

            @endif

        </div>

    </div>


    {{-- AUTOMATIC STATUS --}}
    <div class="happyhour-status-card mb-3 {{ $automatic ? 'active' : 'inactive' }}">

        <div class="status-left">

            <div class="status-icon">
                <i class="bi {{ $automatic ? 'bi-lightning-charge-fill' : 'bi-pause-circle' }}"></i>
            </div>

            <div>

                @if($automatic)

                    <div class="status-title">
                        {{ $theme['name'] }}
                    </div>

                    <div class="status-details">
                        <span>
                            <i class="bi bi-cloud-plus"></i>
                            {{ $theme['upload_multiplier'] }}x Upload
                        </span>

                        <span>
                            <i class="bi bi-clock"></i>
                            {{ $theme['duration_hours'] }}h
                        </span>

                        <span>
                            <i class="bi bi-cloud-download"></i>
                            {{ $theme['free_download'] ? 'Free Download' : 'No Free Download' }}
                        </span>
                    </div>

                @else

                    <div class="status-title">
                        Automatic Happy Hour
                    </div>

                    <div class="status-details">
                        No automatic Happy Hour enabled.
                    </div>

                @endif

            </div>

        </div>

        <span class="hh-status-badge {{ $automatic ? 'is-active' : 'is-off' }}">
            <span class="status-dot"></span>
            {{ $automatic ? 'ACTIVE' : 'OFF' }}
        </span>

    </div>


    {{-- TABLE CARD --}}
    <div class="happyhour-table-card">

        {{-- DESKTOP HEADER --}}
        <div class="row g-0 happyhour-table-header align-items-center">

            <div class="col-3 px-3">
                Happy Hour
            </div>

            <div class="col-1 text-center">
                <i class="bi bi-cloud-plus" data-bs-toggle="tooltip" title="Upload Multiplier"></i>
            </div>

            <div class="col-1 text-center">
                <i class="bi bi-cloud-download" data-bs-toggle="tooltip" title="Free Download"></i>
            </div>

            <div class="col-2 text-center">
                <i class="bi bi-play-circle" data-bs-toggle="tooltip" title="Started"></i>
            </div>

            <div class="col-2 text-center">
                <i class="bi bi-stop-circle" data-bs-toggle="tooltip" title="End"></i>
            </div>

            <div class="col-1 text-center">
                <i class="bi bi-activity" data-bs-toggle="tooltip" title="Status"></i>
            </div>

            <div class="d-none d-md-block col-1 text-center">
                <i class="bi bi-person-check" data-bs-toggle="tooltip" title="Started By"></i>
            </div>

            <div class="col-1 text-center">
                <i class="bi bi-gear" data-bs-toggle="tooltip" title="Actions"></i>
            </div>

        </div>


        {{-- ROWS --}}
        @forelse($happyHours as $hh)

            <div class="row g-0 align-items-center happyhour-row {{ $hh->automatic ? 'happyhour-auto' : '' }}">

                {{-- THEME --}}
                <div class="col-3 px-3 happyhour-theme">

                    <span class="theme-name">
                        {{ $hh->theme }}
                    </span>

                    @if($hh->automatic)
                        <span class="hh-mini-badge auto-badge">
                            AUTO
                        </span>
                    @endif

                </div>


                {{-- UPLOAD --}}
                <div class="col-1 text-center">
                    <span class="upload-multiplier">
                        {{ $hh->upload_multiplier }}x
                    </span>
                </div>


                {{-- FREE --}}
                <div class="col-1 text-center">

                    <span class="hh-mini-badge {{ $hh->free_download ? 'yes-badge' : 'no-badge' }}">
                        {{ $hh->free_download ? 'Yes' : 'No' }}
                    </span>

                </div>


                {{-- START --}}
                <div class="col-2 text-center happyhour-date">
                    {{ $hh->start_at?->format('M d, Y H:i') ?? '—' }}
                </div>


                {{-- END --}}
                <div class="col-2 text-center happyhour-date">
                    {{ $hh->end_at?->format('M d, Y H:i') ?? '—' }}
                </div>


                {{-- STATUS --}}
                <div class="col-1 text-center">

                    <span class="hh-mini-badge {{ $hh->active ? 'active-badge' : 'stopped-badge' }}">
                        {{ $hh->active ? 'Active' : 'Stopped' }}
                    </span>

                </div>


                {{-- STARTED BY --}}
                <div class="d-none d-md-block col-1 text-center happyhour-user">
                    {{ $hh->user?->name ?? 'System' }}
                </div>


                {{-- ACTIONS --}}
                <div class="col-1 text-center">

                    <div class="happyhour-row-actions">

                        @if($hh->active)

                            <form action="{{ route('happyhour.stop', $hh) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('PATCH')

                                <button class="hh-icon-btn stop-btn"
                                        data-bs-toggle="tooltip"
                                        title="Stop">
                                    <i class="bi bi-stop-circle"></i>
                                </button>

                            </form>

                        @endif


                        {{-- DELETE --}}
                        @if($automatic)

                            <span data-bs-toggle="tooltip"
                                  data-bs-placement="top"
                                  title="Cannot delete Happy Hours while Automatic mode is enabled">

                                <button class="hh-icon-btn delete-btn disabled-btn">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </span>

                        @else

                            <form action="{{ route('happyhour.destroy', $hh) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this Happy Hour?');">

                                @csrf
                                @method('DELETE')

                                <button class="hh-icon-btn delete-btn"
                                        data-bs-toggle="tooltip"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="happyhour-empty">
                <i class="bi bi-clock-history"></i>
                <span>No Happy Hours found.</span>
            </div>

        @endforelse

    </div>


    {{-- PAGINATION --}}
    <div class="happyhour-pagination mt-3">
        {{ $happyHours->links('pagination::bootstrap-5') }}
    </div>

</div>


<style>
/* =========================================
   FILEIPLAY HAPPY HOURS
   Dark glass + teal forum style
========================================= */

.happyhour-page {
    max-width: 1550px;
}

/* HEADER */

.happyhour-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.happyhour-page-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 700;
}

.happyhour-page-title i {
    margin-right: .4rem;
    color: var(--ui-accent, #22d3c5);
}

.happyhour-page-subtitle {
    margin: .2rem 0 0;
    color: #64748b;
    font-size: 12px;
}

.happyhour-header-actions {
    display: flex;
    align-items: stretch;
    gap: .45rem;
    flex-wrap: wrap;
}

.happyhour-header-actions form {
    margin: 0;
}

/* BUTTONS */

.hh-btn {
    min-height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    padding: .4rem .65rem;
    border-radius: .45rem;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition:
        transform .15s ease,
        border-color .15s ease,
        background .15s ease;
}

.hh-btn:hover {
    transform: translateY(-1px);
}

.hh-btn-primary {
    color: #071315;
    background: var(--ui-accent, #22d3c5);
    border: 1px solid var(--ui-accent, #22d3c5);
}

.hh-btn-primary:hover {
    color: #071315;
    background: var(--ui-accent-strong, #14b8a6);
    border-color: var(--ui-accent-strong, #14b8a6);
}

.hh-btn-success {
    color: #bbf7d0;
    background: rgba(20,83,45,.5);
    border: 1px solid rgba(74,222,128,.22);
}

.hh-btn-success:hover {
    color: #dcfce7;
    background: rgba(22,101,52,.65);
    border-color: rgba(74,222,128,.35);
}

.hh-btn-danger {
    color: #fecaca;
    background: rgba(127,29,29,.5);
    border: 1px solid rgba(248,113,113,.22);
}

.hh-btn-danger:hover {
    color: #fee2e2;
    background: rgba(153,27,27,.65);
    border-color: rgba(248,113,113,.35);
}

.disabled-btn {
    opacity: .45;
    pointer-events: none;
    cursor: not-allowed;
}

/* STATUS */

.happyhour-status-card {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .75rem .85rem;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .7rem;
    box-shadow: 0 7px 20px rgba(0,0,0,.18);
}

.happyhour-status-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
}

.happyhour-status-card.active::before {
    background: #22c55e;
}

.happyhour-status-card.inactive::before {
    background: #64748b;
}

.status-left {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 0;
}

.status-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .55rem;
    font-size: 15px;
}

.active .status-icon {
    color: #4ade80;
    background: rgba(34,197,94,.07);
    border: 1px solid rgba(74,222,128,.16);
}

.inactive .status-icon {
    color: #94a3b8;
    background: rgba(71,85,105,.22);
    border: 1px solid rgba(148,163,184,.13);
}

.status-title {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 700;
}

.status-details {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem .8rem;
    margin-top: .2rem;
    color: #64748b;
    font-size: 11px;
}

.status-details span {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}

.status-details i {
    color: #94a3b8;
}

.hh-status-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .5rem;
    border-radius: .4rem;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.hh-status-badge.is-active {
    color: #bbf7d0;
    background: rgba(20,83,45,.38);
    border: 1px solid rgba(74,222,128,.18);
}

.hh-status-badge.is-off {
    color: #94a3b8;
    background: rgba(51,65,85,.38);
    border: 1px solid rgba(148,163,184,.13);
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.is-active .status-dot {
    background: #4ade80;
    box-shadow: 0 0 0 3px rgba(74,222,128,.08);
}

.is-off .status-dot {
    background: #64748b;
}

/* TABLE */

.happyhour-table-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .75rem;
    box-shadow: 0 9px 25px rgba(0,0,0,.2);
}

.happyhour-table-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.happyhour-table-header {
    position: sticky;
    top: 56px;
    z-index: 20;
    min-height: 46px;
    color: #64748b;
    background: rgba(15,23,42,.97);
    border-bottom: 1px solid rgba(148,163,184,.13);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .35px;
}

.happyhour-table-header i {
    color: var(--ui-accent, #22d3c5);
    font-size: 15px;
}

.happyhour-row {
    position: relative;
    min-height: 54px;
    color: #cbd5e1;
    border-bottom: 1px solid rgba(148,163,184,.08);
    transition: background .15s ease;
}

.happyhour-row:last-child {
    border-bottom: 0;
}

.happyhour-row:hover {
    background: rgba(34,211,197,.035);
}

/* AUTOMATIC ROW */

.happyhour-auto {
    background: rgba(34,211,197,.025);
}

.happyhour-auto::after {
    content: "";
    position: absolute;
    left: 3px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(34,211,197,.55);
}

/* CELLS */

.happyhour-theme {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .35rem;
}

.theme-name {
    color: #e2e8f0;
    font-size: 12px;
    font-weight: 700;
}

.upload-multiplier {
    color: #67e8f9;
    font-size: 12px;
    font-weight: 700;
}

.happyhour-date,
.happyhour-user {
    color: #94a3b8;
    font-size: 11px;
}

/* BADGES */

.hh-mini-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .2rem .35rem;
    border-radius: .32rem;
    font-size: 9px;
    font-weight: 700;
    white-space: nowrap;
}

.auto-badge {
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border: 1px solid rgba(34,211,238,.17);
}

.yes-badge,
.active-badge {
    color: #bbf7d0;
    background: rgba(20,83,45,.35);
    border: 1px solid rgba(74,222,128,.16);
}

.no-badge,
.stopped-badge {
    color: #94a3b8;
    background: rgba(51,65,85,.4);
    border: 1px solid rgba(148,163,184,.13);
}

/* ACTIONS */

.happyhour-row-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .25rem;
}

.hh-icon-btn {
    width: 29px;
    height: 29px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: .4rem;
    font-size: 12px;
    transition:
        transform .15s ease,
        background .15s ease,
        border-color .15s ease;
}

.hh-icon-btn:hover {
    transform: translateY(-1px);
}

.stop-btn {
    color: #fde68a;
    background: rgba(120,53,15,.38);
    border: 1px solid rgba(251,191,36,.17);
}

.stop-btn:hover {
    color: #fef3c7;
    background: rgba(146,64,14,.55);
    border-color: rgba(251,191,36,.3);
}

.delete-btn {
    color: #fecaca;
    background: rgba(127,29,29,.38);
    border: 1px solid rgba(248,113,113,.17);
}

.delete-btn:hover {
    color: #fee2e2;
    background: rgba(153,27,27,.55);
    border-color: rgba(248,113,113,.3);
}

/* EMPTY */

.happyhour-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    min-height: 110px;
    color: #64748b;
    font-size: 12px;
}

.happyhour-empty i {
    color: var(--ui-accent, #22d3c5);
    font-size: 17px;
}

/* PAGINATION */

.happyhour-pagination {
    display: flex;
    justify-content: center;
}

.happyhour-pagination .pagination {
    margin-bottom: 0;
}

.happyhour-pagination .page-link {
    color: #94a3b8;
    background: rgba(15,23,42,.75);
    border-color: rgba(148,163,184,.13);
    font-size: 11px;
}

.happyhour-pagination .page-link:hover {
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.05);
    border-color: rgba(34,211,197,.2);
}

.happyhour-pagination .page-item.active .page-link {
    color: #071315;
    background: var(--ui-accent, #22d3c5);
    border-color: var(--ui-accent, #22d3c5);
}

/* MOBILE */

@media (max-width: 767.98px) {

    .happyhour-page {
        padding-top: .75rem !important;
        padding-bottom: .75rem !important;
    }

    .happyhour-page-header {
        align-items: flex-start;
    }

    .happyhour-header-actions {
        width: 100%;
    }

    .happyhour-header-actions form,
    .happyhour-header-actions > a,
    .happyhour-header-actions > span {
        flex: 1 1 180px;
    }

    .happyhour-header-actions .hh-btn {
        width: 100%;
    }

    .happyhour-status-card {
        align-items: flex-start;
    }

    .happyhour-table-card {
        overflow-x: auto;
    }

    .happyhour-table-header,
    .happyhour-row {
        min-width: 850px;
    }

    .happyhour-table-header {
        position: static;
    }
}

@media (max-width: 480px) {

    .happyhour-status-card {
        flex-direction: column;
    }

    .hh-status-badge {
        align-self: flex-start;
    }

    .status-details {
        flex-direction: column;
        gap: .2rem;
    }
}
</style>

@endsection
