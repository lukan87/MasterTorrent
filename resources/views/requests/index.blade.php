@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Torrent Requests</h1>

    <div class="mb-3">
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Request
        </a>
    </div>

    @if ($requests->count())
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Requested by</th>
                            <th>IMDB URL</th>
                            <th>Filled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td>{{ $request->category->name }}</td>
                                <td>{{ $request->name }}</td>
                                <td>{{ $request->requester->name ?? 'Unknown' }} on {{ $request->created_at ?? 'Unknown' }}</td>

                                <td>
                                    @if ($request->imdb_url)
                                        <a href="{{ $request->imdb_url }}" target="_blank" class="text-decoration-none">IMDB</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if ($request->filled === 'yes')
                                        <i class="bi bi-check-circle text-success"></i> Filled by  {{ $request->filledBy->name ?? 'Unknown' }}
                                    @else
                                        <i class="bi bi-x-circle text-danger"></i> Not Filled
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('requests.show', $request->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View Request">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('requests.edit', $request->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit Request">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('requests.destroy', $request->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?')">
                                            <i class="bi bi-trash"  data-bs-toggle="tooltip" title="Delete Request"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <p>No torrent requests found.</p>
    @endif
</div>
@endsection
