<!-- resources/views/profile/torrents.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Torrents Uploaded by {{ $user->name }}<a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="btn btn-secondary btn-sm">Back to Profile</a>
                </div>

                <div class="card-body">
                    @if($torrents->isEmpty())
                        <p>No torrents uploaded by this user.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Seeders</th>
                                    <th>Leechers</th>
                                    <th>Times Completed</th>
                                    <th>Uploaded At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($torrents as $torrent)
                                    <tr>
                                        <td><a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}">{{ $torrent->name }}</a></td>
                                        <td>{{ $torrent->seeders }}</td>
                                        <td>{{ $torrent->leechers }}</td>
                                        <td>{{ $torrent->times_completed }}</td>
                                        <td>{{ $torrent->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
