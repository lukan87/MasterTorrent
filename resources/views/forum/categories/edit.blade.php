@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Category: {{ $category->name }}</h1>

    <form action="{{ route('forum.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $category->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Category</button>
    </form>
</div>
@endsection
