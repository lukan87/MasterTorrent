@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4">

<!-- HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h3 class="fw-semibold mb-1">
<i class="bi bi-cpu me-2"></i> System Information
</h3>
<p class="text-muted small mb-0">
Server status and diagnostics
</p>
</div>

<span class="badge bg-info-subtle text-info px-3 py-2">
<i class="bi bi-server me-1"></i> Server Control
</span>

</div>


<!-- SYSTEM STATS -->

<div class="row g-4">

<!-- PHP -->

<div class="col-md-3">
<div class="card system-card h-100">
<div class="card-body">

<div class="system-icon bg-primary-subtle text-primary">
<i class="bi bi-code-slash"></i>
</div>

<h6 class="text-muted mt-3 mb-1">PHP Version</h6>
<h5 class="fw-bold">{{ $phpVersion }}</h5>

</div>
</div>
</div>


<!-- OS -->

<div class="col-md-3">
<div class="card system-card h-100">
<div class="card-body">

<div class="system-icon bg-secondary-subtle text-secondary">
<i class="bi bi-hdd-stack"></i>
</div>

<h6 class="text-muted mt-3 mb-1">Operating System</h6>
<h6 class="fw-semibold">{{ $os }}</h6>

</div>
</div>
</div>


<!-- UPTIME -->

<div class="col-md-3">
<div class="card system-card h-100">
<div class="card-body">

<div class="system-icon bg-success-subtle text-success">
<i class="bi bi-clock-history"></i>
</div>

<h6 class="text-muted mt-3 mb-1">System Uptime</h6>
<h5 class="fw-bold">{{ $uptime }}</h5>

</div>
</div>
</div>


<!-- CACHE -->

<div class="col-md-3">
<div class="card system-card h-100">
<div class="card-body">

<div class="system-icon bg-info-subtle text-info">
<i class="bi bi-lightning"></i>
</div>

<h6 class="text-muted mt-3 mb-1">Cache Status</h6>
<h6 class="fw-semibold">{{ $cacheStatus }}</h6>

</div>
</div>
</div>

</div>


<!-- RESOURCE USAGE -->

<div class="row g-4 mt-1">

<!-- DISK -->

<div class="col-md-6">

<div class="card system-card">

<div class="card-body">

<h6 class="text-muted mb-3">
<i class="bi bi-hdd-network me-2"></i> Disk Storage
</h6>

<div class="small mb-2">
{{ number_format($storage / 1073741824, 2) }} GB free of
{{ number_format($diskTotal / 1073741824, 2) }} GB
</div>

<div class="progress progress-thin">

<div
class="progress-bar bg-info"
style="width: {{ 100 - (($storage / $diskTotal) * 100) }}%">
</div>

</div>

</div>

</div>

</div>


<!-- RAM -->

<div class="col-md-6">

<div class="card system-card">

<div class="card-body">

<h6 class="text-muted mb-3">
<i class="bi bi-memory me-2"></i> RAM Usage
</h6>

<div class="small mb-2">
{{ $ramUsage['used'] }} MB used of {{ $ramUsage['total'] }} MB
</div>

<div class="progress progress-thin">

<div
class="progress-bar bg-warning"
style="width: {{ ($ramUsage['used'] / $ramUsage['total']) * 100 }}%">
</div>

</div>

</div>

</div>

</div>

</div>


<!-- CPU LOAD -->

<div class="row g-4 mt-1">

<div class="col-md-12">

<div class="card system-card">

<div class="card-body">

<h6 class="text-muted mb-3">
<i class="bi bi-speedometer2 me-2"></i> CPU Load Average
</h6>

<div class="d-flex gap-3">

<span class="badge bg-secondary">
1 min: {{ $cpuLoad[0] }}
</span>

<span class="badge bg-secondary">
5 min: {{ $cpuLoad[1] }}
</span>

<span class="badge bg-secondary">
15 min: {{ $cpuLoad[2] }}
</span>

</div>

</div>

</div>

</div>

</div>



<!-- ACTIONS -->

<div class="card system-card mt-4">

<div class="card-body">

<h5 class="fw-semibold mb-4">
<i class="bi bi-tools me-2"></i> Maintenance Actions
</h5>

<div class="row g-3">

<div class="col-md-3">
<form action="{{ route('admin.systemInfo.clearCache') }}" method="POST">
@csrf
<button class="btn btn-danger w-100">
<i class="bi bi-lightning-charge me-1"></i>
Clear Cache
</button>
</form>
</div>

<div class="col-md-3">
<form action="{{ route('admin.systemInfo.clearViews') }}" method="POST">
@csrf
<button class="btn btn-warning w-100">
<i class="bi bi-eye-slash me-1"></i>
Clear Views
</button>
</form>
</div>

<div class="col-md-3">
<form action="{{ route('admin.systemInfo.clearConfig') }}" method="POST">
@csrf
<button class="btn btn-success w-100">
<i class="bi bi-sliders me-1"></i>
Clear Config
</button>
</form>
</div>

<div class="col-md-3">
<form action="{{ route('admin.systemInfo.clearRoutes') }}" method="POST">
@csrf
<button class="btn btn-primary w-100">
<i class="bi bi-diagram-3 me-1"></i>
Clear Routes
</button>
</form>
</div>

<div class="col-md-3">
<a href="{{ route('admin.systemInfo.showRoutes') }}" class="btn btn-info w-100">
<i class="bi bi-list"></i>
Show Routes
</a>
</div>

</div>

</div>

</div>

</div>

<style>

.system-card{
background:rgba(30,35,50,.65);
border:1px solid rgba(255,255,255,.05);
backdrop-filter:blur(10px);
border-radius:12px;
transition:.25s;
}

.system-card:hover{
transform:translateY(-4px);
box-shadow:0 10px 25px rgba(0,0,0,.4);
}

.system-icon{
width:42px;
height:42px;
display:flex;
align-items:center;
justify-content:center;
border-radius:10px;
font-size:20px;
}

.progress-thin{
height:6px;
border-radius:10px;
background:#1e293b;
}

</style>

@endsection