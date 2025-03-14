@extends('layouts.app')

@section('content')
<form action="{{ route('forums.store', $overforum->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Forum Name:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Forum Description:</label>
        <textarea id="description" name="description" class="form-control"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Create Forum</button>
</form>

@endsection
