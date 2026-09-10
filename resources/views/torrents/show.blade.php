@extends('layouts.app')

@section('title', $torrent->name)

@section('content')

<div class="container-fluid">

    {{-- Deleted Info Box --}}
    @include('torrents.partials.deleted-info')

    {{-- =========================
        HEADER (Movie/TV/Game)
    ========================== --}}
    @if($torrent->tmdb_type === 'movie')
        @include('torrents.partials.movie')
    @elseif($torrent->tmdb_type === 'tv')
        @include('torrents.partials.tv')
    @elseif($torrent->steamid)
        @include('torrents.partials.game')
    @else
        @include('torrents.partials.default')
    @endif


@if(Auth::user()->user_class < \App\Models\UserClass::VIP)

@endif

    {{-- =========================
        SHOW BAR (Hidden if deleted)
    ========================== --}}
    @if(!$torrent->trashed())
        @include('torrents.partials.showbar')
    @endif


    {{-- =========================
        Screenshots (Hidden if deleted)
    ========================== --}}
    @if(!$torrent->trashed() && $torrent->images->isNotEmpty())
        @include('torrents.partials.screens')
    @endif


    {{-- =========================
        MediaInfo (Hidden if deleted)
    ========================== --}}
    @if(!$torrent->trashed() && !empty($torrent->mediainfo))
        @include('torrents.partials.mediainfo')
    @endif


{{-- =========================================
    DETAILS SECTION
========================================= --}}

@if(!$torrent->trashed())

<div class="modern-tabs-card mb-4">

    {{-- =====================================
        HEADER
    ====================================== --}}
    <div class="modern-tabs-header">

        <ul class="nav modern-tabs-nav"
            role="tablist">

            {{-- DESCRIPTION --}}
            <li class="nav-item">

                <a class="nav-link active"
                   data-bs-toggle="tab"
                   href="#description">

                    <i class="bi bi-card-text me-2"></i>

                    Description

                </a>

            </li>

            {{-- SUBTITLES --}}
            @if(
                $torrent->subtitles->isNotEmpty() ||
                !empty($externalSubtitles['items'])
            )

            <li class="nav-item">

                <a class="nav-link"
                   data-bs-toggle="tab"
                   href="#subtitles">

                    <i class="bi bi-badge-cc-fill me-2"></i>

                    Subtitles

                </a>

            </li>

            @endif

            {{-- SNATCHED --}}
            @if(
                Auth::check() &&
                Auth::user()->user_class >= \App\Models\UserClass::MODERATOR
            )

            <li class="nav-item">

                <a class="nav-link"
                   data-bs-toggle="tab"
                   href="#snatched">

                    <i class="bi bi-person-check-fill me-2"></i>

                    Snatched

                </a>

            </li>

            @endif

        </ul>

    </div>

    {{-- =====================================
        BODY
    ====================================== --}}
    <div class="modern-tabs-body">

        <div class="tab-content">

            {{-- =====================================
                DESCRIPTION
            ====================================== --}}
            <div class="tab-pane fade show active"
                 id="description">

                <div class="modern-description-card">

                    {{-- HEADER --}}
                    <div class="modern-description-header">

                        <div class="d-flex align-items-center gap-3">

                            <div class="description-icon-box">

                                <i class="bi bi-card-text"></i>

                            </div>

                            <div>

                                <h4 class="modern-description-title mb-1">

                                    Description

                                </h4>

                                <div class="modern-description-subtitle">

                                    Release notes, details and additional information

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- BODY --}}
                    <div class="modern-description-body">

                        <div class="scrollable-content modern-scrollable-content">

                            {!! convertCustomTagsToHtml($torrent->description) !!}

                        </div>

                    </div>

                </div>

            </div>

            {{-- =====================================
                SUBTITLES
            ====================================== --}}
            @if(
                $torrent->subtitles->isNotEmpty() ||
                !empty($externalSubtitles['items'])
            )

            <div class="tab-pane fade"
                 id="subtitles">

                <div class="modern-subtitles-card mt-3">

                    {{-- HEADER --}}
                    <div class="modern-subtitles-header">

                        <div class="d-flex align-items-center gap-3">

                            <div class="subtitle-icon-box">

                                <i class="bi bi-badge-cc-fill"></i>

                            </div>

                            <div>

                                <h4 class="modern-subtitle-title mb-1">

                                    Available Subtitles

                                </h4>

                                <div class="modern-subtitle-subtitle">

                                    Local uploads and external subtitle sources

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- BODY --}}
                    <div class="modern-subtitles-body">

                        {{-- =====================================
                            LOCAL SUBTITLES
                        ====================================== --}}
                        @foreach($torrent->subtitles as $sub)

                            @php

                                $flags = [
                                    'english'  => '🇬🇧',
                                    'romanian' => '🇷🇴',
                                    'italian'  => '🇮🇹',
                                    'french'   => '🇫🇷',
                                    'spanish'  => '🇪🇸',
                                ];

                                $flag =
                                    $flags[strtolower($sub->language ?? '')]
                                    ?? '🏳️';

                            @endphp

                            <div class="subtitle-item">

                                {{-- LEFT --}}
                                <div class="subtitle-left">

                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                        <span class="modern-source-badge local-badge">

                                            LOCAL

                                        </span>

                                        <strong>

                                            {{ $flag }}

                                            {{ strtoupper($sub->language ?? 'N/A') }}

                                        </strong>

                                    </div>

                                    <div class="subtitle-file-name">

                                        {{ $sub->original_name }}

                                    </div>

                                    <div class="subtitle-meta">

                                        Uploaded by

                                        <a href="{{ route('profile.show', ['id'=>$sub->uploaded_by,'name'=>$sub->uploader->name ?? 'Unknown']) }}">

                                            {{ $sub->uploader->name ?? 'Unknown' }}

                                        </a>

                                        • {{ $sub->created_at->diffForHumans() }}

                                    </div>

                                </div>

                                {{-- RIGHT --}}
                                <div class="subtitle-actions">

                                    <a href="{{ route('subtitles.download', $sub) }}"
                                       class="btn modern-subtitle-btn">

                                        <i class="bi bi-download me-1"></i>

                                        Download

                                    </a>

                                    @if(
                                        auth()->id() === $sub->uploaded_by ||
                                        auth()->user()->user_class >= \App\Models\UserClass::MODERATOR
                                    )

                                    <form method="POST"
                                          action="{{ route('subtitles.destroy', $sub) }}"
                                          onsubmit="return confirm('Delete this subtitle?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn modern-delete-btn">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                    @endif

                                </div>

                            </div>

                        @endforeach


                        {{-- =====================================
                            EXTERNAL SUBTITLES
                        ====================================== --}}
                        @if(!empty($externalSubtitles['items']))

                            @foreach($externalSubtitles['items'] as $sub)

                                <div class="subtitle-item external-subtitle">

                                    {{-- LEFT --}}
                                    <div class="subtitle-left">

                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                            <span class="modern-source-badge external-badge">

                                                SUBS.RO

                                            </span>

                                            <strong>

                                                {{ strtoupper($sub['language'] ?? 'N/A') }}

                                            </strong>

                                            @if(!empty($sub['year']))

                                                <span class="subtitle-year">

                                                    {{ $sub['year'] }}

                                                </span>

                                            @endif

                                        </div>

                                        <div class="subtitle-file-name">

                                            {{ $sub['title'] ?? 'Unknown Subtitle' }}

                                        </div>

                                        @if(!empty($sub['description']))

                                            <div class="external-description">

                                                {!! $sub['description'] !!}

                                            </div>

                                        @endif

                                        <div class="subtitle-meta">

                                            @if(!empty($sub['translator']))

                                                By {{ $sub['translator'] }}

                                            @endif

                                            @if(!empty($sub['type']))

                                                • {{ ucfirst($sub['type']) }}

                                            @endif

                                        </div>

                                    </div>

                                    {{-- RIGHT --}}
                                    <div class="subtitle-actions">

                                        @if(!empty($sub['downloadPage']))

                                            <a href="{{ $sub['downloadPage'] }}"
                                               target="_blank"
                                               class="btn modern-subtitle-btn external-download-btn">

                                                <i class="bi bi-download me-1"></i>

                                                Download

                                            </a>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        @endif

                    </div>

                </div>

            </div>

            @endif


            {{-- =====================================
                SNATCHED
            ====================================== --}}
            @if(
                Auth::check() &&
                Auth::user()->user_class >= \App\Models\UserClass::MODERATOR
            )

            <div class="tab-pane fade"
                 id="snatched">

                <div class="modern-snatched-card mt-3">

                    {{-- HEADER --}}
                    <div class="modern-snatched-header">

                        <div class="d-flex align-items-center gap-3">

                            <div class="snatch-icon">

                                <i class="bi bi-person-check-fill"></i>

                            </div>

                            <div>

                                <h4 class="modern-snatched-title mb-1">

                                    Users That Snatched This Torrent

                                </h4>

                                <div class="modern-snatched-subtitle">

                                    {{ $snatched->count() }}
                                    {{ Str::plural('user', $snatched->count()) }}

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- BODY --}}
                    <div class="modern-snatched-body">

                        @if($snatched->isEmpty())

                            <div class="empty-snatched">

                                <i class="bi bi-inbox"></i>

                                <p>

                                    No users have snatched this torrent yet.

                                </p>

                            </div>

                        @else

                            <div class="snatched-grid">

                                @foreach($snatched as $history)

                                <div class="snatched-user-card">

                                    {{-- TOP --}}
                                    <div class="d-flex justify-content-between align-items-start mb-3 gap-3">

                                        <div>

                                            <a href="{{ route('profile.show', ['id'=>$history->user_id,'name'=>$history->user_name]) }}"
                                               class="snatched-user-link">

                                                {{ $history->user_name }}

                                            </a>

                                            @if($history->user_id === $torrent->owner)

                                            <div class="mt-2">

                                                <span class="owner-pill">

                                                    <i class="bi bi-star-fill me-1"></i>

                                                    Torrent Owner

                                                </span>

                                            </div>

                                            @endif

                                        </div>

                                        <span class="seed-status {{ $history->seeder ? 'seeded' : 'not-seeded' }}">

                                            <i class="bi {{ $history->seeder ? 'bi-arrow-up-circle-fill' : 'bi-x-circle-fill' }}"></i>

                                            {{ $history->seeder ? 'Seeding' : 'Not Seeding' }}

                                        </span>

                                    </div>

                                    {{-- STATS --}}
                                    <div class="snatch-stats">

                                        <div class="snatch-stat">

                                            <div class="stat-icon bg-primary">

                                                <i class="bi bi-download"></i>

                                            </div>

                                            <div>

                                                <small>Downloaded</small>

                                                <strong>

                                                    {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}

                                                </strong>

                                            </div>

                                        </div>

                                        <div class="snatch-stat">

                                            <div class="stat-icon bg-success">

                                                <i class="bi bi-upload"></i>

                                            </div>

                                            <div>

                                                <small>Uploaded</small>

                                                <strong>

                                                    {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) }}

                                                </strong>

                                            </div>

                                        </div>

                                        <div class="snatch-stat">

                                            <div class="stat-icon bg-secondary">

                                                <i class="bi bi-clock-history"></i>

                                            </div>

                                            <div>

                                                <small>Seed Time</small>

                                                <strong>

                                                    {{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}

                                                </strong>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                @endforeach

                            </div>

                        @endif

                    </div>

                </div>

            </div>

            @endif

        </div>

    </div>

</div>

@endif






    {{-- =========================
        COMMENTS
    ========================== --}}
    @if(!$torrent->trashed())
        @include('torrents.partials.comments')
    @else
        
    @endif


    
        {{-- Torrente similare --}}
   
    @if(!$torrent->trashed())
        @include('torrents.partials.similar')
    @endif


</div>

{{-- Modal files --}}
@include('torrents.partials.modalfilesrender')




<style>
/* =========================================================
   FILEIPLAY TORRENT DETAILS — FORUM STYLE
   ========================================================= */

.modern-tabs-card {
    background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(0,0,0,.24);
    backdrop-filter: blur(12px);
}

.modern-tabs-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--ui-border);
}

.modern-tabs-nav {
    gap: 8px;
    flex-wrap: wrap;
}

.modern-tabs-nav .nav-link {
    border: 1px solid transparent;
    background: rgba(255,255,255,.035);
    color: rgba(255,255,255,.72);
    border-radius: .6rem;
    padding: 9px 13px;
    font-size: 14px;
    font-weight: 700;
    transition: .2s ease;
}

.modern-tabs-nav .nav-link:hover {
    color: #fff;
    background: rgba(255,255,255,.06);
    border-color: var(--ui-border);
}

.modern-tabs-nav .nav-link.active {
    background: rgba(20, 184, 166, .14);
    color: var(--ui-accent);
    border-color: rgba(20, 184, 166, .35);
    box-shadow: none;
}

.modern-tabs-body {
    padding: 18px;
}

/* Shared inner cards */
.modern-description-card,
.modern-subtitles-card,
.modern-snatched-card {
    background: linear-gradient(135deg, rgba(22,32,51,.92), rgba(15,23,42,.78));
    border: 1px solid var(--ui-border);
    border-radius: .8rem;
    overflow: hidden;
    position: relative;
}

.modern-description-card::before,
.modern-subtitles-card::before,
.modern-snatched-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, var(--ui-accent), var(--ui-accent-strong));
    opacity: .9;
}

.modern-description-header,
.modern-subtitles-header,
.modern-snatched-header {
    padding: 15px 18px;
    border-bottom: 1px solid var(--ui-border);
}

.description-icon-box,
.subtitle-icon-box,
.snatch-icon {
    width: 42px;
    height: 42px;
    border-radius: .65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(20,184,166,.12);
    border: 1px solid rgba(20,184,166,.28);
    color: var(--ui-accent);
    font-size: 18px;
    flex: 0 0 auto;
}

.modern-description-title,
.modern-subtitle-title,
.modern-snatched-title {
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    margin: 0;
}

.modern-description-subtitle,
.modern-subtitle-subtitle,
.modern-snatched-subtitle {
    color: rgba(255,255,255,.58);
    font-size: 13px;
}

.modern-description-body,
.modern-subtitles-body,
.modern-snatched-body {
    padding: 18px;
}

/* Description */
.modern-scrollable-content {
    background: rgba(255,255,255,.025);
    border: 1px solid var(--ui-border);
    border-radius: .65rem;
    padding: 17px;
    color: rgba(255,255,255,.84);
    font-size: 14px;
    line-height: 1.65;
    word-break: break-word;
    overflow-wrap: anywhere;
}

/* Subtitles */
.subtitle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px;
    border-radius: .7rem;
    background: rgba(255,255,255,.025);
    border: 1px solid var(--ui-border);
    margin-bottom: 10px;
    transition: border-color .2s ease, background .2s ease;
}

.subtitle-item:hover {
    background: rgba(255,255,255,.04);
    border-color: rgba(20,184,166,.28);
}

.subtitle-item:last-child {
    margin-bottom: 0;
}

.subtitle-left {
    flex: 1 1 280px;
    min-width: 0;
}

.subtitle-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.modern-source-badge {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .03em;
}

.local-badge {
    background: rgba(34,197,94,.12);
    color: #4ade80;
    border: 1px solid rgba(34,197,94,.22);
}

.external-badge {
    background: rgba(20,184,166,.12);
    color: var(--ui-accent);
    border: 1px solid rgba(20,184,166,.24);
}

.subtitle-file-name {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 5px;
    overflow-wrap: anywhere;
}

.subtitle-meta {
    color: rgba(255,255,255,.55);
    font-size: 13px;
}

.subtitle-meta a {
    color: var(--ui-accent);
    text-decoration: none;
}

.subtitle-meta a:hover {
    color: #fff;
}

.subtitle-year {
    color: rgba(255,255,255,.55);
    font-size: 12px;
}

.external-description {
    margin-top: 8px;
    color: rgba(255,255,255,.72);
    font-size: 13px;
    line-height: 1.5;
}

.modern-subtitle-btn {
    border: 1px solid rgba(20,184,166,.28);
    border-radius: .55rem;
    background: rgba(20,184,166,.11);
    color: var(--ui-accent);
    padding: 7px 11px;
    font-size: 13px;
    font-weight: 700;
    transition: .2s ease;
}

.modern-subtitle-btn:hover {
    background: rgba(20,184,166,.18);
    color: #fff;
    border-color: rgba(20,184,166,.45);
}

.modern-delete-btn {
    border: 1px solid rgba(239,68,68,.25);
    border-radius: .55rem;
    background: rgba(239,68,68,.1);
    color: #f87171;
    padding: 7px 10px;
    font-size: 13px;
}

.modern-delete-btn:hover {
    background: rgba(239,68,68,.17);
    color: #fff;
}

/* Snatched */
.snatched-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
    padding: 0;
}

.snatched-user-card {
    background: rgba(255,255,255,.025);
    border: 1px solid var(--ui-border);
    border-radius: .7rem;
    padding: 15px;
    transition: .2s ease;
}

.snatched-user-card:hover {
    transform: translateY(-2px);
    border-color: rgba(20,184,166,.28);
    box-shadow: 0 8px 20px rgba(0,0,0,.18);
}

.snatched-user-link {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
}

.snatched-user-link:hover {
    color: var(--ui-accent);
}

.owner-pill {
    background: rgba(250,204,21,.1);
    border: 1px solid rgba(250,204,21,.2);
    color: #fde047;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.seed-status {
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.seeded {
    background: rgba(34,197,94,.1);
    border: 1px solid rgba(34,197,94,.2);
    color: #4ade80;
}

.not-seeded {
    background: rgba(239,68,68,.1);
    border: 1px solid rgba(239,68,68,.2);
    color: #f87171;
}

.snatch-stats {
    display: flex;
    flex-direction: column;
    gap: 11px;
}

.snatch-stat {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: .55rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    flex: 0 0 auto;
}

.snatch-stat small {
    display: block;
    color: rgba(255,255,255,.52);
    font-size: 12px;
}

.snatch-stat strong {
    color: #fff;
    font-size: 13px;
}

.empty-snatched {
    text-align: center;
    padding: 35px 18px;
    color: rgba(255,255,255,.58);
    font-size: 14px;
}

.empty-snatched i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
    color: var(--ui-accent);
}

/* Keep Bootstrap utility stat colors, but soften them to fit the theme */
.snatch-stat .bg-primary {
    background: rgba(20,184,166,.16) !important;
}

.snatch-stat .bg-success {
    background: rgba(34,197,94,.16) !important;
}

.snatch-stat .bg-secondary {
    background: rgba(148,163,184,.14) !important;
}

/* Mobile */
@media (max-width: 768px) {
    .modern-tabs-header,
    .modern-tabs-body,
    .modern-description-body,
    .modern-subtitles-body,
    .modern-snatched-body {
        padding: 14px;
    }

    .modern-tabs-nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .modern-tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .modern-tabs-nav .nav-link {
        white-space: nowrap;
        flex: 0 0 auto;
        font-size: 14px;
    }

    .modern-description-header,
    .modern-subtitles-header,
    .modern-snatched-header {
        padding: 13px 15px;
    }

    .description-icon-box,
    .subtitle-icon-box,
    .snatch-icon {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    .subtitle-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .subtitle-actions {
        width: 100%;
    }

    .snatched-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection