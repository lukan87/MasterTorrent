@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 uploader-applications-page">

    @php
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT AGE
        |--------------------------------------------------------------------------
        */

        $accountAgeDays = $user->created_at->diffInDays(now());
        $ageOk = $accountAgeDays >= 30;

        $diff = $user->created_at->diff(now());

        $years = $diff->y;
        $months = $diff->m;
        $weeks = intdiv($diff->d, 7);
        $remainingDays = $diff->d % 7;

        /*
        |--------------------------------------------------------------------------
        | UPLOAD
        |--------------------------------------------------------------------------
        */

        $uploadedGB = $user->uploaded;
        $uploadRequirement = 300;

        $uploadOk = $uploadedGB >= $uploadRequirement;

        /*
        |--------------------------------------------------------------------------
        | RATIO
        |--------------------------------------------------------------------------
        */

        $ratio = $user->downloaded > 0
            ? $user->uploaded / $user->downloaded
            : 0;

        $ratioRequirement = 1.05;

        $ratioOk = $ratio >= $ratioRequirement;

        /*
        |--------------------------------------------------------------------------
        | ELIGIBILITY
        |--------------------------------------------------------------------------
        */

        $eligible = $ageOk && $uploadOk && $ratioOk;

        /*
        |--------------------------------------------------------------------------
        | COOLDOWN
        |--------------------------------------------------------------------------
        */

        $cooldown = $user->uploaderApplicationCooldown();
    @endphp

    {{-- HEADER --}}
    <div class="uploader-header mb-3">
        <div>
            <h1 class="uploader-title">
                <i class="bi bi-person-badge-fill me-2"></i>
                Uploader Applications
            </h1>
            <div class="uploader-subtitle">
                Review uploader requirements and application status
            </div>
        </div>

        <span class="header-badge">
            <i class="bi bi-cloud-arrow-up me-1"></i>
            Uploader
        </span>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="modern-alert alert-success-modern mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="modern-alert alert-danger-modern mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- REQUIREMENTS --}}
    @if($user->user_class < 5)

        <div class="requirements-card mb-3">
            <div class="section-header">
                <div>
                    <i class="bi bi-shield-check me-2"></i>
                    Uploader Requirements
                </div>
            </div>

            <div class="requirements-body">

                {{-- ACCOUNT AGE --}}
                <div class="requirement-row">
                    <div class="requirement-status {{ $ageOk ? 'requirement-pass' : 'requirement-fail' }}">
                        <i class="bi {{ $ageOk ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                    </div>

                    <div class="requirement-info">
                        <strong>Account Age</strong>
                        <span>{{ $years }}y {{ $months }}m {{ $weeks }}w {{ $remainingDays }}d</span>
                    </div>
                </div>

                {{-- UPLOAD --}}
                <div class="requirement-row">
                    <div class="requirement-status {{ $uploadOk ? 'requirement-pass' : 'requirement-fail' }}">
                        <i class="bi {{ $uploadOk ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                    </div>

                    <div class="requirement-info">
                        <strong>Uploaded</strong>
                        <span>
                            {{ App\Helpers\FormatHelper::formatSize($uploadedGB) }}
                            / {{ $uploadRequirement }} GB
                        </span>
                    </div>
                </div>

                {{-- RATIO --}}
                <div class="requirement-row">
                    <div class="requirement-status {{ $ratioOk ? 'requirement-pass' : 'requirement-fail' }}">
                        <i class="bi {{ $ratioOk ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                    </div>

                    <div class="requirement-info">
                        <strong>Ratio</strong>
                        <span>
                            {{ round($ratio, 2) }}
                            / {{ $ratioRequirement }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- APPLICATION STATUS --}}
        <div class="application-status mb-3">

            @if(!$eligible)

                <div class="status-message status-warning">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <strong>Requirements not met</strong>
                        <span>You do not meet the uploader requirements yet.</span>
                    </div>
                </div>

            @elseif($cooldown['blocked'])

                <div class="status-message status-warning">
                    <i class="bi bi-hourglass-split"></i>
                    <div>
                        <strong>Application cooldown</strong>
                        <span>
                            You cannot apply yet. You may apply again in
                            <strong>{{ $cooldown['days_remaining'] }} days</strong>.
                        </span>
                    </div>
                </div>

            @else

                <div class="status-message status-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>You can apply</strong>
                        <span>You meet the requirements and can apply for uploader.</span>
                    </div>
                </div>

                <a
                    href="{{ route('uploadapps.create') }}"
                    class="apply-btn">
                    <i class="bi bi-send-fill me-1"></i>
                    Apply for Uploader
                </a>

            @endif

        </div>

    @endif

    {{-- APPLICATION LIST --}}
    <div class="applications-card">

        <div class="section-header applications-header">
            <div>
                <i class="bi bi-list-check me-2"></i>
                Applications
            </div>

            <span class="application-count">
                {{ $applications->count() }}
            </span>
        </div>

        @if($applications->isEmpty())

            <div class="empty-applications">
                <i class="bi bi-inbox"></i>
                <strong>No applications found.</strong>
                <span>There are currently no uploader applications to display.</span>
            </div>

        @else

            <div class="table-responsive">
                <table class="table applications-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Ratio</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                            <th>Votes</th>
                            <th>Submitted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($applications as $app)

                            <tr>

                                {{-- ID --}}
                                <td>
                                    <span class="application-id">
                                        #{{ $app->id }}
                                    </span>
                                </td>

                                {{-- APPLICANT --}}
                                <td>
                                    <a
                                        href="{{ route('profile.show', $app->applicant->id) }}"
                                        class="applicant-link">
                                        {{ $app->applicant->name }}
                                    </a>
                                </td>

                                {{-- RATIO --}}
                                <td>
                                    <span class="ratio-value">
                                        @if($app->applicant->downloaded > 0)
                                            {{ round($app->applicant->uploaded / $app->applicant->downloaded, 2) }}
                                        @else
                                            ∞
                                        @endif
                                    </span>
                                </td>

                                {{-- UPLOADED --}}
                                <td>
                                    <span class="uploaded-value">
                                        {{ App\Helpers\FormatHelper::formatSize($app->applicant->uploaded) }}
                                    </span>
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if($app->status === 'pending')
                                        <span class="application-status-badge status-pending">
                                            <i class="bi bi-hourglass-split"></i>
                                            Pending
                                        </span>
                                    @elseif($app->status === 'discussion')
                                        <span class="application-status-badge status-discussion">
                                            <i class="bi bi-chat-dots-fill"></i>
                                            Discussion
                                        </span>
                                    @elseif($app->status === 'voting')
                                        <span class="application-status-badge status-voting">
                                            <i class="bi bi-bar-chart-fill"></i>
                                            Voting
                                        </span>
                                    @elseif($app->status === 'accepted')
                                        <span class="application-status-badge status-accepted">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Accepted
                                        </span>
                                    @elseif($app->status === 'rejected')
                                        <span class="application-status-badge status-rejected">
                                            <i class="bi bi-x-circle-fill"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                {{-- VOTES --}}
                                <td>
                                    <div class="votes-cell">
                                        <span class="vote-for">
                                            <i class="bi bi-hand-thumbs-up-fill"></i>
                                            {{ $app->votes_for }}
                                        </span>

                                        <span class="vote-against">
                                            <i class="bi bi-hand-thumbs-down-fill"></i>
                                            {{ $app->votes_against }}
                                        </span>
                                    </div>
                                </td>

                                {{-- SUBMITTED --}}
                                <td>
                                    <span class="submitted-time">
                                        {{ $app->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end">
                                    <a
                                        href="{{ route('uploadapps.show', $app->id) }}"
                                        class="view-btn">
                                        <i class="bi bi-eye me-1"></i>
                                        View
                                    </a>
                                </td>

                            </tr>

                        @endforeach
                    </tbody>

                </table>
            </div>

        @endif

    </div>

    {{-- PAGINATION (STAFF ONLY) --}}
    @if($user->user_class >= 7 && $applications->hasPages())
        <div class="pagination-wrap">
            {{ $applications->links() }}
        </div>
    @endif

</div>

<style>
    .uploader-applications-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .uploader-header,
    .requirements-card,
    .application-status,
    .applications-card {
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

    .uploader-header::before,
    .requirements-card::before,
    .application-status::before,
    .applications-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--ui-accent, #20c997);
        opacity: .75;
    }

    .uploader-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .9rem;
    }

    .uploader-title {
        margin: 0;
        color: #f3f8fb;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
    }

    .uploader-title i {
        color: var(--ui-accent, #20c997);
    }

    .uploader-subtitle {
        margin-top: .15rem;
        color: #718596;
        font-size: 11px;
    }

    .header-badge {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        border-radius: .4rem;
        color: #72e3bb;
        font-size: 11px;
        font-weight: 700;
    }

    .modern-alert {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .7rem;
        border-radius: .5rem;
        font-size: 12px;
    }

    .alert-success-modern {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .alert-danger-modern {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .requirements-card,
    .applications-card {
        overflow: hidden;
    }

    .section-header {
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

    .section-header > div i {
        color: var(--ui-accent, #20c997);
    }

    .requirements-body {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }

    .requirement-row {
        display: flex;
        align-items: center;
        gap: .55rem;
        min-height: 58px;
        padding: .65rem .8rem;
        border-right: 1px solid rgba(255, 255, 255, .055);
    }

    .requirement-row:last-child {
        border-right: 0;
    }

    .requirement-status {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 25px;
        width: 25px;
        height: 25px;
        border-radius: .35rem;
        font-size: 12px;
    }

    .requirement-pass {
        background: rgba(32, 201, 151, .09);
        border: 1px solid rgba(32, 201, 151, .2);
        color: #72e0a9;
    }

    .requirement-fail {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .requirement-info {
        display: flex;
        flex-direction: column;
        gap: .08rem;
        min-width: 0;
    }

    .requirement-info strong {
        color: #dbe7ef;
        font-size: 12px;
    }

    .requirement-info span {
        color: #718596;
        font-size: 11px;
    }

    .application-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .6rem .8rem;
    }

    .status-message {
        display: flex;
        align-items: center;
        gap: .55rem;
        min-width: 0;
    }

    .status-message > i {
        flex: 0 0 auto;
        font-size: 15px;
    }

    .status-message div {
        display: flex;
        flex-direction: column;
        gap: .05rem;
    }

    .status-message strong {
        font-size: 12px;
    }

    .status-message span {
        color: #8fa2ae;
        font-size: 11px;
    }

    .status-warning {
        color: #e7c967;
    }

    .status-success {
        color: #72e0a9;
    }

    .apply-btn,
    .view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        white-space: nowrap;
        border-radius: .4rem;
        font-size: 11px;
        font-weight: 700;
        transition: .15s ease;
    }

    .apply-btn {
        padding: .38rem .65rem;
        background: rgba(32, 201, 151, .1);
        border: 1px solid rgba(32, 201, 151, .25);
        color: #72e3bb;
    }

    .apply-btn:hover {
        background: rgba(32, 201, 151, .18);
        border-color: rgba(32, 201, 151, .4);
        color: #fff;
    }

    .application-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 23px;
        height: 22px;
        padding: 0 .35rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        border-radius: .35rem;
        color: #72e3bb;
        font-size: 10px;
    }

    .applications-table {
        min-width: 900px;
        color: #dbe7ef;
        font-size: 12px;
    }

    .applications-table > :not(caption) > * > * {
        padding: .55rem .65rem;
        border-bottom-color: rgba(255, 255, 255, .055);
    }

    .applications-table thead th {
        background: rgba(255, 255, 255, .018);
        color: #718596;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .applications-table tbody tr {
        transition: background .14s ease;
    }

    .applications-table tbody tr:hover {
        background: rgba(32, 201, 151, .025);
    }

    .application-id {
        color: #718596;
        font-size: 11px;
    }

    .applicant-link {
        color: #72e3bb;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .applicant-link:hover {
        color: #fff;
    }

    .ratio-value,
    .uploaded-value {
        color: #b8c8d1;
        font-size: 11px;
    }

    .application-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border-radius: .3rem;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pending {
        background: rgba(255, 193, 7, .08);
        border: 1px solid rgba(255, 193, 7, .18);
        color: #e7c967;
    }

    .status-discussion {
        background: rgba(13, 202, 240, .08);
        border: 1px solid rgba(13, 202, 240, .18);
        color: #70d9e8;
    }

    .status-voting {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e3bb;
    }

    .status-accepted {
        background: rgba(25, 135, 84, .1);
        border: 1px solid rgba(25, 135, 84, .2);
        color: #72e0a9;
    }

    .status-rejected {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .votes-cell {
        display: flex;
        align-items: center;
        gap: .55rem;
        white-space: nowrap;
    }

    .vote-for {
        color: #72e0a9;
        font-size: 11px;
    }

    .vote-against {
        color: #ff8e98;
        font-size: 11px;
    }

    .submitted-time {
        color: #8fa2ae;
        font-size: 11px;
        white-space: nowrap;
    }

    .view-btn {
        padding: .3rem .5rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        color: #72e3bb;
    }

    .view-btn:hover {
        background: rgba(32, 201, 151, .16);
        border-color: rgba(32, 201, 151, .35);
        color: #fff;
    }

    .empty-applications {
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

    .empty-applications i {
        margin-bottom: .15rem;
        color: var(--ui-accent, #20c997);
        font-size: 22px;
    }

    .empty-applications strong {
        color: #b9c8d0;
        font-size: 13px;
    }

    .empty-applications span {
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
        .uploader-applications-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .uploader-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .requirements-body {
            grid-template-columns: 1fr;
        }

        .requirement-row {
            border-right: 0;
            border-bottom: 1px solid rgba(255, 255, 255, .055);
        }

        .requirement-row:last-child {
            border-bottom: 0;
        }

        .application-status {
            align-items: flex-start;
            flex-direction: column;
        }

        .apply-btn {
            width: 100%;
        }

        .applications-table {
            min-width: 850px;
        }
    }
</style>

@endsection
