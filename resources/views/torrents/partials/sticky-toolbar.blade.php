{{-- Remove this include from torrents.show to disable the floating toolbar. --}}
@if(! $torrent->trashed())
    @once
        <link rel="stylesheet" href="{{ asset('css/torrent-toolbar.css') }}?v={{ filemtime(public_path('css/torrent-toolbar.css')) }}">
        <script src="{{ asset('js/torrent-toolbar.js') }}?v={{ filemtime(public_path('js/torrent-toolbar.js')) }}" defer></script>
    @endonce

    <section class="torrent-sticky-toolbar" data-torrent-sticky-toolbar aria-label="Torrent download and details" hidden>
        <div class="torrent-sticky-inner">
            <button type="button" class="torrent-sticky-title" data-torrent-return title="Back to torrent actions" aria-label="Back to torrent actions for {{ $torrent->name }}">
                <span class="torrent-sticky-icon"><i class="bi bi-file-earmark-play" aria-hidden="true"></i></span>
                <span class="torrent-sticky-name">{{ $torrent->name }}</span>
                <i class="bi bi-arrow-up-short torrent-sticky-return" aria-hidden="true"></i>
            </button>

            <dl class="torrent-sticky-stats">
                <div><dt>Size</dt><dd>{{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}</dd></div>
                <div class="torrent-sticky-seeders"><dt><i class="bi bi-arrow-up" aria-hidden="true"></i>Seeders</dt><dd>{{ number_format($torrent->seeders) }}</dd></div>
                <div class="torrent-sticky-leechers"><dt><i class="bi bi-arrow-down" aria-hidden="true"></i>Leechers</dt><dd>{{ number_format($torrent->leechers) }}</dd></div>
                <div class="torrent-sticky-date"><dt>Uploaded</dt><dd><time datetime="{{ $torrent->created_at->toIso8601String() }}">{{ $torrent->created_at->format('M j, Y') }}</time></dd></div>
            </dl>

            <div class="torrent-sticky-actions">
                @if($torrent->free)<span class="torrent-sticky-free">Freeleech</span>@endif
                @auth
                    @if(Auth::user()->hit_and_run_count > 20)
                        <span class="torrent-sticky-restricted" title="More than 20 hit and runs"><i class="bi bi-lock" aria-hidden="true"></i>Download restricted</span>
                    @else
                        <div class="btn-group">
                            <a class="btn torrent-sticky-download" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"><i class="bi bi-download" aria-hidden="true"></i><span>Download</span></a>
                            @if(Auth::user()->slots > 0 || ($userSeedboxes ?? collect())->isNotEmpty())
                                <button type="button" class="btn torrent-sticky-download dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-label="More download options" aria-expanded="false"></button>
                                <ul class="dropdown-menu dropdown-menu-end torrent-sticky-download-menu">
                                    @if(Auth::user()->slots > 0)
                                        <li><a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug, 'free' => 1]) }}"><i class="bi bi-lightning me-2" aria-hidden="true"></i>Free download</a></li>
                                        <li><a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug, 'double' => 1]) }}"><i class="bi bi-chevron-double-up me-2" aria-hidden="true"></i>Double upload</a></li>
                                    @endif
                                    @if(($userSeedboxes ?? collect())->isNotEmpty())
                                        @if(Auth::user()->slots > 0)<li><hr class="dropdown-divider"></li>@endif
                                        <li class="dropdown-header">Send to seedbox</li>
                                        @foreach($userSeedboxes as $seedbox)
                                            <li>
                                                <form method="POST" action="{{ route('torrents.sendToSeedbox', $torrent) }}">
                                                    @csrf
                                                    <input type="hidden" name="seedbox_id" value="{{ $seedbox->id }}">
                                                    <button type="submit" class="dropdown-item"><i class="bi bi-hdd-network me-2" aria-hidden="true"></i>{{ $seedbox->name }}</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </section>
@endif
