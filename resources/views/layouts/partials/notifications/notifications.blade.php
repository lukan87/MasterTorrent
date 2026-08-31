@php
    $data = $notification->data ?? [];
    $type = $data['type'] ?? null;
@endphp

<li>
    <form method="POST"
          action="{{ route('notifications.read', $notification->id) }}">
        @csrf

        <input type="hidden"
               name="redirect"
               value="{{ $data['url'] ?? route('notifications.index') }}">

        <button type="submit"
                class="dropdown-item text-light text-start bg-transparent border-0 w-100">

            {{-- MAIN TEXT --}}
            <div class="fw-semibold">

                {{-- Torrent Deleted --}}
                @if($type === 'torrent_deleted')
                    <i class="bi bi-trash-fill text-danger me-1"></i>
                    Your torrent
                    <strong>{{ $data['torrent_name'] ?? 'Unknown' }}</strong>
                    was deleted

                {{-- Forum Reply (NEW STYLE WITH TYPE) --}}
                @elseif($type === 'forum_reply')
                    <i class="bi bi-chat-dots-fill text-info me-1"></i>
                    <strong>{{ $data['author'] ?? 'Someone' }}</strong>
                    replied to your post

                {{-- Forum Reply (OLD STYLE WITHOUT TYPE) --}}
                @elseif(isset($data['author']) && isset($data['topic_title']))
                    <i class="bi bi-chat-dots-fill text-info me-1"></i>
                    <strong>{{ $data['author'] }}</strong>
                    replied to
                    <em>{{ $data['topic_title'] }}</em>

                {{-- Fallback --}}
                @else
                    <i class="bi bi-bell-fill text-warning me-1"></i>
                    New notification
                @endif

            </div>

            {{-- Optional: Reason for deletion --}}
            @if($type === 'torrent_deleted' && !empty($data['reason']))
                <div class="small text-danger mt-1">
                    Reason: {{ $data['reason'] }}
                </div>
            @endif

            {{-- Time --}}
            <div class="small text-muted mt-1">
                <i class="bi bi-clock me-1"></i>
                {{ $notification->created_at->diffForHumans() }}
            </div>

        </button>
    </form>
</li>