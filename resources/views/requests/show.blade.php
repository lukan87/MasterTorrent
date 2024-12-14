@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">{{ $request->name }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Request for :: {{ $request->name }}</strong>
        </div>
        <div class="card-body">
<div class="row">
    <div class="col-md-3">
        @if ($request->image)
                <div class="mb-3">
                    <img src="{{ $request->image }}" alt="{{ $request->name }}" class="img-fluid" width="200" />
                </div>
            @endif
            </div>
            <div class="col-md-9">
            @if ($request->category)
                <div class="mb-3">
                    <strong>Category:</strong> {{ $request->category->name }}
                </div>
            @endif

            @if ($request->imdb_url)
                <div class="mb-3">
                    <strong>IMDB URL:</strong>
                    <a href="{{ $request->imdb_url }}" target="_blank" class="text-decoration-none">{{ $request->imdb_url }}</a>
                </div>
            @endif

            @if ($request->tmdb_url)
                <div class="mb-3">
                    <strong>TMDB URL:</strong>
                    <a href="{{ $request->tmdb_url }}" target="_blank" class="text-decoration-none">{{ $request->tmdb_url }}</a>
                </div>
            @endif

            @if ($request->steam_url)
                <div class="mb-3">
                    <strong>Steam URL:</strong>
                    <a href="{{ $request->steam_url }}" target="_blank" class="text-decoration-none">{{ $request->steam_url }}</a>
                </div>
            @endif

            @if ($request->description)
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p>{{ $request->description }}</p>
                </div>
            @endif
        </div>
        </div>
    </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Filled Status</strong>
        </div>
        <div class="card-body">
            @if ($request->filled === 'yes')
                <i class="bi bi-check-circle text-success"></i> <strong>Filled by
                    @if($request->filledBy)
                        {{ $request->filledBy->name ?? 'Unknown'}} on {{ $request->updated_at }}
                     @else
                      Unknown
                    @endif

                </strong>
                <br>
                @if ($request->link)
                    <strong>Link:</strong>
                    <a href="{{ $request->link }}" target="_blank" class="text-decoration-none">{{ $request->name }}</a>
                @endif
            @else
                <i class="bi bi-x-circle text-danger"></i> <strong>Not Filled</strong>
            @endif
        </div>
    </div>

    @if ($request->filled === 'no')
        <!-- Show the form to fill the request -->
        <div class="card mb-4">
            <div class="card-header">
                <strong>Fill This Request</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('requests.fill', $request->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="link" class="form-label">Enter the torrent link to  fill the request</label>
                        <input type="text" class="form-control" name="link" id="link" required>
                    </div>
                    <button type="submit" class="btn btn-success">Fill Request</button>
                </form>
            </div>
        </div>
    @else

    @endif

    <a href="{{ route('requests.index') }}" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Back to Requests
    </a>
</div>
@endsection
