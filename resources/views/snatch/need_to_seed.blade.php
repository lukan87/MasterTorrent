@extends('layouts.app')

@section('content')

<div class="container-fluid snatch-wrapper py-5">

{{-- TOP NAV --}}
<div class="text-center mb-5">
    <div class="glass-nav d-inline-flex">

        <a href="{{ route('snatch.seeding',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn">
            <i class="bi bi-cloud-upload"></i> Seeding
        </a>

        <a href="{{ route('snatch.hitAndRun',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn danger">
            <i class="bi bi-exclamation-triangle"></i> Hit & Run
        </a>

        <a href="{{ route('snatch.snatchlist',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn success">
            <i class="bi bi-collection"></i> Snatch List
        </a>

    </div>
</div>


<h2 class="text-center text-light mb-5 fw-bold">
<i class="bi bi-hourglass-bottom"></i>
Torrents That Need Seeding
</h2>


<div class="snatch-container mx-auto">

@if($needToSeed->isEmpty())

<div class="glass-card text-center p-5">
✅ You have no torrents that need seeding.
</div>

@else

@foreach($needToSeed as $torrent)

@php

$ratio = number_format(($torrent->uploaded / max($torrent->actual_downloaded,1)),2);

$remainingSeedtime = max(0,43200 - $torrent->seedtime);

$progress = min(100,($torrent->seedtime / 43200) * 100);

@endphp


<div class="glass-card snatch-card mb-4 not-seeding">

<div class="row align-items-center">


{{-- TORRENT INFO --}}
<div class="col-md-6">

<strong class="torrent-title">

@if($torrent->torrent)

<a href="{{ route('torrents.show',['id'=>$torrent->torrent->id,'slug'=>$torrent->torrent->slug]) }}">

<i class="bi bi-file-earmark-arrow-down"></i>

{{ $torrent->torrent->name }}

</a>

@else

<span class="text-danger">Torrent Deleted</span>

@endif

</strong>


<div class="meta small text-muted mt-2">

Created: {{ $torrent->created_at->format('Y-m-d H:i') }}

<br>

<span class="not-seeding-label">

<i class="bi bi-exclamation-triangle"></i>
Needs Seeding

</span>

@if($remainingSeedtime > 0)

<div class="seed-warning">

⚠ Seed this torrent to avoid Hit & Run

</div>

@endif

</div>


{{-- SEED PROGRESS --}}
<div class="mt-3">

<div class="d-flex justify-content-between small mb-1">

<span>Seed Progress</span>

<span class="{{ $remainingSeedtime > 0 ? 'text-warning':'text-success' }}">

{{ $remainingSeedtime > 0
? 'Left to seed: '.\App\Helpers\FormatHelper::formatTime($remainingSeedtime)
: 'Completed' }}

</span>

</div>

<div class="progress glass-progress">

<div class="progress-bar progress-glow"
style="width: {{ $progress }}%">
</div>

</div>

</div>

</div>



{{-- RATIO --}}
<div class="col-md-2 text-center">

<div class="ratio-circle">

<svg viewBox="0 0 36 36">

<path
d="M18 2.0845
a 15.9155 15.9155 0 0 1 0 31.831
a 15.9155 15.9155 0 0 1 0 -31.831"
fill="none"
stroke="#1f2937"
stroke-width="3"
/>

<path
stroke-dasharray="{{ min(100,$ratio*100) }},100"
d="M18 2.0845
a 15.9155 15.9155 0 0 1 0 31.831
a 15.9155 15.9155 0 0 1 0 -31.831"
fill="none"
stroke="#ef4444"
stroke-width="3"
/>

</svg>

<div class="ratio-text">

{{ $ratio }}

</div>

</div>

</div>



{{-- STATS + ACTIONS --}}
<div class="col-md-4 text-md-end stats">

<span class="stat-pill upload" data-bs-toggle="tooltip" title="Actual Upload: {{ \App\Helpers\FormatHelper::formatSize($torrent->actual_uploaded) }}">

<i class="bi bi-arrow-up"></i>

{{ \App\Helpers\FormatHelper::formatSize($torrent->uploaded) }}

</span>


<span class="stat-pill download" data-bs-toggle="tooltip" title="Actual Download: {{ \App\Helpers\FormatHelper::formatSize($torrent->actual_downloaded) }}">

<i class="bi bi-arrow-down"></i>

{{ \App\Helpers\FormatHelper::formatSize($torrent->downloaded) }}

</span>


<span class="stat-pill seed">

<i class="bi bi-clock"></i>

{{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}

</span>


<div class="mt-3 d-flex justify-content-md-end justify-content-center gap-2 flex-wrap">


@if(auth()->id() === $torrent->user_id)

@if($torrent->torrent)

<a href="{{ route('torrents.download',[
'id'=>$torrent->torrent->id,
'slug'=>$torrent->torrent->slug
]) }}"
class="btn btn-success btn-sm"
data-bs-toggle="tooltip"
title="Download to continue seeding">

<i class="bi bi-download"></i>

</a>

@endif


@if($torrent->torrent)

<form action="{{ route('bonus.buySeedtime') }}" method="POST">

@csrf

<input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}">

<button type="submit"
class="btn btn-primary btn-sm"
data-bs-toggle="tooltip"
title="Buy seedtime (1000 seedbonus)">

<i class="bi bi-coin"></i>

</button>

</form>

@endif

@endif


@if(auth()->user()->id == 3 && auth()->id() !== $torrent->user_id)

<form action="{{ route('snatch.deleteNeedToSeed',[
'userId'=>$torrent->user_id,
'torrentId'=>$torrent->torrent->id
]) }}"
method="POST"
onsubmit="return confirm('Remove torrent from history?');">

@csrf
@method('DELETE')

<button type="submit"
class="btn btn-danger btn-sm"
data-bs-toggle="tooltip"
title="Remove from history">

<i class="bi bi-trash"></i>

</button>

</form>

@endif

</div>

</div>

</div>

</div>

@endforeach


<div class="mt-5 text-center">

{{ $needToSeed->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>

<style>

/* BACKGROUND */

.snatch-wrapper{
min-height:100vh;
}



/* GLASS */

.glass-card{
background: rgba(255,255,255,0.05);
backdrop-filter: blur(16px);
border:1px solid rgba(255,255,255,0.08);
border-radius:16px;
padding:22px;
transition:all .3s;
box-shadow:0 12px 30px rgba(0,0,0,0.5);
}

.snatch-card:hover{
transform:translateY(-6px);
box-shadow:0 20px 45px rgba(0,0,0,0.7);
}



/* CATEGORY ICON */

.category-icon i{
font-size:28px;
color:#60a5fa;
}



/* LIVE SEEDING */

.live-seeding{
color:#4ade80;
font-weight:600;
}

.pulse{
width:8px;
height:8px;
background:#4ade80;
border-radius:50%;
display:inline-block;
margin-right:5px;
animation:pulse 1.5s infinite;
}

@keyframes pulse{
0%{box-shadow:0 0 0 0 rgba(74,222,128,.7);}
70%{box-shadow:0 0 0 10px rgba(74,222,128,0);}
100%{box-shadow:0 0 0 0 rgba(74,222,128,0);}
}



/* RATIO CIRCLE */

.ratio-circle{
position:relative;
width:60px;
height:60px;
margin:auto;
}

.ratio-circle svg{
transform:rotate(-90deg);
}

.ratio-text{
position:absolute;
top:50%;
left:50%;
transform:translate(-50%,-50%);
font-size:.9rem;
font-weight:700;
color:white;
}



/* PROGRESS */

.glass-progress{
height:8px;
background:rgba(255,255,255,0.08);
}

.progress-glow{
background:linear-gradient(90deg,#22c55e,#4ade80);
box-shadow:0 0 10px rgba(34,197,94,.7);
}



/* STAT PILLS */

.stat-pill{
display:inline-block;
padding:6px 12px;
border-radius:20px;
margin-left:6px;
background:rgba(255,255,255,0.08);
border:1px solid rgba(255,255,255,0.1);
font-size:.85rem;
}

.upload{color:#4ade80;}
.download{color:#60a5fa;}
.seed{color:#facc15;}



/* NAV */

.glass-nav{
background: rgba(255,255,255,0.05);
backdrop-filter: blur(10px);
border-radius:12px;
overflow:hidden;
}

.glass-btn{
padding:10px 20px;
color:white;
text-decoration:none;
border-right:1px solid rgba(255,255,255,0.1);
}

.glass-btn:hover{
background:rgba(255,255,255,0.08);
}



/* SKELETON */

.skeleton-card{
height:90px;
background:linear-gradient(90deg,#1f2937,#374151,#1f2937);
background-size:200% 100%;
animation:skeleton 1.5s infinite;
}

@keyframes skeleton{
0%{background-position:200% 0;}
100%{background-position:-200% 0;}
}

.category-badge{

display:inline-flex;
align-items:center;
gap:6px;

font-size:.75rem;
font-weight:600;

padding:4px 10px;

border-radius:20px;

background:rgba(255,255,255,0.08);

border:1px solid rgba(255,255,255,0.1);

}

.category-badge i{
font-size:14px;
color:#60a5fa;
}


/* NON SEEDING WARNING CARD */

.not-seeding{

border-left:4px solid #ef4444;

background:linear-gradient(
90deg,
rgba(239,68,68,0.15),
rgba(255,255,255,0.03)
);

box-shadow:0 0 12px rgba(239,68,68,0.3);

}


/* OPTIONAL GREEN FOR SEEDING */

.is-seeding{

border-left:4px solid #22c55e;

}


/* WARNING LABEL */

.not-seeding-label{

color:#f87171;

font-weight:600;

font-size:.8rem;

}

</style>


<script>

window.addEventListener("load",function(){

document.getElementById("loadingSkeleton").style.display="none";
document.getElementById("snatchContent").style.display="block";

});

</script>

@endsection