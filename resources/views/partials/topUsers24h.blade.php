<div class="container-fluid mt-3 tt-wrapper">

<div class="accordion modern-lb-accordion" id="lbAccordion">

<div class="accordion-item bg-transparent border-0">

<h2 class="accordion-header">

<button class="accordion-button modern-lb-button collapsed-show text-light"

        type="button"

        data-bs-toggle="collapse"

        data-bs-target="#lbLeaderboards">

    <span class="me-2">🏆</span>

    Community Leaderboards

    <span class="modern-lb-subtitle ms-2">

        (Last 24 Hours)

    </span>

</button>

</h2>

<div id="lbLeaderboards" class="accordion-collapse collapse show">

<div class="accordion-body pt-2">

<div class="row g-2">

{{-- ================= UPLOADERS ================= --}}

<div class="col-xl-4 col-md-6">

<div class="card modern-lb-card border-0 h-100">

<div class="card-header modern-lb-header text-success">

<i class="fas fa-upload me-2"></i>

Top Uploaders

</div>

<ul class="list-group list-group-flush bg-transparent">

@forelse($topUploaders24h as $index => $user)

@php $isMe = auth()->id() === $user->id; @endphp

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center {{ $isMe ? 'lb-highlight' : '' }}">

<div class="d-flex align-items-center gap-2 overflow-hidden">

<span class="badge modern-rank-badge {{ $index==0?'bg-warning text-dark':($index==1?'bg-secondary':($index==2?'bg-dark':'bg-light text-dark')) }}">

{!! $index==0?'🥇':($index==1?'🥈':($index==2?'🥉':$index+1)) !!}

</span>

<a href="{{ route('profile.show',$user->id) }}"

   class="modern-lb-user {{ $isMe?'text-warning':'text-light' }}">

{{ $user->name }} @if($isMe) 👑 @endif

</a>

</div>

<span class="modern-lb-value {{ $isMe?'text-warning':'text-success' }}">

{{ App\Helpers\FormatHelper::formatSize($user->uploaded_24h) }}

</span>

</li>

@empty

<li class="list-group-item modern-empty-item">

No uploads recorded.

</li>

@endforelse

@php $inTop = collect($topUploaders24h)->contains('id', auth()->id()); @endphp

@if(auth()->check() && !$inTop && $userUploadRank24h)

<li class="list-group-item modern-separator">

────────────

</li>

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center lb-highlight">

<div class="modern-extra-info text-warning">

#{{ $userUploadRank24h['rank'] }} {{ auth()->user()->name }} 👑

@if(isset($uploadMovement))

<span class="ms-2 {{ $uploadMovementClass }}" title="Compared to yesterday">

    @if($uploadMovement > 0)

        ↑ +{{ $uploadMovement }}

    @elseif($uploadMovement < 0)

        ↓ {{ $uploadMovement }}

    @else

        —

    @endif

</span>

@endif

@if(!empty($uploadStreak) && $uploadStreak >= 2)

<span class="ms-2 text-warning">

🔥 {{ $uploadStreak }}d

</span>

@endif

@if(!empty($uploadPercentile))

<span class="ms-2 text-info">

Top {{ $uploadPercentile }}%

</span>

@endif

</div>

<span class="modern-lb-value text-warning">

{{ App\Helpers\FormatHelper::formatSize($userUploadRank24h['value']) }}

</span>

</li>

@endif

</ul>

</div>

</div>

{{-- ================= DOWNLOADERS ================= --}}

<div class="col-xl-4 col-md-6">

<div class="card modern-lb-card border-0 h-100">

<div class="card-header modern-lb-header text-danger">

<i class="fas fa-download me-2"></i>

Top Downloaders

</div>

<ul class="list-group list-group-flush bg-transparent">

@forelse($topDownloaders24h as $index => $user)

@php $isMe = auth()->id() === $user->id; @endphp

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center {{ $isMe ? 'lb-highlight' : '' }}">

<div class="d-flex align-items-center gap-2 overflow-hidden">

<span class="badge modern-rank-badge {{ $index==0?'bg-warning text-dark':($index==1?'bg-secondary':($index==2?'bg-dark':'bg-light text-dark')) }}">

{!! $index==0?'🥇':($index==1?'🥈':($index==2?'🥉':$index+1)) !!}

</span>

<a href="{{ route('profile.show',$user->id) }}"

   class="modern-lb-user {{ $isMe?'text-warning':'text-light' }}">

{{ $user->name }} @if($isMe) 👑 @endif

</a>

</div>

<span class="modern-lb-value {{ $isMe?'text-warning':'text-danger' }}">

{{ App\Helpers\FormatHelper::formatSize($user->downloaded_24h) }}

</span>

</li>

@empty

<li class="list-group-item modern-empty-item">

No downloads recorded.

</li>

@endforelse

@php $inTop = collect($topDownloaders24h)->contains('id', auth()->id()); @endphp

@if(auth()->check() && !$inTop && $userDownloadRank24h)

<li class="list-group-item modern-separator">

────────────

</li>

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center lb-highlight">

<div class="modern-extra-info text-warning">

#{{ $userDownloadRank24h['rank'] }} {{ auth()->user()->name }} 👑

@if(isset($downloadMovement))

<span class="ms-2 {{ $downloadMovementClass }}" title="Compared to yesterday">

    @if($downloadMovement > 0)

        ↑ +{{ $downloadMovement }}

    @elseif($downloadMovement < 0)

        ↓ {{ $downloadMovement }}

    @else

        —

    @endif

</span>

@endif

@if(!empty($downloadStreak) && $downloadStreak >= 2)

<span class="ms-2 text-warning">

🔥 {{ $downloadStreak }}d

</span>

@endif

@if(!empty($downloadPercentile))

<span class="ms-2 text-info">

Top {{ $downloadPercentile }}%

</span>

@endif

</div>

<span class="modern-lb-value text-warning">

{{ App\Helpers\FormatHelper::formatSize($userDownloadRank24h['value']) }}

</span>

</li>

@endif

</ul>

</div>

</div>

{{-- ================= SEEDERS ================= --}}

<div class="col-xl-4 col-md-6">

<div class="card modern-lb-card border-0 h-100">

<div class="card-header modern-lb-header text-primary">

<i class="fas fa-seedling me-2"></i>

Top Seeders

</div>

<ul class="list-group list-group-flush bg-transparent">

@forelse($topSeeders24h as $index => $user)

@php $isMe = auth()->id() === $user->id; @endphp

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center {{ $isMe ? 'lb-highlight' : '' }}">

<div class="d-flex align-items-center gap-2 overflow-hidden">

<span class="badge modern-rank-badge {{ $index==0?'bg-warning text-dark':($index==1?'bg-secondary':($index==2?'bg-dark':'bg-light text-dark')) }}">

{!! $index==0?'🥇':($index==1?'🥈':($index==2?'🥉':$index+1)) !!}

</span>

<a href="{{ route('profile.show',$user->id) }}"

   class="modern-lb-user {{ $isMe?'text-warning':'text-light' }}">

{{ $user->name }} @if($isMe) 👑 @endif

</a>

</div>

<span class="modern-lb-value {{ $isMe?'text-warning':'text-primary' }}">

{{ $user->seed_count }} torrents

</span>

</li>

@empty

<li class="list-group-item modern-empty-item">

No active seeders.

</li>

@endforelse

@php $inTop = collect($topSeeders24h)->contains('id', auth()->id()); @endphp

@if(auth()->check() && !$inTop && $userSeederRank)

<li class="list-group-item modern-separator">

────────────

</li>

<li class="list-group-item modern-lb-item d-flex justify-content-between align-items-center lb-highlight">

<div class="modern-extra-info text-warning">

#{{ $userSeederRank['rank'] }} {{ auth()->user()->name }} 👑

@if(!empty($seederStreak) && $seederStreak >= 2)

<span class="ms-2 text-warning">

🔥 {{ $seederStreak }}d

</span>

@endif

@if(!empty($seederPercentile))

<span class="ms-2 text-info">

Top {{ $seederPercentile }}%

</span>

@endif

</div>

<span class="modern-lb-value text-warning">

{{ $userSeederRank['value'] }} torrents

</span>

</li>

@endif

</ul>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

<style>
/* =========================================================
   FILEIPLAY COMMUNITY LEADERBOARDS
   News / Poll matched typography
   Maximum font size: 14px
========================================================= */

.modern-lb-accordion {
    overflow: hidden;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    border-radius: 1rem;
    box-shadow: 0 10px 26px rgba(0,0,0,.16);
}

.modern-lb-button {
    position: relative;
    display: flex;
    align-items: center;
    padding: 1rem 1.15rem;
    color: #f1f5f9 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    font-size: 14px;
    font-weight: 700;
}

.modern-lb-button::before {
    content: "";
    position: absolute;
    left: 0;
    top: 15px;
    bottom: 15px;
    width: 3px;
    background: linear-gradient(
        180deg,
        var(--ui-accent),
        var(--ui-accent-strong)
    );
    border-radius: 0 4px 4px 0;
}

.modern-lb-button::after {
    filter: invert(1);
    opacity: .55;
}

.modern-lb-subtitle {
    color: #8fa3b7 !important;
    font-size: 12px;
    font-weight: 600;
}

/* Cards */
.modern-lb-card {
    position: relative;
    overflow: hidden;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .90),
        rgba(15, 23, 42, .78)
    ) !important;
    border: 1px solid var(--ui-border) !important;
    border-radius: .85rem !important;
    box-shadow: 0 8px 22px rgba(0,0,0,.14);
    transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
}

.modern-lb-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 10%;
    right: 10%;
    height: 1px;
    background: rgba(255,255,255,.07);
    pointer-events: none;
}

.modern-lb-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99,210,198,.22) !important;
    box-shadow: 0 12px 28px rgba(0,0,0,.20);
}

/* Card header */
.modern-lb-header {
    position: relative;
    z-index: 2;
    padding: .75rem .9rem !important;
    color: #dce7f2 !important;
    background: rgba(99,210,198,.035) !important;
    border-bottom: 1px solid rgba(148,163,184,.08) !important;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .01em;
}

.modern-lb-header i {
    color: var(--ui-accent);
}

/* List */
.modern-lb-item {
    position: relative;
    z-index: 1;
    min-height: 0;
    padding: .7rem .8rem;
    color: #cbd5e1;
    background: transparent !important;
    border-color: rgba(148,163,184,.065) !important;
    line-height: 1.3;
    transition: background 160ms ease, border-color 160ms ease;
}

.modern-lb-item:hover {
    background: rgba(99,210,198,.035) !important;
}

.modern-rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 26px;
    padding: .2rem .4rem !important;
    color: #aab8c7 !important;
    background: rgba(148,163,184,.07) !important;
    border: 1px solid rgba(148,163,184,.10);
    border-radius: .4rem;
    font-size: 12px;
    font-weight: 700;
}

.modern-lb-item:first-child .modern-rank-badge {
    border-color: rgba(99,210,198,.18);
}

.modern-lb-user {
    min-width: 0;
    color: #dce7f2 !important;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 160ms ease;
}

.modern-lb-user:hover {
    color: var(--ui-accent) !important;
}

.modern-lb-value {
    flex-shrink: 0;
    color: #9db2c5 !important;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.modern-extra-info {
    min-width: 0;
    color: var(--ui-accent) !important;
    font-size: 12px;
    font-weight: 650;
    line-height: 1.4;
}

/* Current user */
.lb-highlight {
    background: linear-gradient(
        90deg,
        rgba(99,210,198,.085),
        rgba(99,210,198,.025)
    ) !important;
    border-left: 2px solid var(--ui-accent) !important;
    box-shadow: inset 0 0 12px rgba(99,210,198,.035);
}

.lb-highlight .modern-lb-value {
    color: var(--ui-accent) !important;
}

/* Empty / separator */
.modern-empty-item {
    padding: .7rem !important;
    color: #71859b !important;
    background: transparent !important;
    border-color: rgba(148,163,184,.065) !important;
    text-align: center;
    font-size: 12px;
}

.modern-separator {
    padding: .15rem 0 !important;
    color: #52677d !important;
    background: transparent !important;
    border-color: rgba(148,163,184,.05) !important;
    text-align: center;
    font-size: 11px;
}

/* Movement */
.move-big-up,
.move-up,
.move-small-up {
    color: var(--ui-accent) !important;
    font-weight: 700;
}

.move-big-down,
.move-down {
    color: #e58b93 !important;
    font-weight: 700;
}

.modern-extra-info .text-warning,
.modern-extra-info .text-info {
    color: inherit !important;
}

.lb-highlight .text-info {
    color: #8fb8bd !important;
}

/* Prevent Bootstrap utility classes from creating tiny text */
.modern-lb-accordion .small,
.modern-lb-accordion small {
    font-size: 12px !important;
}

/* Mobile */
@media (max-width: 767.98px) {
    .modern-lb-button {
        padding: .85rem .9rem;
        font-size: 14px;
    }

    .modern-lb-subtitle {
        margin-left: .4rem !important;
        font-size: 12px;
    }

    .modern-lb-header {
        padding: .7rem .75rem !important;
        font-size: 14px;
    }

    .modern-lb-item {
        padding: .65rem .7rem !important;
    }

    .modern-lb-user {
        font-size: 14px;
    }

    .modern-lb-value {
        font-size: 13px;
    }

    .modern-extra-info {
        font-size: 12px;
    }

    .modern-rank-badge {
        min-width: 27px;
        height: 25px;
        font-size: 12px;
    }
}
</style>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const key = "leaderboardAccordionState";

    const collapse = document.getElementById("lbLeaderboards");

    if(localStorage.getItem(key) === "closed"){

        collapse.classList.remove("show");

    }

    collapse.addEventListener("shown.bs.collapse", () => localStorage.setItem(key,"open"));

    collapse.addEventListener("hidden.bs.collapse", () => localStorage.setItem(key,"closed"));

});

</script>