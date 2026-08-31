@extends('layouts.app')

@section('content')


<div class="container-fluid px-4 py-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h2 class="fw-semibold mb-1">
<i class="bi bi-speedometer2 me-2"></i>
Admin Dashboard
</h2>

<p class="text-muted small mb-0">
System overview and management tools
</p>
</div>

<div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
<i class="bi bi-shield-lock me-1"></i> Admin Mode
</div>

</div>


<!-- STATS -->
<div class="row g-4 mb-4">

<div class="col-xl-3 col-md-6">
<div class="card stat-card h-100">

<div class="card-body">

<div class="stat-icon bg-primary-subtle text-primary">
<i class="bi bi-file-earmark-arrow-down"></i>
</div>

<h4 class="fw-bold mt-3 mb-0">
{{ number_format($totalTorrents) }}
</h4>

<p class="text-muted small mb-3">
Total Torrents
</p>

<a href="{{ route('admin.torrents.index') }}" class="btn btn-sm btn-outline-primary">
Manage
</a>

</div>
</div>
</div>


<div class="col-xl-3 col-md-6">
<div class="card stat-card h-100">

<div class="card-body">

<div class="stat-icon bg-success-subtle text-success">
<i class="bi bi-people"></i>
</div>

<h4 class="fw-bold mt-3 mb-0">
{{ number_format($totalUsers) }}
</h4>

<p class="text-muted small mb-3">
Registered Users
</p>

<a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-success">
Manage
</a>

</div>
</div>
</div>


<div class="col-xl-3 col-md-6">
<div class="card stat-card h-100">

<div class="card-body">

<div class="stat-icon bg-info-subtle text-info">
<i class="bi bi-film"></i>
</div>

<h4 class="fw-bold mt-3 mb-0">
{{ number_format($totalMovies) }}
</h4>

<p class="text-muted small mb-3">
Movies
</p>

<a href="{{ route('admin.movies.index') }}" class="btn btn-sm btn-outline-info">
Manage
</a>

</div>
</div>
</div>


<div class="col-xl-3 col-md-6">
<div class="card stat-card h-100">

<div class="card-body">

<div class="stat-icon bg-warning-subtle text-warning">
<i class="bi bi-collection-play"></i>
</div>

<h4 class="fw-bold mt-3 mb-0">
{{ number_format($totalSeries) }}
</h4>

<p class="text-muted small mb-3">
TV Series
</p>

<a href="{{ route('admin.series.index') }}" class="btn btn-sm btn-outline-warning">
Manage
</a>

</div>
</div>
</div>


{{-- <div class="col-xl-3 col-md-6">
<div class="card stat-card h-100">

<div class="card-body">

<div class="stat-icon bg-danger-subtle text-danger">
<i class="bi bi-clipboard-data"></i>
</div>

<h4 class="fw-bold mt-3 mb-0">
{{ number_format($totalLogs ?? 0) }}
</h4>

<p class="text-muted small mb-3">
Torrent Logs
</p>

<a href="{{ route('admin.torrent_logs.index') }}" class="btn btn-sm btn-outline-danger">
View
</a>

</div>
</div>
</div> --}}

</div>


<!-- QUICK ACTIONS -->
<div class="row g-4">

@if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

<div class="col-md-6">

<div class="card action-card h-100">

<div class="card-body">

<h5 class="fw-semibold mb-3">
<i class="bi bi-clock-history me-2"></i>
Happy Hour
</h5>

<p class="text-muted small">
Manage manual and automatic happy hours.
</p>

<div class="d-flex gap-2">

<a href="{{ route('happyhour.index') }}" class="btn btn-primary btn-sm">
Open Panel
</a>

<a href="{{ route('happyhour.create') }}" class="btn btn-outline-primary btn-sm">
Start New
</a>

</div>

</div>
</div>

</div>

@endif


@if(Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)

<div class="col-md-6">

<div class="card action-card h-100">

<div class="card-body">

<h5 class="fw-semibold mb-3">
<i class="bi bi-terminal me-2"></i>
Developer Tools
</h5>

<p class="text-muted small">
System diagnostics and developer utilities.
</p>

<div class="d-flex gap-2">

<a href="{{ route('admin.systemInfo.index') }}" class="btn btn-dark btn-sm">
System Info
</a>

<button class="btn btn-outline-secondary btn-sm">
Maintenance
</button>

</div>

</div>
</div>

</div>

@endif


@auth
@if(Auth::user()->user_class >= 8)

<div class="col-md-6">

<div class="card action-card h-100">

<div class="card-body">

<h5 class="fw-semibold mb-3">
<i class="bi bi-megaphone me-2"></i>
Announcements
</h5>

<p class="text-muted small">
Create and manage system announcements.
</p>

<div class="d-flex gap-2">

<a href="{{ route('announcements.index') }}" class="btn btn-primary btn-sm">
View All
</a>

<a href="{{ route('announcements.create') }}" class="btn btn-outline-primary btn-sm">
Create New
</a>

</div>

</div>
</div>

</div>

<div class="col-md-6">

<div class="card action-card h-100">

<div class="card-body">

<h5 class="fw-semibold mb-3">
<i class="bi bi-envelope-check me-2"></i>
Remainder Email Sent to users
</h5>

<p class="text-muted small">
View emails sent to users.
</p>

<div class="d-flex gap-2">

<a href="{{ route('admin.emails.index') }}" class="btn btn-primary btn-sm">
View All
</a>


</div>

</div>
</div>

</div>

@endif


@if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

<div class="col-md-6">

<div class="card action-card h-100">

<div class="card-body">

<h5 class="fw-semibold mb-3">
<i class="bi bi-clipboard-data me-2"></i>
Torrent Logs
</h5>

<p class="text-muted small">
Track uploads, edits, deletions, and moderation actions.
</p>

<div class="d-flex gap-2">

<a href="{{ route('admin.torrent_logs.index') }}" class="btn btn-primary btn-sm">
View Logs
</a>

</div>

</div>
</div>

</div>

@endif
@endauth

</div>

</div>

<style>

    .stat-card{
border:1px solid rgba(255,255,255,.05);
background:rgba(30,35,50,.6);
backdrop-filter:blur(10px);
border-radius:12px;
transition:.25s;
}

.stat-card:hover{
transform:translateY(-4px);
box-shadow:0 10px 25px rgba(0,0,0,.4);
}

.stat-icon{
width:42px;
height:42px;
display:flex;
align-items:center;
justify-content:center;
border-radius:10px;
font-size:20px;
}

.action-card{
border:1px solid rgba(255,255,255,.05);
background:rgba(30,35,50,.6);
backdrop-filter:blur(10px);
border-radius:12px;
}
</style>

@endsection