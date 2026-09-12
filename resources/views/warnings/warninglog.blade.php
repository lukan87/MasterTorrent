@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 warnings-page">

    {{-- HEADER --}}
    <div class="warnings-header mb-3">
        <div>
            <h1 class="warnings-title">
                <i class="bi bi-shield-exclamation me-2"></i>
                Warnings for <strong>{{ $user->name }}</strong>
            </h1>

            <div class="warnings-subtitle">
                Manage active and deleted warnings for this user
            </div>
        </div>

        <span class="user-badge">
            <i class="bi bi-person-fill me-1"></i>
            {{ $user->name }}
        </span>
    </div>

    {{-- ACTIVE WARNINGS --}}
    <div class="section-title mb-2">
        <span>
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Active Warnings
        </span>

        <span class="section-count">
            {{ $warningcount }}
        </span>
    </div>

    @if($warnings->count())

        <div class="warnings-card mb-3">

            <div class="card-section-header">
                <span>
                    <i class="bi bi-list-check me-2"></i>
                    Active Warnings
                </span>
            </div>

            <div class="table-responsive">
                <table class="table warnings-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Warning ID</th>
                            <th>Torrent</th>
                            <th>Issued By</th>
                            <th>Status</th>
                            <th>Expires On</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($warnings as $warning)

                            <tr>

                                {{-- WARNING ID --}}
                                <td>
                                    <span class="warning-id">
                                        #{{ $warning->id }}
                                    </span>
                                </td>

                                {{-- TORRENT --}}
                                <td>
                                    <span class="torrent-name">
                                        {{ $warning->torrenttitle->name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- ISSUED BY --}}
                                <td>
                                    <span class="issued-by">
                                        <i class="bi bi-person-fill me-1"></i>
                                        {{ $warning->warnedBy->name ?? 'Unknown' }}
                                    </span>
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if($warning->active)
                                        <span class="status-badge status-active">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="bi bi-dash-circle-fill"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- EXPIRATION --}}
                                <td>
                                    @if(
                                        $warning->expires_on &&
                                        \Carbon\Carbon::parse($warning->expires_on)->isPast()
                                    )
                                        <span class="expired-badge">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Warning Expired
                                        </span>
                                    @else
                                        <span class="expiry-date">
                                            {{ $warning->expires_on }}
                                        </span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end">
                                    <div class="warning-actions">

                                        <form
                                            action="{{ route('warnings.deactivate', $warning->id) }}"
                                            method="POST">

                                            @csrf

                                            <button
                                                type="submit"
                                                class="action-btn deactivate-btn">
                                                <i class="bi bi-pause-circle me-1"></i>
                                                Deactivate
                                            </button>

                                        </form>

                                        <form
                                            action="{{ route('warnings.delete', $warning->id) }}"
                                            method="POST">

                                            @csrf

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                                onclick="return confirm('Delete this warning?')">
                                                <i class="bi bi-trash me-1"></i>
                                                Delete
                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @endforeach
                    </tbody>

                </table>
            </div>

            <div class="pagination-area">
                {{ $warnings->links('pagination::bootstrap-5') }}
            </div>

        </div>

    @else

        <div class="empty-warning-card mb-3">
            <i class="bi bi-shield-check"></i>
            <strong>No active warnings found.</strong>
        </div>

    @endif

    {{-- DELETED WARNINGS --}}
    <div class="section-title mb-2 mt-3">
        <span>
            <i class="bi bi-trash3-fill me-2"></i>
            Deleted Warnings
        </span>

        <span class="section-count section-count-danger">
            {{ $softDeletedWarningCount }}
        </span>
    </div>

    @if($softDeletedWarnings->count())

        <div class="warnings-card mb-3">

            <div class="card-section-header">
                <span>
                    <i class="bi bi-archive-fill me-2"></i>
                    Deleted Warnings
                </span>
            </div>

            <div class="table-responsive">
                <table class="table warnings-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Warning ID</th>
                            <th>Torrent</th>
                            <th>Issued By</th>
                            <th>Deleted By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($softDeletedWarnings as $warning)

                            <tr class="deleted-warning-row">

                                {{-- WARNING ID --}}
                                <td>
                                    <span class="warning-id">
                                        #{{ $warning->id }}
                                    </span>
                                </td>

                                {{-- TORRENT --}}
                                <td>
                                    <span class="torrent-name">
                                        {{ $warning->torrenttitle->name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- ISSUED BY --}}
                                <td>
                                    <span class="issued-by">
                                        <i class="bi bi-person-fill me-1"></i>
                                        {{ $warning->warnedBy->name ?? 'Unknown' }}
                                    </span>
                                </td>

                                {{-- DELETED BY --}}
                                <td>
                                    <span class="deleted-by">
                                        <i class="bi bi-person-x-fill me-1"></i>
                                        <strong>
                                            {{ $warning->deletedBy->name ?? 'Unknown' }}
                                        </strong>
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end">
                                    <form
                                        action="{{ route('warnings.restore', $warning->id) }}"
                                        method="POST">

                                        @csrf

                                        <button
                                            type="submit"
                                            class="action-btn restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Restore
                                        </button>

                                    </form>
                                </td>

                            </tr>

                        @endforeach
                    </tbody>

                </table>
            </div>

            <div class="pagination-area">
                {{ $softDeletedWarnings->links('pagination::bootstrap-5') }}
            </div>

        </div>

    @else

        <div class="empty-warning-card mb-3">
            <i class="bi bi-trash3"></i>
            <strong>No deleted warnings found.</strong>
        </div>

    @endif

    {{-- BULK ACTIONS --}}
    <div class="bulk-actions">

        <div class="bulk-actions-title">
            <i class="bi bi-tools me-2"></i>
            Warning Management
        </div>

        <div class="bulk-buttons">

            {{-- DEACTIVATE ALL --}}
            <form
                action="{{ route('warnings.deactivateAll', [
                    'id' => $user->id,
                    'username' => $user->username
                ]) }}"
                method="POST">

                @csrf

                <button
                    type="submit"
                    class="bulk-btn deactivate-all-btn"
                    onclick="return confirm('Deactivate all warnings for this user?')">
                    <i class="bi bi-pause-circle-fill me-1"></i>
                    Deactivate All Warnings
                </button>

            </form>

            {{-- DELETE ALL --}}
            <form
                action="{{ route('warnings.deleteAll', [
                    'id' => $user->id,
                    'username' => $user->username
                ]) }}"
                method="POST">

                @csrf

                <button
                    type="submit"
                    class="bulk-btn delete-all-btn"
                    onclick="return confirm('Delete all warnings for this user?')">
                    <i class="bi bi-trash-fill me-1"></i>
                    Delete All Warnings
                </button>

            </form>

        </div>

    </div>

</div>

<style>
    .warnings-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .warnings-header,
    .warnings-card,
    .bulk-actions {
        position: relative;
        background: linear-gradient(
            135deg,
            rgba(22, 32, 51, .96),
            rgba(15, 23, 42, .88)
        );
        border: 1px solid var(--ui-border, rgba(255, 255, 255, .08));
        border-radius: .65rem;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .16);
        overflow: hidden;
    }

    .warnings-header::before,
    .warnings-card::before,
    .bulk-actions::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--ui-accent, #20c997);
        opacity: .75;
    }

    .warnings-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .9rem;
    }

    .warnings-title {
        margin: 0;
        color: #f3f8fb;
        font-size: 18px;
        font-weight: 700;
    }

    .warnings-title i {
        color: #e7c967;
    }

    .warnings-subtitle {
        margin-top: .15rem;
        color: #718596;
        font-size: 11px;
    }

    .user-badge,
    .section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        border-radius: .4rem;
        color: #72e3bb;
        font-size: 10px;
        font-weight: 700;
    }

    .section-count-danger {
        background: rgba(220, 53, 69, .07);
        border-color: rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        color: #b9c8d0;
        font-size: 13px;
        font-weight: 700;
    }

    .section-title > span:first-child i {
        color: #e7c967;
    }

    .warnings-card {
        overflow: hidden;
    }

    .card-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .6rem .8rem;
        background: rgba(255, 255, 255, .022);
        border-bottom: 1px solid rgba(255, 255, 255, .06);
        color: #dbe7ef;
        font-size: 13px;
        font-weight: 700;
    }

    .card-section-header i {
        color: var(--ui-accent, #20c997);
    }

    .warnings-table {
        min-width: 850px;
        color: #dbe7ef;
        font-size: 12px;
    }

    .warnings-table > :not(caption) > * > * {
        padding: .6rem .7rem;
        border-bottom-color: rgba(255, 255, 255, .055);
    }

    .warnings-table thead th {
        background: rgba(255, 255, 255, .018);
        color: #718596;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .warnings-table tbody tr {
        transition: background .14s ease;
    }

    .warnings-table tbody tr:hover {
        background: rgba(32, 201, 151, .025);
    }

    .warnings-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .deleted-warning-row {
        background: rgba(220, 53, 69, .018);
    }

    .warning-id {
        color: #718596;
        font-size: 11px;
        font-weight: 600;
    }

    .torrent-name {
        display: block;
        max-width: 330px;
        overflow: hidden;
        color: #dbe7ef;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .issued-by,
    .deleted-by {
        font-size: 11px;
    }

    .issued-by {
        color: #72e3bb;
    }

    .deleted-by {
        color: #ff8e98;
    }

    .status-badge,
    .expired-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border-radius: .3rem;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .status-inactive {
        background: rgba(108, 117, 125, .08);
        border: 1px solid rgba(108, 117, 125, .18);
        color: #9ba8b0;
    }

    .expired-badge {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .expiry-date {
        color: #8fa2ae;
        font-size: 11px;
        white-space: nowrap;
    }

    .warning-actions {
        display: flex;
        justify-content: flex-end;
        gap: .35rem;
    }

    .warning-actions form {
        margin: 0;
    }

    .action-btn,
    .bulk-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .4rem;
        font-size: 10px;
        font-weight: 700;
        transition: .15s ease;
        white-space: nowrap;
    }

    .action-btn {
        padding: .3rem .5rem;
    }

    .deactivate-btn,
    .deactivate-all-btn {
        background: rgba(255, 193, 7, .07);
        border: 1px solid rgba(255, 193, 7, .18);
        color: #e7c967;
    }

    .deactivate-btn:hover,
    .deactivate-all-btn:hover {
        background: rgba(255, 193, 7, .13);
        border-color: rgba(255, 193, 7, .3);
        color: #fff;
    }

    .delete-btn,
    .delete-all-btn {
        background: rgba(220, 53, 69, .07);
        border: 1px solid rgba(220, 53, 69, .2);
        color: #ff8e98;
    }

    .delete-btn:hover,
    .delete-all-btn:hover {
        background: rgba(220, 53, 69, .14);
        border-color: rgba(220, 53, 69, .38);
        color: #fff;
    }

    .restore-btn {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        color: #72e3bb;
    }

    .restore-btn:hover {
        background: rgba(32, 201, 151, .16);
        border-color: rgba(32, 201, 151, .35);
        color: #fff;
    }

    .pagination-area {
        padding: .55rem .7rem;
        border-top: 1px solid rgba(255, 255, 255, .055);
    }

    .pagination {
        justify-content: center;
        gap: 2px;
        margin: 0;
    }

    .pagination .page-link {
        background: rgba(22, 32, 51, .9);
        border-color: rgba(255, 255, 255, .075);
        color: #aabcc7;
        font-size: 11px;
        padding: .3rem .55rem;
        border-radius: .35rem !important;
    }

    .pagination .page-item.active .page-link {
        background: rgba(32, 201, 151, .13);
        border-color: rgba(32, 201, 151, .28);
        color: #73e2bb;
    }

    .pagination .page-link:hover {
        background: rgba(32, 201, 151, .07);
        border-color: rgba(32, 201, 151, .22);
        color: #fff;
    }

    .empty-warning-card {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-height: 65px;
        padding: .7rem .8rem;
        background: linear-gradient(
            135deg,
            rgba(22, 32, 51, .96),
            rgba(15, 23, 42, .88)
        );
        border: 1px solid var(--ui-border, rgba(255, 255, 255, .08));
        border-radius: .65rem;
        color: #718596;
        font-size: 12px;
    }

    .empty-warning-card i {
        color: var(--ui-accent, #20c997);
        font-size: 18px;
    }

    .empty-warning-card strong {
        color: #b9c8d0;
    }

    .bulk-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .65rem .8rem;
    }

    .bulk-actions-title {
        color: #8fa2ae;
        font-size: 11px;
        font-weight: 700;
    }

    .bulk-actions-title i {
        color: var(--ui-accent, #20c997);
    }

    .bulk-buttons {
        display: flex;
        gap: .4rem;
    }

    .bulk-btn {
        padding: .38rem .6rem;
        font-size: 11px;
    }

    @media (max-width: 900px) {
        .warnings-table {
            min-width: 800px;
        }

        .bulk-actions {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 767.98px) {
        .warnings-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .warnings-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .warning-actions {
            align-items: flex-end;
            flex-direction: column;
        }

        .bulk-buttons {
            flex-direction: column;
            width: 100%;
        }

        .bulk-buttons form,
        .bulk-btn {
            width: 100%;
        }
    }
</style>

@endsection