@extends('layouts.app')

@section('content')


    <!-- Post content displayed in a Bootstrap card -->
    <div class="card mb-4 mt-5">
        <div class="card-header">
            <strong>{{ $topicName }}</strong>

             <!-- Only show edit and delete buttons if the current user is the post owner -->

               <!-- Only show edit and delete buttons if the current user is the post owner -->
            @if(Auth::check() && Auth::user()->id === $posts->first()->user_id)
                <div class="float-end">
                    <!-- Edit Button -->
                    <a href="{{ route('posts.edit', $posts->first()->id) }}" class="btn btn-warning btn-sm">Edit</a>

                    <!-- Delete Button (with confirmation) -->
                    <form action="{{ route('posts.destroy', $posts->first()->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            @endif

        </div>


        <div class="card-body">
            <p class="card-text">{!! convertCustomTagsToHtml( $posts[0]->content ) !!}</p> <!-- Display the post content -->
        </div>
    </div>

   <!-- Display all replies to the post -->
<!-- Display all replies to the post -->
<div class="replies">
    @foreach($posts[0]->replies as $reply)
        <div class="card mb-3 ms-4">
            <div class="card-header">
                <strong>{{ $reply->user->name }}</strong> <!-- Display user name -->
                <small class="text-muted">Posted on: {{ $reply->created_at->format('F j, Y \a\t g:i A') }}</small> <!-- Display creation date and time -->

                <!-- Only show Edit and Delete buttons if the current user is the reply owner -->
                @if(Auth::check() && Auth::user()->id === $reply->user_id)
                    <div class="float-end">
                        <!-- Edit Button -->
                        <a href="{{ route('posts.edit', $reply->id) }}" class="btn btn-warning btn-sm">Edit</a>

                        <!-- Delete Button (with confirmation) -->
                        <form action="{{ route('posts.destroy', $reply->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                @endif

            </div>
            <div class="card-body">
                <p class="card-text">{!! convertCustomTagsToHtml( $reply->content ) !!}</p>
                <!-- Reply Button -->
                <div class="float-end"> <a href="{{ route('posts.reply', $reply->id) }}" class="btn btn-info btn-sm me-3">Reply</a> <!-- Added margin-right for space --></div><!-- Display the reply content -->
            </div>
        </div>

        <!-- Display replies to the reply -->
@if($reply->replies->count())
    <div class="ms-5">
        @foreach($reply->replies as $nestedReply)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>{{ $nestedReply->user->name }}</strong> <!-- Display user name -->
                    <small class="text-muted">Posted on: {{ $nestedReply->created_at->format('F j, Y \a\t g:i A') }}</small> <!-- Display creation date and time -->

                    <!-- Only show Edit and Delete buttons if the current user is the reply owner -->
                    @if(Auth::check() && Auth::user()->id === $nestedReply->user_id)
                        <div class="float-end">
                            <!-- Edit Button -->
                            <a href="{{ route('posts.edit', $nestedReply->id) }}" class="btn btn-warning btn-sm">Edit</a>

                            <!-- Delete Button (with confirmation) -->
                            <form action="{{ route('posts.destroy', $nestedReply->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <p class="card-text">{!! convertCustomTagsToHtml( $nestedReply->content ) !!}</p> <!-- Display the nested reply content -->
                </div>
            </div>
        @endforeach
    </div>
@endif


    @endforeach
</div>




    <!-- Reply Form displayed at the bottom of the post and replies -->
    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="content" class="form-label">Reply to Post</label>
            <textarea class="form-control" id="content" name="content" required></textarea>
        </div>
        <input type="hidden" name="topic_id" value="{{ $topicId }}">
        <input type="hidden" name="parent_post_id" value="{{ $posts[0]->id }}"> <!-- Assuming the post is the first item -->
        <button type="submit" class="btn btn-primary">Post Reply</button>
    </form>
@endsection
