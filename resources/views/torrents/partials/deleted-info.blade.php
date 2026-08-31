@if($torrent->trashed())
<div class="card border-danger mt-5 mb-4 shadow-sm">
    <div class="card-body bg-dark text-danger">

        <div class="d-flex justify-content-between flex-wrap align-items-center">

            <div>
                <h5 class="mb-2">
                    <i class="bi bi-trash-fill"></i>
                    This Torrent Is Deleted
                </h5>

                <div class="small">

                    <div>
                        <strong>Deleted At:</strong>
                        {{ $torrent->deleted_at->format('M d, Y H:i') }}
                    </div>

                    <div>
                        <strong>Deleted By:</strong>
                        {{ $torrent->deletedBy->name ?? 'Unknown' }}
                    </div>

                    <div>
                        <strong>Reason:</strong>
                        {{ $torrent->deletion_reason ?? 'No reason provided' }}
                    </div>

                </div>
            </div>

            <div class="mt-3 mt-md-0">

                {{-- Restore --}}
                <form action="{{ route('torrents.restore', $torrent->id) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <button class="btn btn-success btn-sm">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Restore
                    </button>
                </form>

                {{-- Force Delete --}}
                @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
                <form action="{{ route('torrents.forceDelete', $torrent->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Permanent delete? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-x-circle"></i>
                        Permanent Delete
                    </button>
                </form>
                @endif

            </div>

        </div>

    </div>
</div>
@endif