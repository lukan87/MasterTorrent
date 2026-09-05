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






{{-- COVER HEADER --}}
<div class="elite-cover mt-3"
     @if($coverImage)
        style="background-image: url('{{ $coverImage }}');"
     @endif>

    <div class="container-fluid">

        <div class="elite-header">

            {{-- AVATAR --}}
            <div class="elite-avatar mb-3 mb-lg-0">
                @if($user->profile_image)
                    <img src="{{ $user->profile_image }}">
                @else
                    <div class="elite-avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif
            </div>

            {{-- USER INFO --}}
            <div class="ms-lg-4 text-white text-center text-lg-start flex-grow-1">

      <h2 class="elite-username mb-2 d-flex flex-wrap align-items-center gap-2">

    {{-- Username + Rank --}}
    <span
        class="d-flex align-items-center gap-2"
        data-bs-toggle="tooltip"
        title="Seeder Rank: {{ $user->seeder_rank_name }}"
        style="color: {{ \App\Models\UserClass::getClassColor($user->user_class) }};"
    >
        <span class="username-text">{{ $user->name }}</span>

        <span class="seeder-rank-icon">
            {{ $user->seeder_icon }}
        </span>
    </span>

    {{-- Role --}}
    <span class="badge bg-role fs-6">
        {{ $user->role_name }}
    </span>

    {{-- Warned --}}
    @if($user->warned)
        <span
            class="badge bg-warning fs-6"
            data-bs-toggle="tooltip"
            title="Warned until {{ \Carbon\Carbon::parse($user->warned_until)->format('d F Y, H:i') }}"
        >
            ⚠ Warned
        </span>
    @endif

    {{-- Donor --}}
    @if($user->donor == 'yes')
        <span class="badge bg-success fs-6">
            💚 Donor
        </span>
    @endif

    {{-- VIP --}}
    @if ($user->vip_until)

        <span
            class="elite-vip-inline"
            data-bs-toggle="tooltip"
            title="VIP expires {{ \Carbon\Carbon::parse($user->vip_until)->format('d F Y, H:i') }}"
        >

            <i class="bi bi-gem"></i>

            <span class="elite-vip-label">VIP</span>

        </span>

    @endif

</h2>

                {{-- META BADGES --}}
                <div class="elite-meta">

    <div class="elite-email-card">
        <i class="bi bi-calendar-check fs-5 me-2"></i>
        <span>Member since {{ $user->created_at->format('M Y') }}</span>
    </div>
    <div class="elite-email-card">
        <i class="bi bi-clock-history fs-5 me-2"></i>
        <span>
             Last Seen {{ $user->last_activity ? $user->last_activity->format('M Y') : 'Never' }}
            </span>
    </div>

    @if(method_exists($user,'isOnline') && $user->isOnline())
        <div class="elite-meta-item elite-meta-online">
            <span class="elite-online-dot fs-5 me-2"></span>
            <span>Online</span>
        </div>
    @endif

@if ($isAdmin || $isOwner)

    <div class="elite-email-card">
        <i class="bi bi-globe fs-5 me-2"></i>
        <span>{{ $user->IP }}</span>
    </div>

    <div class="elite-email-card" 
     data-bs-toggle="tooltip" 
     title="{{ $user->subscribed ? 'Subscribed to receive emails' : 'Not subscribed to receive emails' }}">

    <div class="elite-email-left">
        <i class="bi bi-envelope me-2"></i>
        <span class="elite-email-text">{{ $user->email }}</span>
    </div>

    <div class="elite-email-status">
        <i class="ms-2 bi {{ $user->subscribed ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
    </div>

</div>

<div class="elite-email-card">
    <i class="bi bi-person-check fs-5 me-2"></i>

    @if($user->invited_by)
        <span>
            Invited by

            @if($user->inviter?->id)
                <a href="{{ route('profile.show', ['id' => $user->inviter->id, 'username' => $user->inviter->username]) }}"
                   class="text-decoration-none fw-semibold">
                    {{ $user->inviter?->name ?? 'Unknown' }}
                </a>
            @else
                <span class="text-muted">Unknown</span>
            @endif
        </span>
    @else
        <span>Joined independently</span>
    @endif
</div>



@endif

</div>


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

            <small class="text-warning">
                ⚠ Never share this — it identifies you to the tracker.
            </small>

            <small class="text-warning mt-1">
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


            </div>

            {{-- ACTION BUTTONS --}}
            <div class="elite-actions">
                {{-- Message --}}
                @if (Auth::check() && Auth::id() !== $user->id)
                    <a href="{{ route('messages.create',['receiver_id'=>$user->id]) }}"
                       class="btn btn-elite">
                        <i class="bi bi-envelope me-1"></i> Message
                    </a>
                @endif

                {{-- Edit --}}
                @if ($isOwner || $isModerator)

                    <a href="{{ route('profile.edit', ['id'=>$user->id,'name'=>$user->name]) }}"
                       class="btn btn-outline-light elite-outline-btn">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>
                @endif

                {{-- Admin Edit (not self) --}}
                @if ($isModerator && ! $isOwner)
                    <a href="{{ route('admin.users.edit', $user->id) }}"
                       class="btn btn-outline-warning elite-outline-btn">
                        <i class="bi bi-shield-lock me-1"></i> Admin Edit
                    </a>
                @endif

{{-- Delete Account (Only Self) --}}
@if ($isOwner)

    <button class="btn elite-delete-btn"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#deleteAccountBox"
            aria-expanded="{{ $errors->has('password') ? 'true' : 'false' }}">
        <i class="bi bi-trash me-1"></i> Delete Account
    </button>

    <div class="collapse mt-3 @if($errors->has('password')) show @endif"
         id="deleteAccountBox">

        <div class="elite-card p-4 border border-danger">

            <div class="alert alert-danger mb-3">
                <strong>Warning:</strong> This action is permanent and cannot be undone.
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






            </div>

        </div>

    </div>

    {{-- HEADER STATS --}}
<div class="container-fluid elite-header-stats mt-4">

    

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
        <div class="{{ $columnClass }}">
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
        <div class="{{ $columnClass }}">

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
  body {
    background: #0d1117;
    color: #e6edf3;
}

.elite-email-card {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 10px 14px;
    border-radius: 12px;

    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);

    backdrop-filter: blur(8px);
    transition: all 0.25s ease;
}

.elite-email-card:hover {
    background: rgba(255,255,255,0.06);
    transform: translateY(-1px);
}

/* Left side */
.elite-email-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.elite-email-left i {
    font-size: 1.1rem;
    color: #9ca3af;
}

/* Email text */
.elite-email-text {
    font-size: 0.85rem;
    color: #d1d5db;
    word-break: break-all;
}

/* Status icon */
.elite-email-status i {
    font-size: 1rem;
}

.elite-username {
    font-weight: 600;
    font-size: 1.8rem;
}

.elite-meta {
    font-size: 0.85rem;
    gap: 14px;
    margin-top: 6px;
}

.elite-avatar {
    width: 130px;
    max-width: 100%;
}

.elite-avatar img,
.elite-avatar-placeholder {
    width: 100%;
    height: auto;
    border-radius: 16px; /* softer modern look */
    object-fit: contain; /* IMPORTANT - no cropping */
    background: #161b22;
}

.elite-avatar img {
    border: 4px solid #0d1117;
    box-shadow: 0 10px 25px rgba(0,0,0,0.6);
}

/* Placeholder */
.elite-avatar-placeholder {
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    border-radius: 16px;
}


.elite-stat {
    background: linear-gradient(145deg, #161b22, #1c2128);
    padding: 22px;
    border-radius: 16px;
    border: 1px solid #30363d;
    transition: 0.25s ease;
}

.elite-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
}

.elite-stat span {
    font-size: 0.85rem;
    color: #8b949e;
}

.elite-stat h4 {
    font-weight: 600;
    margin-top: 6px;
}


.elite-mini {
    background: #161b22;
    padding: 16px;
    border-radius: 12px;
    border: 1px solid #30363d;
    transition: 0.2s ease;
}

.elite-mini:hover {
    background: #1c2128;
    transform: translateY(-3px);
}




.btn-elite {
    background: #238636;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 6px;
    font-weight: 500;
}


.btn-elite:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(35,134,54,0.4);
}


.elite-activity {
    padding:12px 0;
    border-bottom:1px solid #30363d;
}

.elite-progress {
    background:#30363d;
}

.fancy-link {
    position: relative;
    text-decoration: none;
    color: inherit;
    
}

.fancy-link::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -3px;
    width: 0%;
    height: 2px;
    background: linear-gradient(to right, grey, #0d6efd);
    transition: width 0.25s ease;

}

.fancy-link:hover::after {
      width: 100%;
}


/* BUttons */

.elite-outline-btn {
    border-radius: 10px;
    padding: 8px 18px;
    transition: 0.2s ease;
}

.elite-outline-btn:hover {
    transform: translateY(-2px);
}
/* Buttons */





/* Timeline Scroll */

.elite-timeline-comment:has(span.seedbonus-add) {
    color:#3fb950;
}

.elite-timeline-comment:has(span.seedbonus-remove) {
    color:#f85149;
}

.timeline-filters button {
    margin-left:6px;
}

.timeline-filters .active {
    background:#0d6efd;
    color:white;
}
.elite-timeline-scroll {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}

/* Custom Scrollbar */
.elite-timeline-scroll::-webkit-scrollbar {
    width: 6px;
}
.elite-timeline-scroll::-webkit-scrollbar-thumb {
    background: #30363d;
    border-radius: 10px;
}

/* Timeline Item */
.elite-timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 25px;
}

/* Vertical Line */
.elite-timeline-item::before {
    content: "";
    position: absolute;
    left: 9px;
    top: 0;
    bottom: -25px;
    width: 2px;
    background: #30363d;
}

/* Dot */
.elite-timeline-dot {
    position: absolute;
    left: 3px;
    top: 6px;
    width: 14px;
    height: 14px;
    background: #238636;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(35,134,54,0.6);
    transition: 0.2s ease;
}

.elite-timeline-item:hover .elite-timeline-dot {
    transform: scale(1.2);
}

/* Content Box */
.elite-timeline-content {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 12px;
    padding: 15px 18px;
    transition: 0.2s ease;
}

.elite-timeline-content:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

/* Comment Text */
.elite-timeline-comment {
    font-size: 0.95rem;
}

/* Date */
.elite-timeline-date {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #8b949e;
}

/* Empty State */
.elite-empty-state i {
    opacity: 0.4;
}

/* Moderation Timeline Types */

.elite-timeline-chat {
    color:#58a6ff;
}

.elite-timeline-comment {
    color:#8b949e;
}

.elite-timeline-forum {
    color:#f78166;
}

.elite-timeline-warning {
    color:#d29922;
    font-weight:600;
}

.elite-timeline-vip {
    color:#ffd700;
    font-weight:600;
}

.bg-role {
    background: linear-gradient(135deg, #89a6c7, #1b253270);
    border-radius: 10px;
    padding: 6px 14px;
    font-weight: 600;
}

@media (max-width: 768px) {

    .elite-stat {
        text-align: center;
    }

    .elite-mini {
        text-align: center;
    }

}

/* ===============================
   GitHub Style Profile Header
=================================*/

@media (min-width: 992px) {

    .elite-header {
        display: grid;
        grid-template-columns: 140px 1fr auto;
        gap: 28px;
        align-items: start;
    }

    

    .elite-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .elite-actions .btn {
        padding: 6px 14px;
        font-size: 0.85rem;
        border-radius: 6px;
        white-space: nowrap;
    }

}

/* Mobile layout */
@media (max-width: 991px) {
    .elite-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .elite-actions {
        margin-top: 15px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }
}


/* VIP */
.elite-vip-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    background: linear-gradient(135deg, #b8860b, #ffd700);
    color: #000;
    border-radius: 5px;
    font-weight: 600;
    font-size: 0.85rem;
    box-shadow: 0 4px 15px rgba(255,215,0,0.3);
    transition: 0.2s ease;
}

.elite-vip-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(255,215,0,0.5);
}

.elite-vip-label {
    font-weight: 700;
}

.elite-vip-date {
    font-weight: 500;
}

.elite-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 18px;
    margin-top: 12px;
    font-size: 0.95rem;
    color: #8b949e;
}

.elite-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
}

/* subtle divider between items */
.elite-meta-item:not(:last-child)::after {
    content: "";
    position: absolute;
    right: -10px;
    width: 1px;
    height: 14px;
    background: #30363d;
}

/* online styling */
.elite-meta-online {
    color: #3fb950;
    font-weight: 500;
}

.elite-online-dot {
    width: 8px;
    height: 8px;
    background: #3fb950;
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(63,185,80,0.6);
}

.elite-header-stats {
    border-top: 1px solid rgba(255,255,255,0.05);
    padding-top: 20px;
}

.elite-stat-compact {
    background: rgba(22, 27, 34, 0.55); /* darker translucent */
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);

    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 12px 14px;
    text-align: center;
    transition: 0.25s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}


.elite-stat-compact:hover {
    background: rgba(255,255,255,0.06);
    transform: translateY(-3px);
}

.elite-stat-compact span {
    display: block;
    font-size: 0.75rem;
    color: #8b949e;
}

.elite-stat-compact h5 {
    margin-top: 4px;
    font-weight: 600;
    font-size: 1rem;
    color: #e6edf3;
}

.elite-card {
     background: rgba(22, 27, 34, 0.55); /* darker translucent */
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 12px 14px;
    transition: 0.25s ease;
}

.elite-card:hover {
    background: rgba(255,255,255,0.06);
    transform: translateY(-3px);
}

.elite-cover {
    position: relative;
    padding: 100px 30px 80px 100px;
    border-radius: 10px;
    overflow: hidden;
    background-size: cover;
    background-position: center 5%;
    background-repeat: no-repeat;
}

/* Softer overlay */
.elite-cover::before {
    content: "";
    position: absolute;
    inset: 0;

    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.919) 0%,
        rgba(0, 0, 0, 0.831) 30%,
        rgba(0,0,0,0.55) 60%,
        rgba(32, 33, 34, 0.95) 100%
    );

    z-index: 1;
}


/* Make content sit above overlay */
.elite-cover > * {
    position: relative;
    z-index: 2;
}


.elite-header-content {
    color: #111;
}

.elite-header-stats {
    color: #e6edf3;
}

.elite-header-content h2 {
    color: #111;
}


html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

html::before {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{$profileBackground}}');
    background-position: center top;
    background-size: cover; 
    background-repeat: no-repeat;
    opacity: 0.7;
    z-index: -1;
}

html::after {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    /* This gradient is what creates the "gets darker as you scroll" effect */
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.05) 0%,
        rgba(0,0,0,0.25) 25%,
        rgba(0,0,0,0.55) 55%,
        rgba(0,0,0,0.85) 80%,
        rgba(0,0,0,1) 100%
    );

    pointer-events: none;
    z-index: -1;
}

/*Delete button*/

.elite-delete-btn {
    background: linear-gradient(135deg, #dc3545, #8b0000);
    border: none;
    color: #fff;
    border-radius: 10px;
    padding: 8px 20px;
    font-weight: 500;
    transition: 0.25s ease;
}

.elite-delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220,53,69,0.5);
}

.passkey-text {
    font-family: monospace;
    letter-spacing: 1px;
    font-size: 0.9rem;
    background: rgba(255,255,255,0.05);
    padding: 4px 8px;
    border-radius: 6px;
}

/*Scroll*/

.about-scroll {
    max-height: 550px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 6px; /* prevents content jump when scrollbar appears */
}

/* Optional: smooth modern scrollbar */
/* ABOUT SCROLL */
.about-scroll {
    max-height: 550px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 6px;
}

/* TIMELINE SCROLL */
.elite-timeline-scroll {
    max-height: 550px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 6px;
}

/* Smooth scrollbar */
.about-scroll::-webkit-scrollbar,
.elite-timeline-scroll::-webkit-scrollbar {
    width: 8px;
}

.about-scroll::-webkit-scrollbar-thumb,
.elite-timeline-scroll::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.25);
    border-radius: 6px;
}
/*.about-scroll*/

/* Stats Grid */

.elite-stats-grid .stat-card {

    width: 100%;
    height: 100%;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    text-align: center;
}

/* Label */

.stat-label {

    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;

    color: #8b949e;
}

/* Value */

.stat-value {

    font-size: 1.35rem;
    font-weight: 600;

    margin-top: 6px;

    color: #e6edf3;
}



/*Rank style*/

.rank-box {

    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;

    padding: 14px;
    text-align: center;

    transition: 0.25s ease;
}

.rank-box:hover {

    background: rgba(255,255,255,0.06);
    transform: translateY(-2px);
}

.rank-icon {

    font-size: 1.6rem;
}

.rank-title {

    font-weight: 600;
    margin-top: 4px;
}

.rank-desc {

    font-size: 0.8rem;
    color: #8b949e;
    margin-top: 4px;
}

.rank-score {

    font-size: 0.75rem;
    color: #58a6ff;
    margin-top: 6px;
}


.username-text{
    font-weight:600;
}

.seeder-rank-icon{
    font-size:1.1rem;
    opacity:.95;
}

/* Inline VIP */
.elite-vip-inline{

    display:inline-flex;
    align-items:center;
    gap:6px;

    padding:4px 10px;

    background:linear-gradient(135deg,#b8860b,#ffd700);

    color:#000;
    font-weight:600;

    border-radius:6px;

    font-size:.75rem;
}

.elite-vip-inline i{
    font-size:.9rem;
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

@endsection