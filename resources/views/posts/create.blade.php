@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Create a Post in {{ $topic->title }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('posts.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" required></textarea>
                </div>
                <!-- Hidden input for topic_id -->
                <input type="hidden" name="topic_id" value="{{ $topic->id }}">

                <button type="submit" class="btn btn-primary">Create Post</button>
            </form>
        </div>
    </div>
@endsection
