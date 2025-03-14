@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create a New Topic in {{ $forum->name }}</h1>

    <form action="{{ route('topics.store', $forum->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Topic Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="body" class="form-label">Topic Body:</label>
            <textarea id="body" name="body" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Topic</button>
    </form>

    <br>
    <a href="{{ route('topics.index', $forum->id) }}" class="btn btn-secondary">Back to Topics</a>
</div>
@endsection
