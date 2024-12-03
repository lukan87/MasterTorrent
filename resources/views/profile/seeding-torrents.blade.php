

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Seeding Torrents for <span class="text-primary">{{ $user->name }}</span></h2>
    <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="btn btn-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left-circle"></i> Back to Profile
    </a>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Torrent Name</th>
                    <th>Seeders</th>
                    <th>Leechers</th>
                    <th>Times Completed</th>
                    <th>Upload Date</th>
                    <th>Agent</th>
                    <th>Seed Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $item)
                    <tr>
                        <td>
                            <a href="{{ route('torrents.show', ['id' => $item['torrent']->id, 'slug' => $item['torrent']->slug] ?? '#') }}" class="text-decoration-none text-info">
                                {{ $item['torrent']->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td>{{ $item['torrent']->seeders ?? 0 }}</td>
                        <td>{{ $item['torrent']->leechers ?? 0 }}</td>
                        <td>{{ $item['torrent']->times_completed ?? 0 }}</td>
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
</div>
@endsection

