@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Forums</h1>

    <div class="row">
        @foreach ($categories as $category)
            <div class="col-md-12 mb-4">
                <!-- Card for Category -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ $category->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $category->description }}</p>

                        <h5>Topics in this Category</h5>
                        @if($category->topics->isEmpty())
                            <p>No topics yet.</p>
                        @else
                            <ul class="list-group">
                                @foreach ($category->topics as $topic)
                                    <li class="list-group-item">
                                        <h4><a href="{{ route('forum.show', $topic->id) }}">{{ $topic->title }}</a><br></h4>
                                        <h5><span class="badge badge-info">Posted on {{ $topic->created_at->format('F j, Y, g:i a') }} by {{ $topic->user->name }}</span></h5>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('forum.create', $category->id) }}" class="btn btn-primary">Create a Topic</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
