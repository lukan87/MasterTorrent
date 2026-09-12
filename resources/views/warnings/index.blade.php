@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 warnings-page">

    {{-- HEADER --}}
    <div class="warnings-header mb-3">
        <div>
            <h2 class="warnings-title">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Users with Warnings
            </h2>

            <div class="warnings-subtitle">
                View users who currently have warnings
            </div>
        </div>

        <span class="warnings-badge">
            <i class="bi bi-shield-exclamation me-1"></i>
            Warning Management
        </span>
    </div>

    {{-- USERS TABLE --}}
    <div class="warnings-card">

        <div class="table-responsive">
            <table class="table warnings-table align-middle mb-0">

                <thead>
                    <tr>
                        <th>User</th>
                        <th>Warnings Count</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($usersWithWarnings as $user)

                        <tr>

                            {{-- USER --}}
                            <td>
                                <span class="user-name">
                                    <i class="bi bi-person-fill me-1"></i>
                                    {{ $user->name }}
                                </span>
                            </td>

                            {{-- WARNING COUNT --}}
                            <td>
                                <span class="warning-count">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    {{ $user->warnings_count }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-end">
                                <a
                                    href="{{ route('warnings.show', ['id' => $user->id, 'username' => $user->username]) }}"
                                    class="view-warnings-btn">
                                    <i class="bi bi-eye me-1"></i>
                                    View Warnings
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3">
                                <div class="empty-warnings">
                                    <i class="bi bi-shield-check"></i>
                                    <strong>No users with warnings found.</strong>
                                    <span>There are currently no warning records to display.</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

    {{-- PAGINATION --}}
    @if($usersWithWarnings->hasPages())
        <div class="pagination-wrap">
            {{ $usersWithWarnings->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
    .warnings-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .warnings-header,
    .warnings-card {
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
    .warnings-card::before {
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

    .warnings-badge {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: rgba(255, 193, 7, .07);
        border: 1px solid rgba(255, 193, 7, .18);
        border-radius: .4rem;
        color: #e7c967;
        font-size: 10px;
        font-weight: 700;
    }

    .warnings-table {
        min-width: 650px;
        color: #dbe7ef;
        font-size: 12px;
    }

    .warnings-table > :not(caption) > * > * {
        padding: .6rem .7rem;
        border-bottom-color: rgba(255, 255, 255, .055);
    }

    .warnings-table thead th {
        background: rgba(255, 255, 255, .022);
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

    .user-name {
        color: #dbe7ef;
        font-size: 12px;
        font-weight: 600;
    }

    .user-name i {
        color: var(--ui-accent, #20c997);
    }

    .warning-count {
        display: inline-flex;
        align-items: center;
        padding: .2rem .4rem;
        background: rgba(255, 193, 7, .07);
        border: 1px solid rgba(255, 193, 7, .17);
        border-radius: .3rem;
        color: #e7c967;
        font-size: 10px;
        font-weight: 700;
    }

    .view-warnings-btn {
        display: inline-flex;
        align-items: center;
        padding: .3rem .5rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        border-radius: .4rem;
        color: #72e3bb;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: .15s ease;
    }

    .view-warnings-btn:hover {
        background: rgba(32, 201, 151, .16);
        border-color: rgba(32, 201, 151, .35);
        color: #fff;
    }

    .empty-warnings {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .2rem;
        min-height: 105px;
        padding: 1rem;
        color: #718596;
        text-align: center;
    }

    .empty-warnings i {
        color: var(--ui-accent, #20c997);
        font-size: 22px;
    }

    .empty-warnings strong {
        color: #b9c8d0;
        font-size: 13px;
    }

    .empty-warnings span {
        font-size: 11px;
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

    @media (max-width: 767.98px) {
        .warnings-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .warnings-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .warnings-table {
            min-width: 600px;
        }
    }
</style>

@endsection