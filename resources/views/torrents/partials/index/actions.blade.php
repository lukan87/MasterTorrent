@php
    $viewer = Auth::user();
    $canDownload = $viewer && $viewer->downloadpos !== 'no' && $viewer->hit_and_run_count <= 20;
    $canEdit = $viewer && ($viewer->user_class >= \App\Models\UserClass::MODERATOR || (int) $viewer->id === (int) $torrent->owner);
    $canDelete = $viewer && $viewer->user_class >= \App\Models\UserClass::MODERATOR;
    $downloadReason = ! $viewer ? 'Sign in to download' : ($viewer->downloadpos === 'no' ? 'Download permission disabled' : 'Download restricted: more than 20 hit and runs');
@endphp

<div class="tx-actions" role="group" aria-label="Actions for {{ $torrent->name }}">
    @if($canDownload)
        <div class="btn-group tx-download-group">
            <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
               class="btn tx-action tx-action-download"
               aria-label="Download {{ $torrent->name }}" data-bs-toggle="tooltip" title="Download torrent">
                <i class="bi bi-cloud-arrow-down-fill" aria-hidden="true"></i>
            </a>
            @if($viewer->slots > 0 || ($seedboxes ?? collect())->isNotEmpty())
                <button type="button" class="btn tx-action tx-action-options dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="Download options for {{ $torrent->name }}" title="Slots and seedbox options">
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @include('torrents.partials.download-menu-items', ['seedboxes' => $seedboxes ?? collect()])
                </ul>
            @endif
        </div>
    @else
        <span class="tx-action tx-action-locked" tabindex="0" role="img" aria-label="{{ $downloadReason }}" data-bs-toggle="tooltip" title="{{ $downloadReason }}">
            <i class="bi bi-lock" aria-hidden="true"></i>
        </span>
    @endif

    @if($canEdit)
        <a href="{{ route('torrents.edit', [$torrent->id, $torrent->slug]) }}" class="btn tx-action tx-action-edit"
           aria-label="Edit {{ $torrent->name }}" data-bs-toggle="tooltip" title="Edit torrent">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
        </a>
    @endif

    @if($canDelete)
        <form method="POST" action="{{ route('torrents.destroy', $torrent->slug) }}" class="tx-delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn tx-action tx-action-delete" aria-label="Delete {{ $torrent->name }}" data-bs-toggle="tooltip" title="Delete torrent">
                <i class="bi bi-trash" aria-hidden="true"></i>
            </button>
        </form>
    @endif
</div>
