@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">LastFiles Forums</h1>

    <div class="row">
        @foreach($overforums as $overforum)
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex flex-column align-items-start">
                        <!-- Overforum Name and Description on the Left -->
                        <h5 class="card-title mb-2">
                            {{ $overforum->name }}  
                            <small class="text-muted"> - {{ $overforum->description }}</small>
                        </h5>

                       
<div class="d-flex justify-content-start align-items-center">
    <!-- Edit Button -->
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))
    <a href="{{ route('overforums.edit', $overforum->id) }}" class="btn btn-warning btn-sm mr-3">
        <i class="bi bi-pencil"></i>
    </a>
@endif
 @if (Auth::check() && (Auth::user()->user_class == \App\Models\UserClass::OWNER))
    <!-- Delete Button -->
    <form action="{{ route('overforums.destroy', $overforum->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this overforum?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="bi bi-trash"></i>
        </button>
    </form>
    @endif
</div>
                     


                    </div>
                    
                    
                                      
                    
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Forums:</h6>
                        <ul class="list-group">
                            @foreach($overforum->forums as $forum)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('forums.show', [$overforum->id, $forum->id]) }}" class="text-decoration-none">
                                            {{ $forum->name }}
                                        </a>
                                        <!-- Latest Post with Topic Name -->
                                        @if($forum->topics->count() > 0)
                                            @php
                                                $latestPost = $forum->topics->flatMap->posts->sortByDesc('created_at')->first();
                                                $latestTopic = $latestPost ? $latestPost->topic : null;
                                            @endphp
                                            @if($latestPost)
                                                <small class="d-block text-muted mt-1">
                                                    Latest Post in 
                                                    <strong>{{ $latestTopic->title }}</strong> by 
                                                    <strong>{{ $latestPost->user->name }}</strong> 
                                                    {{ $latestPost->created_at->diffForHumans() }}
                                                </small>
                                            @endif
                                        @endif
                                    </div>
                                    <span class="badge badge-info">
                                        {{ $forum->topics->count() }} Topics
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
                        <a href="{{ route('forums.create', $overforum->id) }}" class="btn btn-success btn-sm">Create New Forum</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))
    <div class="mt-4">
        <a href="{{ route('overforums.create') }}" class="btn btn-primary">Create New Overforum</a>
    </div>
    @endif
</div>
@endsection
