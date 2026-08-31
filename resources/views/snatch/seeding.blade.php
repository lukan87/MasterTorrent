@extends('layouts.app')

@section('content')

<div class="container-fluid snatch-wrapper py-5">

{{-- TOP NAV --}}
<div class="text-center mb-5">
<div class="glass-nav d-inline-flex">

<a href="{{ route('snatch.snatchlist',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn">
<i class="bi bi-collection"></i> Snatch List
</a>

<a href="{{ route('snatch.hitAndRun',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn danger">
<i class="bi bi-exclamation-triangle"></i> Hit & Run
</a>

<a href="{{ route('snatch.needToSeed',['userId'=>$userId ?? Auth::id()]) }}" class="glass-btn warning">
<i class="bi bi-hourglass-bottom"></i> Need to Seed
</a>

</div>
</div>


<h2 class="text-center text-light mb-5 fw-bold">
<i class="bi bi-cloud-upload"></i>
Seeding Torrents
</h2>


<div class="snatch-container mx-auto">


@if($seeding->isEmpty())

<div class="glass-card text-center p-5">
No torrents currently being seeded.
</div>

@else


@foreach($seeding as $history)

<x-snatch-card :history="$history" type="seeding"/>

@endforeach



<div class="mt-5 text-center">

{{ $seeding->links('pagination::bootstrap-5') }}

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