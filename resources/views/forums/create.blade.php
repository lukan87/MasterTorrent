@extends('layouts.app')

@section('content')
<h1>Create Forum in "{{ $category->name }}"</h1>

<form action="{{ route('forums.store', $category->id) }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Forum Name</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" class="form-control"></textarea>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="is_locked" id="is_locked" class="form-check-input">
        <label for="is_locked" class="form-check-label">Locked</label>
    </div>

    <button type="submit" class="btn btn-primary">Create Forum</button>
</form>
@endsection
