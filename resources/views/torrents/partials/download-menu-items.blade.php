@if(Auth::user()->slots > 0)
    <li>
        <a class="dropdown-item"
           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug, 'free' => 1]) }}">
            <i class="bi bi-lightning-fill me-2"></i>Free Download
        </a>
    </li>
    <li>
        <a class="dropdown-item"
           href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug, 'double' => 1]) }}">
            <i class="bi bi-chevron-double-up me-2"></i>Double Upload
        </a>
    </li>
@endif

@if($seedboxes->isNotEmpty())
    @if(Auth::user()->slots > 0)
        <li><hr class="dropdown-divider"></li>
    @endif
    <li class="dropdown-header">
        <i class="bi bi-cloud-upload-fill me-1"></i>Send to seedbox
    </li>
    @foreach($seedboxes as $seedbox)
        <li>
            <button type="button"
                    class="dropdown-item seedbox-send-btn"
                    data-torrent="{{ $torrent->id }}"
                    data-seedbox="{{ $seedbox->id }}">
                <i class="bi bi-hdd-stack me-2"></i>{{ $seedbox->name }}
            </button>
        </li>
    @endforeach
@endif
