


@if($torrent->uploader)
    <i class="bi bi-arrow-90deg-up"></i>
    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
        <a href="{{ route('profile.show', $torrent->uploader->id) }}"
           class="fw-semibold text-decoration-none"
           style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}">
            {{ $torrent->uploader->name }}
        </a>
    @else
        <span style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}">
            {{ $torrent->uploader->name }}
        </span>
    @endif
@else
    <span class="text-muted">Unknown</span>
@endif