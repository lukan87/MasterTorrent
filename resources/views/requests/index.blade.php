@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Torrent Requests</h1>

    <div class="alert alert-warning mb-4" role="alert">
    Cererile completate incorect vor fi șterse. Completați toate câmpurile!
</div>

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

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td>{{ $request->category->name ?? 'No Category'}}</td>
                                <td><a href="{{ route('requests.show', $request->id) }}" data-bs-toggle="tooltip" title="View Request">{{ $request->name }}</a></td>
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

    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::user()->name === $request->requester->name))
    <td>
    <div class="d-flex justify-content-start gap-2">
        <a href="{{ route('requests.edit', $request->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit Request">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
        <form action="{{ route('requests.destroy', $request->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?')">
                <i class="bi bi-trash" data-bs-toggle="tooltip" title="Delete Request"></i>
            </button>
        </form>
    </div>
    </td>
    @endif


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
