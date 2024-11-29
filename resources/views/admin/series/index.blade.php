@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Series</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>

                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($series as $serie)
                <tr>
                    <td>{{ $serie->id }}</td>
                    <td>{{ $serie->name }}</td>

                    <td>
                        <a href="{{ route('admin.series.edit', $serie->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.series.destroy', $serie->id) }}" method="POST" style="display:inline;">
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
