@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1>Edit Forum: {{ $forum->name }}</h1>

    <form method="POST" action="{{ route('forums.update', $forum->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Forum Name</label>
            <input type="text" name="name" value="{{ old('name', $forum->name) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $forum->description) }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_locked" class="form-check-input" value="1" {{ $forum->is_locked ? 'checked' : '' }}>
            <label class="form-check-label">Locked</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Forum</button>
    </form>
</div>
@endsection
