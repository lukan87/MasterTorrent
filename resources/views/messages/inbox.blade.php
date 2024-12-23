@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Inbox</h1>
    <a href="{{ route('messages.outbox') }}" class="btn btn-primary">Go to Outbox</a>


    @if($messages->isEmpty())
        <div class="alert alert-info">
            No messages have been received yet.
        </div>
    @else
        <div class="row">
            @foreach($messages as $message)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <a href="{{ route('messages.show', $message) }}" class="text-decoration-none">
                                <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                            </a>
                            @if(!$message->is_read)
                                <span class="badge bg-primary">New</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <p class="text-muted">{{ $message->sender->name }}</p>
                            <p class="card-text">{{ Str::limit($message->body, 100) }}</p>
                        </div>
                        <div class="card-footer text-muted text-end">
                            <small>Sent {{ $message->created_at->diffForHumans() }}</small>
                            <a href="{{ route('messages.show', $message) }}" class="btn btn-primary btn-sm ms-2">Read More</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
