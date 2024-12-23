@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <!-- Go Back Button -->
    <div class="mb-4">
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Go Back to Torrent
        </a>
    </div>

    <h1 class="mb-4">Users that finished: <span class="text-info">{{ $torrent->name }}</span></h1>

    <!-- History Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>User</th>
                    <th>Seeding</th>
                    <th>Uploaded</th>
                    <th>Downloaded</th>
                    <th>Start at</th>
                    <th>Finished at</th>
                    <th>Time Leeching</th>
                    <th>Last Active</th>
                    <th>Seedtime</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($histories as $history)
                    <tr>
                        <td>
                               @if ($history->user)
                                        <a href="{{ route('profile.show', ['id' => $history->user->id, 'name' => $history->user->name]) }}"
                                               data-bs-toggle="tooltip"
                                               data-bs-title="See {{ $history->user->name }}'s Profile">
                                                 {{ $history->user->name }}
                                        </a>
                                @else
                                            Unknown
                                @endif
                        </td>
                        <td>
                            <span class="badge {{ $history->seeder ? 'bg-success' : 'bg-danger' }}">
                                {{ $history->seeder ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>{{ App\Helpers\FormatHelper::formatSize($history->uploaded) }}</td>
                        <td>{{ App\Helpers\FormatHelper::formatSize($history->downloaded) }}</td>
                        <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $history->completed_at ? $history->completed_at->format('Y-m-d H:i:s') : 'Incomplete' }}</td>
                        <td>
                                @if ($history->completed_at)
                                  {{ $history->completed_at->diffForHumans($history->created_at, true) }}
                                @else
                                  Incomplete
                                @endif
                        </td>
                        <td>{{ $history->updated_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="d-flex justify-content-center mt-4">
        {{ $histories->links('pagination::bootstrap-5') }}
    </div>

</div>
@endsection
