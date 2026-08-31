@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <h1 class="fw-bold text-primary mb-4">
        <i class="bi bi-envelope-fill me-2"></i>
        User Messages
    </h1>


    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            {{ session('success') }}
        </div>
    @endif


    {{-- FILTERS --}}
    <form method="GET" class="row g-2 mb-4 align-items-center">

        <div class="col-md-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control form-control-lg shadow-sm"
                placeholder="Search subject, body or username..."
            >
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select form-select-lg shadow-sm">

                <option value="">All</option>

                <option value="unread"
                    @selected(request('status') === 'unread')>
                    Unread
                </option>

                <option value="read"
                    @selected(request('status') === 'read')>
                    Read
                </option>

            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary btn-lg w-100 shadow-sm">
                <i class="bi bi-funnel me-1"></i>
                Filter
            </button>
        </div>

    </form>


    {{-- MESSAGES --}}
    <div class="card border-0 shadow rounded-4">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr class="text-uppercase small text-secondary">

                        <th>Sender / Receiver</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>

                    </tr>

                </thead>


                <tbody>

                @forelse($messages as $message)

                <tr class="{{ $message->is_read ? '' : 'fw-bold bg-light' }}">

                    {{-- USERS --}}
                    <td>

                        {{-- SENDER --}}
                        @if($message->sender)

                            <a
                                href="{{ route('profile.show', [$message->sender->id, $message->sender->name]) }}"
                                class="text-decoration-none"
                                style="font-size:0.9rem;
                                color:{{ \App\Models\UserClass::getClassColor($message->sender->user_class) }}">

                                {{ $message->sender->name }}

                            </a>

                            @if($message->sender->trashed())
                                <span class="badge bg-danger ms-1">Deleted</span>
                            @endif

                        @else
                            <span class="text-muted">Unknown</span>
                        @endif


                        <span class="text-muted mx-1">→</span>


                        {{-- RECEIVER --}}
                        @if($message->receiver)

                            <a
                                href="{{ route('profile.show', [$message->receiver->id, $message->receiver->name]) }}"
                                class="text-decoration-none"
                                style="font-size:0.9rem;
                                color:{{ \App\Models\UserClass::getClassColor($message->receiver->user_class) }}">

                                {{ $message->receiver->name }}

                            </a>

                            @if($message->receiver->trashed())
                                <span class="badge bg-danger ms-1">Deleted</span>
                            @endif

                        @else
                            <span class="text-muted">—</span>
                        @endif

                    </td>


                    {{-- SUBJECT --}}
                    <td>

                        <button
                            class="btn btn-link text-decoration-none p-0"
                            data-bs-toggle="modal"
                            data-bs-target="#messageModal{{ $message->id }}"
                        >

                            {{ $message->subject ?? '(no subject)' }}

                        </button>


                        {{-- MODAL --}}
                        <div class="modal fade"
                             id="messageModal{{ $message->id }}"
                             tabindex="-1">

                            <div class="modal-dialog modal-lg modal-dialog-centered">

                                <div class="modal-content shadow rounded-4">

                                    <div class="modal-header bg-primary text-white">

                                        <h5 class="modal-title">

                                            {{ $message->subject ?? '(no subject)' }}

                                        </h5>

                                        <button
                                            class="btn-close btn-close-white"
                                            data-bs-dismiss="modal">
                                        </button>

                                    </div>


                                    <div class="modal-body">

                                        <p class="mb-1">

                                            <strong>From:</strong>

                                            {{ $message->sender?->name ?? 'System' }}

                                        </p>

                                        <p>

                                            <strong>To:</strong>

                                            {{ $message->receiver?->name ?? '—' }}

                                        </p>

                                        <hr>

                                        <div class="p-3 rounded fs-5">

                                            {!! convertCustomTagsToHtml($message->body) !!}

                                        </div>

                                    </div>


                                    <div class="modal-footer text-muted small">

                                        Sent {{ $message->created_at->toDayDateTimeString() }}

                                    </div>

                                </div>

                            </div>

                        </div>

                    </td>


                    {{-- STATUS --}}
                    <td>

                        @if($message->is_read)

                            <span class="badge bg-success">
                                Read
                            </span>

                        @else

                            <span class="badge bg-warning text-dark">
                                Unread
                            </span>

                        @endif

                    </td>


                    {{-- CREATED --}}
                    <td>

                        {{ $message->created_at->diffForHumans() }}

                    </td>


                    {{-- ACTIONS --}}
                    <td class="text-end">

                        <form
                            method="POST"
                            action="{{ route('admin.messages.destroy', $message) }}"
                        >

                            @csrf
                            @method('DELETE')

                            <button
                                class="btn btn-sm btn-danger shadow-sm"
                                onclick="return confirm('Delete this message?')"
                            >

                                <i class="bi bi-trash"></i>

                            </button>

                        </form>

                    </td>


                </tr>


                @empty

                <tr>

                    <td colspan="5" class="text-center text-muted p-4">

                        No messages found.

                    </td>

                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- PAGINATION --}}
    <div class="mt-4 d-flex justify-content-center">

        {{ $messages->links('pagination::bootstrap-5') }}

    </div>


</div>

@endsection