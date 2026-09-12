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
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.88));
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .65rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.22);
    font-size: .82rem;
}

.elite-breadcrumb a {
    color: rgba(255,255,255,.72);
    text-decoration: none;
    transition: color .2s ease;
}

.elite-breadcrumb a:hover {
    color: var(--ui-accent, #2dd4bf);
}

.breadcrumb-separator {
    color: rgba(255,255,255,.25);
    font-size: .7rem;
}

.breadcrumb-current {
    color: rgba(255,255,255,.9);
}

.elite-breadcrumb i {
    margin-right: .25rem;
}

.uploads-card {
    background: linear-gradient(135deg, rgba(22,32,51,.97), rgba(15,23,42,.92));
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .7rem;
    box-shadow: 0 10px 28px rgba(0,0,0,.25);
    overflow: hidden;
}

.uploads-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.018);
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
    background: rgba(45,212,191,.07);
    border: 1px solid rgba(45,212,191,.18);
    color: var(--ui-accent, #2dd4bf);
    font-size: 1rem;
}

.uploads-header h5 {
    color: #e8f0f7;
    font-size: .94rem;
    font-weight: 600;
}

.uploads-subtitle {
    color: rgba(203,213,225,.48);
    font-size: .73rem;
}

.elite-outline-btn {
    flex-shrink: 0;
    border-color: rgba(255,255,255,.14);
    color: rgba(255,255,255,.78);
    border-radius: .5rem;
    font-size: .78rem;
    padding: .38rem .7rem;
    transition: all .2s ease;
}

.elite-outline-btn:hover,
.elite-outline-btn:focus {
    color: #fff;
    border-color: rgba(45,212,191,.5);
    background: rgba(45,212,191,.08);
    box-shadow: 0 0 0 .15rem rgba(45,212,191,.07);
}

.uploads-body {
    overflow: hidden;
}

.browse-header {
    color: rgba(203,213,225,.52);
    background: rgba(7,14,27,.4);
    border-bottom: 1px solid rgba(255,255,255,.075);
    font-size: .69rem;
    font-weight: 600;
    letter-spacing: .035em;
    text-transform: uppercase;
}

.torrent-row {
    min-height: 58px;
    border-bottom: 1px solid rgba(255,255,255,.055);
    transition: background .18s ease, border-color .18s ease;
}

.torrent-row:last-child {
    border-bottom: 0;
}

.torrent-row:hover {
    background: rgba(45,212,191,.025);
    border-color: rgba(45,212,191,.09);
}

.torrent-name {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    color: #dce7f3;
    font-size: .84rem;
    font-weight: 600;
    line-height: 1.4;
    text-decoration: none;
    overflow-wrap: anywhere;
}

.torrent-name i {
    flex: 0 0 auto;
    color: rgba(45,212,191,.72);
}

.torrent-name:hover {
    color: var(--ui-accent, #2dd4bf);
}

.deleted-badge {
    flex: 0 0 auto;
    color: #f8b4bb;
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.2);
    font-size: .64rem;
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
    font-size: .68rem;
    font-weight: 600;
}

.stat-badge.seed {
    color: #8fe7c3;
    background: rgba(52,211,153,.08);
    border: 1px solid rgba(52,211,153,.2);
}

.stat-badge.leech {
    color: #f5a7af;
    background: rgba(239,68,68,.07);
    border: 1px solid rgba(239,68,68,.2);
}

.stat-badge.done {
    color: #9ff8eb;
    background: rgba(45,212,191,.08);
    border: 1px solid rgba(45,212,191,.2);
}

.uploaded-time {
    color: rgba(203,213,225,.52);
    font-size: .72rem;
    white-space: nowrap;
}

.uploaded-time i {
    color: rgba(45,212,191,.6);
}

.delete-torrent-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #f5a7af;
    background: rgba(239,68,68,.07);
    border: 1px solid rgba(239,68,68,.2);
    border-radius: .42rem;
    transition: all .18s ease;
}

.delete-torrent-btn:hover,
.delete-torrent-btn:focus {
    color: #fff;
    background: rgba(239,68,68,.15);
    border-color: rgba(239,68,68,.4);
}

.torrent-deleted {
    opacity: .55;
    background: rgba(239,68,68,.025);
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
    background: rgba(45,212,191,.06);
    border: 1px solid rgba(45,212,191,.14);
    color: rgba(45,212,191,.55);
    font-size: 1.2rem;
}

.empty-torrents h5 {
    margin: 0;
    color: rgba(226,232,240,.62);
    font-size: .88rem;
    font-weight: 500;
}

.pagination {
    --bs-pagination-bg: rgba(22,32,51,.9);
    --bs-pagination-border-color: rgba(255,255,255,.08);
    --bs-pagination-color: rgba(255,255,255,.68);
    --bs-pagination-hover-bg: rgba(45,212,191,.08);
    --bs-pagination-hover-color: #8ff5e6;
    --bs-pagination-hover-border-color: rgba(45,212,191,.28);
    --bs-pagination-active-bg: rgba(45,212,191,.16);
    --bs-pagination-active-border-color: rgba(45,212,191,.4);
    --bs-pagination-active-color: #9ff8eb;
    font-size: .78rem;
}

.pagination .page-link {
    margin: 0 .12rem;
    border-radius: .42rem;
}


.delete-modal {
    background: linear-gradient(135deg, rgba(22,32,51,.98), rgba(15,23,42,.97));
    border: 1px solid rgba(255,255,255,.09);
    border-radius: .7rem;
    color: #e2e8f0;
    box-shadow: 0 20px 60px rgba(0,0,0,.55);
    overflow: hidden;
}

.delete-modal .modal-header {
    padding: .85rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: rgba(239,68,68,.035);
}

.delete-modal .modal-title {
    color: #f1f5f9;
    font-size: .92rem;
    font-weight: 600;
}

.delete-modal .modal-title i {
    color: #f5a7af;
}

.delete-modal .modal-body {
    padding: 1rem;
    color: rgba(226,232,240,.78);
    font-size: .84rem;
}

.delete-torrent-preview {
    display: flex;
    align-items: center;
    padding: .7rem .75rem;
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .5rem;
    color: #dce7f3;
    overflow-wrap: anywhere;
}

.delete-torrent-preview i {
    flex: 0 0 auto;
    color: rgba(45,212,191,.75);
}

.delete-warning {
    padding: .65rem .75rem;
    background: rgba(245,158,11,.055);
    border: 1px solid rgba(245,158,11,.16);
    border-radius: .45rem;
    color: rgba(246,217,139,.78);
    font-size: .74rem;
}

.delete-modal .modal-footer {
    padding: .7rem 1rem;
    border-top: 1px solid rgba(255,255,255,.07);
}

.modal-cancel-btn,
.modal-delete-btn {
    border-radius: .45rem;
    font-size: .76rem;
    font-weight: 600;
    padding: .4rem .7rem;
}

.modal-cancel-btn {
    color: rgba(255,255,255,.7);
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
}

.modal-cancel-btn:hover {
    color: #fff;
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.18);
}

.modal-delete-btn {
    color: #fff;
    background: rgba(239,68,68,.16);
    border: 1px solid rgba(239,68,68,.35);
}

.modal-delete-btn:hover {
    color: #fff;
    background: rgba(239,68,68,.26);
    border-color: rgba(239,68,68,.5);
}

@media (max-width: 768px) {
    .container-fluid.py-4 {
        padding-top: 1rem !important;
    }

    .elite-breadcrumb {
        margin-bottom: 1rem !important;
        font-size: .76rem;
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
        font-size: .8rem;
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
