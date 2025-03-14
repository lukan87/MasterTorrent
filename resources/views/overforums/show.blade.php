@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ $overforum->name }}</h1>

    <!-- Display Overforum Description -->
    <div class="mb-4">
        <strong>Description:</strong>
        <p>{{ $overforum->description }}</p>
    </div>

    <!-- List of Forums in the Overforum -->
    <h2>Forums:</h2>
    @if($overforum->forums->count() > 0)
        <ul class="list-group">
            @foreach($overforum->forums as $forum)
                <li class="list-group-item">
                    <a href="{{ route('forums.show', [$overforum->id, $forum->id]) }}" class="text-decoration-none">
                        {{ $forum->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p>No forums available in this overforum.</p>
    @endif

    <!-- Link to Create a New Forum -->
    <div class="mt-4">
        <a href="{{ route('forums.create', $overforum->id) }}" class="btn btn-primary">Create New Forum</a>
    </div>
</div>
@endsection
