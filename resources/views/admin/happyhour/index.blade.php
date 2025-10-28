@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Blurry Card Wrapper -->
    <div class="blur-card p-4 rounded-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h3 class="fw-bold mb-2 text-white">🎉 Happy Hours</h3>
            <form action="{{ route('happyhour.toggleAutomatic') }}" method="POST" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-{{ $automatic ? 'danger' : 'success' }} fw-bold">
                    {{ $automatic ? 'Disable' : 'Enable' }} Automatic Happy Hours
                </button>
            </form>
        </div>

        @if($automatic)
            <div class="alert alert-info rounded-3 mb-4">
                Default theme for today: <strong>{{ $theme['name'] }}</strong> 
                ({{ $theme['upload_multiplier'] }}x, {{ $theme['duration_hours'] }}h, 
                {{ $theme['free_download'] ? 'Free' : 'No Free' }})
            </div>
        @else
            <div class="alert alert-secondary rounded-3 mb-4">
                No automatic Happy Hour active today.
            </div>
        @endif

        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover table-borderless align-middle text-white">
                <thead>
                    <tr class="table-light text-dark">
                        <th>ID</th>
                        <th>Theme</th>
                        <th>Upload</th>
                        <th>Free Download</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Automatic</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($happyHours as $hh)
                        <tr class="align-middle">
                            <td>{{ $hh->id }}</td>
                            <td>{{ $hh->theme }}</td>
                            <td>{{ $hh->upload_multiplier }}x</td>
                            <td>
                                <span class="badge bg-{{ $hh->free_download ? 'success' : 'secondary' }}">
                                    {{ $hh->free_download ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>{{ $hh->start_at ? $hh->start_at->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $hh->end_at ? $hh->end_at->format('Y-m-d H:i') : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $hh->automatic ? 'info' : 'secondary' }}">
                                    {{ $hh->automatic ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $hh->active ? 'success' : 'secondary' }}">
                                    {{ $hh->active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                @if($hh->active)
                                    <form action="{{ route('happyhour.stop', $hh) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-warning">Stop</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @foreach($happyHours as $hh)
                <div class="card mb-3 bg-white bg-opacity-10 text-white border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $hh->theme }}</h5>
                        <p class="card-text mb-1">
                            Upload: <strong>{{ $hh->upload_multiplier }}x</strong> | 
                            Free: <span class="badge bg-{{ $hh->free_download ? 'success' : 'secondary' }}">
                                {{ $hh->free_download ? 'Yes' : 'No' }}
                            </span>
                        </p>
                        <p class="card-text mb-1">
                                Start: {{ $hh->start_at ? $hh->start_at->format('Y-m-d H:i') : '—' }} <br>
                                End: {{ $hh->end_at ? $hh->end_at->format('Y-m-d H:i') : '—' }}
                        </p>

                        <p class="card-text mb-2">
                            Automatic: <span class="badge bg-{{ $hh->automatic ? 'info' : 'secondary' }}">
                                {{ $hh->automatic ? 'Yes' : 'No' }}
                            </span>
                            Active: <span class="badge bg-{{ $hh->active ? 'success' : 'secondary' }}">
                                {{ $hh->active ? 'Yes' : 'No' }}
                            </span>
                        </p>
                        @if($hh->active)
                            <form action="{{ route('happyhour.stop', $hh) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning w-100">Stop</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('happyhour.create') }}" class="btn btn-success btn-lg fw-bold">
                <i class="bi bi-hourglass-split me-1"></i> Start New Happy Hour
            </a>
        </div>
    </div>
</div>

<style>
.blur-card {
    background: rgba(63, 20, 20, 0.01);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
}
.table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.15);
}
.btn-lg {
    padding: 0.75rem 1.5rem;
}
.card-title {
    font-weight: bold;
}
.alert {
    backdrop-filter: blur(8px);
}
</style>
@endsection
