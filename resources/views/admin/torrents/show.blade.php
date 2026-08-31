@extends('layouts.app')

@section('content')

<div class="container-fluid py-4 admin-torrent-view">

<div class="row g-4">

<!-- MAIN COLUMN -->
<div class="col-xl-9">

<!-- HEADER -->
<div class="torrent-header-card">

<div class="torrent-header-content">

<h1 class="torrent-title">
<i class="bi bi-download me-2"></i>
{{ $torrent->name }}
</h1>

<div class="torrent-hash">
{{ strtoupper($torrent->info_hash) }}
</div>

</div>

<div class="torrent-stats">

<div class="stat-pill seeders">
<i class="bi bi-arrow-up-circle"></i>
{{ $seedersCount }}
</div>

<div class="stat-pill leechers">
<i class="bi bi-arrow-down-circle"></i>
{{ $leechersCount }}
</div>

<div class="stat-pill completed">
<i class="bi bi-check-circle"></i>
{{ $timesCompleted }}
</div>

</div>

</div>


<!-- SEEDERS + LEECHERS -->
<div class="card modern-card mt-4">

<div class="card-body">

<div class="row g-4">

<!-- SEEDERS -->
<div class="col-md-6">

<h5 class="section-title text-success">
<i class="bi bi-arrow-up-circle me-2"></i>
Seeders
</h5>

@if($seeders->isEmpty())

<p class="text-muted">No active seeders</p>

@else

<div class="seeders-scroll">

@foreach($seeders as $event)

<div class="peer-row">

<div>
<strong>{{ $event->user->name ?? 'Unknown' }}</strong>

<div class="peer-time">
{{ $event->created_at->diffForHumans() }}
</div>
</div>

<div class="peer-stats">

<div class="upload">
↑ {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}
</div>

<div class="download">
↓ {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}
</div>

</div>

</div>

@endforeach

</div>

@endif

</div>


<!-- LEECHERS -->
<div class="col-md-6">

<h5 class="section-title text-danger">
<i class="bi bi-arrow-down-circle me-2"></i>
Leechers
</h5>

@if($leechers->isEmpty())

<p class="text-muted">No active leechers</p>

@else

<div class="scrollable-list">

@foreach($leechers as $peer)

<div class="peer-row">

<div>

<strong>{{ $peer->user->name ?? 'Unknown' }}</strong>

</div>

<div class="download">
↓ {{ \App\Helpers\FormatHelper::formatSize($peer->downloaded) }}
</div>

</div>

@endforeach

</div>

@endif

</div>

</div>

</div>

</div>



<!-- SNATCH HISTORY -->
<div class="card modern-card mt-4">

<div class="card-body">

<h4 class="section-title mb-4">

<i class="bi bi-clock-history me-2"></i>
Snatched History

</h4>

@if($history->isEmpty())

<div class="alert alert-warning">
No download history available.
</div>

@else

<div class="history-container">

@foreach($history as $event)

<div class="history-row">

<div class="history-user">

<strong>{{ $event->user->name ?? 'Unknown User' }}</strong>

<div class="history-time">
{{ $event->created_at->format('Y-m-d H:i') }}
</div>

</div>

<div class="history-badges">

<span class="badge {{ $event->seeder ? 'bg-success' : 'bg-danger' }}">
Seeder
</span>

<span class="badge {{ $event->completed_at ? 'bg-primary' : 'bg-secondary' }}">
Completed
</span>

</div>

<div class="history-stats">

<span>
↑ {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}
</span>

<span>
↓ {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}
</span>

<span>
Seedtime {{ \App\Helpers\FormatHelper::formatTime($event->seedtime) }}
</span>

</div>

</div>

@endforeach

</div>

<div class="mt-3 d-flex justify-content-center">

{{ $history->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>

</div>


<!-- SIDEBAR -->
<div class="col-xl-3">

<div class="card modern-card sticky-info">

<div class="card-body">

<h5 class="section-title mb-3">

<i class="bi bi-info-circle me-2"></i>
Torrent Info

</h5>

<ul class="info-list">

<li>
<span>ID</span>
<strong>{{ $torrent->id }}</strong>
</li>

<li>
<span>Slug</span>
<strong>{{ $torrent->slug }}</strong>
</li>

<li>
<span>File</span>
<strong>{{ $torrent->file_name }}</strong>
</li>

<li>
<span>Files</span>
<strong>{{ $torrent->num_files }}</strong>
</li>

</ul>

</div>

</div>

</div>

</div>

</div>

<style>

/* background */
.admin-torrent-view{
background: radial-gradient(circle at top,#0d1117,#000);
color:#e6e6e6;
}


/* header */
.torrent-header-card{
background:linear-gradient(145deg,#1a1a1a,#242424);
border-radius:16px;
padding:24px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 10px 35px rgba(0,0,0,.5);
}

.torrent-title{
font-size:1.6rem;
font-weight:700;
}

.torrent-hash{
font-size:.75rem;
background:#111;
padding:4px 8px;
border-radius:6px;
color:#aaa;
}

/* stat pills */

.torrent-stats{
display:flex;
gap:10px;
}

.stat-pill{
display:flex;
align-items:center;
gap:6px;
padding:6px 12px;
border-radius:999px;
font-weight:600;
font-size:.85rem;
}

.seeders{
background:rgba(40,167,69,.15);
color:#6dff9c;
}

.leechers{
background:rgba(220,53,69,.15);
color:#ff9c9c;
}

.completed{
background:rgba(0,123,255,.15);
color:#9cc7ff;
}


/* modern card */

.modern-card{
background:linear-gradient(145deg,#1a1a1a,#222);
border-radius:16px;
border:1px solid rgba(255,255,255,.05);
box-shadow:0 10px 30px rgba(0,0,0,.4);
}


/* section title */

.section-title{
font-weight:600;
letter-spacing:.3px;
}


/* peer list */

.peer-row{
display:flex;
justify-content:space-between;
align-items:center;
padding:10px;
border-bottom:1px solid rgba(255,255,255,.05);
}

.peer-row:last-child{
border-bottom:none;
}

.peer-time{
font-size:.75rem;
color:#888;
}

.peer-stats{
text-align:right;
font-size:.8rem;
}


/* history */

.history-container{
max-height:600px;
overflow:auto;
}

.history-row{
padding:12px;
border-bottom:1px solid rgba(255,255,255,.05);
}

.history-user{
font-size:.95rem;
}

.history-time{
font-size:.75rem;
color:#888;
}

.history-stats{
font-size:.8rem;
color:#aaa;
display:flex;
gap:10px;
margin-top:4px;
}


/* sidebar */

.info-list{
list-style:none;
padding:0;
margin:0;
}

.info-list li{
display:flex;
justify-content:space-between;
padding:8px 0;
border-bottom:1px solid rgba(255,255,255,.05);
}

.sticky-info{
position:sticky;
top:90px;
}

.seeders-scroll{
max-height:320px;
overflow-y:auto;
padding-right:4px;
scrollbar-width:thin;
}

/* Webkit scrollbar */

.seeders-scroll::-webkit-scrollbar{
width:6px;
}

.seeders-scroll::-webkit-scrollbar-track{
background:transparent;
}

.seeders-scroll::-webkit-scrollbar-thumb{
background:rgba(255,255,255,.15);
border-radius:6px;
}

.seeders-scroll::-webkit-scrollbar-thumb:hover{
background:rgba(255,255,255,.3);
}

</style>

@endsection