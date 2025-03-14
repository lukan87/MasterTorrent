@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Topics in {{ $forum->name }}</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <ul class="list-group">
        @foreach($topics as $topic)
            <li class="list-group-item">
                <a href="{{ route('topics.show', [$forum->id, $topic->id]) }}" class="text-decoration-none">
                    {{ $topic->title }}
                </a>
            </li>
        @endforeach
    </ul>
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
    <div class="mt-4">
        <a href="{{ route('topics.create', $forum->id) }}" class="btn btn-primary">Create New Topic</a>
    </div>
    @endif
</div>
@endsection
