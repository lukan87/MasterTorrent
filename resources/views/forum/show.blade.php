{{-- resources/views/forum/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('forum.index') }}">Forum</a></li>
            <li class="breadcrumb-item"><a href="{{ route('forum.show', $topic->id) }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Topic</li>
        </ol>
    </nav>

<div class="card mb-3 mt-5">
    <div class="card-header">
        <h4 class="my-0">{{ $topic->title }}</h4>
        <small class="text-muted">
            Posted by: {{ $topic->user->name }} - {{ $topic->created_at->diffForHumans() }}
        </small>
    </div>
    <div class="card-body">
        <p class="card-text">{!! convertCustomTagsToHtml($topic->content) !!}</p>
        <div class="d-flex justify-content-end">
            <!-- <a href="{{ route('forum.show', $topic->id) }}" class="btn btn-info btn-sm me-2">View</a> -->
                       @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::user()->id === $topic->user_id))
                                <a href="{{ route('topics.edit', $topic->id) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                        @endif
            <form action="{{ route('forum.destroy', $topic->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this topic?');">Delete</button>
            </form>
        </div>
    </div>
</div>


    <hr>

    <h3>Posts:</h3>
@foreach($topic->posts as $post)
    <!-- Check if this is a main post or a reply -->
    @if(is_null($post->parent_id)) 
        <!-- Main post (not a reply) -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>
                    <a href="{{ route('profile.show', ['id' => $post->user->id, 'name' => $post->user->name]) }}">
                        {{ $post->user->name }}
                    </a>
                    ( {{ $post->user->role_name }} )
                </strong> - {{ $post->created_at->diffForHumans() }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-1">
                        <p class="card-title">
                            <img src="{{ $post->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="User Avatar" style="width:100%">
                        </p>
                    </div>
                    <div class="col-md-11">
                        <p class="card-text">{!! convertCustomTagsToHtml($post->content) !!}</p>
                    </div>
                    <div class="d-flex justify-content-end">
                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::user()->id === $post->user_id))
                            <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                        @endif

                        @if(auth()->user() && auth()->user()->userHasPermission('delete_posts'))
                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        @endif

                        <!-- Reply Button -->
                        @if(Auth::check())
                            <a href="{{ route('posts.reply', $post->id) }}" class="btn btn-primary btn-sm ms-2">Reply</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

     <!-- Display replies -->
@foreach($post->replies as $reply)
    <div class="card mt-3 mb-3 ms-5 shadow-sm border-0 rounded"> <!-- Indented replies -->
        <div class="card-header">
            <strong><a href="{{ route('profile.show', ['id' => $reply->user->id, 'name' => $reply->user->name]) }}">{{ $reply->user->name }}</a></strong> - {{ $reply->created_at->diffForHumans() }}

            <!-- Check if this reply is to the owner's post -->
            @if($reply->parent_id === $post->id)
                <span class="badge bg-info text-dark ms-3">Replied to {{ $post->user->name }}'s post</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-1">
                    <p class="card-title">
                        <img src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="User Avatar" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                    </p>
                </div>
                <div class="col-md-11">
                    <p>{!! convertCustomTagsToHtml($reply->content) !!}</p>
                </div>
            </div>
        </div>

        <!-- Delete reply button for the owner or moderator -->
        <div class="d-flex justify-content-end">
            @if(Auth::check() && (Auth::user()->id === $reply->user_id || Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
                <div class="me-3"> <!-- margin-start (left) -->
                    <form action="{{ route('posts.destroyReply', $reply->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm mb-1">Delete Reply</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endforeach



    @endif
@endforeach





{{-- Form to add a new post --}}
@if(auth()->check())
    <div class="card mt-4">
        <div class="card-body">
            <h4 class="card-title">Your Reply</h4>

            {{-- Formatting Toolbar --}}
            <div class="mb-2">
                <!-- Font Size Dropdown with sizes from 1 to 5 -->
                <select id="fontSize" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="14">1 (Small)</option>
                    <option value="16">2 (Normal)</option>
                    <option value="18">3 (Medium)</option>
                    <option value="20">4 (Large)</option>
                    <option value="22">5 (Extra Large)</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('size', document.getElementById('fontSize').value)">Font Size</button>

                <!-- Font Color Dropdown -->
                <select id="fontColor" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="black">Black</option>
                    <option value="red">Red</option>
                    <option value="blue">Blue</option>
                    <option value="green">Green</option>
                    <option value="purple">Purple</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('color', document.getElementById('fontColor').value)">Font Color</button>

                <!-- Font Family Dropdown -->
                <select id="fontFamily" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Courier">Courier</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Times New Roman">Times New Roman</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('font', document.getElementById('fontFamily').value)">Font Family</button>

                <!-- Other Buttons -->
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">YouTube</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('center')">Center</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('quote')">Quote</button>
            </div>

            <form action="{{ route('posts.store', $topic->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="content" class="form-label">Reply</label>
                    <textarea name="content" id="content" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Reply</button>
            </form>
        </div>
    </div>
@else
    <p class="alert alert-warning">You must be logged in to reply to this topic.</p>
@endif



<script>
function insertBBCode(tag, option = null) {
    const textarea = document.getElementById("content");
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursorPosition = textarea.selectionStart; // Store current cursor position
    const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

    // Insert the BBCode tags with the selected text in the middle
    const newText = startTag + selectedText + endTag;
    textarea.value = textarea.value.substring(0, cursorPosition) + newText + textarea.value.substring(textarea.selectionEnd);

    // Set the cursor position in the middle of the tags, right after the opening tag
    const newCursorPosition = cursorPosition + startTag.length;
    textarea.selectionStart = newCursorPosition;
    textarea.selectionEnd = newCursorPosition;

    // Focus back on the textarea
    textarea.focus();
}
</script>



@endsection
