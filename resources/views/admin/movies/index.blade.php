@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Movies</h1>

    <table class="table table-striped">
        <thead>
            <tr>

                <th>Title</th>

                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movies as $movie)
                <tr>
                    <td><a href={{ route('movies.show', $movie->id) }}>{{ $movie->name }}</a></td>


                    <td>
                        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
@endsection
