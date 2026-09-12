@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 uploader-application-page">

    {{-- HEADER --}}
    <div class="application-header mb-3">
        <div>
            <h1 class="application-title">
                <i class="bi bi-person-badge-fill me-2"></i>
                Uploader Application #{{ $application->id }}
            </h1>
            <div class="application-subtitle">
                Review applicant information, answers, discussion and voting
            </div>
        </div>

        <span class="application-header-badge">
            <i class="bi bi-file-earmark-person-fill me-1"></i>
            Application
        </span>
    </div>

    {{-- SUCCESS / ERROR --}}
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

    {{-- APPLICANT INFORMATION --}}
    <div class="modern-card mb-3">
        <div class="section-header">
            <span>
                <i class="bi bi-person-vcard-fill me-2"></i>
                Applicant Information
            </span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">User</span>
                <strong>{{ $application->applicant->name }}</strong>
            </div>

            <div class="info-item">
                <span class="info-label">Account Age</span>
                <strong>{{ $application->applicant->created_at->diffForHumans() }}</strong>
            </div>

            <div class="info-item">
                <span class="info-label">Uploaded</span>
                <strong>
                    {{ App\Helpers\FormatHelper::formatSize($application->applicant->uploaded) }}
                </strong>
            </div>

            <div class="info-item">
                <span class="info-label">Downloaded</span>
                <strong>
                    {{ App\Helpers\FormatHelper::formatSize($application->applicant->downloaded) }}
                </strong>
            </div>

            <div class="info-item">
                <span class="info-label">Ratio</span>
                <strong>
                    @if($application->applicant->downloaded > 0)
                        {{ round($application->applicant->uploaded / $application->applicant->downloaded, 2) }}
                    @else
                        ∞
                    @endif
                </strong>
            </div>
        </div>
    </div>

    {{-- APPLICATION ANSWERS --}}
    <div class="modern-card mb-3">
        <div class="section-header">
            <span>
                <i class="bi bi-question-circle-fill me-2"></i>
                Application Answers
            </span>
        </div>

        <div class="answers-body">

            <div class="answer-block">
                <div class="answer-title">
                    Why should you be promoted?
                </div>

                <div class="answer-text">
                    {{ $application->why_promoted }}
                </div>
            </div>

            <div class="answer-block">
                <div class="answer-title">
                    Torrent Experience
                </div>

                <div class="answer-text">
                    {{ $application->experience }}
                </div>
            </div>

            <div class="answer-block">
                <div class="answer-title">
                    What will you upload?
                </div>

                <div class="answer-text">
                    {{ $application->content_plan }}
                </div>
            </div>

            <div class="answer-block">
                <div class="answer-title">
                    Internal Speed
                </div>

                @if($application->internal_speed)
                    <a
                        href="{{ $application->internal_speed }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="speed-link">
                        <i class="bi bi-speedometer2 me-1"></i>
                        View Speedtest
                    </a>
                @else
                    <span class="not-provided">Not provided</span>
                @endif
            </div>

            <div class="answer-block">
                <div class="answer-title">
                    External Speed
                </div>

                @if($application->external_speed)
                    <a
                        href="{{ $application->external_speed }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="speed-link">
                        <i class="bi bi-speedometer2 me-1"></i>
                        View Speedtest
                    </a>
                @else
                    <span class="not-provided">Not provided</span>
                @endif
            </div>

            <div class="answer-block">
                <div class="answer-title">
                    External Sites
                </div>

                <div class="answer-text">
                    {{ $application->external_sites ?? 'None provided' }}
                </div>
            </div>

            <div class="answer-flags">
                <div class="flag-item">
                    <span>Scene Access</span>

                    @if($application->scene_access)
                        <span class="yes-badge">
                            <i class="bi bi-check-lg"></i>
                            Yes
                        </span>
                    @else
                        <span class="no-badge">
                            <i class="bi bi-x-lg"></i>
                            No
                        </span>
                    @endif
                </div>

                <div class="flag-item">
                    <span>Knows How To Create Torrents</span>

                    @if($application->know_torrents)
                        <span class="yes-badge">
                            <i class="bi bi-check-lg"></i>
                            Yes
                        </span>
                    @else
                        <span class="no-badge">
                            <i class="bi bi-x-lg"></i>
                            No
                        </span>
                    @endif
                </div>

                <div class="flag-item">
                    <span>Understands Seeding</span>

                    @if($application->understand_seeding)
                        <span class="yes-badge">
                            <i class="bi bi-check-lg"></i>
                            Yes
                        </span>
                    @else
                        <span class="no-badge">
                            <i class="bi bi-x-lg"></i>
                            No
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- STAFF DISCUSSION --}}
    @if(auth()->user()->user_class >= 7)

        <div class="modern-card mb-3">
            <div class="section-header">
                <span>
                    <i class="bi bi-chat-square-text-fill me-2"></i>
                    Staff Discussion
                </span>

                <span class="section-count">
                    {{ $application->comments->count() }}
                </span>
            </div>

            <div class="discussion-body">

                @forelse($application->comments as $comment)

                    <div class="comment-item">
                        <div class="comment-header">
                            <strong>{{ $comment->user->name }}</strong>

                            <span>
                                {{ $comment->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="comment-text">
                            {{ $comment->comment }}
                        </div>
                    </div>

                @empty

                    <div class="empty-comments">
                        <i class="bi bi-chat-left-dots"></i>
                        <span>No comments yet.</span>
                    </div>

                @endforelse

                <form
                    method="POST"
                    action="{{ route('uploadapps.comment', $application->id) }}"
                    class="comment-form">

                    @csrf

                    <label for="staff-comment" class="form-label">
                        Add to staff discussion
                    </label>

                    <textarea
                        id="staff-comment"
                        name="comment"
                        class="form-control admin-textarea"
                        rows="3"
                        placeholder="Staff discussion..."
                        required></textarea>

                    <button type="submit" class="comment-btn">
                        <i class="bi bi-send-fill me-1"></i>
                        Post Comment
                    </button>

                </form>

            </div>
        </div>

    @endif

    {{-- STAFF VOTING --}}
    @if(auth()->user()->user_class >= 7)

        <div class="modern-card mb-3">
            <div class="section-header">
                <span>
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    Staff Voting
                </span>
            </div>

            <div class="voting-body">

                <div class="vote-summary">
                    <div class="vote-summary-item vote-approve">
                        <i class="bi bi-hand-thumbs-up-fill"></i>
                        <span>Votes For</span>
                        <strong>{{ $application->votes_for }}</strong>
                    </div>

                    <div class="vote-summary-item vote-reject">
                        <i class="bi bi-hand-thumbs-down-fill"></i>
                        <span>Votes Against</span>
                        <strong>{{ $application->votes_against }}</strong>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('uploadapps.vote', $application->id) }}"
                    class="voting-actions">

                    @csrf

                    <button
                        type="submit"
                        name="vote"
                        value="approve"
                        class="approve-btn">
                        <i class="bi bi-check-lg me-1"></i>
                        Approve
                    </button>

                    <button
                        type="submit"
                        name="vote"
                        value="reject"
                        class="reject-btn">
                        <i class="bi bi-x-lg me-1"></i>
                        Reject
                    </button>

                </form>

            </div>
        </div>

    @endif

    {{-- VOTE DETAILS --}}
    <div class="modern-card mb-3">
        <div class="section-header">
            <span>
                <i class="bi bi-people-fill me-2"></i>
                Vote Details
            </span>
        </div>

        @forelse($application->votes as $vote)

            <div class="vote-detail-row">
                <strong>{{ $vote->user->name }}</strong>

                @if($vote->vote === 'approve')
                    <span class="vote-detail-badge vote-detail-approve">
                        <i class="bi bi-check-lg"></i>
                        Approve
                    </span>
                @else
                    <span class="vote-detail-badge vote-detail-reject">
                        <i class="bi bi-x-lg"></i>
                        Reject
                    </span>
                @endif
            </div>

        @empty

            <div class="empty-votes">
                <i class="bi bi-bar-chart"></i>
                <span>No votes have been recorded yet.</span>
            </div>

        @endforelse
    </div>

    @php
        $total = $application->votes_for + $application->votes_against;
        $percent = $total > 0
            ? ($application->votes_for / $total) * 100
            : 0;
    @endphp

    {{-- VOTE PROGRESS --}}
    <div class="vote-progress-card mb-3">
        <div class="progress-heading">
            <span>Approval Progress</span>
            <strong>{{ round($percent) }}%</strong>
        </div>

        <div class="modern-progress">
            <div
                class="modern-progress-bar"
                role="progressbar"
                style="width: {{ $percent }}%"
                aria-valuenow="{{ round($percent) }}"
                aria-valuemin="0"
                aria-valuemax="100">
            </div>
        </div>
    </div>

    {{-- FINAL DECISION --}}
    @if(
        auth()->user()->user_class >= 7 &&
        !in_array($application->status, ['accepted', 'rejected'])
    )

        <div class="final-decision-card">
            <div class="section-header">
                <span>
                    <i class="bi bi-gavel me-2"></i>
                    Final Decision
                </span>
            </div>

            <div class="final-decision-body">

                <form
                    method="POST"
                    action="{{ route('uploadapps.accept', $application->id) }}">

                    @csrf

                    <button type="submit" class="approve-btn">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Accept Application
                    </button>

                </form>

                <form
                    method="POST"
                    action="{{ route('uploadapps.reject', $application->id) }}">

                    @csrf

                    <button type="submit" class="reject-btn">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        Reject Application
                    </button>

                </form>

            </div>
        </div>

    @endif

</div>

<style>
    .uploader-application-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .application-header,
    .modern-card,
    .application-status,
    .vote-progress-card,
    .final-decision-card {
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

    .application-header::before,
    .modern-card::before,
    .vote-progress-card::before,
    .final-decision-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--ui-accent, #20c997);
        opacity: .75;
    }

    .application-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .9rem;
    }

    .application-title {
        margin: 0;
        color: #f3f8fb;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
    }

    .application-title i {
        color: var(--ui-accent, #20c997);
    }

    .application-subtitle {
        margin-top: .15rem;
        color: #718596;
        font-size: 11px;
    }

    .application-header-badge,
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

    .section-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .6rem .8rem;
        background: rgba(255, 255, 255, .022);
        border-bottom: 1px solid rgba(255, 255, 255, .06);
        color: #dbe7ef;
        font-size: 13px;
        font-weight: 700;
    }

    .section-header i {
        color: var(--ui-accent, #20c997);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: .1rem;
        min-height: 58px;
        padding: .65rem .8rem;
        border-right: 1px solid rgba(255, 255, 255, .055);
    }

    .info-item:last-child {
        border-right: 0;
    }

    .info-label {
        color: #718596;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .info-item strong {
        color: #dbe7ef;
        font-size: 12px;
        font-weight: 600;
    }

    .answers-body {
        padding: .75rem .8rem;
    }

    .answer-block {
        padding: .6rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, .055);
    }

    .answer-block:first-child {
        padding-top: .1rem;
    }

    .answer-block:last-child {
        border-bottom: 0;
    }

    .answer-title {
        margin-bottom: .25rem;
        color: #8fa2ae;
        font-size: 11px;
        font-weight: 700;
    }

    .answer-text {
        color: #dbe7ef;
        font-size: 13px;
        line-height: 1.55;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .speed-link {
        display: inline-flex;
        align-items: center;
        color: #72e3bb;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .speed-link:hover {
        color: #fff;
    }

    .not-provided {
        color: #718596;
        font-size: 12px;
    }

    .answer-flags {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .5rem;
        padding-top: .65rem;
    }

    .flag-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .45rem .55rem;
        background: rgba(255, 255, 255, .018);
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: .4rem;
        color: #aabcc7;
        font-size: 11px;
    }

    .yes-badge,
    .no-badge {
        display: inline-flex;
        align-items: center;
        gap: .2rem;
        padding: .15rem .3rem;
        border-radius: .25rem;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .yes-badge {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .no-badge {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .discussion-body {
        padding: .75rem .8rem;
    }

    .comment-item {
        padding: .55rem .6rem;
        margin-bottom: .45rem;
        background: rgba(7, 15, 27, .3);
        border: 1px solid rgba(255, 255, 255, .055);
        border-left: 2px solid rgba(32, 201, 151, .45);
        border-radius: .4rem;
    }

    .comment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .comment-header strong {
        color: #72e3bb;
        font-size: 12px;
    }

    .comment-header span {
        color: #718596;
        font-size: 10px;
        white-space: nowrap;
    }

    .comment-text {
        margin-top: .3rem;
        color: #c7d4db;
        font-size: 12px;
        line-height: 1.5;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .empty-comments,
    .empty-votes {
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: .75rem;
        color: #718596;
        font-size: 11px;
    }

    .empty-comments i,
    .empty-votes i {
        color: var(--ui-accent, #20c997);
        font-size: 16px;
    }

    .comment-form {
        padding-top: .65rem;
        margin-top: .65rem;
        border-top: 1px solid rgba(255, 255, 255, .055);
    }

    .comment-form .form-label {
        margin-bottom: .25rem;
        color: #8fa2ae;
        font-size: 11px;
        font-weight: 600;
    }

    .admin-textarea {
        background: rgba(7, 15, 27, .5) !important;
        border: 1px solid rgba(255, 255, 255, .09) !important;
        border-radius: .4rem;
        color: #dbe7ef !important;
        font-size: 12px;
        resize: vertical;
        box-shadow: none !important;
    }

    .admin-textarea::placeholder {
        color: #647889;
    }

    .admin-textarea:focus {
        border-color: rgba(32, 201, 151, .38) !important;
        box-shadow: 0 0 0 .12rem rgba(32, 201, 151, .055) !important;
    }

    .comment-btn,
    .approve-btn,
    .reject-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .36rem .6rem;
        border-radius: .4rem;
        font-size: 11px;
        font-weight: 700;
        transition: .15s ease;
    }

    .comment-btn {
        margin-top: .5rem;
        background: rgba(32, 201, 151, .1);
        border: 1px solid rgba(32, 201, 151, .25);
        color: #72e3bb;
    }

    .comment-btn:hover {
        background: rgba(32, 201, 151, .18);
        border-color: rgba(32, 201, 151, .4);
        color: #fff;
    }

    .voting-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .8rem;
    }

    .vote-summary {
        display: flex;
        gap: .8rem;
    }

    .vote-summary-item {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: 11px;
    }

    .vote-summary-item span {
        color: #8fa2ae;
    }

    .vote-summary-item strong {
        color: #dbe7ef;
    }

    .vote-approve i {
        color: #72e0a9;
    }

    .vote-reject i {
        color: #ff8e98;
    }

    .voting-actions,
    .final-decision-body {
        display: flex;
        gap: .4rem;
    }

    .approve-btn {
        background: rgba(32, 201, 151, .09);
        border: 1px solid rgba(32, 201, 151, .22);
        color: #72e0a9;
    }

    .approve-btn:hover {
        background: rgba(32, 201, 151, .17);
        border-color: rgba(32, 201, 151, .38);
        color: #fff;
    }

    .reject-btn {
        background: rgba(220, 53, 69, .07);
        border: 1px solid rgba(220, 53, 69, .22);
        color: #ff8e98;
    }

    .reject-btn:hover {
        background: rgba(220, 53, 69, .14);
        border-color: rgba(220, 53, 69, .38);
        color: #fff;
    }

    .vote-detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .5rem .8rem;
        border-bottom: 1px solid rgba(255, 255, 255, .055);
    }

    .vote-detail-row:last-child {
        border-bottom: 0;
    }

    .vote-detail-row strong {
        color: #cbd8de;
        font-size: 12px;
    }

    .vote-detail-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border-radius: .3rem;
        font-size: 10px;
        font-weight: 700;
    }

    .vote-detail-approve {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .vote-detail-reject {
        background: rgba(220, 53, 69, .08);
        border: 1px solid rgba(220, 53, 69, .18);
        color: #ff8e98;
    }

    .vote-progress-card {
        padding: .7rem .8rem;
    }

    .progress-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .35rem;
        color: #8fa2ae;
        font-size: 11px;
        font-weight: 600;
    }

    .progress-heading strong {
        color: #72e3bb;
        font-size: 12px;
    }

    .modern-progress {
        height: 7px;
        overflow: hidden;
        background: rgba(255, 255, 255, .06);
        border-radius: 999px;
    }

    .modern-progress-bar {
        height: 100%;
        min-width: 0;
        background: var(--ui-accent, #20c997);
        border-radius: inherit;
        transition: width .2s ease;
    }

    .final-decision-body {
        align-items: center;
        padding: .7rem .8rem;
    }

    .final-decision-body form {
        margin: 0;
    }

    @media (max-width: 900px) {
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .info-item:nth-child(2) {
            border-right: 0;
        }

        .answer-flags {
            grid-template-columns: 1fr;
        }

        .voting-body {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 767.98px) {
        .uploader-application-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .application-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .info-item,
        .info-item:nth-child(2) {
            border-right: 0;
            border-bottom: 1px solid rgba(255, 255, 255, .055);
        }

        .info-item:last-child {
            border-bottom: 0;
        }

        .vote-summary {
            flex-direction: column;
            gap: .4rem;
        }

        .final-decision-body {
            align-items: stretch;
            flex-direction: column;
        }

        .final-decision-body form,
        .final-decision-body button {
            width: 100%;
        }
    }
</style>

@endsection
