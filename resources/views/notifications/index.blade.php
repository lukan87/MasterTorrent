@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Page header --}}
    <div class="mb-4 text-center">
        <h1 class="fw-bold mb-1">
            <i class="bi bi-bell me-2"></i> Notifications
        </h1>
        <div class="text-muted small">
            All your recent activity updates
        </div>
    </div>
    
@if($notifications->count())
    <div class="d-flex justify-content-end gap-2 mb-3">

        @if(auth()->user()->unreadNotifications->count())
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="btn btn-outline-success btn-sm" data-bs-toggle="tooltip" title="Mark all as read">
                    <i class="bi bi-check2-all me-1 fs-5"></i> Mark all as read
                </button>
            </form>
        @endif

        <form method="POST"
              action="{{ route('notifications.deleteAll') }}"
              onsubmit="return confirm('Delete ALL notifications?')">
            @csrf
            @method('DELETE')

            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1 fs-5"></i> Delete all
            </button>
        </form>

    </div>
@endif


    {{-- Notifications card --}}
    <div class="card bg-dark bg-opacity-50 border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-body p-0">

            @forelse($notifications as $notification)
                <div class="notification-item p-3 border-bottom border-secondary
                    {{ is_null($notification->read_at) ? 'unread' : '' }}">

                    <div class="d-flex justify-content-between align-items-start gap-3">

                        {{-- Notification content --}}
                        <form method="POST"
                              action="{{ route('notifications.read', $notification->id) }}"
                              class="flex-grow-1">
                            @csrf

                            <button type="submit"
                                    class="w-100 text-start bg-transparent border-0 text-light">

                                @php
    $data = $notification->data ?? [];
    $type = $data['type'] ?? null;
@endphp

<div class="fw-semibold">

    {{-- Torrent Deleted --}}
    @if($type === 'torrent_deleted')
        <i class="bi bi-trash-fill text-danger me-1"></i>
        Your torrent
        <strong>{{ $data['torrent_name'] ?? 'Unknown' }}</strong>
        was deleted

        @if(!empty($data['reason']))
            <div class="small text-danger mt-1">
                Reason: {{ $data['reason'] }}
            </div>
        @endif

    {{-- Forum Reply (new system) --}}
    @elseif($type === 'forum_reply')
        <i class="bi bi-chat-dots-fill text-info me-1"></i>
        <strong>{{ $data['author'] ?? 'Someone' }}</strong>
        replied to your post

        @if(!empty($data['topic_title']))
            <em class="d-block mt-1">{{ $data['topic_title'] }}</em>
        @endif

    {{-- Forum Reply (old structure without type) --}}
    @elseif(isset($data['author']) && isset($data['topic_title']))
        <i class="bi bi-chat-dots-fill text-info me-1"></i>
        <strong>{{ $data['author'] }}</strong>
        replied to
        <em>{{ $data['topic_title'] }}</em>

    {{-- Fallback --}}
    @else
        <i class="bi bi-bell-fill text-warning me-1"></i>
        You have a new activity
    @endif

</div>

                                <div class="small text-muted mt-1">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                    <span class="ms-2">
                                        {{ $notification->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>

                            </button>
                        </form>

                        {{-- Delete (single) --}}
                        <form method="POST"
                              action="{{ route('notifications.delete', $notification->id) }}"
                              onsubmit="return confirm('Delete this notification?')">
                            @csrf
                            @method('DELETE')

                            <button class="btn" data-bs-toggle="tooltip" title="Delete Notification" >
                                <i class="bi bi-trash fs-5 text-danger"></i>
                            </button>
                        </form>

                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                    <div class="fw-semibold">You’re all caught up</div>
                    <div class="small">No notifications to show</div>
                </div>
            @endforelse

        </div>
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

{{-- Modern polish --}}
<style>
.notification-item {
    transition: background-color .2s ease, transform .15s ease;
}

.notification-item:hover {
    background-color: rgba(255,255,255,0.04);
}

.notification-item.unread {
    background: linear-gradient(
        90deg,
        rgba(13,110,253,0.12),
        rgba(13,110,253,0.02)
    );
}

.notification-item.unread:hover {
    background: linear-gradient(
        90deg,
        rgba(13,110,253,0.18),
        rgba(13,110,253,0.04)
    );
}

.notification-item button {
    padding: 0;
}

.notification-item em {
    font-style: normal;
    color: #9ec5fe;
}

.card {
    backdrop-filter: blur(6px);
}
</style>
@endsection
