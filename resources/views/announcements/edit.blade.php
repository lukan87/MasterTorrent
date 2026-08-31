@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Announcement</h2>

    <form method="POST" action="{{ route('announcements.update', $announcement->id) }}">
        @csrf
        @method('PUT')

        <input type="text" name="title" class="form-control mb-2"
               value="{{ $announcement->title }}" required>

        <textarea name="body" class="form-control mb-2" rows="5" required>{{ $announcement->body }}</textarea>

        <select name="type" class="form-control mb-2">
            <option value="info" {{ $announcement->type == 'info' ? 'selected' : '' }}>Info</option>
            <option value="success" {{ $announcement->type == 'success' ? 'selected' : '' }}>Success</option>
            <option value="warning" {{ $announcement->type == 'warning' ? 'selected' : '' }}>Warning</option>
        </select>

        <input type="number" name="priority" class="form-control mb-2"
               value="{{ $announcement->priority }}">

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection