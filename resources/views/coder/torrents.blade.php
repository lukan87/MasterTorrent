@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Torrents</h1>

    <!-- Search Form -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search torrents...">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Seeders</th>
                    <th>Leechers</th>
                    <th>Times Completed</th>
                    <th>Thanks</th>
                    <th>Comments</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($torrents as $torrent)
                <tr>
                    <td>{{ $torrent->id }}</td>
                    <td>{{ $torrent->name }}</td>
                    <td>{{ $torrent->seeders }}</td>
                    <td>{{ $torrent->leechers }}</td>
                    <td>{{ $torrent->times_completed }}</td>
                    <td>{{ $torrent->thanks_count }}</td>
                    <td>{{ $torrent->comments_count }}</td>
                    <td>{{ $torrent->created_at->format('Y-m-d') }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#torrentModal{{ $torrent->id }}">
                            View Details
                        </button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="torrentModal{{ $torrent->id }}" tabindex="-1" aria-labelledby="torrentModalLabel{{ $torrent->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="torrentModalLabel{{ $torrent->id }}">Torrent Details: {{ $torrent->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h6>Comments</h6>
                                @if($torrent->comments_count > 0)
                                    <ul class="list-group mb-3">
                                        @foreach($torrent->comments as $comment)
                                            <li class="list-group-item">
                                                <strong>{{ $comment->user->name ?? 'User' }}:</strong> {{ $comment->comment }}
                                                <br><small class="text-muted">{{ $comment->created_at->format('Y-m-d H:i') }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No comments yet.</p>
                                @endif

                                <h6>Thanks</h6>
                                @if($torrent->thanks_count > 0)
                                    <ul class="list-group">
                                        @foreach($torrent->thanks as $thank)
                                            <li class="list-group-item">
                                                {{ $thank->user->name ?? 'User' }} 
                                                <small class="text-muted">{{ $thank->created_at->format('Y-m-d H:i') }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No thanks yet.</p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
