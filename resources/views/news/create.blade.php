@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create News Article</h1>

    <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" id="title" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" class="form-control" id="content" rows="5" required></textarea>
        </div>

        <!-- <div class="mb-3">
            <label for="image" class="form-label">Image (optional)</label>
            <input type="file" name="image" class="form-control" id="image" accept="image/*">
        </div> -->

        <button type="submit" class="btn btn-primary">Publish News</button>
    </form>
</div>
@endsection
