{{-- resources/views/forum/categories/create.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Create New Forum Category</h1>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Category creation form --}}
    <form action="{{ route('forum.categories.store' ) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Category Description</label>
            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Category</button>
        <a href="{{ route('forum.categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
