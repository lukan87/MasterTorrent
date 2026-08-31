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


<style>

/* =========================================
   MAIN CARD
========================================= */

.modern-tabs-card{

    background:
        linear-gradient(
            145deg,
            rgba(20,25,40,.72),
            rgba(10,14,24,.92)
        );

    border-radius:24px;

    border:
        1px solid rgba(255,255,255,.06);

    overflow:hidden;

    backdrop-filter:blur(18px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.45);
}

/* =========================================
   HEADER
========================================= */

.modern-tabs-header{

    padding:18px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.modern-tabs-nav{

    gap:12px;

    flex-wrap:wrap;
}

.modern-tabs-nav .nav-link{

    border:none;

    background:rgba(255,255,255,.05);

    color:rgba(255,255,255,.72);

    border-radius:14px;

    padding:12px 18px;

    font-weight:700;

    transition:.25s ease;
}

.modern-tabs-nav .nav-link.active{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:#fff;

    box-shadow:
        0 10px 25px rgba(59,130,246,.25);
}

/* =========================================
   BODY
========================================= */

.modern-tabs-body{
    padding:24px;
}

/* =========================================
   DESCRIPTION
========================================= */

.modern-description-card,
.modern-subtitles-card,
.modern-snatched-card{

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:24px;

    overflow:hidden;
}

.modern-description-header,
.modern-subtitles-header,
.modern-snatched-header{

    padding:22px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.description-icon-box,
.subtitle-icon-box,
.snatch-icon{

    width:58px;
    height:58px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:#fff;

    font-size:1.3rem;
}

.modern-description-title,
.modern-subtitle-title,
.modern-snatched-title{

    color:#fff;

    font-size:1.35rem;

    font-weight:800;
}

.modern-description-subtitle,
.modern-subtitle-subtitle,
.modern-snatched-subtitle{

    color:rgba(255,255,255,.58);

    font-size:.9rem;
}

.modern-description-body,
.modern-subtitles-body,
.modern-snatched-body{

    padding:24px;
}

/* =========================================
   DESCRIPTION CONTENT
========================================= */

.modern-scrollable-content{

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:20px;

    padding:22px;

    color:#e5e7eb;

    line-height:1.75;

    word-break:break-word;

    overflow-wrap:anywhere;
}

/* =========================================
   SUBTITLE ITEMS
========================================= */

.subtitle-item{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding:18px;

    border-radius:18px;

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    margin-bottom:14px;
}

.subtitle-item:last-child{
    margin-bottom:0;
}

.subtitle-left{
    flex:1;
}

.subtitle-actions{

    display:flex;

    gap:10px;

    flex-wrap:wrap;
}

.modern-source-badge{

    padding:5px 10px;

    border-radius:999px;

    font-size:.72rem;

    font-weight:800;
}

.local-badge{

    background:rgba(34,197,94,.18);

    color:#4ade80;
}

.external-badge{

    background:rgba(59,130,246,.18);

    color:#93c5fd;
}

.subtitle-file-name{

    color:#fff;

    font-weight:700;

    margin-bottom:6px;
}

.subtitle-meta{

    color:rgba(255,255,255,.55);

    font-size:.82rem;
}

.subtitle-meta a{

    color:#93c5fd;

    text-decoration:none;
}

.subtitle-year{

    color:rgba(255,255,255,.55);

    font-size:.8rem;
}

.external-description{

    margin-top:10px;

    color:rgba(255,255,255,.72);

    font-size:.85rem;

    line-height:1.5;
}

.modern-subtitle-btn{

    border:none;

    border-radius:14px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:#fff;

    padding:10px 16px;

    font-weight:700;
}

.modern-delete-btn{

    border:none;

    border-radius:14px;

    background:rgba(239,68,68,.18);

    color:#f87171;

    padding:10px 14px;
}

/* =========================================
   SNATCHED
========================================= */

.snatched-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fill,minmax(320px,1fr));

    gap:18px;
}

.snatched-user-card{

    background:rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:20px;

    padding:20px;

    transition:.25s ease;
}

.snatched-user-card:hover{

    transform:translateY(-4px);

    border-color:rgba(124,58,237,.25);
}

.snatched-user-link{

    color:#fff;

    text-decoration:none;

    font-weight:800;
}

.owner-pill{

    background:rgba(250,204,21,.18);

    color:#fde047;

    padding:5px 10px;

    border-radius:999px;

    font-size:.72rem;

    font-weight:800;
}

.seed-status{

    padding:7px 12px;

    border-radius:999px;

    font-size:.75rem;

    font-weight:800;

    display:inline-flex;

    align-items:center;

    gap:6px;
}

.seeded{

    background:rgba(34,197,94,.18);

    color:#4ade80;
}

.not-seeded{

    background:rgba(239,68,68,.18);

    color:#f87171;
}

.snatch-stats{

    display:flex;

    flex-direction:column;

    gap:14px;
}

.snatch-stat{

    display:flex;

    align-items:center;

    gap:14px;
}

.stat-icon{

    width:44px;
    height:44px;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    color:#fff;
}

.snatch-stat small{

    display:block;

    color:rgba(255,255,255,.55);
}

.snatch-stat strong{
    color:#fff;
}

/* =========================================
   EMPTY
========================================= */

.empty-snatched{

    text-align:center;

    padding:50px 20px;

    color:rgba(255,255,255,.58);
}

.empty-snatched i{

    font-size:3rem;

    margin-bottom:14px;

    display:block;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-tabs-header,
    .modern-tabs-body,
    .modern-description-body,
    .modern-subtitles-body,
    .modern-snatched-body{

        padding:18px;
    }

    .modern-tabs-nav{

        flex-wrap:nowrap;

        overflow-x:auto;

        scrollbar-width:none;
    }

    .modern-tabs-nav::-webkit-scrollbar{
        display:none;
    }

    .modern-tabs-nav .nav-link{

        white-space:nowrap;

        flex:0 0 auto;
    }

    .subtitle-item{

        flex-direction:column;

        align-items:flex-start;
    }

    .snatched-grid{
        grid-template-columns:1fr;
    }
}

</style>



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


    .snatched-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:1rem;
    padding:1rem;
}

.snatched-user-card{
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(255,255,255,0.06);
    border-radius:16px;
    padding:1.2rem;
    transition:all .25s ease;
    backdrop-filter:blur(12px);
}

.snatched-user-card:hover{
    transform:translateY(-4px);
    border-color:rgba(255,255,255,0.15);
    box-shadow:0 10px 30px rgba(0,0,0,.25);
}

.snatched-user-link{
    color:#fff;
    text-decoration:none;
    font-size:1rem;
}

.snatched-user-link:hover{
    color:#4da3ff;
}

.snatch-stats{
    display:flex;
    flex-direction:column;
    gap:.8rem;
}

.snatch-stat{
    display:flex;
    align-items:center;
    gap:.8rem;
}

.stat-icon{
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:1rem;
}

.snatch-icon{
    width:48px;
    height:48px;
    border-radius:14px;
    background:linear-gradient(135deg,#0d6efd,#4f8cff);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:1.3rem;
}
</style>

@endsection