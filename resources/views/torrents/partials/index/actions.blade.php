     
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

                    @if($seedboxes->isNotEmpty())
                        <button type="button"
                                class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Send to seedbox"
                                title="Send to seedbox">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
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