@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <div class="elite-breadcrumb mb-4">
        <a href="{{ url('/') }}" class="breadcrumb-item">
            <i class="bi bi-house-door"></i> Home
        </a>

        <span class="breadcrumb-separator">
            <i class="bi bi-chevron-right"></i>
        </span>

        <a href="{{ route('profile.show',['id'=>$user->id,'name'=>$user->name]) }}" class="breadcrumb-item">
            <i class="bi bi-person-circle"></i> {{ $user->name }}
        </a>

        <span class="breadcrumb-separator">
            <i class="bi bi-chevron-right"></i>
        </span>

        <span class="breadcrumb-current">
            <i class="bi bi-cloud-arrow-up"></i> Uploaded Torrents
        </span>
    </div>

    <div class="elite-card uploads-card">

        <div class="uploads-header">
            <div class="uploads-heading">
                <div class="uploads-icon">
                    <i class="bi bi-cloud-arrow-up"></i>
                </div>

                <div>
                    <h5 class="mb-1">
                        Torrents Uploaded by {{ $user->name }}
                    </h5>

                    <span class="uploads-subtitle">
                        Torrents uploaded to the FileIplay community
                    </span>
                </div>
            </div>

            <a href="{{ route('profile.show',['id'=>$user->id,'name'=>$user->name]) }}"
               class="btn btn-outline-light btn-sm elite-outline-btn">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="uploads-body">

            @if($torrents->isEmpty())

                <div class="empty-torrents">
                    <div class="empty-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>

                    <h5>No torrents uploaded by this user</h5>
                </div>

            @else

                <div class="browse-header row g-0 px-3 py-2 align-items-center">
                    <div class="col-md-6">
                        Name
                    </div>

                    <div class="col-md-1 text-center">
                        Seed
                    </div>

                    <div class="col-md-1 text-center">
                        Leech
                    </div>

                    <div class="col-md-1 text-center">
                        Done
                    </div>

                    <div class="col-md-2 text-center">
                        Uploaded
                    </div>

                    @if(Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::OWNER)
                        <div class="col-md-1 text-center">
                            Action
                        </div>
                    @endif
                </div>

                @foreach($torrents as $torrent)

                    <div class="row torrent-row g-0 px-3 py-3 align-items-center {{ $torrent->trashed() ? 'torrent-deleted' : '' }}">

                        <div class="col-md-6 torrent-title-cell">
                            <a href="{{ route('torrents.show',['id'=>$torrent->id,'slug'=>$torrent->slug]) }}"
                               class="torrent-name">

                                <i class="bi bi-file-earmark-play me-2"></i>
                                {{ $torrent->name }}

                                @if($torrent->trashed())
                                    <span class="badge deleted-badge ms-2">Deleted</span>
                                @endif
                            </a>
                        </div>

                        <div class="col-md-1 text-center torrent-stat">
                            <span class="stat-badge seed">
                                {{ $torrent->seeders }}
                            </span>
                        </div>

                        <div class="col-md-1 text-center torrent-stat">
                            <span class="stat-badge leech">
                                {{ $torrent->leechers }}
                            </span>
                        </div>

                        <div class="col-md-1 text-center torrent-stat">
                            <span class="stat-badge done">
                                {{ $torrent->times_completed }}
                            </span>
                        </div>

                        <div class="col-md-2 text-center uploaded-time">
                            <i class="bi bi-clock me-1"></i>
                            {{ $torrent->created_at->diffForHumans() }}
                        </div>

                        @if(
                            !$torrent->trashed() &&
                            (Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::OWNER)
                        )
                            <div class="col-md-1 text-center">
                                <button
                                    class="btn btn-sm delete-torrent-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteTorrentModal"
                                    data-id="{{ $torrent->id }}"
                                    data-name="{{ $torrent->name }}"
                                    title="Delete torrent"
                                    data-bs-toggle="tooltip">

                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endif

                    </div>

                @endforeach

            @endif

        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>

</div>


    {{-- Delete Torrent Modal --}}
    <div class="modal fade" id="deleteTorrentModal" tabindex="-1" aria-labelledby="deleteTorrentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content delete-modal">

                <form method="POST" id="deleteTorrentForm">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTorrentModalLabel">
                            <i class="bi bi-trash3 me-2"></i>
                            Delete Torrent
                        </h5>

                        <button type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            Are you sure you want to delete
                            <strong id="torrentName"></strong>?
                        </p>

                        <label for="reasonSelect" class="form-label">
                            Deletion Reason
                        </label>

                        <select name="deletion_reason"
                                class="form-select"
                                id="reasonSelect">
                            <option value="0 seeders and 0 leechers">
                                0 seeders and 0 leechers
                            </option>
                            <option value="DMCA request">
                                DMCA request
                            </option>
                            <option value="Duplicate torrent">
                                Duplicate torrent
                            </option>
                            <option value="Bad content">
                                Bad content
                            </option>
                            <option value="custom">
                                Custom reason
                            </option>
                        </select>

                        <div class="mt-3 d-none" id="customReasonBox">
                            <input type="text"
                                   name="custom_reason"
                                   class="form-control"
                                   placeholder="Enter custom reason">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn modal-cancel-btn"
                                data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn modal-delete-btn">
                            <i class="bi bi-trash3 me-1"></i>
                            Delete Torrent
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

<style>
/* =========================================================
   FILEIPLAY — USER UPLOADED TORRENTS
   Dark navy / teal forum theme
========================================================= */

.elite-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
    padding: .65rem .85rem;
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.95)), var(--theme-surface, rgba(10,15,27,.88)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .65rem;
    box-shadow: 0 8px 24px var(--theme-shadow, rgba(0,0,0,.22));
    font-size: var(--site-font-body, 13px);
}

.elite-breadcrumb a {
    color: var(--theme-muted, rgba(255,255,255,.72));
    text-decoration: none;
    transition: color .2s ease;
}

.elite-breadcrumb a:hover {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.breadcrumb-separator {
    color: var(--theme-muted, rgba(255,255,255,.25));
    font-size: var(--site-font-small, 13px);
}

.breadcrumb-current {
    color: var(--theme-text, rgba(255,255,255,.9));
}

.elite-breadcrumb i {
    margin-right: .25rem;
}

.uploads-card {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.97)), var(--theme-surface, rgba(10,15,27,.92)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .7rem;
    box-shadow: 0 10px 28px var(--theme-shadow, rgba(0,0,0,.25));
    overflow: hidden;
}

.uploads-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.07));
    background: var(--theme-surface-alt, rgba(255,255,255,0.0126));
}

.uploads-heading {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
}

.uploads-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .55rem;
    background: var(--theme-teal-soft, rgba(45,212,191,.07));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.18));
    color: var(--ui-accent, var(--theme-on-action, #2dd4bf));
    font-size: 1rem;
}

.uploads-header h5 {
    color: var(--theme-text, #e8f0f7);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.uploads-subtitle {
    color: var(--theme-muted, rgba(203,213,225,.48));
    font-size: var(--site-font-small, 13px);
}

.elite-outline-btn {
    flex-shrink: 0;
    border-color: var(--theme-border, rgba(255,255,255,.14));
    color: var(--theme-muted, rgba(255,255,255,.78));
    border-radius: .5rem;
    font-size: var(--site-font-body, 13px);
    padding: .38rem .7rem;
    transition: all .2s ease;
}

.elite-outline-btn:hover,
.elite-outline-btn:focus {
    color:  var(--theme-text, #fff);
    border-color: var(--theme-teal-border, rgba(45,212,191,.5));
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    box-shadow: 0 0 0 .15rem var(--theme-shadow, rgba(45,212,191,.07));
}

.uploads-body {
    overflow: hidden;
}

.browse-header {
    color: var(--theme-muted, rgba(203,213,225,.52));
    background: var(--theme-surface, rgba(5,9,18,.4));
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.075));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    letter-spacing: .035em;
    text-transform: uppercase;
}

.torrent-row {
    min-height: 58px;
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.055));
    transition: background .18s ease, border-color .18s ease;
}

.torrent-row:last-child {
    border-bottom: 0;
}

.torrent-row:hover {
    background: var(--theme-teal-soft, rgba(45,212,191,.025));
    border-color: var(--theme-teal-border, rgba(45,212,191,.09));
}

.torrent-name {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    color: var(--theme-text, #dce7f3);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    line-height: 1.4;
    text-decoration: none;
    overflow-wrap: anywhere;
}

.torrent-name i {
    flex: 0 0 auto;
    color: var(--theme-teal-text, rgba(45,212,191,.72));
}

.torrent-name:hover {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.deleted-badge {
    flex: 0 0 auto;
    color: var(--theme-red-text, #f8b4bb);
    background: var(--theme-red-soft, rgba(239,68,68,.08));
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.2));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    padding: .27rem .45rem;
    border-radius: .38rem;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    padding: .25rem .42rem;
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
}

.stat-badge.seed {
    color: var(--theme-teal-text, #8fe7c3);
    background: var(--theme-teal-soft, rgba(52,211,153,.08));
    border: 1px solid var(--theme-teal-border, rgba(52,211,153,.2));
}

.stat-badge.leech {
    color: var(--theme-red-text, #f5a7af);
    background: var(--theme-red-soft, rgba(239,68,68,.07));
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.2));
}

.stat-badge.done {
    color: var(--theme-teal-text, #9ff8eb);
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.2));
}

.uploaded-time {
    color: var(--theme-muted, rgba(203,213,225,.52));
    font-size: var(--site-font-small, 13px);
    white-space: nowrap;
}

.uploaded-time i {
    color: var(--theme-teal-text, rgba(45,212,191,.6));
}

.delete-torrent-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-red-text, #f5a7af);
    background: var(--theme-red-soft, rgba(239,68,68,.07));
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.2));
    border-radius: .42rem;
    transition: all .18s ease;
}

.delete-torrent-btn:hover,
.delete-torrent-btn:focus {
    color: var(--theme-text, #fff);
    background: var(--theme-red-soft, rgba(239,68,68,.15));
    border-color: var(--theme-red-border, rgba(239,68,68,.4));
}

.torrent-deleted {
    opacity: .55;
    background: var(--theme-red-soft, rgba(239,68,68,.025));
}

.empty-torrents {
    padding: 3rem 1rem;
    text-align: center;
}

.empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .65rem;
    background: var(--theme-teal-soft, rgba(45,212,191,.06));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.14));
    color: var(--theme-teal-text, rgba(45,212,191,.55));
    font-size: 1.2rem;
}

.empty-torrents h5 {
    margin: 0;
    color: var(--theme-muted, rgba(226,232,240,.62));
    font-size: var(--site-font-body, 13px);
    font-weight: 500;
}

.pagination {
    --bs-pagination-bg: var(--theme-surface, rgba(14,21,33,.9));
    --bs-pagination-border-color: var(--theme-border, rgba(255,255,255,.08));
    --bs-pagination-color: var(--theme-muted, rgba(255,255,255,.68));
    --bs-pagination-hover-bg: var(--theme-teal-soft, rgba(45,212,191,.08));
    --bs-pagination-hover-color: var(--theme-teal-text, #8ff5e6);
    --bs-pagination-hover-border-color: var(--theme-teal-border, rgba(45,212,191,.28));
    --bs-pagination-active-bg: var(--theme-teal-soft, rgba(45,212,191,.16));
    --bs-pagination-active-border-color: var(--theme-teal-border, rgba(45,212,191,.4));
    --bs-pagination-active-color: var(--theme-teal-text, #9ff8eb);
    font-size: var(--site-font-body, 13px);
}

.pagination .page-link {
    margin: 0 .12rem;
    border-radius: .42rem;
}


.delete-modal {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.98)), var(--theme-surface, rgba(10,15,27,.97)));
    border: 1px solid var(--theme-border, rgba(255,255,255,.09));
    border-radius: .7rem;
    color: var(--theme-text, #e2e8f0);
    box-shadow: 0 20px 60px var(--theme-shadow, rgba(0,0,0,.55));
    overflow: hidden;
}

.delete-modal .modal-header {
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.07));
    background: var(--theme-red-soft, rgba(239,68,68,.035));
}

.delete-modal .modal-title {
    color: var(--theme-text, #f1f5f9);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.delete-modal .modal-title i {
    color: var(--theme-red-text, #f5a7af);
}

.delete-modal .modal-body {
    padding: 1rem;
    color: var(--theme-muted, rgba(226,232,240,.78));
    font-size: var(--site-font-body, 13px);
}

.delete-torrent-preview {
    display: flex;
    align-items: center;
    padding: .7rem .75rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0175));
    border: 1px solid var(--theme-border, rgba(255,255,255,.07));
    border-radius: .5rem;
    color: var(--theme-text, #dce7f3);
    overflow-wrap: anywhere;
}

.delete-torrent-preview i {
    flex: 0 0 auto;
    color: var(--theme-teal-text, rgba(45,212,191,.75));
}

.delete-warning {
    padding: .65rem .75rem;
    background: var(--theme-amber-soft, rgba(245,158,11,.055));
    border: 1px solid var(--theme-amber-border, rgba(245,158,11,.16));
    border-radius: .45rem;
    color: var(--theme-amber-text, rgba(246,217,139,.78));
    font-size: var(--site-font-small, 13px);
}

.delete-modal .modal-footer {
    padding: .7rem 1rem;
    border-top: 1px solid var(--theme-border, rgba(255,255,255,.07));
}

.modal-cancel-btn,
.modal-delete-btn {
    border-radius: .45rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    padding: .4rem .7rem;
}

.modal-cancel-btn {
    color: var(--theme-muted, rgba(255,255,255,.7));
    background: var(--theme-surface-alt, rgba(255,255,255,0.028));
    border: 1px solid var(--theme-border, rgba(255,255,255,.1));
}

.modal-cancel-btn:hover {
    color: var(--theme-text, #fff);
    background: var(--theme-surface-alt, rgba(255,255,255,0.056));
    border-color: var(--theme-border, rgba(255,255,255,.18));
}

.modal-delete-btn {
    color: var(--theme-text, #fff);
    background: var(--theme-red-soft, rgba(239,68,68,.16));
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.35));
}

.modal-delete-btn:hover {
    color: var(--theme-text, #fff);
    background: var(--theme-red-soft, rgba(239,68,68,.26));
    border-color: var(--theme-red-border, rgba(239,68,68,.5));
}

@media (max-width: 768px) {
    .container-fluid.py-4 {
        padding-top: 1rem !important;
    }

    .elite-breadcrumb {
        margin-bottom: 1rem !important;
        font-size: var(--site-font-body, 13px);
        padding: .6rem .7rem;
    }

    .uploads-header {
        align-items: flex-start;
        flex-direction: column;
        padding: .85rem;
    }

    .elite-outline-btn {
        width: 100%;
    }

    .browse-header,
    .torrent-row {
        min-width: 760px;
    }

    .uploads-body {
        overflow-x: auto;
    }

    .browse-header {
        padding-left: .7rem !important;
        padding-right: .7rem !important;
    }

    .torrent-row {
        padding-left: .7rem !important;
        padding-right: .7rem !important;
    }

    .torrent-name {
        font-size: var(--site-font-body, 13px);
    }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('deleteTorrentModal');
    const form = document.getElementById('deleteTorrentForm');
    const nameField = document.getElementById('torrentName');
    const reasonSelect = document.getElementById('reasonSelect');
    const customReasonBox = document.getElementById('customReasonBox');

    if (!modalElement || !form) {
        return;
    }

    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        const torrentId = button.getAttribute('data-id');
        const torrentName = button.getAttribute('data-name');

        if (nameField) {
            nameField.textContent = torrentName || 'this torrent';
        }

        if (torrentId) {
            form.action = '/admin/torrents/' + encodeURIComponent(torrentId) + '/destroy';
        }
    });

    if (reasonSelect && customReasonBox) {
        reasonSelect.addEventListener('change', function () {
            customReasonBox.classList.toggle('d-none', this.value !== 'custom');
        });
    }
});
</script>

@endsection
