@extends('layouts.app')

@section('content')

<form action="{{ route('posts.storeReply', $post->id) }}" method="POST">
    @csrf
    <textarea name="content" rows="4" class="form-control" placeholder="Write your reply..."></textarea>

    <!-- Display validation error for the content field -->
    @if($errors->has('content'))
        <div class="alert alert-danger mt-2">
            <strong>{{ $errors->first('content') }}</strong>
        </div>
    @endif

    <button type="submit" class="btn btn-success mt-2">Submit Reply</button>
</form>

@endsection
