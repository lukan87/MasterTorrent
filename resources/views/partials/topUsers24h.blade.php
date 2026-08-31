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

/* =========================================
   ACCORDION
========================================= */

.modern-lb-accordion{

    background:
        rgba(40,40,40,.35);

    backdrop-filter:blur(8px);

    border-radius:18px;

    border:
        1px solid rgba(255,255,255,.06);
}

.modern-lb-button{

    font-size:1rem;

    font-weight:700;

    padding:14px 18px;

    box-shadow:none !important;
}

.modern-lb-button:not(.collapsed){

    background:transparent;

    color:#fff;
}

.modern-lb-button::after{

    filter:invert(1);
}

.modern-lb-subtitle{

    font-size:.82rem;

    color:rgba(255,255,255,.5);

    font-weight:500;
}

/* =========================================
   CARD
========================================= */

.modern-lb-card{

    background:
        rgba(255,255,255,.03);

    backdrop-filter:blur(10px);

    border-radius:16px;

    border:
        1px solid rgba(255,255,255,.05);

    overflow:hidden;
}

.modern-lb-header{

    background:
        rgba(255,255,255,.02);

    font-size:.92rem;

    font-weight:700;

    padding:12px 16px;

    border-bottom:
        1px solid rgba(255,255,255,.04);
}

/* =========================================
   LIST ITEMS
========================================= */

.modern-lb-item{

    background:transparent !important;

    border-color:
        rgba(255,255,255,.04) !important;

    padding:10px 14px;

    min-height:auto;

    line-height:1.2;
}

.modern-lb-item:hover{

    background:
        rgba(255,255,255,.03) !important;
}

.modern-rank-badge{

    font-size:.7rem;

    padding:4px 6px;

    min-width:28px;
}

.modern-lb-user{

    text-decoration:none;

    font-size:1rem;

    font-weight:600;

    white-space:nowrap;

    overflow:hidden;

    text-overflow:ellipsis;

    transition:.2s ease;
}

.modern-lb-user:hover{

    opacity:.85;
}

.modern-lb-value{

    font-size:.95rem;

    font-weight:700;

    white-space:nowrap;
}

.modern-extra-info{

    font-size:.95rem;

    line-height:1.3;
}

/* =========================================
   EMPTY / SEPARATOR
========================================= */

.modern-empty-item{

    background:transparent !important;

    border-color:
        rgba(255,255,255,.04) !important;

    color:rgba(255,255,255,.45);

    text-align:center;

    padding:12px;

    font-size:.82rem;
}

.modern-separator{

    background:transparent !important;

    border-color:
        rgba(255,255,255,.04) !important;

    text-align:center;

    color:rgba(255,255,255,.2);

    padding:4px 0;

    font-size:.7rem;
}

/* =========================================
   HIGHLIGHT
========================================= */

.lb-highlight{

    background:
        linear-gradient(
            90deg,
            rgba(255,193,7,.12),
            rgba(255,193,7,.03)
        ) !important;

    border-left:
        2px solid #ffc107 !important;

    box-shadow:
        inset 0 0 10px rgba(255,193,7,.08);
}

/* =========================================
   MOVEMENT COLORS
========================================= */

.move-big-up{

    color:#00ff87;

    font-weight:bold;
}

.move-up{

    color:#28ff7a;
}

.move-small-up{

    color:#7dffb3;
}

.move-down{

    color:#ff6b6b;
}

.move-big-down{

    color:#ff2e2e;

    font-weight:bold;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-lb-button{

        font-size:.92rem;

        padding:12px 14px;
    }

    .modern-lb-header{

        font-size:.86rem;

        padding:10px 14px;
    }

    .modern-lb-item{

        padding:9px 12px;
    }

    .modern-lb-user{

        font-size:.82rem;
    }

    .modern-lb-value{

        font-size:.76rem;
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