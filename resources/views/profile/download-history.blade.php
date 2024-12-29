@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">{{ $user->name }}'s Download History</h1>
                <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="btn btn-outline-secondary btn-sm">Back to Profile</a>
            </div>

            @if($downloadHistory->isEmpty())
                <div class="alert alert-info text-center" role="alert">
                    No download history available.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Torrent</th>
                                <th scope="col">Downloaded</th>
                                <th scope="col">Uploaded</th>
                                <th scope="col">Seed Time</th>
                                <th scope="col">Seeder</th>
                                <th scope="col">Completed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result as $history)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $history['torrent'] ? $history['torrent']->name : 'Deleted Torrent' }}
                                        </strong>
                                    </td>
                                    <td>{{ \App\Helpers\FormatHelper::formatSize($history['downloaded']) ?? '0' }}</td>
                                    <td>{{ \App\Helpers\FormatHelper::formatSize($history['uploaded']) ?? '0' }}</td>
                                    <td>{{ $history['seedtime'] ? gmdate('H:i:s', $history['seedtime']) : 'N/A' }}</td>
                                    <td>
    <span class="{{ $history['seeder'] === 1 ? 'text-success' : 'text-danger' }}">
        {{ $history['seeder'] === 1 ? 'Yes' : 'No' }}
    </span>
</td>
                                    <td>{{ $history['completed_at'] ? $history['completed_at']->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $downloadHistory->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
