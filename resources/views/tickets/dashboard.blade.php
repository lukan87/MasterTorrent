@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 ticket-dashboard-page">

    <div class="dashboard-header mb-4">
        <div>
            <h2 class="dashboard-title">
                <i class="bi bi-speedometer2"></i>
                Ticket Dashboard
            </h2>
            <p class="dashboard-subtitle">Overview of the current support ticket activity.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="dashboard-stat stat-open">
                <div class="stat-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['open'] }}</div>
                    <div class="stat-label">Open</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="dashboard-stat stat-staff">
                <div class="stat-icon">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['waiting_staff'] }}</div>
                    <div class="stat-label">Waiting Staff</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="dashboard-stat stat-user">
                <div class="stat-icon">
                    <i class="bi bi-person-clock"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['waiting_user'] }}</div>
                    <div class="stat-label">Waiting User</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="dashboard-stat stat-resolved">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['resolved'] }}</div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>

    </div>

    <div class="dashboard-panel">

        <div class="dashboard-panel-header">
            <div>
                <i class="bi bi-clock-history"></i>
                Recent Tickets
            </div>
        </div>

        <div class="dashboard-panel-body">

            <div class="table-responsive">
                <table class="table ticket-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($recentTickets as $ticket)

                            <tr>

                                <td class="ticket-id">
                                    #{{ $ticket->id }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route('tickets.show', [
                                            'id' => $ticket->id,
                                            'slug' => $ticket->slug
                                        ]) }}"
                                        class="ticket-title"
                                    >
                                        {{ $ticket->title }}
                                    </a>
                                </td>

                                <td>
                                    <span class="ticket-status">
                                        {{ $ticket->status }}
                                    </span>
                                </td>

                                <td>
                                    <span class="ticket-priority priority-{{ strtolower($ticket->priority) }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>

            @if($recentTickets->isEmpty())
                <div class="dashboard-empty">
                    <i class="bi bi-life-preserver"></i>
                    <span>No recent tickets found.</span>
                </div>
            @endif

        </div>

    </div>

</div>

<style>
/* =========================================
   FILEIPLAY TICKET DASHBOARD
   Dark glass + teal forum style
========================================= */

.ticket-dashboard-page {
    max-width: 1500px;
}

.dashboard-header {
    padding: .25rem .1rem;
}

.dashboard-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
}

.dashboard-title i {
    margin-right: .4rem;
    color: var(--ui-accent, #22d3c5);
}

.dashboard-subtitle {
    margin: .2rem 0 0;
    color: #64748b;
    font-size: 13px;
}

/* STATS */
.dashboard-stat {
    position: relative;
    display: flex;
    align-items: center;
    gap: .7rem;
    min-height: 76px;
    padding: .8rem;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .75rem;
    box-shadow: 0 8px 22px rgba(0,0,0,.2);
    transition: transform .18s ease, border-color .18s ease;
}

.dashboard-stat::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
}

.dashboard-stat:hover {
    transform: translateY(-2px);
    border-color: rgba(34,211,197,.24);
}

.stat-open::before,
.stat-staff::before,
.stat-user::before {
    background: var(--ui-accent, #22d3c5);
}

.stat-resolved::before {
    background: #22c55e;
}

.stat-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.17);
    border-radius: .6rem;
    font-size: 17px;
}

.stat-resolved .stat-icon {
    color: #4ade80;
    background: rgba(34,197,94,.07);
    border-color: rgba(74,222,128,.17);
}

.stat-value {
    color: #f8fafc;
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    margin-top: .25rem;
    color: #94a3b8;
    font-size: 12px;
}

/* PANEL */
.dashboard-panel {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .85rem;
    box-shadow: 0 10px 28px rgba(0,0,0,.22);
}

.dashboard-panel::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.dashboard-panel-header {
    display: flex;
    align-items: center;
    gap: .45rem;
    min-height: 44px;
    padding: .7rem .9rem;
    color: #f1f5f9;
    background: rgba(15,23,42,.4);
    border-bottom: 1px solid var(--ui-border, rgba(148,163,184,.16));
    font-size: 14px;
    font-weight: 700;
}

.dashboard-panel-header i {
    color: var(--ui-accent, #22d3c5);
}

.dashboard-panel-body {
    padding: .35rem .65rem .65rem;
}

/* TABLE */
.ticket-table {
    color: #cbd5e1;
    font-size: 13px;
}

.ticket-table thead th {
    padding: .65rem .55rem;
    color: #64748b;
    background: rgba(15,23,42,.35);
    border-bottom: 1px solid rgba(148,163,184,.13);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .45px;
    white-space: nowrap;
}

.ticket-table tbody td {
    padding: .7rem .55rem;
    color: #cbd5e1;
    background: transparent;
    border-bottom: 1px solid rgba(148,163,184,.08);
}

.ticket-table tbody tr:last-child td {
    border-bottom: 0;
}

.ticket-table tbody tr {
    transition: background .15s ease;
}

.ticket-table tbody tr:hover td {
    background: rgba(34,211,197,.035);
}

.ticket-id {
    color: #64748b !important;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.ticket-title {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.ticket-title:hover {
    color: var(--ui-accent, #22d3c5);
}

.ticket-status,
.ticket-priority {
    display: inline-flex;
    align-items: center;
    min-height: 25px;
    padding: .25rem .5rem;
    border-radius: .4rem;
    font-size: 11px;
    font-weight: 600;
}

.ticket-status {
    color: #67e8f9;
    background: rgba(14,116,144,.22);
    border: 1px solid rgba(34,211,238,.2);
}

.ticket-priority {
    color: #cbd5e1;
    background: rgba(71,85,105,.42);
    border: 1px solid rgba(148,163,184,.15);
}

.priority-critical {
    color: #fecaca;
    background: rgba(127,29,29,.65);
    border-color: rgba(248,113,113,.22);
}

.priority-high {
    color: #fde68a;
    background: rgba(120,53,15,.65);
    border-color: rgba(251,191,36,.22);
}

.priority-medium {
    color: #67e8f9;
    background: rgba(14,116,144,.22);
    border-color: rgba(34,211,238,.2);
}

/* EMPTY */
.dashboard-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: 2rem 1rem;
    color: #64748b;
    font-size: 13px;
}

.dashboard-empty i {
    color: var(--ui-accent, #22d3c5);
    font-size: 22px;
}

/* MOBILE */
@media (max-width: 768px) {
    .ticket-dashboard-page {
        padding-left: .5rem !important;
        padding-right: .5rem !important;
    }

    .dashboard-stat {
        min-height: 68px;
        padding: .65rem;
    }

    .stat-icon {
        width: 34px;
        height: 34px;
        flex-basis: 34px;
        font-size: 15px;
    }

    .stat-value {
        font-size: 16px;
    }

    .stat-label {
        font-size: 11px;
    }

    .ticket-table {
        min-width: 600px;
    }
}
</style>

@endsection
