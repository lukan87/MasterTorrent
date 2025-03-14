@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Overforum</h1>

    <form action="{{ route('overforums.update', $overforum->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $overforum->name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control">{{ old('description', $overforum->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Overforum</button>
        <a href="{{ route('overforums.index') }}" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
