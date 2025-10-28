<!-- resources/views/profile/torrents.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">

            <!-- Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h5 class="mb-0 text-center text-md-start">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> Torrents Uploaded by {{ $user->name }}
                    </h5>
                    <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="btn btn-sm w-100 w-md-auto">
                        <i class="bi bi-arrow-left"></i> Back to Profile
                    </a>
                </div>

                <div class="card-body">
                    @if($torrents->isEmpty())
                        <div class="alert alert-info text-center mb-0">
                            <i class="bi bi-info-circle me-2"></i> No torrents uploaded by this user.
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Seeders</th>
                                        <th>Leechers</th>
                                        <th>Times Completed</th>
                                        <th>Uploaded At</th>
                                        @if(Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)
                                            <th class="text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($torrents as $torrent)
                                        <tr>
                                            <td>
                                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-decoration-none fw-semibold">
                                                    {{ $torrent->name }}
                                                </a>
                                            </td>
                                            <td>{{ $torrent->seeders }}</td>
                                            <td>{{ $torrent->leechers }}</td>
                                            <td>{{ $torrent->times_completed }}</td>
                                            <td>{{ $torrent->created_at->format('d M Y, h:i A') }}</td>

                                            @if(Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)
                                                <td class="text-center">
                                                    <button class="btn btn-danger btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-torrent-id="{{ $torrent->id }}"
                                                            data-torrent-name="{{ $torrent->name }}">
                                                        <i class="bi bi-trash3-fill"></i> Delete
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="d-md-none">
                            @foreach($torrents as $torrent)
                                <div class="border rounded-3 p-3 mb-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-semibold text-decoration-none">
                                            {{ $torrent->name }}
                                        </a>
                                        @if(Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)
                                            <button class="btn btn-sm btn-danger ms-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-torrent-id="{{ $torrent->id }}"
                                                    data-torrent-name="{{ $torrent->name }}">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <ul class="list-unstyled small mb-0">
                                        <li><strong>Seeders:</strong> {{ $torrent->seeders }}</li>
                                        <li><strong>Leechers:</strong> {{ $torrent->leechers }}</li>
                                        <li><strong>Completed:</strong> {{ $torrent->times_completed }}</li>
                                        <li><strong>Uploaded:</strong> {{ $torrent->created_at->format('d M Y, h:i A') }}</li>
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $torrents->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm modal-md modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Torrent Deletion
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="deleteTorrentForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <p class="fw-semibold text-muted mb-3">
                Are you sure you want to delete the torrent:
                <span id="torrentName" class="text-danger"></span>?
            </p>

            <div class="mb-3">
                <label for="deletion_reason" class="form-label fw-semibold">Reason for deletion:</label>
                <select name="deletion_reason" id="deletion_reason" class="form-select">
                    <option value="">0 seeders and 0 leechers</option>
                    <option value="custom">Custom reason</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="customReasonContainer">
                <label for="custom_reason" class="form-label fw-semibold">Custom Reason:</label>
                <input type="text" name="custom_reason" id="custom_reason" class="form-control" placeholder="Enter your reason here...">
            </div>
        </div>

        <div class="modal-footer flex-column flex-sm-row">
          <button type="button" class="btn btn-secondary w-100 w-sm-auto mb-2 mb-sm-0" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-danger w-100 w-sm-auto">
            <i class="bi bi-trash-fill me-1"></i> Delete Torrent
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    #torrentName {
    display: inline-block;
    max-width: 100%;
    word-break: break-word;
    overflow-wrap: anywhere;
}
@media (max-width: 576px) {
    #deleteModal .modal-dialog {
        margin: 0 10px; /* tighter fit for small screens */
    }
    #deleteModal .modal-content {
        border-radius: 10px;
    }
}
</style>

<!-- Responsive Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteTorrentForm');
    const torrentNameSpan = document.getElementById('torrentName');
    const deletionReasonSelect = document.getElementById('deletion_reason');
    const customReasonContainer = document.getElementById('customReasonContainer');

    // Toggle custom reason
    deletionReasonSelect.addEventListener('change', function() {
        customReasonContainer.classList.toggle('d-none', this.value !== 'custom');
    });

    // Set modal content
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        deleteForm.action = `/profile/torrents/${button.dataset.torrentId}/delete`;
        torrentNameSpan.textContent = `"${button.dataset.torrentName}"`;
    });
});
</script>

@endsection
