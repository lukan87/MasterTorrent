@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Outbox</h1>

    @if($messages->isEmpty())
        <div class="alert alert-info">
            No messages have been sent yet.
        </div>
    @else
        <div class="row">
            @foreach($messages as $message)
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Sent to: {{ $message->receiver->name }}</p>
                            <p class="card-text">{{ Str::limit($message->body, 100) }}</p>
                        </div>
                        <div class="card-footer text-end">
                            <span class="badge bg-secondary">{{ $message->created_at->diffForHumans() }}</span>
                            <a href="{{ route('messages.show', $message) }}" class="btn btn-primary btn-sm ms-2">Read More</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
