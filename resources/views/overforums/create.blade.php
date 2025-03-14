@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Overforum</h1>

    <form action="{{ route('overforums.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Overforum Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Overforum</button>
    </form>
</div>
@endsection
