@extends('layouts.app')

@section('content')


    <!-- Topic Details Section -->
    <div class="card mb-4 mt-3">
        <div class="card-header">
            <h1 class="display-4 mb-0">{{ $topic->title }}</h1>
        </div>
        <div class="card-body">
            <p class="lead mb-0">{!! convertCustomTagsToHtml($topic->body) !!}</p>
        </div>
    </div>
    <hr>

    <!-- Posts Section -->
    <div class="mb-4">
        <h3>Posts</h3>
        @forelse($posts as $post)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <!-- User Avatar -->
                    <div class="col-12 col-sm-3 col-xxl-1 order-0 order-sm-1 order-xxl-0">
                        <div class="user-avatar">
                            @if($post->user->profile_image)
                                <img 
                                    src="{{ $post->user->profile_image }}" 
                                    alt="{{ $post->user->name }}" 
                                    class="rounded" 
                                    style="width: 100px; height: 100px;"
                                >
                            @else
                                <div class="placeholder-avatar">
                                    {{ strtoupper(substr($post->user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                
                    <!-- Post Content -->
                    <div class="col-sm-9 col-xxl-11 order-1 order-sm-0 order-xxl-1">
                        <small class="card-header text-muted">
                            <a href="{{ route('profile.show', $post->user->id) }}" class="text-decoration-none">
            {{ $post->user->name }}
        </a>
                            posted on {{ $post->created_at->format('F j, Y \a\t h:i A') }}
                        </small>
                        <p class="mb-1"><b>{!! convertCustomTagsToHtml($post->content) !!}</b></p>

                         <!-- Actions for Edit/Delete -->
                         <div class="mt-3">
                            @if(Auth::check())
                            <div class="float-end">
                                @if(Auth::id() === $post->user_id || Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                                    <!-- Edit Button -->
                                    <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit post">
                                        <i class="bi bi-pencil"></i> 
                                    </a>
                                @endif
                                
                                @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                                    <!-- Delete Button -->
                                    <form 
                                        action="{{ route('posts.destroy', $post->id) }}" 
                                        method="POST" 
                                        class="d-inline-block" 
                                        onsubmit="return confirm('Are you sure you want to delete this post?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete post">
                                            <i class="bi bi-trash"></i> 
                                        </button>
                                    </form>
                                @endif
                            </div>
                            @endif
                        </div>
                        
                        <!-- Reply Form -->
                        <button class="btn btn-secondary btn-sm text-decoration-none mt-2" data-bs-toggle="collapse" data-bs-target="#reply-form-{{ $post->id }}">
                        <i class="bi bi-reply" data-bs-toggle="tooltip" title="Reply to post"> Reply</i> 
                        </button>

                        <div class="collapse mt-3" id="reply-form-{{ $post->id }}">
                            <form action="{{ route('posts.reply', $post->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <textarea name="content" class="form-control" rows="3" placeholder="Write your reply..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm mt-2">Submit Reply</button>
                            </form>
                        </div>

                        @if($post->replies->count() > 0)
    <div class="mt-4 ps-4 border-start">
        <h6>Replies:</h6>
        @foreach($post->replies->sortByDesc('created_at') as $reply)
            <div class="mb-2">
                <small><i>
                    <strong>
                        <a href="{{ route('profile.show', $reply->user->id) }}" class="text-decoration-none">
                            {{ $reply->user->name }}
                        </a>
                    </strong> 
                    replied on {{ $reply->created_at->format('F j, Y \a\t h:i A') }}
                </i></small>
                <p class="mb-1">{!! convertCustomTagsToHtml($reply->content) !!}</p>

                <!-- Actions for Edit/Delete -->
                @if(Auth::check())
                    <div class="mt-2">
                        <!-- Edit Button (only for reply owner) -->
                        @if(Auth::id() === $reply->user_id || (Auth::user()->user_class > \App\Models\UserClass::MODERATOR))
                            <a href="{{ route('posts.edit', $reply->id) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif

                        <!-- Delete Button (only for MODERATOR or higher) -->
                        @if(Auth::user()->user_class > \App\Models\UserClass::MODERATOR)
                            <form action="{{ route('posts.destroy', $reply->id) }}" method="POST" class="d-inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete this reply?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

                    
                    </div>
                </div>
            </div>
        </div>
        @empty
            <p class="text-muted">No posts yet. Be the first to comment!</p>
        @endforelse
    </div>

   <!-- Pagination Links -->
<div class="d-flex justify-content-center">
    {{ $posts->links('pagination::bootstrap-5') }}
</div>

    <!-- Add a New Post Form -->
    <div class="card">
        <div class="card-header">
            <h4>Add a Post</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('posts.store', $topic->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="content">Your Comment</label>
                    <textarea name="content" id="content" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Submit Post</button>
            </form>
        </div>
    </div>

    <br>
    <a href="{{ route('topics.show', ['forumId' => $topic->forum->id, 'topicId' => $topic->id]) }}" class="btn btn-secondary mt-4">
        Back to Topic: {{ $topic->title }}
    </a>


<style>
    .user-avatar {
        width: 100px;
        height: 100px;
    }
    
    .placeholder-avatar {
        width: 100px;
        height: 100px;
        background-color: #6c757d; /* Gray background */
        color: #fff; /* White text */
        font-size: 36px;
        font-weight: bold;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        text-transform: uppercase;
        overflow: hidden;
    }
</style>
@endsection
