{{-- resources/views/forum/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Topic</h1>

    <form action="{{ route('forum.store', $category->id) }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea name="content" id="content" class="form-control" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Create Topic</button>
</form>


</div>
@endsection
