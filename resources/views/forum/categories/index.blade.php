@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Forum Categories</h1>

    <!-- Create New Category Button -->
    <a href="{{ route('forum.categories.create') }}" class="btn btn-primary mb-3">Create New Category</a>

    <!-- Loop through each category and display in an individual card -->
    <div class="row">
        @foreach ($categories as $category)
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $category->name }}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $category->description }}</p>

                    <!-- List of topics for the category -->
                    <h6>Topics</h6>
                    <ul class="list-unstyled">
                        @foreach ($category->topics as $topic)
                            <li>{{ $topic->title }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer text-muted">
                    <a href="{{ route('forum.categories.edit', $category->id) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                    <form action="{{ route('forum.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
