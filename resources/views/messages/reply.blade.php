{{-- resources/views/messages/reply.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Reply to: {{ $message->subject ?? '(No Subject)' }}</h1>

    <div class="card">
        <div class="card-header">
            Reply to Message
        </div>
        <div class="card-body">
            <p><strong>From:</strong> {{ $message->sender->name }}</p>

            <div class="message-body bg-light p-3 rounded border">
                <p class="font-italic text-dark">{{ $message->body }}</p>
            </div>

            <form action="{{ route('messages.storeReply', $message) }}" method="POST" class="mt-4">
                @csrf
                <div class="form-group">
                    <label for="body">Your Reply:</label>
                    <textarea name="body" id="body" rows="4" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Send Reply</button>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <form action="{{ route('messages.destroy', $message) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Message</button>
        </form>
    </div>
</div>
@endsection
