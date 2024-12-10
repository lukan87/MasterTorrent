@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">Seeding Torrents for <span class="font-weight-bold">{{ $user->name }}</span></h2>
        <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left-circle"></i> Back to Profile
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Torrent Name</th>
                    <th>Seeders</th>
                    <th>Leechers</th>
                    <th>Times Completed</th>
                    <th>Uploaded</th>
                    <th>Downloaded</th>
                    <th>Upload Date</th>
                    <th>Agent</th>
                    <th>Seed Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $item)
                    <tr class="hover-shadow">
                        <td>
                            <a href="{{ route('torrents.show', ['id' => $item['torrent']->id, 'slug' => $item['torrent']->slug] ?? '#') }}" class="text-decoration-none text-info font-weight-bold">
                                {{ $item['torrent']->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td>{{ $item['torrent']->seeders ?? 0 }}</td>
                        <td>{{ $item['torrent']->leechers ?? 0 }}</td>
                        <td>{{ $item['torrent']->times_completed ?? 0 }}</td>
                        <td>{{ \App\Helpers\FormatHelper::formatSize($item['uploaded']) ?? '0' }}</td>
                        <td>{{ \App\Helpers\FormatHelper::formatSize($item['downloaded']) ?? '0' }}</td>
                        <td>
                            {{ $item['torrent'] && $item['torrent']->created_at ? $item['torrent']->created_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>
                        <td>{{ $item['agent'] }}</td>
                        <td>{{ \App\Helpers\FormatHelper::formatTime($item['seeding_time']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Pagination Links -->
    <div class="d-flex justify-content-center mt-4">
        {{ $seedingTorrents->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    .table th, .table td {
        vertical-align: middle;
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease-in-out;
    }

    .btn-outline-primary {
        border-radius: 50px;
        padding-left: 15px;
        padding-right: 15px;
    }

    .text-primary {
        color: #007bff !important;
    }

    .font-weight-bold {
        font-weight: 600;
    }
</style>
@endsection
