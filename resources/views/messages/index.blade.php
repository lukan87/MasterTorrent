@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Messages</h1>

    <div class="row">
        <!-- Inbox Section -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="mb-0">Inbox</h2>
                    <a href="{{ route('messages.inbox') }}" class="btn btn-link p-0">View All Inbox Messages</a>
                </div>
                <div class="card-body">
                    @if($inboxMessages->isEmpty())
                        <p>No messages in your inbox.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($inboxMessages as $message)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('messages.show', $message) }}" class="text-decoration-none">
                                        <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                                        - from {{ $message->sender->name }}
                                    </a>
                                    @if(!$message->is_read)
                                        <span class="badge bg-success">New</span>
                                    @endif
                                    <p class="small text-muted">{{ $message->created_at->diffForHumans() }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Outbox Section -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="mb-0">Outbox</h2>
                    <a href="{{ route('messages.outbox') }}" class="btn btn-link p-0">View All Outbox Messages</a>
                </div>
                <div class="card-body">
                    @if($outboxMessages->isEmpty())
                        <p>No messages in your outbox.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($outboxMessages as $message)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('messages.show', $message) }}" class="text-decoration-none">
                                        <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                                        - to {{ $message->receiver->name }}
                                    </a>
                                    <p class="small text-muted">{{ $message->created_at->diffForHumans() }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
