@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">{{ $category->name }}</h1>
    <p>{{ $category->description }}</p>

    {{-- Button to Create New Topic --}}
    <div class="mb-3">
        <a href="{{ route('forum.create', $category->id) }}" class="btn btn-primary">Create New Topic</a>
    </div>

    {{-- List Topics under this Category --}}
    <h4>Topics in this Category</h4>
    @if($topics->isEmpty())
        <p>No topics in this category yet.</p>
    @else
        <ul class="list-group">
            @foreach($topics as $topic)
                <li class="list-group-item">
                    <a href="{{ route('forum.show', $topic->id) }}">{{ $topic->title }}</a>
                    <span class="badge badge-secondary">Created by {{ $topic->user->name }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
