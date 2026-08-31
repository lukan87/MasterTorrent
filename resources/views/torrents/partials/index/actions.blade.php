     
     <!-- Actions browse -->
     
     <div class="d-flex justify-content-end gap-1 flex-wrap">

            {{-- DOWNLOAD / SEEDBOX --}}
            @if(Auth::check() && Auth::user()->hit_and_run_count <= 20)
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
                       class="btn btn-secondary">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </a>

                    @if($seedboxes->isNotEmpty())
                        <button class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown"></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach($seedboxes as $seedbox)
                                <li>
                                    <button type="button"
            class="dropdown-item seedbox-send-btn"
            data-torrent="{{ $torrent->id }}"
            data-seedbox="{{ $seedbox->id }}">
        <i class="bi bi-cloud-upload-fill me-2"></i>
        {{ $seedbox->name }}
    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- MODERATOR --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrents.edit', [$torrent->id, $torrent->slug]) }}"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i>
                </a>
            @endif

            {{-- ADMIN --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <form method="POST" action="{{ route('torrents.destroy', $torrent->slug) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif

        </div>