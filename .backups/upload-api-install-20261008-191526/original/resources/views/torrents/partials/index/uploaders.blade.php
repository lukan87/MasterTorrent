


@if(!$torrent->canRevealUploader())
    <span class="text-muted"><i class="bi bi-incognito" aria-hidden="true"></i> Anonymous</span>
@elseif($torrent->uploader)
    <i class="bi bi-arrow-90deg-up"></i>
    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
        <a href="{{ route('profile.show', $torrent->uploader->id) }}"
           class="fw-semibold text-decoration-none"
           style="--member-color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}; color: var(--member-color)">
            {{ $torrent->uploader->name }}
        </a>
    @else
        <span style="--member-color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class) }}; color: var(--member-color)">
            {{ $torrent->uploader->name }}
        </span>
    @endif
@else
    <span class="text-muted">Unknown</span>
@endif