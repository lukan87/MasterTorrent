@extends('layouts.app')

@section('content')

<div class="member-profile">

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
                        class="profile-avatar"
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
                            style="color: {{ \App\Models\UserClass::getClassColor($user->user_class) }};"
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
                    <strong>{{ number_format($user->torrents()->count()) }}</strong>
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

    <div
        class="accordion profile-accordion"
        id="seederRankAccordion"
    >

        {{-- RANK GUIDE --}}
        <div class="accordion-item profile-panel">

            <h2
                class="accordion-header"
                id="headingSeederRank"
            >

                <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseSeederRank"
                    aria-expanded="false"
                    aria-controls="collapseSeederRank"
                >

                    <span class="accordion-heading-icon">
                        <i class="bi bi-award"></i>
                    </span>

                    <span>
                        <strong>Seeder Rank System</strong>
                        <small>
                            See how reputation and ranks work
                        </small>
                    </span>

                </button>

            </h2>


            <div
                id="collapseSeederRank"
                class="accordion-collapse collapse"
                aria-labelledby="headingSeederRank"
                data-bs-parent="#seederRankAccordion"
            >

                <div class="accordion-body">

                    <p class="rank-intro">
                        Seeder Reputation determines your rank.
                        Reputation increases when you seed torrents for longer
                        periods and when you seed larger torrents.
                    </p>


                    <div class="rank-grid">

                        <div class="rank-box">
                            <div class="rank-icon">🌱</div>
                            <div class="rank-title">New Seeder</div>
                            <div class="rank-desc">
                                Starting rank for new users beginning their seeding journey.
                            </div>
                            <div class="rank-score">
                                0 – 100 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥉</div>
                            <div class="rank-title">Bronze</div>
                            <div class="rank-desc">
                                You have started contributing by seeding torrents.
                            </div>
                            <div class="rank-score">
                                101 – 300 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥈</div>
                            <div class="rank-title">Silver</div>
                            <div class="rank-desc">
                                Consistent seeder helping keep torrents alive.
                            </div>
                            <div class="rank-score">
                                301 – 600 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥇</div>
                            <div class="rank-title">Gold</div>
                            <div class="rank-desc">
                                Strong contributor with significant seeding activity.
                            </div>
                            <div class="rank-score">
                                601 – 1000 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">💎</div>
                            <div class="rank-title">Elite</div>
                            <div class="rank-desc">
                                Highly dedicated seeder supporting the tracker ecosystem.
                            </div>
                            <div class="rank-score">
                                1001 – 2000 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">👑</div>
                            <div class="rank-title">Legend</div>
                            <div class="rank-desc">
                                Top tier seeder with exceptional long-term contribution.
                            </div>
                            <div class="rank-score">
                                2001+ reputation
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- SUBSCRIPTIONS --}}
        @if($isOwner || $isModerator)

            <div class="accordion-item profile-panel">

                <h2
                    class="accordion-header"
                    id="headingSubscriptions"
                >

                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseSubscriptions"
                        aria-expanded="false"
                        aria-controls="collapseSubscriptions"
                    >

                        <span class="accordion-heading-icon subscriptions">
                            <i class="bi bi-bell"></i>
                        </span>

                        <span>
                            <strong>Subscribed Torrents</strong>

                            <small>
                                Titles followed by this member
                            </small>
                        </span>

                        <span class="subscription-count">
                            {{ count($subscribedTorrents) }}
                        </span>

                    </button>

                </h2>


                <div
                    id="collapseSubscriptions"
                    class="accordion-collapse collapse"
                    aria-labelledby="headingSubscriptions"
                    data-bs-parent="#seederRankAccordion"
                >

                    <div class="accordion-body">

                        <p class="rank-intro">
                            Titles you are subscribed to. When a new upload
                            matches one of these, you'll be notified by private message.
                        </p>


                        <div class="subscribed-list">

                            @forelse($subscribedTorrents as $subTorrent)

                                @php
                                    $libraryRoute =
                                        !empty($subTorrent->tmdbid)
                                        && !empty($subTorrent->library_type)
                                            ? (
                                                $subTorrent->library_type === 'series'
                                                    ? 'library.series.show'
                                                    : 'library.movies.show'
                                            )
                                            : null;

                                    $libraryHref = $libraryRoute
                                        ? route($libraryRoute, [
                                            'tmdbid' => $subTorrent->tmdbid,
                                            'slug' => $subTorrent->library_slug,
                                        ])
                                        : route('torrents.show', [
                                            'id' => $subTorrent->id,
                                            'slug' => $subTorrent->slug
                                        ]);
                                @endphp


                                <a
                                    href="{{ $libraryHref }}"
                                    class="sub-torrent-row"
                                >

                                    <img
                                        class="subscribed-poster"
                                        src="{{ $subTorrent->poster ?: asset('images/noposter.jpg') }}"
                                        alt=""
                                        loading="lazy"
                                    >


                                    <div class="subscribed-info">

                                        <span class="subscribed-name">
                                            {{ \Illuminate\Support\Str::limit($subTorrent->name, 60) }}
                                        </span>

                                        <div class="subscribed-meta">

                                            <span title="Seeders">
                                                <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                                {{ $subTorrent->seeders ?? 0 }}
                                            </span>

                                            <span title="Leechers">
                                                <i class="bi bi-arrow-down-circle-fill text-danger"></i>
                                                {{ $subTorrent->leechers ?? 0 }}
                                            </span>

                                            <span title="Times completed">
                                                <i class="bi bi-check-circle-fill text-info"></i>
                                                {{ $subTorrent->times_completed ?? 0 }}
                                            </span>

                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right subscribed-arrow"></i>

                                </a>

                            @empty

                                <div class="subscribed-empty">

                                    <i class="bi bi-bell-slash"></i>

                                    <strong>No subscriptions yet</strong>

                                    <span>
                                        This member hasn't subscribed to any titles.
                                    </span>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            </div>

        @endif

    </div>


    {{-- INVITE TREE --}}
    @include('profile.partials.invite-tree')


    {{-- =====================================================
         PASSKEY
    ====================================================== --}}

    @if($isAdmin || $isOwner)

        <div class="profile-panel passkey-card">

            <div class="passkey-main">

                <div class="passkey-heading">

                    <span class="passkey-icon">
                        <i class="bi bi-key"></i>
                    </span>

                    <div>
                        <strong>Tracker Passkey</strong>
                        <small>
                            Private authentication key for torrent downloads
                        </small>
                    </div>

                </div>


                <div
                    id="passkeyDisplay"
                    class="passkey-text"
                    data-full="{{ $user->passkey }}"
                >
                    {{ str_repeat('*', max(0, strlen($user->passkey ?? '') - 3)) . substr($user->passkey ?? '', -3) }}
                </div>


                <div class="passkey-warning">
                    <i class="bi bi-shield-exclamation"></i>

                    Never share this key. Anyone with it may download using your account.
                </div>

            </div>


            <div class="passkey-actions">

                <button
                    type="button"
                    class="btn btn-outline-light"
                    onclick="togglePasskey(this)"
                >
                    <i class="bi bi-eye"></i>
                    <span>Show</span>
                </button>


                <form
                    action="{{ route('profile.passkey.regenerate', $user->id) }}"
                    method="POST"
                    onsubmit="return confirm('Regenerating will invalidate ALL current torrent links. Continue?')"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn btn-outline-danger"
                    >
                        <i class="bi bi-arrow-repeat"></i>
                        Regenerate
                    </button>

                </form>

            </div>

        </div>

    @endif

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

<style>

:root {
    --profile-bg: #071019;
    --profile-panel: rgba(10, 22, 34, .96);
    --profile-panel-light: rgba(15, 30, 44, .96);
    --profile-border: rgba(148, 163, 184, .14);
    --profile-border-hover: rgba(45, 212, 191, .42);
    --profile-text: #edf6fb;
    --profile-muted: #8fa4b3;
    --profile-cyan: #22d3ee;
    --profile-teal: #2dd4bf;
    --profile-green: #4ade80;
    --profile-red: #fb7185;
    --profile-yellow: #fbbf24;
    --profile-blue: #60a5fa;
}


/* =========================================================
   PAGE
========================================================= */

.member-profile {
    position: relative;
    color: var(--profile-text);
}

html,
body {
    min-height: 100%;
    background: var(--profile-bg);
}

html::before {
    opacity: .2;

    background-image:
        linear-gradient(
            to bottom,
            rgba(3, 9, 16, .78),
            rgba(3, 9, 16, .98)
        ),
        url('{{ $profileBackground }}');

    background-position: center top;
    background-size: cover;
    background-attachment: fixed;
}

.profile-panel {
    border: 1px solid var(--profile-border);
    border-radius: 14px;

    background:
        linear-gradient(
            145deg,
            rgba(16, 31, 46, .97),
            rgba(7, 17, 28, .97)
        );

    box-shadow:
        0 14px 35px rgba(0, 0, 0, .22),
        inset 0 1px 0 rgba(255, 255, 255, .025);
}


/* =========================================================
   HERO
========================================================= */

.profile-hero {
    position: relative;

    min-height: 520px;

    overflow: hidden;

    background-color: #08121b;
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center;

    border-bottom:
        1px solid rgba(148, 163, 184, .12);

    box-shadow:
        0 20px 50px rgba(0, 0, 0, .38);
}

.profile-hero-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;

    background:
        linear-gradient(
            to bottom,
            rgba(2, 8, 14, .20) 0%,
            rgba(2, 8, 14, .16) 28%,
            rgba(2, 8, 14, .40) 55%,
            rgba(2, 8, 14, .88) 84%,
            #071019 100%
        ),
        linear-gradient(
            90deg,
            rgba(2, 8, 14, .62),
            rgba(2, 8, 14, .12) 55%,
            rgba(2, 8, 14, .05)
        );
}

.profile-hero-inner {
    position: relative;
    z-index: 2;

    min-height: 520px;

    padding:
        0 38px 38px;
}

.profile-hero-content {
    position: absolute;

    left: 38px;
    right: 38px;
    bottom: 38px;

    display: grid;

    grid-template-columns:
        190px minmax(0, 1fr) auto;

    align-items: end;

    gap: 28px;
}


/* AVATAR */

.profile-avatar-wrap {
    position: relative;

    width: 190px;
    height: 190px;
}

.profile-avatar {
    display: block;

    width: 190px;
    height: 190px;

    object-fit: cover;

    border-radius: 50%;

    border:
        4px solid rgba(245, 250, 253, .96);

    background: #08131c;

    box-shadow:
        0 14px 38px rgba(0, 0, 0, .72),
        0 0 0 5px rgba(9, 20, 30, .62);
}

.profile-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 4rem;
    color: #71859a;
}

.profile-online-indicator {
    position: absolute;

    right: 6px;
    bottom: 10px;

    width: 28px;
    height: 28px;

    border-radius: 50%;

    background: #4ade80;

    border: 4px solid #08131c;

    box-shadow:
        0 0 15px rgba(74, 222, 128, .65);
}


/* IDENTITY */

.profile-identity {
    min-width: 0;
}

.profile-name-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 9px;
}

.profile-name {
    color: #fff;

    font-size:
        clamp(2rem, 3vw, 2.6rem);

    font-weight: 800;
    line-height: 1.05;

    letter-spacing: -.8px;

    text-shadow:
        0 3px 16px rgba(0, 0, 0, .75);
}

.profile-seeder-icon {
    margin-left: 5px;

    font-size: 1.2rem;
}

.profile-role-badge,
.profile-small-badge {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    padding:
        5px 10px;

    border-radius: 999px;

    font-size: .76rem;
    font-weight: 700;

    backdrop-filter: blur(8px);
}

.profile-role-badge {
    color: #e5eef4;

    border:
        1px solid rgba(203, 213, 225, .28);

    background:
        rgba(15, 30, 43, .82);
}

.profile-small-badge.donor {
    color: #86efac;

    border:
        1px solid rgba(74, 222, 128, .28);

    background:
        rgba(22, 101, 52, .32);
}

.profile-small-badge.vip {
    color: #fcd34d;

    border:
        1px solid rgba(251, 191, 36, .28);

    background:
        rgba(120, 83, 12, .28);
}

.profile-small-badge.warned {
    color: #fbbf24;

    border:
        1px solid rgba(251, 191, 36, .28);

    background:
        rgba(120, 53, 15, .30);
}

.profile-tagline {
    max-width: 760px;

    margin-top: 13px;

    color: #dce8ef;

    font-size: .94rem;
    line-height: 1.55;

    text-shadow:
        0 2px 10px rgba(0, 0, 0, .8);
}

.profile-meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 22px;

    margin-top: 19px;
}

.profile-meta-item {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    color: #dbe7ed;

    font-size: .82rem;

    text-shadow:
        0 2px 8px rgba(0, 0, 0, .7);
}

.profile-meta-item i {
    color: #c8d9e2;
}

.profile-meta-item.online {
    color: #86efac;
}

.profile-online-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #4ade80;

    box-shadow:
        0 0 9px rgba(74, 222, 128, .8);
}


/* =========================================================
   HERO ACTIONS
========================================================= */

.profile-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;

    gap: 8px;
}

.profile-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    min-height: 44px;

    padding:
        0 16px;

    border-radius: 9px;

    text-decoration: none;

    font-size: .82rem;
    font-weight: 700;

    transition:
        .18s ease;

    cursor: pointer;
}

.profile-action-btn:hover {
    transform:
        translateY(-1px);
}

.profile-action-btn.primary {
    color: #67e8f9;

    border:
        1px solid rgba(34, 211, 238, .55);

    background:
        rgba(5, 35, 46, .72);
}

.profile-action-btn.primary:hover {
    color: #cffafe;

    border-color: #22d3ee;

    background:
        rgba(8, 53, 66, .88);
}

.profile-action-btn.warning {
    color: #fcd34d;

    border:
        1px solid rgba(251, 191, 36, .45);

    background:
        rgba(58, 41, 10, .68);
}

.profile-action-btn.danger {
    color: #fda4af;

    border:
        1px solid rgba(251, 113, 133, .40);

    background:
        rgba(65, 14, 24, .66);
}


/* =========================================================
   USERNAME TOOLTIP
========================================================= */

.profile-username-tooltip {
    cursor: help;
}

.profile-tooltip {
    --bs-tooltip-bg: transparent;
    --bs-tooltip-opacity: 1;

    max-width: none !important;
}

.profile-tooltip .tooltip-inner {
    width: 560px;

    max-width:
        min(560px, calc(100vw - 30px));

    padding:
        14px 16px;

    text-align: left;

    color: #e8f3f7;

    background:
        linear-gradient(
            145deg,
            rgba(9, 24, 36, .99),
            rgba(5, 16, 26, .995)
        );

    border:
        1px solid rgba(94, 234, 212, .28);

    border-radius: 12px;

    box-shadow:
        0 18px 45px rgba(0, 0, 0, .5);
}

.profile-tooltip-header {
    display: flex;
    justify-content: space-between;

    gap: 18px;

    padding-bottom: 11px;

    margin-bottom: 4px;

    border-bottom:
        1px solid rgba(255, 255, 255, .1);
}

.profile-tooltip-header span {
    color: #67e8f9;
}

.profile-tooltip-divider {
    height: 1px;

    margin: 7px 0;

    background:
        rgba(255, 255, 255, .08);
}

.profile-tooltip-row {
    display: grid;

    grid-template-columns:
        22px minmax(100px, 1fr) auto;

    align-items: center;

    gap: 10px;

    min-height: 36px;

    padding:
        6px 0;

    border-bottom:
        1px solid rgba(255, 255, 255, .06);
}

.profile-tooltip-row:last-child {
    border-bottom: 0;
}

.profile-tooltip-row i {
    color: #67e8f9;
}

.profile-tooltip-row span {
    color:
        rgba(218, 234, 240, .70);
}

.profile-tooltip-row strong {
    color: #fff;

    text-align: right;

    overflow-wrap: anywhere;
}


/* =========================================================
   DASHBOARD
========================================================= */

.profile-dashboard {
    padding-top: 28px;
}


/* JUMP NAV */

.profile-jump-links {
    display: inline-flex;
    align-items: center;

    gap: 4px;

    margin-bottom: 27px;

    padding: 5px;

    border:
        1px solid rgba(148, 163, 184, .13);

    border-radius: 11px;

    background:
        rgba(7, 18, 29, .76);
}

.profile-jump-links a {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    min-height: 36px;

    padding:
        0 12px;

    border-radius: 7px;

    color: #8fa4b3;

    text-decoration: none;

    font-size: .77rem;
    font-weight: 600;

    transition:
        .15s ease;
}

.profile-jump-links a:hover,
.profile-jump-links a.active {
    color: #dffbff;

    background:
        rgba(34, 211, 238, .10);
}

.profile-jump-links a.active {
    color: #67e8f9;
}


/* SECTION HEADING */

.profile-section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 18px;
}

.profile-eyebrow {
    display: block;

    margin-bottom: 4px;

    color: #5eead4;

    font-size: .65rem;
    font-weight: 800;

    letter-spacing: .13em;
    text-transform: uppercase;
}

.profile-section-heading h2,
.subsection-heading h3,
.content-panel-heading h3 {
    margin: 0;

    color: #f8fafc;

    font-weight: 750;

    letter-spacing: -.025em;
}

.profile-section-heading h2 {
    font-size: 1.45rem;
}

.profile-section-heading p {
    margin:
        5px 0 0;

    color: var(--profile-muted);

    font-size: .78rem;
}

.profile-tenure {
    display: flex;
    align-items: center;

    gap: 10px;

    padding:
        8px 12px;

    border:
        1px solid rgba(148, 163, 184, .12);

    border-radius: 9px;

    background:
        rgba(10, 22, 34, .65);
}

.profile-tenure > i {
    color: #5eead4;

    font-size: 1rem;
}

.profile-tenure div {
    display: flex;
    flex-direction: column;
}

.profile-tenure small {
    color: #738897;

    font-size: .61rem;

    text-transform: uppercase;
    letter-spacing: .06em;
}

.profile-tenure strong {
    color: #dbe7ed;

    font-size: .75rem;
}


/* =========================================================
   TRANSFER CARDS
========================================================= */

.transfer-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;
}

.transfer-card {
    position: relative;

    display: flex;
    align-items: center;

    gap: 14px;

    min-width: 0;

    padding:
        17px;

    overflow: hidden;

    border:
        1px solid var(--profile-border);

    border-radius: 12px;

    background:
        linear-gradient(
            145deg,
            rgba(15, 30, 44, .96),
            rgba(7, 17, 28, .96)
        );

    transition:
        transform .17s ease,
        border-color .17s ease;
}

.transfer-card:hover {
    transform:
        translateY(-2px);

    border-color:
        rgba(45, 212, 191, .30);
}

.transfer-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 46px;

    width: 46px;
    height: 46px;

    border-radius: 11px;

    font-size: 1.25rem;
}

.transfer-card.uploaded .transfer-icon {
    color: #86efac;

    background:
        rgba(34, 197, 94, .10);
}

.transfer-card.downloaded .transfer-icon {
    color: #93c5fd;

    background:
        rgba(59, 130, 246, .10);
}

.transfer-card.ratio .transfer-icon {
    color: #67e8f9;

    background:
        rgba(6, 182, 212, .10);
}

.transfer-card.seeding .transfer-icon {
    color: #c4b5fd;

    background:
        rgba(139, 92, 246, .10);
}

.transfer-content {
    min-width: 0;
}

.transfer-content > span {
    display: block;

    margin-bottom: 2px;

    color: #8296a5;

    font-size: .65rem;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .07em;
}

.transfer-content strong {
    display: block;

    color: #f1f7fa;

    font-size: 1.12rem;
    font-weight: 750;

    overflow-wrap: anywhere;
}

.transfer-content small {
    display: block;

    margin-top: 3px;

    color: #647887;

    font-size: .66rem;
}

.stat-link {
    color: inherit;
    text-decoration: none;
}

.stat-link:hover {
    color: #5eead4;
}


/* =========================================================
   FEATURE CARDS
========================================================= */

.profile-feature-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(0, .75fr);

    gap: 12px;

    margin-top: 12px;
}

.profile-feature-card {
    padding: 20px;

    border:
        1px solid var(--profile-border);

    border-radius: 13px;

    background:
        linear-gradient(
            145deg,
            rgba(14, 29, 43, .98),
            rgba(7, 17, 28, .98)
        );

    box-shadow:
        0 12px 30px rgba(0, 0, 0, .18);
}

.feature-card-top {
    display: flex;
    align-items: center;

    gap: 12px;
}

.feature-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 44px;
    height: 44px;

    flex: 0 0 44px;

    border-radius: 11px;

    color: #fcd34d;

    background:
        rgba(251, 191, 36, .10);

    font-size: 1.2rem;
}

.health-card .feature-icon {
    color: #86efac;

    background:
        rgba(34, 197, 94, .10);
}

.feature-heading {
    min-width: 0;

    display: flex;
    flex-direction: column;

    flex: 1;
}

.feature-heading span {
    color: #7f94a3;

    font-size: .65rem;
    font-weight: 700;

    letter-spacing: .07em;
    text-transform: uppercase;
}

.feature-heading strong {
    margin-top: 2px;

    color: #edf5f9;

    font-size: .96rem;
}

.feature-score {
    color: #f8fafc;

    font-size: 1.35rem;
    font-weight: 800;

    white-space: nowrap;
}

.feature-score small {
    margin-left: 2px;

    color: #7f94a3;

    font-size: .65rem;
    font-weight: 600;
}

.feature-progress-header {
    display: flex;
    justify-content: space-between;

    margin-top: 23px;
    margin-bottom: 7px;

    color: #8498a7;

    font-size: .68rem;
}

.feature-progress-header strong {
    color: #cbd9e1;
}

.profile-progress {
    height: 7px;

    overflow: hidden;

    border-radius: 999px;

    background:
        rgba(255, 255, 255, .07);
}

.feature-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 14px;

    padding-top: 12px;

    border-top:
        1px solid rgba(148, 163, 184, .10);

    font-size: .72rem;
}

.feature-footer span {
    color: #718694;
}

.feature-footer strong {
    color: #dce8ee;
}

.health-meter {
    display: flex;
    align-items: center;

    gap: 17px;

    margin-top: 20px;
}

.health-meter-ring {
    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 74px;

    width: 74px;
    height: 74px;

    border-radius: 50%;

    border:
        7px solid rgba(74, 222, 128, .22);

    box-shadow:
        inset 0 0 20px rgba(74, 222, 128, .05);
}

.health-meter-ring span {
    color: #dffbea;

    font-size: .88rem;
    font-weight: 800;
}

.health-meter-info {
    flex: 1;
    min-width: 0;
}

.health-meter-info > span {
    display: block;

    margin-bottom: 9px;

    color: #dbe7ed;

    font-size: .78rem;
    font-weight: 600;
}

.health-meter-info small {
    display: block;

    margin-top: 7px;

    color: #6f8492;

    font-size: .65rem;
}


/* =========================================================
   COMMUNITY
========================================================= */

.subsection-heading {
    margin:
        27px 0 12px;
}

.subsection-heading h3 {
    font-size: 1rem;
}

.community-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 10px;
}

.community-stat-card {
    display: flex;
    align-items: center;

    gap: 11px;

    min-width: 0;

    min-height: 70px;

    padding:
        12px 13px;

    color: inherit;

    text-decoration: none;

    border:
        1px solid rgba(148, 163, 184, .12);

    border-radius: 10px;

    background:
        rgba(9, 21, 33, .82);

    transition:
        transform .16s ease,
        border-color .16s ease,
        background .16s ease;
}

a.community-stat-card:hover {
    color: inherit;

    transform:
        translateY(-1px);

    border-color:
        rgba(45, 212, 191, .28);

    background:
        rgba(13, 29, 43, .94);
}

.community-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 36px;

    width: 36px;
    height: 36px;

    border-radius: 9px;

    color: #94a3b8;

    background:
        rgba(148, 163, 184, .08);
}

.community-stat-icon.bonus {
    color: #fcd34d;
}

.community-stat-icon.invite {
    color: #67e8f9;
}

.community-stat-icon.slots {
    color: #c4b5fd;
}

.community-stat-icon.comments {
    color: #93c5fd;
}

.community-stat-icon.thanks {
    color: #fda4af;
}

.community-stat-icon.posts {
    color: #5eead4;
}

.community-stat-icon.uploads {
    color: #86efac;
}

.community-stat-icon.hitrun {
    color: #fb7185;
}

.community-stat-card > div {
    min-width: 0;

    display: flex;
    flex-direction: column;

    flex: 1;
}

.community-stat-card small {
    color: #728795;

    font-size: .62rem;
    font-weight: 700;

    letter-spacing: .05em;
    text-transform: uppercase;
}

.community-stat-card strong {
    margin-top: 2px;

    color: #eaf3f7;

    font-size: .94rem;
}

.card-arrow {
    color: #536a78;

    font-size: .7rem;
}

.hitrun-card strong {
    color: #fda4af;
}


/* =========================================================
   SECONDARY AREA
========================================================= */

.profile-secondary {
    margin-top: 26px;
}

.profile-accordion {
    display: flex;
    flex-direction: column;

    gap: 10px;
}

.profile-accordion .accordion-item {
    overflow: hidden;
}

.profile-accordion .accordion-button {
    display: flex;
    align-items: center;

    gap: 12px;

    min-height: 65px;

    padding:
        11px 15px;

    color: #e4edf2 !important;

    background:
        rgba(9, 21, 33, .88) !important;

    box-shadow: none !important;
}

.profile-accordion .accordion-button:not(.collapsed) {
    color: #dffbff !important;

    background:
        rgba(12, 29, 43, .96) !important;
}

.profile-accordion .accordion-button::after {
    margin-left: 12px;

    filter: invert(1);

    opacity: .55;
}

.accordion-heading-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    border-radius: 9px;

    color: #fcd34d;

    background:
        rgba(251, 191, 36, .10);
}

.accordion-heading-icon.subscriptions {
    color: #67e8f9;

    background:
        rgba(34, 211, 238, .10);
}

.profile-accordion .accordion-button > span:nth-child(2) {
    display: flex;
    flex-direction: column;

    flex: 1;

    text-align: left;
}

.profile-accordion .accordion-button strong {
    font-size: .82rem;
}

.profile-accordion .accordion-button small {
    margin-top: 2px;

    color: #718694;

    font-size: .64rem;
}

.subscription-count {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    flex: 0 0 auto !important;

    min-width: 27px;
    height: 27px;

    padding:
        0 7px;

    border-radius: 999px;

    color: #67e8f9;

    background:
        rgba(34, 211, 238, .10);

    font-size: .7rem;
    font-weight: 700;
}

.profile-accordion .accordion-body {
    padding: 20px;

    background:
        rgba(6, 16, 26, .66);
}

.rank-intro {
    margin:
        0 0 17px;

    color: #8397a5;

    font-size: .74rem;
}

.rank-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 10px;
}

.rank-box {
    padding: 15px;

    text-align: center;

    border:
        1px solid rgba(148, 163, 184, .11);

    border-radius: 10px;

    background:
        rgba(8, 18, 29, .72);

    transition:
        .15s ease;
}

.rank-box:hover {
    border-color:
        rgba(45, 212, 191, .26);

    transform:
        translateY(-1px);
}

.rank-icon {
    font-size: 1.5rem;
}

.rank-title {
    margin-top: 4px;

    color: #e5edf5;

    font-size: .83rem;
    font-weight: 700;
}

.rank-desc {
    margin-top: 5px;

    color: #778b99;

    font-size: .67rem;
    line-height: 1.45;
}

.rank-score {
    margin-top: 8px;

    color: #5eead4;

    font-size: .66rem;
    font-weight: 700;
}


/* =========================================================
   SUBSCRIPTIONS
========================================================= */

.subscribed-list {
    display: flex;
    flex-direction: column;

    gap: 5px;
}

.sub-torrent-row {
    display: flex;
    align-items: center;

    gap: 12px;

    padding:
        9px 10px;

    border-radius: 9px;

    color: #e2e8f0;

    text-decoration: none;

    transition:
        .15s ease;
}

.sub-torrent-row:hover {
    color: #fff;

    background:
        rgba(34, 211, 238, .07);
}

.subscribed-poster {
    width: 38px;
    height: 54px;

    flex-shrink: 0;

    object-fit: cover;

    border-radius: 6px;

    background: #1e293b;
}

.subscribed-info {
    display: flex;
    flex-direction: column;

    gap: 4px;

    min-width: 0;

    flex: 1;
}

.subscribed-name {
    color: #e7eff3;

    font-size: .76rem;
    font-weight: 600;
}

.subscribed-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 12px;

    color: #718694;

    font-size: .66rem;
}

.subscribed-arrow {
    color: #536a78;
}

.subscribed-empty {
    display: flex;
    flex-direction: column;
    align-items: center;

    padding:
        28px 12px;

    color: #718694;

    text-align: center;
}

.subscribed-empty > i {
    margin-bottom: 8px;

    font-size: 1.6rem;
}

.subscribed-empty strong {
    color: #b8c8d1;

    font-size: .78rem;
}

.subscribed-empty span {
    margin-top: 3px;

    font-size: .68rem;
}


/* =========================================================
   PASSKEY
========================================================= */

.passkey-card {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 22px;

    margin-top: 12px;

    padding: 17px;

    border-color:
        rgba(251, 191, 36, .15);
}

.passkey-main {
    min-width: 0;
    flex: 1;
}

.passkey-heading {
    display: flex;
    align-items: center;

    gap: 10px;

    margin-bottom: 12px;
}

.passkey-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 36px;
    height: 36px;

    border-radius: 9px;

    color: #fcd34d;

    background:
        rgba(251, 191, 36, .09);
}

.passkey-heading div {
    display: flex;
    flex-direction: column;
}

.passkey-heading strong {
    color: #eaf1f5;

    font-size: .8rem;
}

.passkey-heading small {
    color: #748896;

    font-size: .63rem;
}

.passkey-text {
    max-width: 650px;

    overflow: hidden;
    text-overflow: ellipsis;

    padding:
        8px 10px;

    border:
        1px solid rgba(148, 163, 184, .11);

    border-radius: 7px;

    color: #cbd5e1;

    background:
        rgba(0, 0, 0, .22);

    font-family:
        ui-monospace,
        SFMono-Regular,
        Consolas,
        monospace;

    font-size: .73rem;

    letter-spacing: 1px;
}

.passkey-warning {
    margin-top: 8px;

    color: #c6a65a;

    font-size: .64rem;
}

.passkey-actions {
    display: flex;
    align-items: center;

    gap: 7px;
}

.passkey-actions .btn {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    font-size: .72rem;
}


/* =========================================================
   ABOUT + TIMELINE
========================================================= */

.profile-content-sections {
    padding-top: 26px;
    padding-bottom: 30px;
}

.content-panel {
    height: 100%;

    padding: 18px;
}

.content-panel-heading {
    display: flex;
    align-items: center;

    gap: 11px;

    margin-bottom: 16px;
}

.content-panel-heading h3 {
    font-size: .96rem;
}

.content-panel-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    border-radius: 9px;
}

.content-panel-icon.about {
    color: #67e8f9;

    background:
        rgba(34, 211, 238, .09);
}

.content-panel-icon.timeline {
    color: #5eead4;

    background:
        rgba(45, 212, 191, .09);
}

.about-scroll,
.elite-timeline-scroll {
    max-height: 550px;

    overflow-y: auto;
    overflow-x: hidden;

    padding-right: 5px;
}

.about-scroll {
    color: #b9c9d1;

    font-size: .78rem;
    line-height: 1.65;
}

.about-scroll::-webkit-scrollbar,
.elite-timeline-scroll::-webkit-scrollbar {
    width: 5px;
}

.about-scroll::-webkit-scrollbar-thumb,
.elite-timeline-scroll::-webkit-scrollbar-thumb {
    border-radius: 999px;

    background:
        rgba(148, 163, 184, .24);
}


/* TIMELINE */

.timeline-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 15px;
}

.timeline-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 24px;
    height: 20px;

    margin-left: 5px;

    padding:
        0 6px;

    border-radius: 999px;

    color: #67e8f9;

    background:
        rgba(34, 211, 238, .10);

    font-size: .62rem;
}

.timeline-filters {
    display: flex;
    flex-wrap: wrap;

    gap: 4px;
}

.timeline-filters button {
    padding:
        5px 8px;

    border:
        1px solid rgba(148, 163, 184, .13);

    border-radius: 6px;

    color: #7f94a3;

    background:
        rgba(8, 18, 29, .55);

    font-size: .63rem;

    transition:
        .15s ease;
}

.timeline-filters button:hover,
.timeline-filters button.active {
    color: #5eead4;

    border-color:
        rgba(45, 212, 191, .28);

    background:
        rgba(45, 212, 191, .07);
}

.timeline-item {
    position: relative;

    padding-left: 25px;

    margin-bottom: 12px;
}

.timeline-item::before {
    content: "";

    position: absolute;

    left: 6px;
    top: 17px;
    bottom: -15px;

    width: 1px;

    background:
        rgba(148, 163, 184, .13);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-dot {
    position: absolute;

    left: 1px;
    top: 11px;

    width: 11px;
    height: 11px;

    border-radius: 50%;

    background: #2dd4bf;

    box-shadow:
        0 0 9px rgba(45, 212, 191, .40);
}

.timeline-card {
    padding:
        11px 12px;

    border:
        1px solid rgba(148, 163, 184, .11);

    border-radius: 9px;

    background:
        rgba(8, 18, 29, .66);
}

.timeline-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 12px;
}

.timeline-comment {
    color: #aebfc8;

    font-size: .74rem;
    line-height: 1.5;
}

.timeline-chat {
    color: #67e8f9;
}

.timeline-forum {
    color: #fda4af;
}

.timeline-warning {
    color: #fcd34d;
}

.timeline-vip {
    color: #fcd34d;
}

.timeline-staff {
    display: inline-flex;
    align-items: center;

    gap: 4px;

    flex-shrink: 0;

    padding:
        3px 6px;

    border-radius: 5px;

    color: #94a3b8;

    background:
        rgba(255, 255, 255, .04);

    font-size: .59rem;
}

.timeline-date {
    display: flex;
    align-items: center;

    gap: 5px;

    margin-top: 7px;

    color: #607583;

    font-size: .61rem;
}


/* =========================================================
   DELETE ACCOUNT
========================================================= */

.deleted-account-panel {
    display: flex;
    align-items: flex-start;

    gap: 16px;

    padding: 20px;

    border-color:
        rgba(248, 113, 113, .35);
}

.deleted-account-icon {
    color: #f87171;

    font-size: 1.8rem;
}

.delete-panel {
    padding: 20px;

    border-color:
        rgba(248, 113, 113, .28);
}

.delete-panel-header {
    display: flex;

    gap: 12px;

    margin-bottom: 16px;
}

.delete-panel-header > i {
    color: #f87171;

    font-size: 1.4rem;
}

.delete-panel-header strong {
    color: #fda4af;
}

.delete-panel-header p {
    margin:
        2px 0 0;

    color: #8fa4b3;

    font-size: .72rem;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199.98px) {

    .profile-hero-content {
        grid-template-columns:
            160px minmax(0, 1fr);
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 160px;
        height: 160px;
    }

    .profile-actions {
        grid-column: 2;

        justify-content: flex-start;
    }

    .transfer-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 991.98px) {

    .profile-hero,
    .profile-hero-inner {
        min-height: 610px;
    }

    .profile-hero-inner {
        padding:
            0 20px 28px;
    }

    .profile-hero-content {
        left: 20px;
        right: 20px;
        bottom: 28px;

        display: flex;
        flex-direction: column;

        align-items: center;

        gap: 13px;

        text-align: center;
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 135px;
        height: 135px;
    }

    .profile-identity {
        width: 100%;
    }

    .profile-name-row,
    .profile-meta-row,
    .profile-actions {
        justify-content: center;
    }

    .profile-tagline {
        margin-left: auto;
        margin-right: auto;
    }

    .profile-feature-grid {
        grid-template-columns: 1fr;
    }

    .community-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 767.98px) {

    .profile-section-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .profile-tenure {
        width: 100%;
    }

    .rank-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .passkey-card {
        align-items: stretch;
        flex-direction: column;
    }

    .passkey-actions {
        justify-content: flex-start;
    }

    .timeline-heading {
        flex-direction: column;
    }

    .profile-tooltip .tooltip-inner {
        width:
            min(560px, calc(100vw - 24px));
    }

    .profile-tooltip-row {
        grid-template-columns:
            20px 1fr;
    }

    .profile-tooltip-row strong {
        grid-column: 2;

        text-align: left;
    }

}


@media (max-width: 575.98px) {

    .profile-hero,
    .profile-hero-inner {
        min-height: 575px;
    }

    .profile-hero-inner {
        padding:
            0 10px 22px;
    }

    .profile-hero-content {
        left: 10px;
        right: 10px;
        bottom: 22px;
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 108px;
        height: 108px;
    }

    .profile-avatar {
        border-width: 3px;
    }

    .profile-online-indicator {
        width: 23px;
        height: 23px;

        right: 1px;
        bottom: 3px;
    }

    .profile-name {
        font-size: 1.65rem;
    }

    .profile-tagline {
        font-size: .82rem;
    }

    .profile-meta-row {
        flex-direction: column;

        gap: 7px;

        margin-top: 13px;
    }

    .profile-actions {
        display: grid;

        width: 100%;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 6px;
    }

    .profile-action-btn {
        width: 100%;

        min-height: 40px;

        padding:
            0 8px;

        font-size: .72rem;
    }

    .profile-dashboard {
        padding-top: 20px;
    }

    .profile-jump-links {
        display: flex;

        width: 100%;

        overflow-x: auto;

        white-space: nowrap;
    }

    .profile-jump-links a {
        flex: 0 0 auto;
    }

    .transfer-grid {
        grid-template-columns: 1fr;

        gap: 8px;
    }

    .transfer-card {
        padding: 13px;
    }

    .profile-feature-card {
        padding: 15px;
    }

    .feature-card-top {
        align-items: flex-start;
    }

    .feature-score {
        font-size: 1.1rem;
    }

    .health-meter {
        align-items: flex-start;
    }

    .community-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 7px;
    }

    .community-stat-card {
        min-height: 65px;

        padding:
            10px;
    }

    .community-stat-icon {
        width: 32px;
        height: 32px;

        flex-basis: 32px;
    }

    .card-arrow {
        display: none;
    }

    .rank-grid {
        grid-template-columns: 1fr;
    }

    .profile-accordion .accordion-body {
        padding: 14px;
    }

    .passkey-actions {
        display: grid;

        grid-template-columns:
            repeat(2, 1fr);
    }

    .passkey-actions .btn,
    .passkey-actions form {
        width: 100%;
    }

    .passkey-actions .btn {
        justify-content: center;
    }

    .timeline-filters {
        width: 100%;
    }

}


/* Smooth section jumps */
#profile-overview,
#profile-about,
#profile-timeline,
#seederRankAccordion {
    scroll-margin-top: 90px;
}

</style>


{{-- =========================================================
     JAVASCRIPT
========================================================== --}}

<script>

function togglePasskey(button) {

    const element =
        document.getElementById('passkeyDisplay');

    if (!element) {
        return;
    }

    const full =
        element.dataset.full || '';

    const masked =
        '*'.repeat(Math.max(0, full.length - 3))
        + full.slice(-3);

    const showing =
        element.textContent.trim() === full;

    element.textContent =
        showing ? masked : full;

    if (button) {

        const icon =
            button.querySelector('i');

        const text =
            button.querySelector('span');

        if (showing) {

            if (icon) {
                icon.className = 'bi bi-eye';
            }

            if (text) {
                text.textContent = 'Show';
            }

        } else {

            if (icon) {
                icon.className = 'bi bi-eye-slash';
            }

            if (text) {
                text.textContent = 'Hide';
            }

        }

    }

}


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

@endsection