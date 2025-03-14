@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Forums in {{ $overforum->name }}</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <ul class="list-group mb-4">
        @foreach($forums as $forum)
            <li class="list-group-item">
                <a href="{{ route('forums.show', [$overforum->id, $forum->id]) }}" class="text-decoration-none">
                    {{ $forum->name }}
                </a>
            </li>
        @endforeach
    </ul>
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
    <div>
        <a href="{{ route('forums.create', $overforum->id) }}" class="btn btn-primary">Create New Forum</a>
    </div>
    @endif
</div>
@endsection
