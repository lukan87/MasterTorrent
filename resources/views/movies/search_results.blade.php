@extends('layouts.app')

@section('content')

    <h1>Select Movies</h1>

    <!-- Error Message Display -->
    @if (Session::has('message'))
<script>
    swal("Message","{{ Session::get('message') }}" ,'success',{
        button:true,
        button:"OK",

    });
</script>
@endif
    <form action="{{ route('movies.bulkSelect') }}" method="POST">
        @csrf
        <div class="list-group">
            @if($movies->isNotEmpty()) <!-- Check if movies collection is not empty -->
                @foreach($movies as $movie)
                    @php
                        // Check if the movie already exists in the database
                        $existingMovie = \App\Models\Movie::where('tmdb_id', $movie['id'])->first();
                    @endphp
                                       <!-- Check if poster_path exists before displaying the movie -->
                    @if(!empty($movie['poster_path']))
                        <div class="row mb-2">
                            <div class="col-md-1">
                                <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $movie['poster_path'] }}" width="100%">
                            </div>
                            <div class="list-group-item col-md-11">
                                <h5>{{ $movie['title'] }}</h5>
                                <p>{{ $movie['release_date'] }}</p>
                                <p>{{ $movie['overview'] }}</p>

                                @if($existingMovie)
                                    <span class="text-success" data-bs-toggle="tooltip" data-bs-title="Movie already in database">Already in database <i class="bi bi-check2-circle"></i></span>
                                @else
                                    <input type="checkbox" name="movies[]" value="{{ $movie['id'] }}" id="movie-{{ $movie['id'] }}">
                                    <label for="movie-{{ $movie['id'] }}">Select</label>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="alert alert-warning" role="alert">
                    No movies found. Please try a different search.
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-success">Add Selected Movies to Database</button>
    </form>

@endsection
