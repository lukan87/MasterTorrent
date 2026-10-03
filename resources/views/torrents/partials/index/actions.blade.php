     
     <!-- Actions browse -->
     
     <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">

            {{-- DOWNLOAD / SEEDBOX --}}
            @if(Auth::check() && Auth::user()->hit_and_run_count <= 20)
                <div class="btn-group btn-group-xl">
                    <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
                       class="btn btn-secondary tx-btn-download"
                       aria-label="Download"
                       data-bs-toggle="tooltip" title="Download">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </a>

                    @if(Auth::user()->slots > 0 || $seedboxes->isNotEmpty())
                        <button type="button"
                                class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Download options"
                                title="Download options">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @include('torrents.partials.download-menu-items')
                        </ul>
                    @endif
                </div>
            @endif

            {{-- MODERATOR --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrents.edit', [$torrent->id, $torrent->slug]) }}"
                   class="btn btn-warning btn-xl"
                   aria-label="Edit torrent"
                   data-bs-toggle="tooltip" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                </a>
            @endif

            {{-- ADMIN --}}
            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <form method="POST" action="{{ route('torrents.destroy', $torrent->slug) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm"
                            aria-label="Delete torrent"
                            data-bs-toggle="tooltip" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif

        </div>