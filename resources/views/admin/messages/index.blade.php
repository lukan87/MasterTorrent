@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 admin-messages-page">

    {{-- HEADER --}}
    <div class="messages-header mb-3">
        <div>
            <h1 class="messages-title">
                <i class="bi bi-envelope-fill me-2"></i>
                User Messages
            </h1>
            <div class="messages-subtitle">
                Manage and review messages between users
            </div>
        </div>

        <span class="messages-count">
            <i class="bi bi-chat-square-text me-1"></i>
            {{ $messages->total() }} messages
        </span>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="modern-alert modern-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>

            <button
                type="button"
                class="btn-close btn-close-white ms-auto"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>
        </div>
    @endif

    {{-- FILTERS --}}
    <div class="messages-filter-card mb-3">
        <form method="GET" class="row g-2 align-items-end">

            <div class="col-lg-6 col-md-5">
                <label for="message-search" class="filter-label">
                    <i class="bi bi-search me-1"></i>
                    Search
                </label>

                <input
                    id="message-search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control admin-input"
                    placeholder="Subject, message or username..."
                >
            </div>

            <div class="col-lg-3 col-md-4">
                <label for="message-status" class="filter-label">
                    <i class="bi bi-filter me-1"></i>
                    Status
                </label>

                <select id="message-status" name="status" class="form-select admin-input">
                    <option value="">All messages</option>
                    <option value="unread" @selected(request('status') === 'unread')>
                        Unread
                    </option>
                    <option value="read" @selected(request('status') === 'read')>
                        Read
                    </option>
                </select>
            </div>

            <div class="col-lg-3 col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn filter-btn flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>
                        Filter
                    </button>

                    <a
                        href="{{ url()->current() }}"
                        class="btn reset-btn"
                        title="Reset filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>

        </form>
    </div>

    {{-- MESSAGES --}}
    <div class="messages-card">
        <div class="table-responsive">
            <table class="table messages-table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Sender / Receiver</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($messages as $message)

                        <tr class="{{ $message->is_read ? '' : 'message-unread' }}">

                            {{-- USERS --}}
                            <td>
                                <div class="message-users">

                                    {{-- SENDER --}}
                                    <div class="user-line">
                                        @if($message->sender)
                                            <a
                                                href="{{ route('profile.show', [$message->sender->id, $message->sender->name]) }}"
                                                class="user-link"
                                                style="color:{{ \App\Models\UserClass::getClassColor($message->sender->user_class) }};">
                                                {{ $message->sender->name }}
                                            </a>

                                            @if($message->sender->trashed())
                                                <span class="deleted-badge">Deleted</span>
                                            @endif
                                        @else
                                            <span class="unknown-user">Unknown</span>
                                        @endif
                                    </div>

                                    <span class="user-arrow">
                                        <i class="bi bi-arrow-right"></i>
                                    </span>

                                    {{-- RECEIVER --}}
                                    <div class="user-line">
                                        @if($message->receiver)
                                            <a
                                                href="{{ route('profile.show', [$message->receiver->id, $message->receiver->name]) }}"
                                                class="user-link"
                                                style="color:{{ \App\Models\UserClass::getClassColor($message->receiver->user_class) }};">
                                                {{ $message->receiver->name }}
                                            </a>

                                            @if($message->receiver->trashed())
                                                <span class="deleted-badge">Deleted</span>
                                            @endif
                                        @else
                                            <span class="unknown-user">—</span>
                                        @endif
                                    </div>

                                </div>
                            </td>

                            {{-- SUBJECT --}}
                            <td>
                                <button
                                    type="button"
                                    class="subject-button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#messageModal{{ $message->id }}">
                                    {{ $message->subject ?? '(no subject)' }}
                                </button>

                                {{-- MESSAGE MODAL --}}
                                <div
                                    class="modal fade"
                                    id="messageModal{{ $message->id }}"
                                    tabindex="-1"
                                    aria-labelledby="messageModalLabel{{ $message->id }}"
                                    aria-hidden="true">

                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content message-modal">

                                            <div class="modal-header">
                                                <div>
                                                    <div class="modal-kicker">
                                                        <i class="bi bi-envelope-open me-1"></i>
                                                        User Message
                                                    </div>

                                                    <h5
                                                        class="modal-title"
                                                        id="messageModalLabel{{ $message->id }}">
                                                        {{ $message->subject ?? '(no subject)' }}
                                                    </h5>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                </button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="message-meta">
                                                    <div>
                                                        <span>From</span>
                                                        <strong>
                                                            {{ $message->sender?->name ?? 'System' }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>To</span>
                                                        <strong>
                                                            {{ $message->receiver?->name ?? '—' }}
                                                        </strong>
                                                    </div>
                                                </div>

                                                <div class="message-body">
                                                    {!! convertCustomTagsToHtml($message->body) !!}
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <span>
                                                    <i class="bi bi-clock me-1"></i>
                                                    Sent {{ $message->created_at->toDayDateTimeString() }}
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                @if($message->is_read)
                                    <span class="status-badge status-read">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Read
                                    </span>
                                @else
                                    <span class="status-badge status-unread">
                                        <i class="bi bi-envelope-fill"></i>
                                        Unread
                                    </span>
                                @endif
                            </td>

                            {{-- CREATED --}}
                            <td>
                                <span class="created-time">
                                    {{ $message->created_at->diffForHumans() }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-end">
                                <form
                                    method="POST"
                                    action="{{ route('admin.messages.destroy', $message) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="delete-btn"
                                        title="Delete message"
                                        onclick="return confirm('Delete this message?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-messages">
                                    <i class="bi bi-envelope-open"></i>
                                    <strong>No messages found.</strong>
                                    <span>Try changing your search or status filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if($messages->hasPages())
        <div class="pagination-wrap">
            {{ $messages->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
    .admin-messages-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .messages-header,
    .messages-filter-card,
    .messages-card {
        position: relative;
        background: linear-gradient(
            135deg,
            rgba(22, 32, 51, .96),
            rgba(15, 23, 42, .88)
        );
        border: 1px solid var(--ui-border, rgba(255, 255, 255, .08));
        border-radius: .65rem;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .16);
        overflow: hidden;
    }

    .messages-header::before,
    .messages-filter-card::before,
    .messages-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--ui-accent, #20c997);
        opacity: .75;
    }

    .messages-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .9rem;
    }

    .messages-title {
        margin: 0;
        color: #f3f8fb;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
    }

    .messages-title i {
        color: var(--ui-accent, #20c997);
    }

    .messages-subtitle {
        margin-top: .15rem;
        color: #718596;
        font-size: 11px;
    }

    .messages-count {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .2);
        border-radius: .4rem;
        color: #72e3bb;
        font-size: 11px;
        font-weight: 700;
    }

    .modern-alert {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .7rem;
        border-radius: .5rem;
        font-size: 12px;
    }

    .modern-alert-success {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .messages-filter-card {
        padding: .7rem .8rem;
    }

    .filter-label {
        display: block;
        margin-bottom: .25rem;
        color: #8fa2ae;
        font-size: 11px;
        font-weight: 600;
    }

    .filter-label i {
        color: var(--ui-accent, #20c997);
    }

    .admin-input {
        min-height: 34px;
        background: rgba(7, 15, 27, .5) !important;
        border: 1px solid rgba(255, 255, 255, .09) !important;
        border-radius: .4rem;
        color: #dbe7ef !important;
        font-size: 12px;
        box-shadow: none !important;
    }

    .admin-input::placeholder {
        color: #647889;
    }

    .admin-input:focus {
        background: rgba(7, 15, 27, .62) !important;
        border-color: rgba(32, 201, 151, .38) !important;
        box-shadow: 0 0 0 .12rem rgba(32, 201, 151, .055) !important;
    }

    .admin-input option {
        background: #172234;
        color: #fff;
    }

    .filter-btn,
    .reset-btn {
        min-height: 34px;
        border-radius: .4rem;
        font-size: 12px;
        font-weight: 700;
    }

    .filter-btn {
        background: rgba(32, 201, 151, .1);
        border: 1px solid rgba(32, 201, 151, .25);
        color: #72e3bb;
    }

    .filter-btn:hover {
        background: rgba(32, 201, 151, .18);
        border-color: rgba(32, 201, 151, .4);
        color: #fff;
    }

    .reset-btn {
        width: 35px;
        padding: 0;
        background: rgba(255, 255, 255, .035);
        border: 1px solid rgba(255, 255, 255, .08);
        color: #91a2ad;
    }

    .reset-btn:hover {
        background: rgba(255, 255, 255, .07);
        color: #fff;
        border-color: rgba(32, 201, 151, .25);
    }

    .messages-card {
        overflow-x: auto;
    }

    .messages-table {
        min-width: 850px;
        color: #dbe7ef;
        font-size: 12px;
    }

    .messages-table > :not(caption) > * > * {
        padding: .6rem .7rem;
        border-bottom-color: rgba(255, 255, 255, .055);
    }

    .messages-table thead th {
        background: rgba(255, 255, 255, .022);
        color: #718596;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .messages-table tbody tr {
        transition: background .14s ease;
    }

    .messages-table tbody tr:hover {
        background: rgba(32, 201, 151, .025);
    }

    .messages-table tbody tr.message-unread {
        background: rgba(32, 201, 151, .035);
    }

    .messages-table tbody tr.message-unread:hover {
        background: rgba(32, 201, 151, .06);
    }

    .message-users {
        display: flex;
        align-items: center;
        gap: .4rem;
        min-width: 230px;
    }

    .user-line {
        min-width: 0;
    }

    .user-link {
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .user-link:hover {
        color: #fff !important;
    }

    .unknown-user {
        color: #718596;
        font-size: 12px;
    }

    .user-arrow {
        color: #5d707c;
        font-size: 11px;
    }

    .deleted-badge {
        display: inline-block;
        margin-left: .25rem;
        padding: .12rem .3rem;
        background: rgba(220, 53, 69, .09);
        border: 1px solid rgba(220, 53, 69, .18);
        border-radius: .25rem;
        color: #ff8e98;
        font-size: 9px;
        font-weight: 700;
    }

    .subject-button {
        max-width: 360px;
        padding: 0;
        overflow: hidden;
        border: 0;
        background: transparent;
        color: #dce8ed;
        font-size: 12px;
        font-weight: 600;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .subject-button:hover {
        color: var(--ui-accent, #20c997);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border-radius: .3rem;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-read {
        background: rgba(32, 201, 151, .08);
        border: 1px solid rgba(32, 201, 151, .18);
        color: #72e0a9;
    }

    .status-unread {
        background: rgba(255, 193, 7, .08);
        border: 1px solid rgba(255, 193, 7, .18);
        color: #e7c967;
    }

    .created-time {
        color: #8fa2ae;
        font-size: 11px;
        white-space: nowrap;
    }

    .delete-btn {
        width: 29px;
        height: 29px;
        padding: 0;
        border: 1px solid rgba(220, 53, 69, .25);
        border-radius: .38rem;
        background: rgba(220, 53, 69, .07);
        color: #ff8e98;
        font-size: 11px;
    }

    .delete-btn:hover {
        background: rgba(220, 53, 69, .14);
        border-color: rgba(220, 53, 69, .42);
        color: #fff;
    }

    .empty-messages {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .25rem;
        min-height: 100px;
        color: #718596;
    }

    .empty-messages i {
        color: var(--ui-accent, #20c997);
        font-size: 22px;
    }

    .empty-messages strong {
        color: #b9c8d0;
        font-size: 13px;
    }

    .empty-messages span {
        font-size: 11px;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: .65rem;
    }

    .pagination {
        gap: 2px;
        margin-bottom: 0;
    }

    .pagination .page-link {
        background: rgba(22, 32, 51, .9);
        border-color: rgba(255, 255, 255, .075);
        color: #aabcc7;
        font-size: 11px;
        padding: .3rem .55rem;
        border-radius: .35rem !important;
    }

    .pagination .page-item.active .page-link {
        background: rgba(32, 201, 151, .13);
        border-color: rgba(32, 201, 151, .28);
        color: #73e2bb;
    }

    .pagination .page-link:hover {
        background: rgba(32, 201, 151, .07);
        border-color: rgba(32, 201, 151, .22);
        color: #fff;
    }

    .message-modal {
        background: linear-gradient(
            135deg,
            rgba(22, 32, 51, .99),
            rgba(15, 23, 42, .97)
        );
        border: 1px solid var(--ui-border, rgba(255, 255, 255, .08));
        border-radius: .65rem;
        color: #dbe7ef;
        box-shadow: 0 16px 45px rgba(0, 0, 0, .38);
        overflow: hidden;
    }

    .message-modal .modal-header,
    .message-modal .modal-footer {
        border-color: rgba(255, 255, 255, .065);
    }

    .message-modal .modal-header {
        padding: .75rem .9rem;
        background: rgba(255, 255, 255, .018);
    }

    .modal-kicker {
        margin-bottom: .15rem;
        color: var(--ui-accent, #20c997);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .message-modal .modal-title {
        color: #f1f5f9;
        font-size: 15px;
        font-weight: 700;
    }

    .message-modal .modal-body {
        padding: .85rem .9rem;
        font-size: 13px;
    }

    .message-meta {
        display: flex;
        gap: 1.5rem;
        padding-bottom: .65rem;
        margin-bottom: .7rem;
        border-bottom: 1px solid rgba(255, 255, 255, .065);
    }

    .message-meta div {
        display: flex;
        flex-direction: column;
        gap: .1rem;
    }

    .message-meta span {
        color: #718596;
        font-size: 10px;
        text-transform: uppercase;
    }

    .message-meta strong {
        color: #dce8ed;
        font-size: 12px;
    }

    .message-body {
        padding: .7rem;
        background: rgba(7, 15, 27, .4);
        border: 1px solid rgba(255, 255, 255, .065);
        border-radius: .45rem;
        color: #dbe7ef;
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .message-modal .modal-footer {
        padding: .55rem .9rem;
        color: #718596;
        font-size: 10px;
    }

    @media (max-width: 767.98px) {
        .admin-messages-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .messages-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .messages-count {
            align-self: flex-start;
        }

        .message-meta {
            flex-direction: column;
            gap: .55rem;
        }

        .messages-table {
            min-width: 800px;
        }
    }
</style>

@endsection
