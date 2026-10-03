
@extends('layouts.app')

@section('content')

@php
    $inviteOnly = config('app.invite_only');
    $userClass = Auth::user()->user_class ?? 0;

    $usedInvites = $invites->where('is_used', true)->count();

    $activeInvites = $invites
        ->filter(fn ($invite) => !$invite->is_used && !$invite->expired)
        ->count();
@endphp

<div class="container mt-4">

    {{-- Copy Alert --}}
    <div id="copyAlert"
         class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3 shadow"
         style="display: none; z-index: 9999;"
         role="alert">

        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span id="copyAlertText">Copied!</span>
        </div>

    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- Error Message --}}
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- Information --}}
    <p class="text-muted">
        Codes expire after 14 days. Revoking an active, unused code returns one
        invite to your balance. You will receive a private message when someone
        joins using your code.
    </p>


    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">

        <h3 class="fw-semibold mb-0">
            <i class="bi bi-person-plus-fill text-primary me-2"></i>
            Your Invites
        </h3>

        <span class="badge bg-primary fs-6 px-3 py-2">
            Available: {{ $inviteCount }}
        </span>

    </div>


    {{-- Invite Stats --}}
    <div class="row g-3 mb-4">

        {{-- Active --}}
        <div class="col-md-4">

            <div class="card glass shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <div class="text-muted small">
                        Active Invites
                    </div>

                    <div class="fs-4 fw-bold text-success">
                        {{ $activeInvites }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Used --}}
        <div class="col-md-4">

            <div class="card glass shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <div class="text-muted small">
                        Used Invites
                    </div>

                    <div class="fs-4 fw-bold text-primary">
                        {{ $usedInvites }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Total --}}
        <div class="col-md-4">

            <div class="card glass shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <div class="text-muted small">
                        Total Invites
                    </div>

                    <div class="fs-4 fw-bold">
                        {{ $invites->count() }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Main Card --}}
    <div class="card shadow-sm border-0">

        <div class="card-body">


            {{-- Signup Status --}}
            @if(!$inviteOnly)

                <div class="alert alert-info d-flex align-items-center mb-4">

                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>

                    <div>
                        Signups are currently <strong>open</strong>.
                        Invite codes are optional.
                    </div>

                </div>

            @endif


            {{-- Create Invite Button --}}
            <div class="mb-4">

                @if(!$inviteOnly && $userClass >= 7)

                    <form action="{{ route('invites.create') }}"
                          method="POST">

                        @csrf

                        <button type="submit"
                                class="btn btn-success"
                                @disabled($inviteCount <= 0)>

                            <i class="bi bi-plus-circle me-1"></i>
                            Create New Invite

                        </button>

                    </form>


                @elseif($inviteOnly)

                    @if($userClass >= \App\Models\UserClass::ELITE_USER)

                        <form action="{{ route('invites.create') }}"
                              method="POST">

                            @csrf

                            <button type="submit"
                                    class="btn btn-success"
                                    @disabled($inviteCount <= 0)>

                                <i class="bi bi-plus-circle me-1"></i>
                                Create New Invite

                            </button>

                        </form>

                    @else

                        <span data-bs-toggle="tooltip"
                              title="You need to be an Elite user to create invite codes">

                            <button type="button"
                                    class="btn btn-secondary"
                                    disabled>

                                <i class="bi bi-lock me-1"></i>
                                Create New Invite

                            </button>

                        </span>

                    @endif

                @endif

            </div>


            {{-- Invite List --}}
            @if($invites->isEmpty())

                <div class="text-center py-5">

                    <i class="bi bi-envelope-open text-muted"
                       style="font-size: 50px;"></i>

                    <p class="text-muted mt-3 mb-0">
                        You haven't created any invites yet.
                    </p>

                </div>

            @else

                <div class="invite-list">

                    @foreach($invites as $invite)

                        @php
                            $registrationLink = route('register', [
                                'invite_code' => $invite->invite_code
                            ]);
                        @endphp


                        <div class="invite-row py-3 px-3 mb-3 {{ $invite->is_used ? 'used' : '' }}">

                            <div class="row align-items-center g-3">


                                {{-- Invite Code / Registration Link --}}
                                <div class="col-lg-4 col-md-6">

                                    {{-- Invite Code Label --}}
                                    <div class="small text-muted mb-1">
                                        Invite Code
                                    </div>


                                    {{-- Invite Code --}}
                                    <div class="invite-code d-flex align-items-center gap-2">

                                        @if($invite->is_used || $invite->expired)

                                            <code class="px-2 py-1 rounded text-decoration-line-through text-muted">
                                                {{ $invite->invite_code }}
                                            </code>

                                        @else

                                            <code class="px-2 py-1 bg-warning bg-opacity-10 rounded">
                                                {{ $invite->invite_code }}
                                            </code>


                                            {{-- Copy Invite Code --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    onclick="copyToClipboard(
                                                        @js($invite->invite_code),
                                                        'Invite code copied!'
                                                    )"
                                                    data-bs-toggle="tooltip"
                                                    title="Copy invite code">

                                                <i class="bi bi-clipboard"></i>

                                            </button>

                                        @endif

                                    </div>


                                    {{-- Created / Expiry --}}
                                    <div class="small text-muted mt-2">

                                        Created {{ $invite->created_at->diffForHumans() }}

                                        @if(!$invite->is_used)

                                            <br>

                                            {{ $invite->expired ? 'Expired' : 'Expires' }}

                                            {{ $invite->expires_at->utc()->format('M j, Y H:i') }} UTC

                                        @endif

                                    </div>


                                    {{-- Registration Link --}}
                                    @if(!$invite->is_used && !$invite->expired)

                                        <div class="registration-link-wrapper mt-3">

                                            <div class="small text-muted mb-1">

                                                <i class="bi bi-link-45deg me-1"></i>
                                                Registration Link

                                            </div>


                                            <div class="input-group input-group-sm">

                                                <input type="text"
                                                       class="form-control registration-link-input"
                                                       value="{{ $registrationLink }}"
                                                       readonly
                                                       onclick="this.select();">


                                                <button type="button"
                                                        class="btn btn-outline-primary"
                                                        onclick="copyToClipboard(
                                                            @js($registrationLink),
                                                            'Registration link copied!'
                                                        )"
                                                        data-bs-toggle="tooltip"
                                                        title="Copy registration link">

                                                    <i class="bi bi-clipboard"></i>

                                                </button>

                                            </div>

                                        </div>

                                    @endif

                                </div>


                                {{-- Status --}}
                                <div class="col-lg-1 col-md-2">

                                    @if($invite->is_used)

                                        <span class="badge bg-success">

                                            <i class="bi bi-check-circle me-1"></i>
                                            Used

                                        </span>

                                    @elseif(!$invite->expired)

                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>

                                    @endif


                                    @if(!$invite->is_used && $invite->expired)

                                        <span class="badge bg-danger">
                                            Expired
                                        </span>

                                    @endif

                                </div>


                                {{-- User --}}
                                <div class="col-lg-3 col-md-4">

                                    @if($invite->is_used && $invite->usedBy)

                                        <a href="{{ route('profile.show', $invite->usedBy->id) }}"
                                           class="fw-semibold text-decoration-none">

                                            <i class="bi bi-person-circle text-primary me-1"></i>

                                            {{ $invite->usedBy->name }}

                                        </a>


                                        @if($invite->usedBy->trashed())

                                            <span class="badge bg-danger ms-1">
                                                Deleted
                                            </span>

                                        @endif


                                        <div class="invite-user-info mt-1">

                                            <span class="text-success">

                                                Joined
                                                {{ $invite->usedBy->created_at->diffForHumans() }}

                                            </span>

                                            <br>

                                            <span class="text-danger">

                                                Last Seen
                                                {{ $invite->usedBy->updated_at->diffForHumans() }}

                                            </span>

                                        </div>

                                    @else

                                        <span class="text-muted">
                                            Not used yet
                                        </span>

                                    @endif

                                </div>


                                {{-- Activity --}}
                                <div class="col-lg-3 col-md-10">

                                    @if($invite->is_used && $invite->usedBy)

                                        <div class="invite-stats d-flex flex-wrap align-items-center gap-3">


                                            {{-- Uploaded --}}
                                            <span class="text-success"
                                                  data-bs-toggle="tooltip"
                                                  title="Uploaded">

                                                <i class="bi bi-arrow-up-circle me-1"></i>

                                                {{ \App\Helpers\FormatHelper::formatSize(
                                                    $invite->usedBy->uploaded
                                                ) }}

                                            </span>


                                            {{-- Downloaded --}}
                                            <span class="text-danger"
                                                  data-bs-toggle="tooltip"
                                                  title="Downloaded">

                                                <i class="bi bi-arrow-down-circle me-1"></i>

                                                {{ \App\Helpers\FormatHelper::formatSize(
                                                    $invite->usedBy->downloaded
                                                ) }}

                                            </span>


                                            {{-- Ratio --}}
                                            <span class="text-info"
                                                  data-bs-toggle="tooltip"
                                                  title="Ratio">

                                                <i class="bi bi-activity me-1"></i>

                                                @if(($invite->usedBy->downloaded ?? 0) > 0)

                                                    {{ number_format(
                                                        $invite->usedBy->uploaded /
                                                        $invite->usedBy->downloaded,
                                                        2
                                                    ) }}

                                                @else

                                                    ∞

                                                @endif

                                            </span>

                                        </div>

                                    @endif

                                </div>


                                {{-- Action --}}
                                <div class="col-lg-1 col-md-2 text-end">

                                    @if(!$invite->is_used && !$invite->expired)

                                        <form action="{{ route('invites.delete', $invite->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Revoke this invite and return one invite to your balance?');">

                                            @csrf
                                            @method('DELETE')


                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Revoke invite">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>

</div>


<style>

    /* Invite Row */
    .invite-row {
        background: #262424;
        border: 1px solid #1c202472;
        border-radius: 8px;
        transition:
            background .15s ease,
            border-color .15s ease,
            transform .15s ease;
    }

    .invite-row:hover {
        background: #202223;
        border-color: #437db8;
    }


    /* Used Invite */
    .invite-row.used {
        opacity: .7;
    }


    /* Invite Code */
    .invite-code code {
        font-size: 14px;
        word-break: break-all;
    }


    /* Registration Link */
    .registration-link-input {
        font-size: 12px;
        min-width: 0;
    }

    .registration-link-input:focus {
        box-shadow: none;
        border-color: var(--bs-primary);
    }


    /* User Information */
    .invite-user-info {
        font-size: 13px;
    }


    /* Upload / Download / Ratio */
    .invite-stats span {
        font-size: 16px;
        font-weight: 500;
        white-space: nowrap;
    }


    /* Copy Alert */
    #copyAlert {
        min-width: 260px;
        max-width: 90%;
        text-align: center;
        border: 0;
    }


    /* Mobile */
    @media (max-width: 767.98px) {

        .invite-row {
            padding: 16px !important;
        }

        .registration-link-wrapper {
            width: 100%;
        }

        .invite-stats {
            margin-top: 5px;
        }

    }

</style>


<script>

    let copyAlertTimeout = null;


    /**
     * Copy text to clipboard and show notification.
     */
    function copyToClipboard(text, message = 'Copied!') {

        navigator.clipboard.writeText(text)

            .then(() => {

                showCopyAlert(message);

            })

            .catch(() => {

                fallbackCopy(text, message);

            });

    }


    /**
     * Fallback for browsers where navigator.clipboard
     * is unavailable.
     */
    function fallbackCopy(text, message) {

        const textarea = document.createElement('textarea');

        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';

        document.body.appendChild(textarea);

        textarea.focus();
        textarea.select();

        try {

            document.execCommand('copy');

            showCopyAlert(message);

        } catch (error) {

            showCopyAlert('Unable to copy to clipboard.', true);

        }

        document.body.removeChild(textarea);

    }


    /**
     * Display copy notification.
     */
    function showCopyAlert(message, error = false) {

        const alertBox = document.getElementById('copyAlert');
        const alertText = document.getElementById('copyAlertText');

        if (!alertBox || !alertText) {
            return;
        }


        // Stop previous timer
        if (copyAlertTimeout) {
            clearTimeout(copyAlertTimeout);
        }


        // Message
        alertText.textContent = message;


        // Alert colour
        alertBox.classList.remove(
            'alert-success',
            'alert-danger'
        );

        alertBox.classList.add(
            error ? 'alert-danger' : 'alert-success'
        );


        // Icon
        const icon = alertBox.querySelector('i');

        if (icon) {

            icon.className = error
                ? 'bi bi-exclamation-circle-fill me-2'
                : 'bi bi-check-circle-fill me-2';

        }


        // Show
        alertBox.style.display = 'block';


        // Hide after 2 seconds
        copyAlertTimeout = setTimeout(() => {

            alertBox.style.display = 'none';

        }, 2000);

    }


    /**
     * Initialise Bootstrap tooltips.
     */
    document.addEventListener('DOMContentLoaded', function () {

        const tooltipTriggerList =
            document.querySelectorAll('[data-bs-toggle="tooltip"]');

        [...tooltipTriggerList].map(
            tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl)
        );

    });

</script>

@endsection
