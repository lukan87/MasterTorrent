@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Search Results</h2>

    @if($torrents->isEmpty())
        <div class="alert alert-warning">
            No torrents found for your search criteria.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($torrents as $torrent)
                        <tr>
                            <td>{{ $torrent->name }}</td>
                            <td>{{ $torrent->category->name }}</td>
                            <td>{{ $torrent->formatted_size }}</td>
                            <td>{{ $torrent->owner ? $torrent->owner->name : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('torrents.show', ['slug' => $torrent->slug]) }}" class="btn btn-primary btn-sm">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination links -->
        <div class="d-flex justify-content-center">
            {{ $torrents->links() }}
        </div>
    @endif
</div>
@endsection
