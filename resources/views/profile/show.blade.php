@extends('layouts.app')

@section('content')

@if($user->trashed())

<div class="container-fluid mt-3">

    <div class="elite-card border border-danger d-flex align-items-start gap-3 p-4">

        <div class="text-danger fs-2">

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

    $isModerator = $authUser && $authUser->user_class >= \App\Models\UserClass::MODERATOR;

    $isAdmin = $authUser && $authUser->user_class >= \App\Models\UserClass::ADMIN;



    // SEEDTIME

$seedtime = \Carbon\CarbonInterval::seconds($totalSeedTime)->cascade();

$years = $seedtime->years;

$months = $seedtime->months;

$days    = $seedtime->dayz; 

$hours = $seedtime->hours;

$minutes = $seedtime->minutes;

@endphp











{{-- =========================================================
     FILEIPLAY PROFILE HERO
     ========================================================= --}}

<div class="profile-hero mt-3"
     @if($coverImage)
         style="background-image: url('{{ $coverImage }}');"
     @endif>

    <div class="profile-hero-overlay"></div>

    <div class="container-fluid profile-hero-inner">
        <div class="profile-hero-content">

            {{-- AVATAR --}}
            <div class="profile-avatar-wrap">
                @if($user->profile_image)
                    <img src="{{ $user->profile_image }}"
                         alt="{{ $user->name }}"
                         class="profile-avatar">
                @else
                    <div class="profile-avatar profile-avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif

                @if(method_exists($user, 'isOnline') && $user->isOnline())
                    <span class="profile-online-indicator"
                          title="Online"></span>
                @endif
            </div>

            {{-- USER DETAILS --}}
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
                                                {{ $user->inviter?->name ?? 'Unknown' }}
                                            @else
                                                Independently
                                            @endif
                                        </strong>
                                    </div>
                                @endif
                            "
                        >
                            {{ $user->name }}
                            <span class="profile-seeder-icon">{{ $user->seeder_icon }}</span>
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

                {{-- SHORT PROFILE DESCRIPTION --}}
                @if(!empty($user->info))
                    <div class="profile-tagline">
                        {{ \Illuminate\Support\Str::limit(strip_tags($user->info), 120) }}
                    </div>
                @endif

                {{-- MAIN META ROW --}}
                <div class="profile-meta-row">

                    <div class="profile-meta-item">
                        <i class="bi bi-calendar3"></i>
                        <span>Member since {{ $user->created_at->format('M Y') }}</span>
                    </div>

                    <div class="profile-meta-item">
                        <i class="bi bi-clock"></i>
                        <span>
                            Last seen {{ $user->last_activity ? $user->last_activity->format('M Y') : 'Never' }}
                        </span>
                    </div>

                    @if(method_exists($user,'isOnline') && $user->isOnline())
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
                    <a href="{{ route('messages.create',['receiver_id'=>$user->id]) }}"
                       class="profile-action-btn primary">
                        <i class="bi bi-envelope"></i>
                        <span>Message</span>
                    </a>
                @endif

                @if($isOwner || $isModerator)
                    <a href="{{ route('profile.edit', ['id'=>$user->id,'name'=>$user->name]) }}"
                       class="profile-action-btn primary">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Profile</span>
                    </a>
                @endif

                @if($isModerator && ! $isOwner)
                    <a href="{{ route('admin.users.edit', $user->id) }}"
                       class="profile-action-btn warning">
                        <i class="bi bi-shield-lock"></i>
                        <span>Admin Edit</span>
                    </a>
                @endif

                @if($isOwner)
                    <button class="profile-action-btn danger"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#deleteAccountBox"
                            aria-expanded="{{ $errors->has('password') ? 'true' : 'false' }}">
                        <i class="bi bi-trash"></i>
                        <span>Delete Account</span>
                    </button>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- PASSKEY --}}
@if ($isAdmin || $isOwner)
    <div class="elite-card mt-3 p-3 passkey-card">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">

            <div class="flex-grow-1">
                <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-key text-warning"></i>
                    Passkey
                </div>

                <div id="passkeyDisplay"
                     class="passkey-text"
                     data-full="{{ $user->passkey }}">
                    {{ str_repeat('*', strlen($user->passkey) - 3) . substr($user->passkey, -3) }}
                </div>

                <small class="text-warning d-block">
                    ⚠ Never share this — it identifies you to the tracker.
                </small>

                <small class="text-warning d-block mt-1">
                    If exposed, someone can download using your ratio.
                </small>
            </div>

            <div class="d-flex flex-column gap-2">
                <button type="button"
                        class="btn btn-sm btn-outline-light"
                        onclick="togglePasskey()">
                    <i class="bi bi-eye"></i> Show
                </button>

                <form action="{{ route('profile.passkey.regenerate', $user->id) }}"
                      method="POST"
                      onsubmit="return confirm('Regenerating will invalidate ALL current torrent links. Continue?')">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="btn btn-sm btn-danger">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Regenerate
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- DELETE ACCOUNT --}}
@if($isOwner)
    <div class="collapse mt-3 @if($errors->has('password')) show @endif"
         id="deleteAccountBox">
        <div class="elite-card p-4 border border-danger">
            <div class="alert alert-danger mb-3">
                <strong>Warning:</strong>
                This action is permanent and cannot be undone.
            </div>

            <form action="{{ route('profile.delete', [$user->id, $user->name]) }}"
                  method="POST">
                @csrf
                @method('DELETE')

                <div class="mb-3">
                    <label class="form-label text-danger fw-bold">
                        Confirm Password to Delete Account
                    </label>

                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Enter your password"
                           required>

                    @error('password')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit"
                        class="btn btn-danger w-100 fw-bold">
                    Permanently Delete My Account
                </button>
            </form>
        </div>
    </div>
@endif


{{-- HEADER STATS --}}

<div id="profile-overview" class="container-fluid elite-header-stats mt-4">
<div id="profile-stats-anchor"></div>





@include('profile.partials.invite-tree')

<div class="row g-3 align-items-stretch elite-stats-grid">

<div class="accordion mt-4" id="seederRankAccordion">

    <div class="accordion-item elite-card border-0">

        <h2 class="accordion-header" id="headingSeederRank">

            <button class="accordion-button collapsed bg-transparent text-white shadow-none"

                    type="button"

                    data-bs-toggle="collapse"

                    data-bs-target="#collapseSeederRank"

                    aria-expanded="false"

                    aria-controls="collapseSeederRank">

                <i class="bi bi-award me-2 text-warning"></i>

                Seeder Rank System

            </button>

        </h2>

        <div id="collapseSeederRank"

             class="accordion-collapse collapse"

             aria-labelledby="headingSeederRank"

             data-bs-parent="#seederRankAccordion">

            <div class="accordion-body p-4">

                <p class="text-muted small mb-4">

                    Your <strong>Seeder Reputation</strong> determines your rank.

                    Reputation increases when you seed torrents for longer periods

                    and when you seed larger torrents.

                </p>

                <div class="row g-3">

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">🌱</div>

                            <div class="rank-title">New Seeder</div>

                            <div class="rank-desc">Starting rank for new users beginning their seeding journey.</div>

                            <div class="rank-score">0 – 100 reputation</div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">🥉</div>

                            <div class="rank-title">Bronze</div>

                            <div class="rank-desc">You have started contributing by seeding torrents.</div>

                            <div class="rank-score">101 – 300 reputation</div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">🥈</div>

                            <div class="rank-title">Silver</div>

                            <div class="rank-desc">Consistent seeder helping keep torrents alive.</div>

                            <div class="rank-score">301 – 600 reputation</div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">🥇</div>

                            <div class="rank-title">Gold</div>

                            <div class="rank-desc">Strong contributor with significant seeding activity.</div>

                            <div class="rank-score">601 – 1000 reputation</div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">💎</div>

                            <div class="rank-title">Elite</div>

                            <div class="rank-desc">Highly dedicated seeder supporting the tracker ecosystem.</div>

                            <div class="rank-score">1001 – 2000 reputation</div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="rank-box">

                            <div class="rank-icon">👑</div>

                            <div class="rank-title">Legend</div>

                            <div class="rank-desc">Top tier seeder with exceptional long-term contribution.</div>

                            <div class="rank-score">2001+ reputation</div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="accordion-item elite-card border-0">

        <h2 class="accordion-header" id="headingSubscriptions">

            <button class="accordion-button collapsed bg-transparent text-white shadow-none"

                    type="button"

                    data-bs-toggle="collapse"

                    data-bs-target="#collapseSubscriptions"

                    aria-expanded="false"

                    aria-controls="collapseSubscriptions">

                <i class="bi bi-bell me-2 text-warning"></i>

                Subscribed Torrents

                <span class="badge text-bg-warning rounded-pill ms-2">{{ count($subscribedTorrents) }}</span>

            </button>

        </h2>

        <div id="collapseSubscriptions"

             class="accordion-collapse collapse"

             aria-labelledby="headingSubscriptions"

             data-bs-parent="#seederRankAccordion">

            <div class="accordion-body p-4">

                <p class="text-muted small mb-3">

                    Titles you are subscribed to. When a new upload matches one of these,

                    you'll be notified by private message.

                </p>

                @forelse($subscribedTorrents as $subTorrent)
                    @php
                        // Link to the library show page for this TMDB id so the user
                        // can browse every uploaded torrent for the title.
                        $libraryRoute = ! empty($subTorrent->tmdbid) && ! empty($subTorrent->library_type)
                            ? ($subTorrent->library_type === 'series'
                                ? 'library.series.show'
                                : 'library.movies.show')
                            : null;
                        $libraryHref  = $libraryRoute
                            ? route($libraryRoute, [
                                'tmdbid' => $subTorrent->tmdbid,
                                'slug'   => $subTorrent->library_slug,
                            ])
                            : route('torrents.show', ['id' => $subTorrent->id, 'slug' => $subTorrent->slug]);
                    @endphp
                    <a href="{{ $libraryHref }}"
                       class="sub-torrent-row">
                        <img class="subscribed-poster"
                             src="{{ $subTorrent->poster ?: asset('images/noposter.jpg') }}"
                             alt=""
                             loading="lazy">
                        <div class="subscribed-info">
                            <span class="subscribed-name">{{ \Illuminate\Support\Str::limit($subTorrent->name, 60) }}</span>
                            <div class="subscribed-meta">
                                <span title="Seeders"><i class="bi bi-arrow-up-circle-fill text-success"></i> {{ $subTorrent->seeders ?? 0 }}</span>
                                <span class="ms-2" title="Leechers"><i class="bi bi-arrow-down-circle-fill text-danger"></i> {{ $subTorrent->leechers ?? 0 }}</span>
                                <span class="ms-2" title="Times completed"><i class="bi bi-check-circle-fill text-info"></i> {{ $subTorrent->times_completed ?? 0 }}</span>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted ms-auto"></i>
                    </a>
                @empty
                    <div class="subscribed-empty">
                        <i class="bi bi-bell-slash text-muted"></i>
                        You haven't subscribed to any titles yet.
                    </div>
                @endforelse

            </div>

        </div>

    </div>

</div>

{{-- Uploaded --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Uploaded</div>

        <div class="stat-value">

            @if ($isOwner || $isModerator)

                <a href="{{ route('snatch.seeding', ['userId' => $user->id]) }}" class="fancy-link">

                    {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}

                </a>

            @else

                {{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}

            @endif

        </div>

    </div>

</div>

{{-- Downloaded --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Downloaded</div>

        <div class="stat-value">

            @if ($isOwner || $isModerator)

                <a href="{{ route('snatch.leeching', ['userId' => $user->id]) }}" class="fancy-link">

                    {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}

                </a>

            @else

                {{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}

            @endif

        </div>

    </div>

</div>

{{-- Ratio --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Ratio</div>

        <div class="stat-value">

            {{ $ratio }}

        </div>

    </div>

</div>

{{-- Average Seedtime --}}

{{-- @php

$avgHours = floor($avgSeedTime / 3600);

@endphp

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Avg Seedtime</div>

        <div class="stat-value">

            {{ $avgHours }}h

        </div>

    </div>

</div> --}}

{{-- Seeding Size --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Seeding Size</div>

        <div class="stat-value">

            {{ \App\Helpers\FormatHelper::formatSize($totalSeedSize) }}

        </div>

    </div>

</div>

{{-- Seeding Health --}}

@php

$healthColor = 'bg-danger';

if ($seedingHealth >= 80) $healthColor = 'bg-success';

elseif ($seedingHealth >= 50) $healthColor = 'bg-warning';

@endphp

<div class="col-6 col-md-3 col-xl-3 d-flex" data-bs-toggle="tooltip"

title="Seeding Health shows how consistently you seed torrents. Each torrent reaches 100% after 12 hours of seeding.">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Seeding Health</div>

        <div class="progress elite-progress w-100 mt-2" style="height:8px;">

            <div class="progress-bar {{ $healthColor }}"

                 role="progressbar"

                 style="width: {{ $seedingHealth }}%">

            </div>

        </div>

        <div class="stat-value mt-1">

            {{ $seedingHealth }}%

        </div>

    </div>

</div>

{{-- Seeder Reputation + Rank Progress --}}

<div class="col-6 col-md-3 col-xl-3 d-flex"

     data-bs-toggle="tooltip"

     title="Seeder Reputation measures your overall contribution. It increases based on seedtime and torrent size. Larger torrents and longer seed times increase your reputation.">

    <div class="elite-stat-compact stat-card w-100">

        <div class="stat-label">Seeder Reputation</div>

        <div class="stat-value d-flex align-items-center gap-2">

            <span class="ms-1">

                {{ $user->seeder_icon }} {{ $user->seeder_rank_name }} with

               {{ number_format($user->seeding_reputation, 2) }} pts

            </span>

        </div>

        {{-- Progress bar --}}

       @php

$progress = $user->seeder_rank_progress ?? 0;

@endphp

<div class="progress elite-progress mt-2 w-100" style="height:6px;">

    <div class="progress-bar bg-success"

         role="progressbar"

         style="width: {{ $progress }}%;">

    </div>

</div>

        <div class="small text-muted mt-1">

            {{ $user->seeder_rank_progress }}%

            @if($user->next_seeder_rank)

                • Next:

                {{ $user->next_seeder_rank['icon'] }}

                {{ $user->next_seeder_rank['name'] }}

            @else

                • Max Rank 👑

            @endif

        </div>

    </div>

</div>

{{-- Seedtime --}}

{{-- <div class="col-6 col-md-3 col-xl-3 d-flex" data-bs-toggle="tooltip" title="Total time you have spent seeding torrents. This includes all torrents you have completed and continued to seed.">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Seedtime</div>

        <div class="stat-value">

            {{ $years }}y {{ $months }}m {{ $days }}d {{ $hours }}h {{ $minutes }}m

        </div>

    </div>

</div> --}}

{{-- Seedbonus --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Seedbonus</div>

        <div class="stat-value">

                <a href="{{ route('shop') }}" class="fancy-link">

                    {{ $user->seedbonus }}

                </a>

        </div>

    </div>

</div>

{{-- Invites --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Invites</div>

        <div class="stat-value">

            @if (auth()->id() === $user->id)

                <a class="fancy-link" href="{{ route('invites.index') }}">

                    {{ Auth::user()->invites }}

                </a>

            @else

                {{ $user->invites }}

            @endif

        </div>

    </div>

</div>

{{-- Slots --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">Slots</div>

        <div class="stat-value">

            @if (auth()->id() === $user->id || (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))

                <a href="{{ route('profile.tokens', ['id' => $user->id, 'name' => $user->name]) }}" class="fancy-link">

                    {{ $user->slots }}

                </a>

            @else

                {{ $user->slots }}

            @endif

        </div>

    </div>

</div>







{{-- Comments --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label"><i class="bi bi-chat-dots me-1"></i>Comments</div>

        <div class="stat-value">

           <a href="{{ route('profile.comments', ['id'=>$user->id,'name'=>$user->name]) }}" class="fancy-link">

    {{ number_format($commentCount) }}

</a>

        </div>

    </div>

</div>

{{-- Thanks --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label"><i class="bi bi-heart me-1"></i>Thanks</div>

        <div class="stat-value">

           <a href="{{ route('profile.thanks', ['id'=>$user->id,'name'=>$user->name]) }}" class="fancy-link">

    {{ number_format($thanksCount) }}

</a>

        </div>

    </div>

</div>

{{-- Forum Posts --}}

<div class="col-6 col-md-3 col-xl-3 d-flex">

    <div class="elite-stat-compact stat-card">

        <div class="stat-label">

            <i class="bi bi-chat-square-text me-1"></i>

            Forum Posts

        </div>

        <div class="stat-value">

            <a href="{{ route('profile.posts', ['id'=>$user->id,'name'=>$user->name]) }}"

       class="fancy-link">

        {{ number_format($forumPostCount) }}

    </a>

</div>

    </div>

</div>

</div>

</div>

<div class="container-fluid py-4">

 @if ($isOwner || $isModerator)

         <div class="row g-3 mb-4">

            <div class="col-md-3">

                <div class="elite-stat-compact">

                    <strong class="text-success fancy-link"><i class="bi bi-cloud-plus fs-5"></i>

                        <a href="{{ route('profile.torrents', ['id' => $user->id, 'name' => $user->name]) }}">

                                Uploads:  {{ $user->torrents()->count() }}

                         </a>

                    </strong>

                </div>

            </div>

            <div class="col-md-3">

                <div class="elite-stat-compact">

                    <strong class="text-info fancy-link"><i class="bi bi-exclamation-octagon me-1 fs-5"></i>                        

                    <a href="{{ route('warnings.show', ['id' => $user->id, 'username' => $user->username]) }}">

                                             Warnings {{ $user->warnings()->whereNull('expires_on')->count() }}

                                        </a>

                    </strong>

                </div>

            </div>

            <div class="col-md-3">

                <div class="elite-stat-compact">

                    <strong class="text-danger fancy-link"><i class="bi bi-exclamation-triangle me-1 fs-5"></i>

                    <a href="{{ route('snatch.hitAndRun', ['userId' => $user->id]) }}">

                                             H&R ({{$user->hit_and_run_count}})

                                        </a>

                                        </strong>

                </div>

            </div>

            <div class="col-md-3">

                <div class="elite-stat-compact">

                    <strong class="text-info fancy-link">

                    <i class="bi bi-list-check me-1 fs-5"></i>

                     <a href="{{ route('snatch.snatchlist', ['userId' => $user->id]) }}">

                         Snatchlist

                    </a>

                    </strong>

                </div>

            </div>

        </div>

        @endif

</div>



     <div class="container py-4">





      @php

    $canSeeTimeline = Auth::check() &&

        (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR

        || Auth::user()->id === $user->id);

    $hasAbout = !empty($user->info);

    $hasTimeline = $canSeeTimeline && $user->timeline->isNotEmpty();

    $bothVisible = $hasAbout && $hasTimeline;

    $columnClass = $bothVisible ? 'col-md-6' : 'col-md-12';

@endphp

@if($hasAbout || $canSeeTimeline)

<div class="row">

    {{-- =======================

         ABOUT SECTION

    ======================== --}}

    @if($hasAbout)

        <div id="profile-about" class="{{ $columnClass }}">

            <div class="elite-card p-4 mb-4">

                <h5 class="mb-3">About</h5>

                <div class="about-scroll">

                    {!! convertCustomTagsToHtml($user->info) !!}

                </div>

            </div>

        </div>

    @endif



    {{-- =======================

         TIMELINE SECTION

    ======================== --}}

   @if($hasTimeline)

        <div id="profile-timeline" class="{{ $columnClass }}">

            <div class="elite-card p-4 mb-4">

                <div class="d-flex align-items-center mb-3 flex-wrap">

    <h5 class="mb-0 fw-semibold d-flex align-items-center me-3">

        <i class="bi bi-activity me-2 text-primary"></i>

        Timeline

    </h5>

   <span id="timelineCount" class="badge bg-primary me-auto px-3 py-2">

    {{ $user->timeline->count() }}

</span>

    {{-- FILTER BUTTONS --}}

    <div class="timeline-filters">

        <button class="btn btn-sm btn-outline-light active"

                onclick="filterTimeline('all', this)">

            All

        </button>

        <button class="btn btn-sm btn-outline-warning"

                onclick="filterTimeline('moderation', this)">

            Moderation

        </button>

        <button class="btn btn-sm btn-outline-info"

                onclick="filterTimeline('upload', this)">

            Uploads

        </button>

        <button class="btn btn-sm btn-outline-success"

                onclick="filterTimeline('stats', this)">

            Stats

        </button>

    </div>

</div>

                @if ($user->timeline->isEmpty())

                    <div class="elite-empty-state text-center py-4">

                        <i class="bi bi-clock-history display-6 text-muted mb-2"></i>

                        <p class="text-muted mb-0">

                            No timeline entries yet.

                        </p>

                    </div>

                @else

                    <div class="elite-timeline-scroll">

                       @foreach($timeline as $entry)

                          @php

$type = 'general';

$text = strtolower($entry->comment);

if(str_contains($text,'chat') ||

   str_contains($text,'forum') ||

   str_contains($text,'comment') ||

   str_contains($text,'warn') ||

   str_contains($text,'vip')) {

    $type = 'moderation';

}

elseif(str_contains($text,'upload') || str_contains($text,'torrent')) {

    $type = 'upload';

}

elseif(str_contains($text,'download') ||

       str_contains($text,'ratio') ||

       str_contains($text,'seedbonus') ||

       str_contains($text,'stats')) {

    $type = 'stats';

}

@endphp

<div class="elite-timeline-item timeline-item"

     data-type="{{ $type }}">

                                <div class="elite-timeline-dot"></div>

                                <div class="elite-timeline-content">

                                    <div class="elite-timeline-header d-flex justify-content-between align-items-start">

                                        @php

$comment = $entry->comment;

$type = 'default';

if(str_contains($comment,'Chat access') || str_contains($comment,'chat')) {

    $type = 'chat';

}

elseif(str_contains($comment,'Comments')) {

    $type = 'comment';

}

elseif(str_contains($comment,'Forum')) {

    $type = 'forum';

}

elseif(str_contains($comment,'Warn')) {

    $type = 'warning';

}

elseif(str_contains($comment,'VIP')) {

    $type = 'vip';

}

@endphp

<div class="elite-timeline-comment elite-timeline-{{ $type }}">

    {!! convertCustomTagsToHtml($comment) !!}

</div>

                                        @if ($entry->staff)

                                            <span class="badge bg-dark ms-3">

                                                {{ $entry->staff->name }}

                                            </span>

                                        @endif

                                    </div>

                                    <div class="elite-timeline-date">

                                        {{ $entry->created_at->format('d M Y • H:i') }}

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>

    @endif

</div>

@endif



        </div>

        <style>
/* =========================================================
   FILEIPLAY PREMIUM PROFILE
   ========================================================= */

:root {
    --fi-bg: #071019;
    --fi-panel: rgba(13, 27, 40, .94);
    --fi-panel-2: rgba(9, 21, 33, .97);
    --fi-border: rgba(126, 158, 180, .18);
    --fi-border-hover: rgba(45, 212, 191, .48);
    --fi-text: #edf6fb;
    --fi-muted: #9aafbd;
    --fi-accent: #18d5f4;
    --fi-accent-2: #2dd4bf;
}

html,
body {
    min-height: 100%;
    margin: 0;
    background: var(--fi-bg);
    color: var(--fi-text);
}

body {
    font-size: .875rem;
}

/* Page background */
html::before {
    opacity: .22;
    background-image:
        linear-gradient(to bottom, rgba(3, 9, 16, .78), rgba(3, 9, 16, .98)),
        url('{{ $profileBackground }}');
    background-position: center top;
    background-size: cover;
    background-attachment: fixed;
}

html::after {
    background: linear-gradient(
        to bottom,
        rgba(3, 9, 16, .08) 0%,
        rgba(3, 9, 16, .38) 38%,
        rgba(3, 9, 16, .88) 72%,
        rgba(3, 9, 16, 1) 100%
    );
}

/* Shared cards */
.elite-card,
.elite-stat-compact,
.rank-box {
    border: 1px solid var(--fi-border);
    background: linear-gradient(
        145deg,
        rgba(17, 31, 46, .96),
        rgba(8, 18, 29, .96)
    );
    box-shadow:
        0 8px 24px rgba(0, 0, 0, .24),
        inset 0 1px 0 rgba(255, 255, 255, .025);
}

.elite-card {
    border-radius: .75rem;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.elite-card:hover,
.elite-stat-compact:hover,
.rank-box:hover {
    border-color: var(--fi-border-hover);
}

/* =========================================================
   CINEMATIC PROFILE HERO
   ========================================================= */

.profile-hero {
    position: relative;
    min-height: 545px;

    overflow: visible;

    border: 1px solid rgba(91, 126, 148, .18);
    border-radius: 0;

    background-color: #08121b;
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;

    box-shadow:
        0 18px 45px rgba(0, 0, 0, .38);
}

.profile-hero-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;

    background:
        linear-gradient(
            to bottom,
            rgba(3, 10, 17, .24) 0%,
            rgba(3, 10, 17, .18) 35%,
            rgba(3, 10, 17, .34) 53%,
            rgba(3, 10, 17, .73) 73%,
            rgba(3, 10, 17, .97) 100%
        ),
        linear-gradient(
            90deg,
            rgba(3, 10, 17, .58) 0%,
            rgba(3, 10, 17, .18) 43%,
            rgba(3, 10, 17, .08) 100%
        );
}

.profile-hero-inner {
    position: relative;
    z-index: 2;
    min-height: 545px;
    padding: 0 38px 38px;
}

.profile-hero-content {
    position: absolute;
    left: 38px;
    right: 38px;
    bottom: 38px;

    display: grid;
    grid-template-columns: 205px minmax(0, 1fr) auto;

    align-items: end;
    gap: 26px;
}

/* =========================================================
   AVATAR
   ========================================================= */

.profile-avatar-wrap {
    position: relative;
    width: 205px;
    height: 205px;
    align-self: end;
}

.profile-avatar {
    width: 205px;
    height: 205px;

    display: block;

    object-fit: cover;
    object-position: center;

    border-radius: 50%;

    border: 4px solid rgba(245, 250, 253, .94);

    background: #08131c;

    box-shadow:
        0 12px 35px rgba(0, 0, 0, .72),
        0 0 0 5px rgba(9, 20, 30, .65);
}

.profile-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 4.2rem;
    color: #72879a;
}

.profile-online-indicator {
    position: absolute;

    right: 5px;
    bottom: 9px;

    width: 30px;
    height: 30px;

    border-radius: 50%;

    background: #43d46d;

    border: 4px solid #08131c;

    box-shadow: 0 0 14px rgba(67, 212, 109, .7);
}

/* =========================================================
   IDENTITY
   ========================================================= */

.profile-identity {
    min-width: 0;
    padding-bottom: 4px;
}

.profile-name-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 10px;
}

.profile-name {
    color: #ffffff;

    font-size: clamp(2rem, 3vw, 2.55rem);
    font-weight: 800;

    line-height: 1.05;
    letter-spacing: -.8px;

    text-shadow: 0 3px 16px rgba(0, 0, 0, .72);
}

.profile-seeder-icon {
    display: inline-block;
    margin-left: 5px;
    font-size: 1.25rem;
}

.profile-role-badge,
.profile-small-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    min-height: 32px;
    padding: 5px 10px;

    border-radius: 999px;

    font-size: .78rem;
    font-weight: 700;

    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.profile-role-badge {
    color: #edf5fa;

    border: 1px solid rgba(203, 213, 225, .30);

    background: rgba(15, 30, 43, .88);
}

.profile-small-badge.donor {
    color: #86efac;
    border: 1px solid rgba(74, 222, 128, .28);
    background: rgba(22, 101, 52, .34);
}

.profile-small-badge.vip {
    color: #fcd34d;
    border: 1px solid rgba(251, 191, 36, .30);
    background: rgba(120, 83, 12, .28);
}

.profile-small-badge.warned {
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, .28);
    background: rgba(120, 53, 15, .28);
}

.profile-tagline {
    margin-top: 13px;

    max-width: 760px;

    color: #f1f5f9;

    font-size: 1rem;
    line-height: 1.5;

    text-shadow: 0 2px 10px rgba(0, 0, 0, .75);
}

/* =========================================================
   META ROW
   ========================================================= */

.profile-meta-row {
    position: relative;

    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 24px;

    margin-top: 20px;

    color: #edf5f8;
}

.profile-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    color: #eef5f8;

    font-size: .88rem;
    font-weight: 500;

    white-space: nowrap;

    text-shadow: 0 2px 8px rgba(0, 0, 0, .7);
}

.profile-meta-item > i {
    color: #f2f8fb;
    font-size: 1rem;
}

.profile-meta-item.online {
    color: #7cf29a;
}

.profile-online-dot {
    width: 9px;
    height: 9px;

    border-radius: 50%;

    background: #48dc72;

    box-shadow: 0 0 10px rgba(72, 220, 114, .75);
}

/* =========================================================
   DETAILS HOVER CARD
   ========================================================= */

/* Bootstrap 5 username profile tooltip */
.profile-username-tooltip {
    cursor: help;
    text-decoration: none;
    text-decoration-thickness: 1px;
    text-underline-offset: 5px;
}

.profile-username-tooltip:hover {
    text-decoration: underline;
    text-decoration-color: rgba(103, 232, 249, 0.55);
}

.profile-tooltip-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 2px 0 12px;
    margin-bottom: 3px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-tooltip-header strong {
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
}

.profile-tooltip-header span {
    padding: 3px 9px;
    border: 1px solid rgba(103, 232, 249, 0.25);
    border-radius: 999px;
    color: #67e8f9;
    font-size: 0.72rem;
    font-weight: 600;
}

.profile-tooltip-divider {
    height: 1px;
    margin: 8px 0;
    background: rgba(255, 255, 255, 0.1);
}

.profile-tooltip {
    --bs-tooltip-bg: transparent;
    --bs-tooltip-opacity: 1;
    max-width: none !important;
}

.profile-tooltip .tooltip-inner {
    width: 590px;
    max-width: min(590px, calc(100vw - 30px));
    padding: 14px 16px;
    text-align: left;
    color: #e8f3f7;
    background: linear-gradient(145deg, rgba(9, 24, 36, 0.99), rgba(5, 16, 26, 0.995));
    border: 1px solid rgba(94, 234, 212, 0.28);
    border-radius: 12px;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.5);
}

.profile-tooltip .tooltip-arrow::before {
    border-top-color: #091824;
}

.profile-tooltip-row {
    display: grid;
    grid-template-columns: 22px minmax(100px, 1fr) auto;
    align-items: center;
    gap: 10px;
    min-height: 38px;
    padding: 7px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.profile-tooltip-row:last-child {
    border-bottom: 0;
}

.profile-tooltip-row > i {
    color: #67e8f9;
    font-size: 0.95rem;
    text-align: center;
}

.profile-tooltip-row > span {
    color: rgba(218, 234, 240, 0.72);
    font-size: 0.86rem;
}

.profile-tooltip-row > strong {
    color: #fff;
    font-size: 0.88rem;
    font-weight: 600;
    text-align: right;
    overflow-wrap: anywhere;
}

@media (max-width: 767.98px) {
    .profile-tooltip .tooltip-inner {
        width: min(590px, calc(100vw - 24px));
    }

    .profile-tooltip-row {
        grid-template-columns: 20px 1fr;
    }

    .profile-tooltip-row > strong {
        grid-column: 2;
        text-align: left;
        margin-top: -5px;
    }
}

.profile-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;

    gap: 9px;

    padding-bottom: 7px;
}

.profile-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    min-height: 48px;
    padding: 0 19px;

    border-radius: 9px;

    font-size: .9rem;
    font-weight: 700;

    text-decoration: none;

    transition:
        transform .18s ease,
        background .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;

    cursor: pointer;
}

.profile-action-btn.primary {
    color: #18d9f5;

    border: 1px solid rgba(24, 213, 244, .82);

    background: rgba(4, 24, 34, .72);

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .28);
}

.profile-action-btn.primary:hover {
    color: #a9f8ff;

    background: rgba(8, 48, 61, .90);

    border-color: #36e5fb;

    box-shadow:
        0 0 18px rgba(24, 213, 244, .12);

    transform: translateY(-1px);
}

.profile-action-btn.warning {
    color: #fcd34d;
    border: 1px solid rgba(251, 191, 36, .55);
    background: rgba(50, 35, 8, .72);
}

.profile-action-btn.danger {
    color: #fca5a5;
    border: 1px solid rgba(248, 113, 113, .42);
    background: rgba(60, 12, 18, .65);
}

/* =========================================================
   STATS
   ========================================================= */

.elite-header-stats {
    margin-top: 30px;
    padding-top: 0;
}

.elite-stat-compact {
    width: 100%;
    min-height: 82px;

    padding: .75rem .7rem;

    border-radius: .65rem;

    text-align: center;

    transition:
        transform .16s ease,
        border-color .16s ease;
}

.elite-stats-grid .stat-card {
    width: 100%;
    height: 100%;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stat-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .055em;
    color: #8195a4;
}

.stat-value {
    margin-top: .35rem;

    color: #edf5fb;

    font-size: 1.02rem;
    font-weight: 700;
    line-height: 1.25;
}

.fancy-link {
    position: relative;
    color: inherit;
    text-decoration: none;
}

.fancy-link:hover {
    color: #8ff5e5;
}

/* =========================================================
   PASSKEY
   ========================================================= */

.passkey-card {
    border-color: rgba(251, 191, 36, .18);

    background:
        linear-gradient(
            145deg,
            rgba(40, 34, 20, .55),
            rgba(10, 20, 31, .96)
        );
}

.passkey-text {
    display: inline-block;

    max-width: 100%;

    overflow: hidden;
    text-overflow: ellipsis;

    font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
    letter-spacing: 1px;
    font-size: .78rem;

    color: #cbd5e1;

    background: rgba(0, 0, 0, .25);

    border: 1px solid rgba(148, 163, 184, .10);

    padding: .4rem .55rem;

    border-radius: .45rem;
}

/* =========================================================
   SEEDER RANK
   ========================================================= */

#seederRankAccordion {
    margin-top: 18px !important;
}

#seederRankAccordion .accordion-item {
    overflow: hidden;
}

#seederRankAccordion .accordion-button {
    min-height: 46px;
    padding: .7rem .9rem;

    background: rgba(8, 15, 29, .78) !important;

    color: #dbe7f2 !important;

    font-size: .84rem;
    font-weight: 700;
}

#seederRankAccordion .accordion-button:not(.collapsed) {
    color: #8ff5e5 !important;
    box-shadow: inset 3px 0 0 var(--fi-accent-2);
}

#seederRankAccordion .accordion-button::after {
    filter: invert(1);
    opacity: .65;
}

.rank-box {
    height: 100%;
    padding: .85rem;

    border-radius: .6rem;

    background: rgba(8, 15, 29, .72);

    text-align: center;

    transition: all .16s ease;
}

.rank-icon { font-size: 1.45rem; }

.rank-title {
    margin-top: .25rem;
    color: #e5edf5;
    font-size: .86rem;
    font-weight: 700;
}

.rank-desc {
    margin-top: .3rem;
    color: #8492a6;
    font-size: .72rem;
    line-height: 1.45;
}

.rank-score {
    margin-top: .45rem;
    color: #5eead4;
    font-size: .7rem;
    font-weight: 600;
}

/* =========================================================
   ABOUT / TIMELINE
   ========================================================= */

.about-scroll,
.elite-timeline-scroll {
    max-height: 550px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 7px;
}

.about-scroll::-webkit-scrollbar,
.elite-timeline-scroll::-webkit-scrollbar {
    width: 6px;
}

.about-scroll::-webkit-scrollbar-thumb,
.elite-timeline-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, .25);
    border-radius: 999px;
}

.timeline-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-left: auto;
}

.timeline-filters button {
    margin-left: 0;
    padding: .28rem .52rem;
    border-radius: .42rem;
    font-size: .7rem;
}

.timeline-filters .active {
    border-color: rgba(45, 212, 191, .45);
    background: rgba(20, 184, 166, .13);
    color: #8ff5e5;
}

.elite-timeline-item {
    position: relative;
    padding-left: 27px;
    margin-bottom: 18px;
}

.elite-timeline-item::before {
    left: 8px;
    bottom: -18px;
    width: 1px;
    background: rgba(148, 163, 184, .17);
}

.elite-timeline-dot {
    left: 3px;
    top: 7px;
    width: 12px;
    height: 12px;
    background: #2dd4bf;
    box-shadow: 0 0 9px rgba(45, 212, 191, .38);
}

.elite-timeline-content {
    padding: .72rem .85rem;

    border: 1px solid rgba(148, 163, 184, .13);
    border-radius: .6rem;

    background: rgba(8, 15, 29, .78);
}

.elite-timeline-content:hover {
    border-color: rgba(45, 212, 191, .20);
    background: rgba(14, 25, 42, .90);
}

.elite-timeline-comment {
    font-size: .8rem;
    line-height: 1.5;
}

.elite-timeline-date {
    margin-top: .4rem;
    color: #718096;
    font-size: .68rem;
}

.elite-empty-state {
    padding: 1.5rem !important;

    border: 1px dashed rgba(148, 163, 184, .14);
    border-radius: .55rem;

    background: rgba(8, 15, 29, .45);
}

.elite-timeline-chat { color: #67e8f9; }
.elite-timeline-comment { color: #a5b4c7; }
.elite-timeline-forum { color: #fda4af; }
.elite-timeline-warning { color: #fcd34d; font-weight: 600; }
.elite-timeline-vip { color: #fcd34d; font-weight: 600; }

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1199.98px) {
    .profile-hero-content {
        grid-template-columns: 165px minmax(0, 1fr);
        gap: 20px;
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 165px;
        height: 165px;
    }

    .profile-actions {
        grid-column: 2;
        justify-content: flex-start;
        padding-bottom: 0;
    }
}

@media (max-width: 991.98px) {
    .profile-hero {
        min-height: 610px;
    }

    .profile-hero-inner {
        min-height: 610px;
        padding: 0 20px 28px;
    }

    .profile-hero-content {
        left: 20px;
        right: 20px;
        bottom: 28px;

        display: flex;
        flex-direction: column;

        align-items: center;

        text-align: center;

        gap: 14px;
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 140px;
        height: 140px;
    }

    .profile-online-indicator {
        right: 1px;
        bottom: 3px;
    }

    .profile-identity {
        width: 100%;
        padding-bottom: 0;
    }

    .profile-name-row {
        justify-content: center;
    }

    .profile-tagline {
        margin-left: auto;
        margin-right: auto;
    }

    .profile-meta-row {
        justify-content: center;
        gap: 13px;
    }

    .profile-actions {
        width: 100%;
        justify-content: center;
    }

    .profile-navigation {
        margin: 0 20px;
    }

    .profile-nav-inner {
        overflow-x: auto;
    }

    .profile-nav-item {
        min-width: 150px;
        flex: 0 0 auto;
    }
}

@media (max-width: 575.98px) {
    .profile-hero {
        min-height: 590px;

        border-radius: 0;
    }

    .profile-hero-inner {
        min-height: 590px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .profile-hero-content {
        left: 10px;
        right: 10px;
        bottom: 22px;
    }

    .profile-avatar-wrap,
    .profile-avatar {
        width: 112px;
        height: 112px;
    }

    .profile-avatar {
        border-width: 3px;
    }

    .profile-name {
        font-size: 1.65rem;
    }

    .profile-role-badge,
    .profile-small-badge {
        min-height: 28px;
        padding: 4px 8px;
        font-size: .7rem;
    }

    .profile-tagline {
        font-size: .88rem;
    }

    .profile-meta-row {
        flex-direction: column;
        gap: 8px;
        margin-top: 14px;
    }

    .profile-meta-item {
        font-size: .78rem;
    }

    .profile-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .profile-action-btn {
        width: 100%;
        min-height: 42px;
        padding: 0 9px;
        font-size: .76rem;
    }

    .profile-detail-row {
        grid-template-columns: 20px 105px minmax(0, 1fr);
        font-size: .76rem;
    }

    .profile-navigation {
        margin-left: 10px;
        margin-right: 10px;
    }

    .profile-nav-inner {
        min-height: 64px;
    }

    .profile-nav-item {
        min-width: 120px;
        padding: 0 14px;
        font-size: .8rem;
    }

    .profile-nav-item i {
        font-size: 1rem;
    }

    .elite-header-stats {
        margin-top: 20px;
    }

    .elite-stat-compact {
        min-height: 76px;
    }

    .stat-value {
        font-size: .9rem;
    }

    .timeline-filters {
        width: 100%;
        margin-top: .65rem;
        justify-content: flex-start;
    }

    .passkey-card .btn {
        width: 100%;
    }
}
</style>










<script>

function togglePasskey() {

    const el = document.getElementById("passkeyDisplay");

    const full = el.dataset.full;

    const current = el.innerText;

    const masked = "*".repeat(full.length - 3) + full.slice(-3);

    if (current === masked) {

        el.innerText = full;

    } else {

        el.innerText = masked;

    }

}

</script>

<script>

function filterTimeline(type, btn)

{

    const items = document.querySelectorAll('.timeline-item');

    let visible = 0;

    items.forEach(item => {

        if(type === 'all')

        {

            item.style.display = '';

            visible++;

        }

        else

        {

            if(item.dataset.type === type)

            {

                item.style.display = '';

                visible++;

            }

            else

            {

                item.style.display = 'none';

            }

        }

    });

    document.querySelectorAll('.timeline-filters button')

        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');

    document.getElementById('timelineCount').textContent = visible;

}

</script>

@endif



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        bootstrap.Tooltip.getOrCreateInstance(element, {
            html: true,
            trigger: 'hover focus',
            container: 'body'
        });
    });
});
</script>
@endpush
<style>
    .sub-torrent-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: .6rem;
        color: #e2e8f0;
        text-decoration: none;
        transition: background .15s ease;
    }

    .sub-torrent-row:hover {
        background: rgba(77, 163, 255, .12);
        color: #ffffff;
    }

    .sub-torrent-row + .sub-torrent-row {
        margin-top: 6px;
    }

    .subscribed-poster {
        width: 40px;
        height: 56px;
        object-fit: cover;
        border-radius: .35rem;
        flex-shrink: 0;
        background: #1e293b;
    }

    .subscribed-info {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .subscribed-name {
        font-size: 13.5px;
        font-weight: 600;
        line-height: 1.25;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .subscribed-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        font-size: 12px;
        color: #94a3b8;
    }

    .subscribed-meta span {
        white-space: nowrap;
    }

    .subscribed-empty {
        padding: 22px 12px;
        text-align: center;
        font-size: 13px;
        color: #94a3b8;
    }
</style>

@endsection