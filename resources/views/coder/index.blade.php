@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">Coder Dashboard</h1>

    <!-- Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #2b4183; color: #fff; transition: transform 0.2s;">
                <div class="card-body">
                    <i class="bi bi-people-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $usersCount }}</h2>
                    <p class="mb-2">Total Users</p>
                    <canvas id="usersSparkline" height="30"></canvas>
                    <div class="progress mt-2" style="height: 8px;">
                        @php
                            $usersPercent = $usersCount ? ($usersLast30Days / $usersCount) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-light animated-bar" role="progressbar" style="width: 0%;" data-target="{{ $usersPercent }}"></div>
                    </div>
                    <small>{{ number_format($usersPercent, 1) }}% new this month</small>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #fd7e14; color: #fff; transition: transform 0.2s;">
                <div class="card-body">
                    <i class="bi bi-person-check-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $activeUsers }}</h2>
                    <p class="mb-2">Active Users</p>
                    <canvas id="activeUsersSparkline" height="30"></canvas>
                    @php
                        $activePercent = $usersCount ? ($activeUsers / $usersCount) * 100 : 0;
                    @endphp
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-light animated-bar" role="progressbar" style="width: 0%;" data-target="{{ $activePercent }}"></div>
                    </div>
                    <small>{{ number_format($activePercent, 1) }}% active now</small>
                </div>
            </div>
        </div>

        <!-- Total Torrents -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <a href="{{ route('coder.torrents') }}" class="text-decoration-none w-100">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #147954; color: #fff; transition: transform 0.2s;">
                <div class="card-body">
                    <i class="bi bi-hdd-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $torrentsCount }}</h2>
                    <p class="mb-0">Total Torrents</p>
                    <canvas id="torrentsSparkline" height="30"></canvas>
                </div>
            </div>
            </a>
        </div>
    </div>

    <!-- Other Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Users Registered Last 30 Days -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #614e1d; color: #fff;">
                <div class="card-body">
                    <i class="bi bi-calendar-event-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $usersLast30Days }}</h2>
                    <p class="mb-0">Users Registered Last 30 Days</p>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #6f42c1; color: #fff;">
                <div class="card-body">
                    <i class="bi bi-chat-dots-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $commentsCount }}</h2>
                    <p class="mb-0">Comments</p>
                </div>
            </div>
        </div>

        <!-- Thanks -->
        <div class="col-12 col-md-6 col-lg-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100" style="background-color: #e83e8c; color: #fff;">
                <div class="card-body">
                    <i class="bi bi-hand-thumbs-up-fill fs-2 mb-2"></i>
                    <h2 class="fw-bold">{{ $thanksCount }}</h2>
                    <p class="mb-0">Thanks</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info Row -->
    <div class="row g-4">
        <div class="col-12 col-md-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100">
                <div class="card-body">
                    <i class="bi bi-terminal fs-2 mb-2"></i>
                    <h5 class="fw-bold">PHP Version</h5>
                    <p class="mb-0">{{ phpversion() }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100">
                <div class="card-body">
                    <i class="bi bi-box-seam fs-2 mb-2"></i>
                    <h5 class="fw-bold">Laravel Version</h5>
                    <p class="mb-0">{{ app()->version() }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 d-flex">
            <div class="card shadow-sm text-center p-3 flex-fill h-100">
                <div class="card-body">
                    <i class="bi bi-cpu-fill fs-2 mb-2"></i>
                    <h5 class="fw-bold">Operating System</h5>
                    <p class="mb-0">{{ PHP_OS }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card:hover {
    transform: translateY(-5px);
}
.progress {
    border-radius: 4px;
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Animate progress bars
    document.querySelectorAll('.animated-bar').forEach(bar => {
        const target = bar.getAttribute('data-target');
        setTimeout(() => { bar.style.width = target + '%'; }, 200);
    });

    // Initialize sparklines
    const usersSparkline = new Chart(document.getElementById('usersSparkline'), {
        type: 'line',
        data: {
            labels: Array.from({length: 7}, (_, i) => `Day ${i+1}`),
            datasets: [{
                data: [5, 10, 8, 12, 15, 9, 11], // example data, replace with dynamic later
                borderColor: '#fff',
                backgroundColor: 'rgba(255,255,255,0.2)',
                tension: 0.3,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    const activeUsersSparkline = new Chart(document.getElementById('activeUsersSparkline'), {
        type: 'line',
        data: {
            labels: Array.from({length: 7}, (_, i) => `Day ${i+1}`),
            datasets: [{
                data: [2,4,3,5,6,3,4],
                borderColor: '#fff',
                backgroundColor: 'rgba(255,255,255,0.2)',
                tension: 0.3,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    const torrentsSparkline = new Chart(document.getElementById('torrentsSparkline'), {
        type: 'line',
        data: {
            labels: Array.from({length: 7}, (_, i) => `Day ${i+1}`),
            datasets: [{
                data: [10,12,11,13,15,14,16],
                borderColor: '#fff',
                backgroundColor: 'rgba(255,255,255,0.2)',
                tension: 0.3,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
});
</script>
@endpush
