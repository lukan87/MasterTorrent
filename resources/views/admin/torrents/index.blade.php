@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Torrents</h1>

    <table class="table table-striped">
        <thead>
            <tr>

                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($torrents as $torrent)
                <tr>
                    <td><a href={{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}>{{ $torrent->name }}</a>
                <br>Seeders: {{ $torrent->seeders}} / Leechers: {{ $torrent->leechers}} / Times Completed: {{ $torrent->times_completed}}

                    </td>


                    <td>
                        <a href="{{ route('admin.torrents.show', $torrent->id) }}" class="btn btn-info btn-sm">Show Torrent Info</a>
                        <!-- <a href="{{ route('admin.torrents.edit', $torrent->id) }}" class="btn btn-warning btn-sm">Edit</a> -->
                        <form action="{{ route('admin.torrents.destroy', $torrent->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>
@endsection
