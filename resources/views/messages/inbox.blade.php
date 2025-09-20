@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="fw-bold text-white"><i class="bi bi-inbox-fill me-2"></i>Inbox</h1>
        <a href="{{ route('messages.outbox') }}" class="btn btn-outline-light">
            <i class="bi bi-send-fill me-1"></i>Outbox
        </a>
    </div>

    @if($messages->isEmpty())
        <div class="alert alert-dark d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2"></i>
            No messages have been received yet.
        </div>
    @else
        <div class="table-responsive" style="max-height: 70vh;">
            <table class="table align-middle text-white table-gmail">
                <thead class="table-head">
                    <tr>
                        <th scope="col"><i class="bi bi-hash"></i></th>
                        <th scope="col"><i class="bi bi-envelope-fill me-1"></i>Subject</th>
                        <th scope="col"><i class="bi bi-person-circle me-1"></i>Sender</th>
                        <th scope="col"><i class="bi bi-check-circle me-1"></i>Status</th>
                        <th scope="col"><i class="bi bi-clock me-1"></i>Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $index => $message)
                        <tr class="{{ !$message->is_read ? 'unread-message' : '' }}">
                            <th scope="row">{{ ($messages->currentPage() - 1) * $messages->perPage() + $index + 1 }}</th>
                            <td>
                                <a href="{{ route('messages.show', $message) }}" class="text-decoration-none text-white fw-bold">
                                    {{ $message->subject ?? '(No Subject)' }}
                                </a>
                            </td>
                            <td><i class="bi bi-person-circle me-1"></i>{{ $message->sender->name }}</td>
                            <td>
                                @if(!$message->is_read)
                                    <span class="badge bg-success"><i class="bi bi-star-fill me-1"></i>New</span>
                                @else
                                    <span class="badge bg-secondary">Read</span>
                                @endif
                            </td>
                            <td><i class="bi bi-clock me-1"></i>{{ $message->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $messages->links('pagination::bootstrap-5') }}
    </div>
@endif
    @endif
</div>

<style>
body {
    background-color: #121212;
}

/* Rounded Gmail-style table */
.table-gmail {
    border-radius: 12px;
    overflow: hidden;
    background-color: #1c1c1c;
    font-size: 0.95rem;
}

/* Header styling with gradient and sticky */
.table-head th {
    position: sticky;
    top: 0;
    background: linear-gradient(90deg, #2c2c2c, #3a3a3a);
    color: #f8f9fa;
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0,0,0,0.5);
    padding: 0.75rem;
}

/* Hover effect for rows */
.table-gmail tbody tr {
    transition: all 0.2s ease-in-out;
}

.table-gmail tbody tr:hover {
    background-color: #2a2a2a;
    cursor: pointer;
}

/* Unread messages with left indicator like Gmail */
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
    background-color: #28a745; /* green indicator */
    border-radius: 0 3px 3px 0;
}

/* Table cells vertical align */
.table th, .table td {
    vertical-align: middle;
}

/* Links hover */
a.text-white:hover {
    text-decoration: underline;
}

/* Scrollable container */
.table-responsive {
    overflow-y: auto;
}
</style>
@endsection
