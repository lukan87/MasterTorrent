@extends('layouts.admin')

@section('admin-content')

<div class="container-fluid py-3 admin-messages-page">

    {{-- HEADER --}}
    <div class="messages-header mb-3 admin-page-header">
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

    <a href="{{ route('admin.users.mass-messages.index') }}" class="btn btn-outline-secondary mb-3">Mass message history</a>

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

            <div class="col-lg-5 col-md-12">
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

            <div class="col-lg-2 col-md-4">
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

            <div class="col-lg-2 col-md-4">
                <label for="message-type" class="filter-label">Message type</label>
                <select id="message-type" name="type" class="form-select admin-input">
                    <option value="">All types</option>
                    <option value="normal" @selected(request('type') === 'normal')>Normal messages</option>
                    <option value="mass" @selected(request('type') === 'mass')>Mass messages</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-4">
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
                                                style="--member-color: {{ \App\Models\UserClass::getClassColor($message->sender->user_class) }}; color: var(--member-color);">
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
                                                style="--member-color: {{ \App\Models\UserClass::getClassColor($message->receiver->user_class) }}; color: var(--member-color);">
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
                                <a class="subject-button" href="{{ route('admin.messages.show', $message) }}">
                                    {{ $message->subject ?? '(no subject)' }}
                                </a>

                                @include('messages.mass-pill', ['message' => $message, 'showMassActions' => true, 'showNormal' => true])

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
                                @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
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
                                @endif
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
        color: var(--theme-text, #dbe7ef);
        font-size: var(--site-font-body, 13px);
    }

    .messages-header,
    .messages-filter-card,
    .messages-card {
        position: relative;
        background: linear-gradient(
            135deg,
            var(--theme-surface, rgba(14,21,33,.96)),
            var(--theme-surface, rgba(10,15,27,.88))
        );
        border: 1px solid var(--ui-border, var(--theme-border, rgba(255, 255, 255, .08)));
        border-radius: .65rem;
        box-shadow: 0 6px 18px var(--theme-shadow, rgba(0, 0, 0, .16));
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
        background: var(--theme-teal-action, var(--ui-accent, #20c997));
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
        color: var(--theme-text, #f3f8fb);
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
    }

    .messages-title i {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
    }

    .messages-subtitle {
        margin-top: .15rem;
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .messages-count {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .3rem .55rem;
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border: 1px solid var(--theme-teal-border, rgba(32, 201, 151, .2));
        border-radius: .4rem;
        color: var(--theme-teal-text, #72e3bb);
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
    }

    .modern-alert {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .7rem;
        border-radius: .5rem;
        font-size: var(--site-font-small, 13px);
    }

    .modern-alert-success {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border: 1px solid var(--theme-teal-border, rgba(32, 201, 151, .18));
        color: var(--theme-green-text, #72e0a9);
    }

    .messages-filter-card {
        padding: .7rem .8rem;
    }

    .filter-label {
        display: block;
        margin-bottom: .25rem;
        color: var(--theme-muted, #8fa2ae);
        font-size: var(--site-font-small, 13px);
        font-weight: 600;
    }

    .filter-label i {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
    }

    .admin-input {
        min-height: 34px;
        background: var(--theme-control, rgba(5,10,18,.5)) !important;
        border: 1px solid var(--theme-border, rgba(255, 255, 255, .09)) !important;
        border-radius: .4rem;
        color: var(--theme-text, #dbe7ef) !important;
        font-size: var(--site-font-small, 13px);
        box-shadow: none !important;
    }

    .admin-input::placeholder {
        color: var(--theme-muted, #647889);
    }

    .admin-input:focus {
        background: var(--theme-control, rgba(5,10,18,.62)) !important;
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .38)) !important;
        box-shadow: 0 0 0 .12rem var(--theme-shadow, rgba(32, 201, 151, .055)) !important;
    }

    .admin-input option {
        background: var(--theme-control, #0f1622);
        color: var(--theme-text, #fff);
    }

    .filter-btn,
    .reset-btn {
        min-height: 34px;
        border-radius: .4rem;
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
    }

    .filter-btn {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .1));
        border: 1px solid var(--theme-teal-border, rgba(32, 201, 151, .25));
        color: var(--theme-teal-text, #72e3bb);
    }

    .filter-btn:hover {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .18));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .4));
        color:  var(--theme-text, #fff);
    }

    .reset-btn {
        width: 35px;
        padding: 0;
        background: var(--theme-surface-alt, rgba(255,255,255,0.0245));
        border: 1px solid var(--theme-border, rgba(255, 255, 255, .08));
        color: var(--theme-muted, #91a2ad);
    }

    .reset-btn:hover {
        background: var(--theme-surface-alt, rgba(255,255,255,0.049));
        color: var(--theme-text, #fff);
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .25));
    }

    .messages-card {
        overflow-x: auto;
    }

    .messages-table {
        min-width: 850px;
        color: var(--theme-text, #dbe7ef);
        font-size: var(--site-font-small, 13px);
    }

    .messages-table > :not(caption) > * > * {
        padding: .6rem .7rem;
        border-bottom-color: var(--theme-border, rgba(255, 255, 255, .055));
    }

    .messages-table thead th {
        background: var(--theme-surface-alt, rgba(255,255,255,0.0154));
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .messages-table tbody tr {
        transition: background .14s ease;
    }

    .messages-table tbody tr:hover {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .025));
    }

    .messages-table tbody tr.message-unread {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .035));
    }

    .messages-table tbody tr.message-unread:hover {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .06));
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
        font-size: var(--site-font-small, 13px);
        font-weight: 600;
        text-decoration: none;
    }

    .user-link:hover {
        color: var(--theme-text, #fff) !important;
    }

    .unknown-user {
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
    }

    .user-arrow {
        color: var(--theme-muted, #5d707c);
        font-size: var(--site-font-small, 13px);
    }

    .deleted-badge {
        display: inline-block;
        margin-left: .25rem;
        padding: .12rem .3rem;
        background: var(--theme-red-soft, rgba(220, 53, 69, .09));
        border: 1px solid var(--theme-red-border, rgba(220, 53, 69, .18));
        border-radius: .25rem;
        color: var(--theme-red-text, #ff8e98);
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
    }

    .subject-button {
        max-width: 360px;
        padding: 0;
        overflow: hidden;
        border: 0;
        background: transparent;
        color: var(--theme-text, #dce8ed);
        font-size: var(--site-font-small, 13px);
        font-weight: 600;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .subject-button:hover {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .4rem;
        border-radius: .3rem;
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
        white-space: nowrap;
    }

    .status-read {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .08));
        border: 1px solid var(--theme-teal-border, rgba(32, 201, 151, .18));
        color: var(--theme-green-text, #72e0a9);
    }

    .status-unread {
        background: var(--theme-amber-soft, rgba(255, 193, 7, .08));
        border: 1px solid var(--theme-amber-border, rgba(255, 193, 7, .18));
        color: var(--theme-amber-text, #e7c967);
    }

    .created-time {
        color: var(--theme-muted, #8fa2ae);
        font-size: var(--site-font-small, 13px);
        white-space: nowrap;
    }

    .delete-btn {
        width: 29px;
        height: 29px;
        padding: 0;
        border: 1px solid var(--theme-red-border, rgba(220, 53, 69, .25));
        border-radius: .38rem;
        background: var(--theme-red-soft, rgba(220, 53, 69, .07));
        color: var(--theme-red-text, #ff8e98);
        font-size: var(--site-font-small, 13px);
    }

    .delete-btn:hover {
        background: var(--theme-red-soft, rgba(220, 53, 69, .14));
        border-color: var(--theme-red-border, rgba(220, 53, 69, .42));
        color: var(--theme-text, #fff);
    }

    .empty-messages {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .25rem;
        min-height: 100px;
        color: var(--theme-muted, #718596);
    }

    .empty-messages i {
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
        font-size: 22px;
    }

    .empty-messages strong {
        color: var(--theme-text, #b9c8d0);
        font-size: var(--site-font-body, 13px);
    }

    .empty-messages span {
        font-size: var(--site-font-small, 13px);
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
        background: var(--theme-surface, rgba(14,21,33,.9));
        border-color: var(--theme-border, rgba(255, 255, 255, .075));
        color: var(--theme-muted, #aabcc7);
        font-size: var(--site-font-small, 13px);
        padding: .3rem .55rem;
        border-radius: .35rem !important;
    }

    .pagination .page-item.active .page-link {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .13));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .28));
        color: var(--theme-teal-text, #73e2bb);
    }

    .pagination .page-link:hover {
        background: var(--theme-teal-soft, rgba(32, 201, 151, .07));
        border-color: var(--theme-teal-border, rgba(32, 201, 151, .22));
        color:  var(--theme-text, #fff);
    }

    .message-modal {
        background: linear-gradient(
            135deg,
            var(--theme-surface, rgba(14,21,33,.99)),
            var(--theme-surface, rgba(10,15,27,.97))
        );
        border: 1px solid var(--ui-border, var(--theme-border, rgba(255, 255, 255, .08)));
        border-radius: .65rem;
        color: var(--theme-text, #dbe7ef);
        box-shadow: 0 16px 45px var(--theme-shadow, rgba(0, 0, 0, .38));
        overflow: hidden;
    }

    .message-modal .modal-header,
    .message-modal .modal-footer {
        border-color: var(--theme-border, rgba(255, 255, 255, .065));
    }

    .message-modal .modal-header {
        padding: .75rem .9rem;
        background: var(--theme-surface-alt, rgba(255,255,255,0.0126));
    }

    .modal-kicker {
        margin-bottom: .15rem;
        color: var(--ui-accent, var(--theme-teal-text, #20c997));
        font-size: var(--site-font-small, 13px);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .message-modal .modal-title {
        color: var(--theme-text, #f1f5f9);
        font-size: var(--site-font-body, 13px);
        font-weight: 700;
    }

    .message-modal .modal-body {
        padding: .85rem .9rem;
        font-size: var(--site-font-body, 13px);
    }

    .message-meta {
        display: flex;
        gap: 1.5rem;
        padding-bottom: .65rem;
        margin-bottom: .7rem;
        border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .065));
    }

    .message-meta div {
        display: flex;
        flex-direction: column;
        gap: .1rem;
    }

    .message-meta span {
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
        text-transform: uppercase;
    }

    .message-meta strong {
        color: var(--theme-text, #dce8ed);
        font-size: var(--site-font-small, 13px);
    }

    .message-body {
        padding: .7rem;
        background: var(--theme-surface, rgba(5,10,18,.4));
        border: 1px solid var(--theme-border, rgba(255, 255, 255, .065));
        border-radius: .45rem;
        color: var(--theme-text, #dbe7ef);
        font-size: var(--site-font-body, 13px);
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .message-modal .modal-footer {
        padding: .55rem .9rem;
        color: var(--theme-muted, #718596);
        font-size: var(--site-font-small, 13px);
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
