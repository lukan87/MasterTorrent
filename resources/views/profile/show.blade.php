@extends('layouts.app')

@section('content')

<div class="member-profile">
@if(!$user->trashed())
<div class="container-fluid mt-3" data-profile-activity>
    <nav class="d-flex flex-wrap gap-2" aria-label="Member activity">
        @foreach(['profile.comments' => 'Comments', 'profile.thanks' => 'Thanks', 'profile.posts' => 'Forum posts', 'profile.seedingTorrents' => 'Seeding', 'profile.download-history' => 'Download history', 'profile.tokens' => 'Active tokens'] as $activityRoute => $activityLabel)
            @if(Route::has($activityRoute) && (!in_array($activityRoute, ['profile.seedingTorrents', 'profile.download-history', 'profile.tokens'], true) || auth()->id() === $user->id || (auth()->user()?->user_class ?? 0) >= \App\Models\UserClass::MODERATOR))
                <a class="btn btn-outline-secondary btn-sm" data-activity-tab href="{{ route($activityRoute, [$user->id, $user->name]) }}">{{ $activityLabel }}</a>
            @endif
        @endforeach
    </nav>
    <div data-activity-content class="mt-3" hidden></div>
</div>
@endif


@if($user->trashed())

    {{-- =========================================================
         DELETED ACCOUNT
    ========================================================== --}}
    <div class="container-fluid mt-3">
        <div class="profile-panel deleted-account-panel">
            <div class="deleted-account-icon">
                <i class="bi bi-trash3-fill"></i>
            </div>

            <div class="flex-grow-1">
                <h5 class="text-danger mb-1 fw-semibold">
                    This account has been deleted
                </h5>

                <div class="small text-muted mb-2">
                    Deleted
                    <strong>{{ $user->deleted_at->diffForHumans() }}</strong>

                    @if($user->deletedBy)
                        by <strong>{{ $user->deletedBy->name }}</strong>
                    @endif
                </div>

                <div class="small">
                    This profile is archived and no longer active.

                    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                        <span class="text-warning">
                            Moderators may restore this account from the admin panel.
                        </span>
                    @else
                        If you believe this was a mistake, please contact the staff team.
                    @endif
                </div>
            </div>
        </div>
    </div>

@else

@php
    $ratio = $user->downloaded > 0
        ? number_format($user->uploaded / $user->downloaded, 2)
        : '∞';

    $coverImage = filter_var($user->cover, FILTER_VALIDATE_URL)
        ? $user->cover
        : asset('images/default-cover.jpg');

    $profileBackground = $user->background
        ? $user->background
        : asset('images/default-profile-bg.jpg');

    $authUser = auth()->user();

    $isOwner = $authUser && $authUser->id === $user->id;

    $isModerator = $authUser
        && $authUser->user_class >= \App\Models\UserClass::MODERATOR;

    $isAdmin = $authUser
        && $authUser->user_class >= \App\Models\UserClass::ADMIN;

    $rankProgress = min(100, max(0, $user->seeder_rank_progress ?? 0));

    $healthColor = 'bg-danger';

    if ($seedingHealth >= 80) {
        $healthColor = 'bg-success';
    } elseif ($seedingHealth >= 50) {
        $healthColor = 'bg-warning';
    }

    $canSeeTimeline = Auth::check()
        && (
            Auth::user()->user_class >= \App\Models\UserClass::MODERATOR
            || Auth::user()->id === $user->id
        );

    $hasAbout = !empty($user->info);

    $hasTimeline = $canSeeTimeline
        && $user->timeline->isNotEmpty();
@endphp


{{-- =========================================================
     PROFILE HERO
========================================================== --}}

<div class="profile-hero mt-3"
     style="background-image: url('{{ $coverImage }}');">

    <div class="profile-hero-overlay"></div>

    <div class="container-fluid profile-hero-inner">

        <div class="profile-hero-content">

            {{-- AVATAR --}}
            <div class="profile-avatar-wrap">

                @if($user->profile_image)

                    <img
                        src="{{ $user->profile_image }}"
                        alt="{{ $user->name }}"
                        class="profile-avatar" width="136" height="136" decoding="async"
                    >

                @else

                    <div class="profile-avatar profile-avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>

                @endif

                @if(method_exists($user, 'isOnline') && $user->isOnline())
                    <span
                        class="profile-online-indicator"
                        data-bs-toggle="tooltip"
                        title="Online"
                    ></span>
                @endif

            </div>


            {{-- IDENTITY --}}
            <div class="profile-identity">

                <div class="profile-name-row">

                    <h1 class="profile-name mb-0">

                        <span
                            class="profile-username-tooltip"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            data-bs-custom-class="profile-tooltip"
                            style="--member-color: {{ \App\Models\UserClass::getClassColor($user->user_class) }}; color: var(--member-color);"
                            title="
                                <div class='profile-tooltip-header'>
                                    <strong>{{ $user->name }}</strong>
                                    <span>{{ $user->role_name }}</span>
                                </div>

                                <div class='profile-tooltip-row'>
                                    <i class='bi bi-trophy'></i>
                                    <span>Seeder Rank</span>
                                    <strong>{{ $user->seeder_rank_name }}</strong>
                                </div>

                                <div class='profile-tooltip-row'>
                                    <i class='bi bi-calendar3'></i>
                                    <span>Member since</span>
                                    <strong>{{ $user->created_at->format('d F Y') }}</strong>
                                </div>

                                <div class='profile-tooltip-row'>
                                    <i class='bi bi-clock'></i>
                                    <span>Last seen</span>
                                    <strong>{{ $user->last_activity ? $user->last_activity->format('d F Y') : 'Never' }}</strong>
                                </div>

                                @if(method_exists($user, 'isOnline') && $user->isOnline())
                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-circle-fill'></i>
                                        <span>Status</span>
                                        <strong>Online</strong>
                                    </div>
                                @else
                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-circle'></i>
                                        <span>Status</span>
                                        <strong>Offline</strong>
                                    </div>
                                @endif

                                @if($user->donor == 'yes')
                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-heart-fill'></i>
                                        <span>Supporter</span>
                                        <strong>Donor</strong>
                                    </div>
                                @endif

                                @if($user->vip_until)
                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-star-fill'></i>
                                        <span>VIP</span>
                                        <strong>{{ \Carbon\Carbon::parse($user->vip_until)->format('d F Y') }}</strong>
                                    </div>
                                @endif

                                @if($user->warned)
                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-exclamation-triangle-fill'></i>
                                        <span>Status</span>
                                        <strong>Warned</strong>
                                    </div>
                                @endif

                                @if($isAdmin || $isOwner)

                                    <div class='profile-tooltip-divider'></div>

                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-diagram-3'></i>
                                        <span>IP Address</span>
                                        <strong>{{ $user->IP }}</strong>
                                    </div>

                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-envelope'></i>
                                        <span>Email</span>
                                        <strong>{{ $user->email }}</strong>
                                    </div>

                                    <div class='profile-tooltip-row'>
                                        <i class='bi bi-link-45deg'></i>
                                        <span>Joined</span>

                                        <strong>
                                            @if($user->invited_by)
                                                Invited by {{ $user->inviter?->name ?? 'Unknown' }}
                                            @else
                                                Independently
                                            @endif
                                        </strong>
                                    </div>

                                @endif
                            "
                        >
                            {{ $user->name }}

                            <span class="profile-seeder-icon">
                                {{ $user->seeder_icon }}
                            </span>
                        </span>

                    </h1>


                    <span class="profile-role-badge">
                        <i class="bi bi-person-fill"></i>
                        {{ $user->role_name }}
                    </span>


                    @if($isOwner)
                        @include('partials.email-subscription-switch', [
                            'emailToggleId' => 'profile-email-subscribe',
                            'emailSubscribed' => (bool) $user->subscribed,
                            'emailPreferenceUrl' => route('user.email-preferences'),
                        ])
                        @push('scripts')
                            <script src="{{ asset('js/email-subscription.js') }}" defer></script>
                        @endpush
                    @endif

                    @if($user->donor == 'yes')
                        <span class="profile-small-badge donor">
                            <i class="bi bi-heart-fill"></i>
                            Donor
                        </span>
                    @endif


                    @if($user->vip_until)
                        <span
                            class="profile-small-badge vip"
                            data-bs-toggle="tooltip"
                            title="VIP expires {{ \Carbon\Carbon::parse($user->vip_until)->format('d F Y, H:i') }}"
                        >
                            <i class="bi bi-gem"></i>
                            VIP
                        </span>
                    @endif


                    @if($user->warned)
                        <span
                            class="profile-small-badge warned"
                            data-bs-toggle="tooltip"
                            title="Warned until {{ \Carbon\Carbon::parse($user->warned_until)->format('d F Y, H:i') }}"
                        >
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Warned
                        </span>
                    @endif

                </div>


                @if(!empty($user->info))
                    <div class="profile-tagline">
                        {{ \Illuminate\Support\Str::limit(strip_tags($user->info), 120) }}
                    </div>
                @endif


                <div class="profile-meta-row">

                    <div class="profile-meta-item">
                        <i class="bi bi-calendar3"></i>

                        <span>
                            Member since {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>


                    <div class="profile-meta-item">
                        <i class="bi bi-clock"></i>

                        <span>
                            Last seen
                            {{ $user->last_activity
                                ? $user->last_activity->diffForHumans()
                                : 'Never'
                            }}
                        </span>
                    </div>


                    @if(method_exists($user, 'isOnline') && $user->isOnline())

                        <div class="profile-meta-item online">
                            <span class="profile-online-dot"></span>
                            <span>Online</span>
                        </div>

                    @endif

                </div>

            </div>


            {{-- ACTIONS --}}
            <div class="profile-actions">

                @if(Auth::check() && Auth::id() !== $user->id)

                    <a
                        href="{{ route('messages.create', ['receiver_id' => $user->id]) }}"
                        class="profile-action-btn primary"
                    >
                        <i class="bi bi-envelope"></i>
                        <span>Message</span>
                    </a>

                    <a
                        href="{{ route('tickets.create', ['user_id' => $user->id]) }}"
                        class="profile-action-btn danger"
                    >
                        <i class="bi bi-flag-fill" aria-hidden="true"></i>
                        <span>Report User</span>
                    </a>

                @endif


                @if($isOwner || $isModerator)

                    <a
                        href="{{ route('profile.edit', [
                            'id' => $user->id,
                            'name' => $user->name
                        ]) }}"
                        class="profile-action-btn primary"
                    >
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Profile</span>
                    </a>

                @endif


                @if($isModerator && !$isOwner)

                    <a
                        href="{{ route('admin.users.edit', $user->id) }}"
                        class="profile-action-btn warning"
                    >
                        <i class="bi bi-shield-lock"></i>
                        <span>Admin Edit</span>
                    </a>

                @endif


                @if($isOwner)

                    <button
                        class="profile-action-btn danger"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#deleteAccountBox"
                        aria-expanded="{{ $errors->has('password') ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-trash"></i>
                        <span>Delete Account</span>
                    </button>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     DELETE ACCOUNT
========================================================== --}}

@if($isOwner)

<div
    class="collapse container-fluid mt-3 @if($errors->has('password')) show @endif"
    id="deleteAccountBox"
>
    <div class="profile-panel delete-panel">

        <div class="delete-panel-header">
            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <strong>Delete account</strong>
                <p>
                    This action is permanent and cannot be undone.
                </p>
            </div>
        </div>

        <form
            action="{{ route('profile.delete', [$user->id, $user->name]) }}"
            method="POST"
        >
            @csrf
            @method('DELETE')

            <div class="mb-3">

                <label class="form-label text-danger fw-semibold">
                    Confirm password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter your password"
                    required
                >

                @error('password')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <button
                type="submit"
                class="btn btn-danger"
            >
                <i class="bi bi-trash3 me-1"></i>
                Permanently Delete Account
            </button>

        </form>

    </div>
</div>

@endif


{{-- =========================================================
     PROFILE OVERVIEW
========================================================== --}}

<div
    id="profile-overview"
    class="container-fluid profile-dashboard"
>

    {{-- JUMP NAVIGATION --}}
    <nav
        class="profile-jump-links"
        aria-label="Profile sections"
    >

        <a href="#profile-overview" class="active">
            <i class="bi bi-grid"></i>
            <span>Overview</span>
        </a>

        @if($hasAbout)
            <a href="#profile-about">
                <i class="bi bi-person"></i>
                <span>About</span>
            </a>
        @endif

        @if($hasTimeline)
            <a href="#profile-timeline">
                <i class="bi bi-clock-history"></i>
                <span>Timeline</span>
            </a>
        @endif

        <a href="#seederRankAccordion">
            <i class="bi bi-award"></i>
            <span>Rank Guide</span>
        </a>

    </nav>


    {{-- SECTION HEADER --}}
    <div class="profile-section-heading">

        <div>
            <span class="profile-eyebrow">
                Member overview
            </span>

            <h2>
                Sharing &amp; Community
            </h2>

            <p>
                Transfer activity, seeding reputation and community contribution.
            </p>
        </div>

        <div class="profile-tenure">
            <i class="bi bi-calendar-check"></i>

            <div>
                <small>Member since</small>
                <strong>{{ $user->created_at->format('d M Y') }}</strong>
            </div>
        </div>

    </div>


    {{-- =====================================================
         TRANSFER SUMMARY
    ====================================================== --}}

    <div class="transfer-grid">

        {{-- UPLOADED --}}
        <article class="transfer-card uploaded">

            <div class="transfer-icon">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>

            <div class="transfer-content">

                <span>Uploaded</span>

                <strong>
                    @if($isOwner || $isModerator)

                        <a
                            href="{{ route('snatch.seeding', ['userId' => $user->id]) }}"
                            class="stat-link"
                        >
                            {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}
                        </a>

                    @else

                        {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}

                    @endif
                </strong>

                <small>Total data shared</small>

            </div>

        </article>


        {{-- DOWNLOADED --}}
        <article class="transfer-card downloaded">

            <div class="transfer-icon">
                <i class="bi bi-cloud-arrow-down"></i>
            </div>

            <div class="transfer-content">

                <span>Downloaded</span>

                <strong>
                    @if($isOwner || $isModerator)

                        <a
                            href="{{ route('snatch.snatchlist', ['userId' => $user->id]) }}"
                            class="stat-link"
                        >
                            {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}
                        </a>

                    @else

                        {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}

                    @endif
                </strong>

                <small>Total data received</small>

            </div>

        </article>

        

        {{-- RATIO --}}
        
        <article class="transfer-card seeding">

            <div class="transfer-icon">
                <i class="bi bi-speedometer2"></i>
            </div>

            <div class="transfer-content">

                <span>Ratio</span>

                <strong>
                   {{ $ratio }}
                </strong>

                <small>Upload / download ratio</small>

            </div>

        </article>

        {{-- SEEDING SIZE --}}
        <article class="transfer-card seeding">

            <div class="transfer-icon">
                <i class="bi bi-hdd-stack"></i>
            </div>

            <div class="transfer-content">

                <span>Seeding Size</span>

                <strong>
                    {{ \App\Helpers\FormatHelper::formatSize($totalSeedSize) }}
                </strong>

                <small>Data currently being shared</small>

            </div>

        </article>

    </div>


    {{-- =====================================================
         SEEDER STATUS
    ====================================================== --}}

    <div class="profile-feature-grid">

        {{-- REPUTATION --}}
        <article class="profile-feature-card reputation-card">

            <div class="feature-card-top">

                <div class="feature-icon">
                    <i class="bi bi-award"></i>
                </div>

                <div class="feature-heading">
                    <span>Seeder Reputation</span>

                    <strong>
                        {{ $user->seeder_icon }}
                        {{ $user->seeder_rank_name }}
                    </strong>
                </div>

                <div class="feature-score">
                    {{ number_format($user->seeding_reputation, 2) }}
                    <small>pts</small>
                </div>

            </div>


            <div class="feature-progress-header">

                <span>Rank progress</span>

                <strong>
                    {{ number_format($rankProgress, 0) }}%
                </strong>

            </div>


            <div class="progress profile-progress">

                <div
                    class="progress-bar bg-success"
                    role="progressbar"
                    style="width: {{ $rankProgress }}%"
                    aria-valuenow="{{ $rankProgress }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>

            </div>


            <div class="feature-footer">

                @if($user->next_seeder_rank)

                    <span>Next rank</span>

                    <strong>
                        {{ $user->next_seeder_rank['icon'] }}
                        {{ $user->next_seeder_rank['name'] }}
                    </strong>

                @else

                    <span>Rank status</span>
                    <strong>Maximum rank 👑</strong>

                @endif

            </div>

            <button type="button" class="seeder-guide-trigger" data-bs-toggle="modal" data-bs-target="#seederRankModal" aria-haspopup="dialog" aria-label="View Seeder Rank System">
                View rank guide <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </button>

        </article>


        {{-- SEEDING HEALTH --}}
        <article
            class="profile-feature-card health-card"
            data-bs-toggle="tooltip"
            title="Seeding Health shows how consistently torrents are seeded. Each torrent reaches 100% after 12 hours of seeding."
        >

            <div class="feature-card-top">

                <div class="feature-icon">
                    <i class="bi bi-heart-pulse"></i>
                </div>

                <div class="feature-heading">
                    <span>Seeding Health</span>
                    <strong>Contribution health</strong>
                </div>

                <div class="feature-score">
                    {{ $seedingHealth }}<small>%</small>
                </div>

            </div>


            <div class="health-meter">

                <div class="health-meter-ring">
                    <span>{{ $seedingHealth }}%</span>
                </div>

                <div class="health-meter-info">

                    <span>
                        @if($seedingHealth >= 80)
                            Excellent seeding consistency
                        @elseif($seedingHealth >= 50)
                            Good, but can be improved
                        @else
                            Seeding needs attention
                        @endif
                    </span>

                    <div class="progress profile-progress">

                        <div
                            class="progress-bar {{ $healthColor }}"
                            role="progressbar"
                            style="width: {{ $seedingHealth }}%"
                            aria-valuenow="{{ $seedingHealth }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>

                    </div>

                    <small>
                        Keep completed torrents active to improve health.
                    </small>

                </div>

            </div>

        </article>

    </div>


    {{-- =====================================================
         COMMUNITY
    ====================================================== --}}

    <div class="subsection-heading">

        <div>
            <span class="profile-eyebrow">
                Community
            </span>

            <h3>Activity &amp; rewards</h3>
        </div>

    </div>


    <div class="community-grid">

        {{-- SEEDBONUS --}}
        <a
            href="{{ route('shop') }}"
            class="community-stat-card"
        >
            <span class="community-stat-icon bonus">
                <i class="bi bi-coin"></i>
            </span>

            <div>
                <small>Seedbonus</small>
                <strong>{{ number_format($user->seedbonus, 2) }}</strong>
            </div>

            <i class="bi bi-chevron-right card-arrow"></i>
        </a>


        {{-- INVITES --}}
        @if(auth()->id() === $user->id)

            <a
                href="{{ route('invites.index') }}"
                class="community-stat-card"
            >

                <span class="community-stat-icon invite">
                    <i class="bi bi-person-plus"></i>
                </span>

                <div>
                    <small>Invites</small>
                    <strong>{{ $user->invites }}</strong>
                </div>

                <i class="bi bi-chevron-right card-arrow"></i>

            </a>

        @else

            <div class="community-stat-card">

                <span class="community-stat-icon invite">
                    <i class="bi bi-person-plus"></i>
                </span>

                <div>
                    <small>Invites</small>
                    <strong>{{ $user->invites }}</strong>
                </div>

            </div>

        @endif


        {{-- SLOTS --}}
        @if(
            auth()->id() === $user->id
            || ($authUser && $authUser->user_class >= \App\Models\UserClass::ADMIN)
        )

            <a
                href="{{ route('profile.tokens', [
                    'id' => $user->id,
                    'name' => $user->name
                ]) }}"
                class="community-stat-card"
            >

                <span class="community-stat-icon slots">
                    <i class="bi bi-ticket-perforated"></i>
                </span>

                <div>
                    <small>Slots</small>
                    <strong>{{ $user->slots }}</strong>
                </div>

                <i class="bi bi-chevron-right card-arrow"></i>

            </a>

        @else

            <div class="community-stat-card">

                <span class="community-stat-icon slots">
                    <i class="bi bi-ticket-perforated"></i>
                </span>

                <div>
                    <small>Slots</small>
                    <strong>{{ $user->slots }}</strong>
                </div>

            </div>

        @endif


        {{-- COMMENTS --}}
        <a
            href="{{ route('profile.comments', [
                'id' => $user->id,
                'name' => $user->name
            ]) }}"
            class="community-stat-card"
        >

            <span class="community-stat-icon comments">
                <i class="bi bi-chat-dots"></i>
            </span>

            <div>
                <small>Comments</small>
                <strong>{{ number_format($commentCount) }}</strong>
            </div>

            <i class="bi bi-chevron-right card-arrow"></i>

        </a>


        {{-- THANKS --}}
        <a
            href="{{ route('profile.thanks', [
                'id' => $user->id,
                'name' => $user->name
            ]) }}"
            class="community-stat-card"
        >

            <span class="community-stat-icon thanks">
                <i class="bi bi-heart"></i>
            </span>

            <div>
                <small>Thanks</small>
                <strong>{{ number_format($thanksCount) }}</strong>
            </div>

            <i class="bi bi-chevron-right card-arrow"></i>

        </a>


        {{-- FORUM POSTS --}}
        <a
            href="{{ route('profile.posts', [
                'id' => $user->id,
                'name' => $user->name
            ]) }}"
            class="community-stat-card"
        >

            <span class="community-stat-icon posts">
                <i class="bi bi-chat-square-text"></i>
            </span>

            <div>
                <small>Forum Posts</small>
                <strong>{{ number_format($forumPostCount) }}</strong>
            </div>

            <i class="bi bi-chevron-right card-arrow"></i>

        </a>


        @if($isOwner || $isModerator)
            <button type="button" class="community-stat-card subscriptions-card" data-bs-toggle="modal" data-bs-target="#subscribedTorrentsModal" aria-haspopup="dialog" aria-label="View subscribed torrents">
                <span class="community-stat-icon subscriptions"><i class="bi bi-bell" aria-hidden="true"></i></span>
                <div>
                    <small>Subscribed Torrents</small>
                    <strong>{{ number_format(count($subscribedTorrents)) }}</strong>
                </div>
                <i class="bi bi-arrow-up-right card-arrow" aria-hidden="true"></i>
            </button>
        @endif

        @if($user->invited_by || $user->invitees_count > 0)
            <button type="button" class="community-stat-card invitation-tree-card" data-bs-toggle="modal" data-bs-target="#invitationTreeModal" aria-haspopup="dialog" aria-label="View invitation tree">
                <span class="community-stat-icon invitation-tree-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                <div>
                    <small>Invitation Tree</small>
                    <strong>{{ number_format($user->invitees_count) }} invited</strong>
                </div>
                <i class="bi bi-arrow-up-right card-arrow" aria-hidden="true"></i>
            </button>
        @endif

        @if(!empty($achievementCategories))
            <button type="button" class="community-stat-card achievements-card" data-bs-toggle="modal" data-bs-target="#achievements" aria-haspopup="dialog" aria-label="View achievements">
                <span class="community-stat-icon achievements"><i class="bi bi-trophy" aria-hidden="true"></i></span>
                <div>
                    <small>Achievements</small>
                    <strong>{{ collect($achievementCategories)->sum('earned_count') }} / {{ collect($achievementCategories)->sum(fn ($category) => count($category['tiers'])) }}</strong>
                </div>
                <i class="bi bi-arrow-up-right card-arrow" aria-hidden="true"></i>
            </button>
        @endif

        {{-- USER UPLOADS --}}
        @if($isOwner || $isModerator)

            <a
                href="{{ route('profile.torrents', [
                    'id' => $user->id,
                    'name' => $user->name
                ]) }}"
                class="community-stat-card"
            >

                <span class="community-stat-icon uploads">
                    <i class="bi bi-cloud-arrow-up"></i>
                </span>

                <div>
                    <small>User Uploads</small>
                    <strong>{{ number_format($user->torrents_count) }}</strong>
                </div>

                <i class="bi bi-chevron-right card-arrow"></i>

            </a>


            {{-- HIT & RUN --}}
            <a
                href="{{ route('snatch.hitAndRun', ['userId' => $user->id]) }}"
                class="community-stat-card hitrun-card"
            >

                <span class="community-stat-icon hitrun">
                    <i class="bi bi-exclamation-triangle"></i>
                </span>

                <div>
                    <small>Hit &amp; Runs</small>
                    <strong>{{ number_format($user->hit_and_run_count) }}</strong>
                </div>

                <i class="bi bi-chevron-right card-arrow"></i>

            </a>

        @endif

    </div>

</div>


{{-- =========================================================
     PROFILE TOOLS / ACCORDION
========================================================== --}}

<div class="container-fluid profile-secondary">

    <div class="profile-dialogs">

        @include('profile.partials.achievements', ['lazyAchievements' => true])
        @include('profile.partials.seeder-rank-modal')

        @if($isOwner || $isModerator)
            @include('profile.partials.subscribed-torrents-modal')
        @endif

    </div>


    {{-- INVITE TREE --}}
    @include('profile.partials.invite-tree')



</div>


{{-- =========================================================
     ABOUT + TIMELINE
========================================================== --}}

@if($hasAbout || $hasTimeline)

<div class="container-fluid profile-content-sections">

    <div class="row g-4">

        {{-- ABOUT --}}
        @if($hasAbout)

            <div
                id="profile-about"
                class="{{ $hasTimeline ? 'col-xl-5' : 'col-12' }}"
            >

                <section class="profile-panel content-panel">

                    <div class="content-panel-heading">

                        <span class="content-panel-icon about">
                            <i class="bi bi-person-lines-fill"></i>
                        </span>

                        <div>
                            <span class="profile-eyebrow">
                                Profile
                            </span>

                            <h3>About {{ $user->name }}</h3>
                        </div>

                    </div>


                    <div class="about-scroll">
                        {!! convertCustomTagsToHtml($user->info) !!}
                    </div>

                </section>

            </div>

        @endif


        {{-- TIMELINE --}}
        @if($hasTimeline)

            <div
                id="profile-timeline"
                class="{{ $hasAbout ? 'col-xl-7' : 'col-12' }}"
            >

                <section class="profile-panel content-panel">

                    <div class="timeline-heading">

                        <div class="content-panel-heading">

                            <span class="content-panel-icon timeline">
                                <i class="bi bi-activity"></i>
                            </span>

                            <div>
                                <span class="profile-eyebrow">
                                    Account history
                                </span>

                                <h3>
                                    Timeline

                                    <span
                                        id="timelineCount"
                                        class="timeline-count"
                                    >
                                        {{ $user->timeline->count() }}
                                    </span>
                                </h3>
                            </div>

                        </div>


                        <div class="timeline-filters">

                            <button
                                type="button"
                                class="active"
                                onclick="filterTimeline('all', this)"
                            >
                                All
                            </button>

                            <button
                                type="button"
                                onclick="filterTimeline('moderation', this)"
                            >
                                Moderation
                            </button>

                            <button
                                type="button"
                                onclick="filterTimeline('upload', this)"
                            >
                                Uploads
                            </button>

                            <button
                                type="button"
                                onclick="filterTimeline('stats', this)"
                            >
                                Stats
                            </button>

                        </div>

                    </div>


                    <div class="elite-timeline-scroll">

                        @foreach($timeline as $entry)

                            @php
                                $timelineType = 'general';

                                $timelineText = strtolower($entry->comment);

                                if (
                                    str_contains($timelineText, 'chat')
                                    || str_contains($timelineText, 'forum')
                                    || str_contains($timelineText, 'comment')
                                    || str_contains($timelineText, 'warn')
                                    || str_contains($timelineText, 'vip')
                                ) {
                                    $timelineType = 'moderation';
                                } elseif (
                                    str_contains($timelineText, 'upload')
                                    || str_contains($timelineText, 'torrent')
                                ) {
                                    $timelineType = 'upload';
                                } elseif (
                                    str_contains($timelineText, 'download')
                                    || str_contains($timelineText, 'ratio')
                                    || str_contains($timelineText, 'seedbonus')
                                    || str_contains($timelineText, 'stats')
                                ) {
                                    $timelineType = 'stats';
                                }

                                $comment = $entry->comment;

                                $commentType = 'default';

                                if (
                                    str_contains($comment, 'Chat access')
                                    || str_contains($comment, 'chat')
                                ) {
                                    $commentType = 'chat';
                                } elseif (str_contains($comment, 'Comments')) {
                                    $commentType = 'comment';
                                } elseif (str_contains($comment, 'Forum')) {
                                    $commentType = 'forum';
                                } elseif (str_contains($comment, 'Warn')) {
                                    $commentType = 'warning';
                                } elseif (str_contains($comment, 'VIP')) {
                                    $commentType = 'vip';
                                }
                            @endphp


                            <div
                                class="timeline-item"
                                data-type="{{ $timelineType }}"
                            >

                                <span class="timeline-dot"></span>

                                <div class="timeline-card">

                                    <div class="timeline-card-top">

                                        <div class="timeline-comment timeline-{{ $commentType }}">
                                            {!! convertCustomTagsToHtml($comment) !!}
                                        </div>


                                        @if($entry->staff)

                                            <span class="timeline-staff">
                                                <i class="bi bi-shield-check"></i>
                                                {{ $entry->staff->name }}
                                            </span>

                                        @endif

                                    </div>


                                    <div class="timeline-date">
                                        <i class="bi bi-clock"></i>
                                        {{ $entry->created_at->format('d M Y • H:i') }}
                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </section>

            </div>

        @endif

    </div>

</div>

@endif


{{-- =========================================================
     STYLES
========================================================== --}}

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">
<style>html { --profile-background-image: url('{{ $profileBackground }}'); }</style>
@endpush


{{-- =========================================================
     JAVASCRIPT
========================================================== --}}

<script>

function filterTimeline(type, button) {

    const items =
        document.querySelectorAll('.timeline-item');

    let visible = 0;

    items.forEach(function (item) {

        const shouldShow =
            type === 'all'
            || item.dataset.type === type;

        item.style.display =
            shouldShow ? '' : 'none';

        if (shouldShow) {
            visible++;
        }

    });


    document
        .querySelectorAll('.timeline-filters button')
        .forEach(function (filterButton) {

            filterButton.classList.remove('active');

        });


    if (button) {
        button.classList.add('active');
    }


    const count =
        document.getElementById('timelineCount');

    if (count) {
        count.textContent = visible;
    }

}

</script>


@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(function (element) {

                bootstrap.Tooltip.getOrCreateInstance(
                    element,
                    {
                        html: true,
                        trigger: 'hover focus',
                        container: 'body'
                    }
                );

            });

    }
);

</script>

@endpush


{{-- Keep any shared profile styling/functions you already use --}}
@include('profile.partials.design')

@endif

</div>

@vite('resources/js/profile-activity.js')
@endsection