@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Outbox</h1>

    @if($messages->isEmpty())
        <div class="alert alert-info">
            No messages have been sent yet.
        </div>
    @else
        <div class="list-group">
            @foreach($messages as $message)
                <a href="{{ route('messages.show', $message) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $message->subject ?? '(No Subject)' }}</strong><br>
                        <small>Sent to: {{ $message->receiver->name }}</small>
                    </div>
                    <span class="badge badge-secondary">{{ $message->created_at->diffForHumans() }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
