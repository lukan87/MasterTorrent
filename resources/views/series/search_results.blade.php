@extends('layouts.app')

@section('content')

    <h1>Select Series</h1>

    <!-- Error Message Display -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('series.bulkSelect') }}" method="POST">
        @csrf
        <div class="list-group">
            @if($series->isNotEmpty()) <!-- Check if series collection is not empty -->
                @foreach($series as $serie)

                @php
                        // Check if the movie already exists in the database
                        $existingSeries = \App\Models\Series::where('tmdb_id', $serie['id'])->first();
                    @endphp

                  @if(!empty($serie['poster_path']))
                     <div class="row mb-2">
                        <div class="col-md-1">
                            <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $serie['poster_path'] }}" width="100%">
                        </div>
                        <div class="list-group-item col-md-11">
                            <h5>{{ $serie['name'] }}</h5> <!-- Updated to use 'name' for series -->
                            <p>{{ $serie['first_air_date'] }}</p> <!-- Changed to 'first_air_date' for series -->
                            <p>{{ $serie['overview'] }}</p>
                            @if($existingSeries)
                            <span class="text-success" data-bs-toggle="tooltip" data-bs-title="Series already in database">Already in database <i class="bi bi-check2-circle"></i></span>
                                @else
                            <input type="checkbox" name="series[]" value="{{ $serie['id'] }}" id="series-{{ $serie['id'] }}"> <!-- Updated input name -->
                            <label for="series-{{ $serie['id'] }}">Select</label>
                            @endif
                        </div>
                     </div>
                   @endif
                @endforeach
            @else
                <div class="alert alert-warning" role="alert">
                    No series found. Please try a different search. <!-- Updated alert message -->
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-success">Add Selected Series to Database</button> <!-- Updated button text -->
    </form>

@endsection
