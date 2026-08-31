@extends('layouts.app')

@section('content')
<h1>Create New Category</h1>

<form action="{{ route('categories.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Category Name</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" class="form-control" rows="6"></textarea>
    </div>

    <div class="mb-3">
        <label for="position" class="form-label">Position (order)</label>
        <input type="number" name="position" id="position" class="form-control" value="0">
    </div>

    <div class="mb-3">
        <label for="min_class_required" class="form-label">Minimum User Class Required</label>
        <select name="min_class_required" id="min_class_required" class="form-control" required>
            @foreach(\App\Models\UserClass::getClasses() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-success">Create Category</button>
</form>
@endsection
