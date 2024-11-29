<!-- resources/views/shoutbox/reply.blade.php -->

@extends('layout.default') {{-- Assuming you have a main layout file --}}



@section('content')
    <h2>Reply to Shout</h2>

    <p>Original Message:</p>
    <p>{{ $parentMessage->message }}</p>
    <div class="container">
        <div class="row" style="margin: auto">
        <div class="chatbox__textarea-container form__group">
        <form action="{{ route('shoutbox.reply', $parentMessage->id) }}" method="post">
            @csrf
            <label for="content">Your Reply:</label>

            <textarea class="chatbox__textarea form__textarea mb-3" name="content" id="content" required></textarea><br>
            <center><button class="btn btn-success" type="submit">Reply</button></center>
        </form>
        </div>
        </div>
        </div>
@endsection
