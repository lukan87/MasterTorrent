@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold text-gradient mb-1">
                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
            </h1>
            <p class="text-muted mb-0">Overview of your system statistics and quick actions</p>
        </div>
        <div class="d-flex">
            <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                <i class="bi bi-shield-lock me-1"></i> Admin Mode
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4">
        <!-- Torrents Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-earmark-arrow-down fs-3 text-primary"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.torrents.index') }}">Manage</a></li>
                                <li><a class="dropdown-item" href="#">View Reports</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ number_format($totalTorrents) }}</h3>
                    <p class="text-muted mb-3">Total Torrents</p>
                    <a href="{{ route('admin.torrents.index') }}" class="btn btn-sm btn-outline-primary stretched-link">
                        Manage Torrents <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people fs-3 text-success"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Manage</a></li>
                                <li><a class="dropdown-item" href="#">View New Users</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ number_format($totalUsers) }}</h3>
                    <p class="text-muted mb-3">Registered Users</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-success stretched-link">
                        Manage Users <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Movies Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-film fs-3 text-info"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.movies.index') }}">Manage</a></li>
                                <li><a class="dropdown-item" href="#">View Recent</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ number_format($totalMovies) }}</h3>
                    <p class="text-muted mb-3">Movies Catalog</p>
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-sm btn-outline-info stretched-link">
                        Manage Movies <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Series Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-collection-play fs-3 text-warning"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.series.index') }}">Manage</a></li>
                                <li><a class="dropdown-item" href="#">View Recent</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ number_format($totalSeries) }}</h3>
                    <p class="text-muted mb-3">TV Series</p>
                    <a href="{{ route('admin.series.index') }}" class="btn btn-sm btn-outline-warning stretched-link">
                        Manage Series <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer Section -->
    @if (Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-terminal me-2"></i>Developer Tools
                        </h5>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Restricted</span>
                    </div>
                    <p class="text-muted">Advanced system management and diagnostics</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="{{ route('admin.systemInfo.index') }}" class="btn btn-dark">
                            <i class="bi bi-server me-1"></i> System Info
                        </a>
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-tools me-1"></i> Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Happy Hour Quick Link -->
<div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2"></i>Happy Hour
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary">Restricted</span>
            </div>
            <p class="text-muted">Manage manual and automatic Happy Hours</p>
            <div class="d-grid gap-2 d-md-flex">
                <a href="{{ route('happyhour.index') }}" class="btn btn-primary">
                    <i class="bi bi-hourglass-split me-1"></i> Open Happy Hour Panel
                </a>
                <a href="{{ route('happyhour.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i> Start New Happy Hour
                </a>
            </div>
        </div>
    </div>
</div>

    </div>
    @endif
</div>

<style>
    .text-gradient {
        background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .hover-scale {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-scale:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .stretched-link::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }
</style>

@push('scripts')
<script>
    // Add any dashboard-specific scripts here
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Add animation to cards when they come into view
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    });
</script>
@endpush
@endsection