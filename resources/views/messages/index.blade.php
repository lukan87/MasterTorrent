@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="fw-bold text-white mb-4"><i class="bi bi-chat-left-dots-fill me-2"></i>Messages</h1>

    <div class="row">
        <!-- Inbox Section -->
        <div class="col-md-6 mb-4">
            <div class="card message-card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 text-white"><i class="bi bi-inbox-fill me-2"></i>Inbox</h2>
                    <a href="{{ route('messages.inbox') }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-box-arrow-in-down me-1"></i>View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($inboxMessages->isEmpty())
                        <div class="p-3 text-muted">No messages in your inbox.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($inboxMessages as $message)
                                <li class="list-group-item message-item {{ !$message->is_read ? 'unread-message' : '' }}">
                                    <a href="{{ route('messages.show', $message) }}" class="d-block text-decoration-none text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                                                <span class="text-muted"> – from {{ $message->sender->name }}</span>
                                            </div>
                                            <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if($inboxMessages->count() >= 10)
                            <div class="text-end p-2 border-top">
                                <a href="{{ route('messages.inbox') }}" class="text-decoration-none text-info">
                                    <i class="bi bi-arrow-right-circle me-1"></i> You have more messages...
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Outbox Section -->
        <div class="col-md-6 mb-4">
            <div class="card message-card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 text-white"><i class="bi bi-send-fill me-2"></i>Outbox</h2>
                    <a href="{{ route('messages.outbox') }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-box-arrow-up me-1"></i>View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($outboxMessages->isEmpty())
                        <div class="p-3 text-muted">No messages in your outbox.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($outboxMessages as $message)
                                <li class="list-group-item message-item">
                                    <a href="{{ route('messages.show', $message) }}" class="d-block text-decoration-none text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $message->subject ?? '(No Subject)' }}</strong>
                                                <span class="text-muted"> – to {{ $message->receiver->name }}</span>
                                            </div>
                                            <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if($outboxMessages->count() >= 10)
                            <div class="text-end p-2 border-top">
                                <a href="{{ route('messages.outbox') }}" class="text-decoration-none text-info">
                                    <i class="bi bi-arrow-right-circle me-1"></i> You have more messages...
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background-color: #121212;
}

.message-card {
    background-color: #1c1c1c;
    border-radius: 12px;
    overflow: hidden;
    border: none;
}

.message-card .card-header {
    background: linear-gradient(90deg, #2c2c2c, #3a3a3a);
    border-bottom: 1px solid #2c2c2c;
}

.message-item {
    background-color: transparent;
    border-bottom: 1px solid #2a2a2a;
    transition: all 0.2s ease-in-out;
    padding: 0.75rem 1rem;
}

.message-item:hover {
    background-color: #2a2a2a;
    cursor: pointer;
}

/* Unread messages with left indicator */
.unread-message {
    font-weight: bold;
    position: relative;
}

.unread-message::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 5px;
    height: 100%;
    background-color: #28a745;
    border-radius: 0 3px 3px 0;
}

.list-group-item {
    background-color: transparent;
    border: none;
    color: #fff;
}
</style>
@endsection
