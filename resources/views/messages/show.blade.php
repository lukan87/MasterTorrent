@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">{{ $message->subject ?? '(No Subject)' }}</h1>

    <div class="card">
        <div class="card-header">
            Message Details
        </div>
        <div class="card-body">
            <p><strong>From:</strong> {{ $message->sender->name }}</p>

            <!-- Message Body with Styling -->
            <div class="message-body bg-light p-3 rounded border">
                <p class="font-italic text-dark">{{ $message->body }}</p>
            </div>
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
