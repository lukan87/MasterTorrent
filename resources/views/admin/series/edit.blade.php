@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Series: {{ $series->name }}</h1>
    <form action="{{ route('admin.series.update', $series->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $series->name }}">
        </div>

        <div class="form-group">
            <label for="poster_path">Poster Path</label>
            <input type="text" name="poster_path" class="form-control" value="{{ $series->poster_path }}">
        </div>

        <div class="form-group">
            <label for="overview">Overview</label>
            <textarea name="overview" class="form-control">{{ $series->overview }}</textarea>
        </div>

        <div class="form-group">
            <label for="backdrop_path">Backdrop Path</label>
            <input type="text" name="backdrop_path" class="form-control" value="{{ $series->backdrop_path }}">
        </div>

        <button type="submit" class="btn btn-success">Update Series</button>
    </form>
    </div>
@endsection
