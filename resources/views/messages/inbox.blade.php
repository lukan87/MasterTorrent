@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="fw-bold text-white mb-0">
            <i class="bi bi-inbox-fill me-2"></i>Inbox
        </h1>
        <a href="{{ route('messages.outbox') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-send-fill me-1"></i>Outbox
        </a>
    </div>

    @if($messages->isEmpty())
        <div class="alert alert-dark d-flex align-items-center justify-content-center">
            <i class="bi bi-info-circle-fill me-2"></i>
            No messages have been received yet.
        </div>
    @else
        <div class="table-responsive" style="max-height: 70vh;">
            <table class="table align-middle text-white table-gmail mb-0">
                <thead class="table-head">
                    <tr>
                        <th scope="col" style="width: 5%;"><i class="bi bi-hash"></i></th>
                        <th scope="col"><i class="bi bi-envelope-fill me-1"></i>Subject</th>
                        <th scope="col"><i class="bi bi-person-circle me-1"></i>Sender</th>
                        <th scope="col" class="text-center"><i class="bi bi-check-circle me-1"></i>Status</th>
                        <th scope="col"><i class="bi bi-clock me-1"></i>Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $index => $message)
                        <tr class="{{ !$message->is_read ? 'unread-message' : '' }}">
                            <td>{{ ($messages->currentPage() - 1) * $messages->perPage() + $index + 1 }}</td>
                            <td>
                                <a href="{{ route('messages.show', $message) }}"
                                   class="text-decoration-none text-white fw-semibold d-block text-truncate"
                                   style="max-width: 250px;">
                                   {{ $message->subject ?? '(No Subject)' }}
                                </a>
                            </td>
                            <td><i class="bi bi-person-circle me-1"></i>{{ $message->sender->name }}</td>
                            <td class="text-center">
                                @if(!$message->is_read)
                                    <span class="badge bg-success px-2 py-1">
                                        <i class="bi bi-star-fill me-1"></i>New
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">Read</span>
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
    color: #f8f9fa;
}

/* Gmail-style table container */
.table-gmail {
    border-radius: 12px;
    background-color: #1c1c1c;
    font-size: 0.95rem;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
}

/* Sticky header */
.table-head th {
    position: sticky;
    top: 0;
    background: linear-gradient(90deg, #2c2c2c, #3a3a3a);
    color: #f8f9fa;
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0,0,0,0.4);
    padding: 0.75rem;
    white-space: nowrap;
}

/* Row hover */
.table-gmail tbody tr {
    transition: background-color 0.25s ease-in-out;
}

.table-gmail tbody tr:hover {
    background-color: #2a2a2a;
    cursor: pointer;
}

/* Unread row highlight */
.unread-message {
    font-weight: 600;
    background-color: #212121 !important;
    box-shadow: inset 4px 0 0 #28a745; /* ✅ green bar on the left */
}

/* Avoid layout shift */
.unread-message td,
.unread-message th {
    border-left: 0;
}

/* Table cell alignment */
.table th, .table td {
    vertical-align: middle;
    white-space: nowrap;
}

/* Truncate long subject text gracefully */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Scrollable table */
.table-responsive {
    overflow-y: auto;
    border-radius: 12px;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    h1 {
        font-size: 1.4rem;
    }
    .table-gmail th, .table-gmail td {
        font-size: 0.85rem;
        white-space: normal;
    }
}

@media (max-width: 576px) {
    .btn-outline-light {
        width: 100%;
    }
    .table-responsive {
        max-height: none;
    }
}
</style>
@endsection
