@extends('layouts.app')

@section('content')

@php

$status = request('status', 'active');

@endphp

<div class="min-vh-100 py-5">

    <div class="container admin-torrents-page">

        <!-- Header -->

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

            <h2 class="admin-torrents-header mb-3 mb-md-0"><i class="bi bi-collection-play me-2"></i>Manage Torrents</h2>

        </div>

        <!-- Filters -->

      <!-- Premium Admin Search -->

<div class="card admin-search border-0 mb-4">

<div class="card-body">

<form method="GET" action="{{ route('admin.torrents.index') }}">

<div class="premium-search-combined">

    <!-- SEARCH NAME -->

    <div class="search-input-wrapper">

        <i class="bi bi-search search-icon"></i>

        <input

            type="text"

            name="search"

            class="form-control premium-input-combined"

            placeholder="Torrent name…"

            value="{{ request('search') }}">

    </div>

    <div class="search-divider"></div>

    <!-- UPLOADER -->

    <div class="search-input-wrapper small-input">

        <i class="bi bi-person search-icon"></i>

        <input

            type="text"

            name="uploader"

            class="form-control premium-input-combined"

            placeholder="Uploader"

            value="{{ request('uploader') }}">

    </div>

    <div class="search-divider"></div>

    <!-- STATUS -->

    <div class="premium-inline-select">

        <i class="bi bi-activity me-1 search-icon"></i>

        <select name="status" class="premium-select">

            <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>

            Active

            </option>

            <option value="dead" {{ request('status') == 'dead' ? 'selected' : '' }}>

            Dead

            </option>

            <option value="trashed" {{ request('status') == 'trashed' ? 'selected' : '' }}>

            Trashed

            </option>

        </select>

    </div>

    <div class="search-divider"></div>

    <!-- FREE -->

    <div class="premium-inline-select">

        <i class="bi bi-gem me-1 search-icon"></i>

        <select name="free" class="premium-select">

            <option value="">All</option>

            <option value="yes" {{ request('free') == 'yes' ? 'selected' : '' }}>Free</option>

            <option value="no" {{ request('free') == 'no' ? 'selected' : '' }}>Normal</option>

        </select>

    </div>

    <div class="search-divider"></div>

    <!-- DATE -->

    <input type="date" name="from" class="premium-date" value="{{ request('from') }}">

    <span class="text-muted small">→</span>

    <input type="date" name="to" class="premium-date" value="{{ request('to') }}">

    <div class="search-divider"></div>

    <!-- BUTTON -->

    <button class="premium-search-btn">

    <i class="bi bi-search me-1"></i>

    Search

</button>

<a href="{{ route('admin.torrents.index') }}" class="premium-reset-btn">

    <i class="bi bi-arrow-counterclockwise"></i>

</a>

</div>

</form>

</div>

</div>

        <!-- Torrent List -->

<div class="torrent-list-card">

<div class="card-body p-0">

{{-- HEADER --}}

<div class="row g-0 border-bottom small text-uppercase text-muted align-items-center torrent-header">

    <div class="col-12 col-md-7 px-3 py-3"></div>

   <div class="d-none d-md-block col-md-1 text-center py-3">

@if($status === 'trashed')

<i class="bi bi-trash3 text-danger fs-5"

data-bs-toggle="tooltip"

title="Deleted at"></i>

@else

<i class="bi bi-clock text-warning fs-5"

data-bs-toggle="tooltip"

title="Created at"></i>

@endif

</div>

    <div class="d-none d-md-block col-md-1 text-center py-3">

        <i class="bi bi-floppy text-success fs-5"></i>

    </div>

    <div class="col-6 col-md-1 text-center py-3">

@if($status === 'trashed')

<i class="bi bi-person-x text-danger fs-5"

data-bs-toggle="tooltip"

title="Deleted by"></i>

@else

<i class="bi bi-person-up text-success fs-5"

data-bs-toggle="tooltip"

title="Uploader"></i>

@endif

</div>

    <div class="col-12 col-md-2 text-center px-3 py-3">

        <i class="bi bi-gear fs-5"></i>

    </div>

</div>

{{-- ROWS --}}

@forelse($torrents as $torrent)

<div class="row g-0 align-items-center border-bottom py-3 torrent-row {{ $status === 'trashed' ? 'torrent-trashed' : '' }}">

{{-- TORRENT INFO --}}

<div class="col-12 col-md-7 d-flex px-3">

<div class="torrent-name-tags">

@if($status === 'trashed')

<span class="torrent-title text-muted">

<i class="bi bi-trash3 text-danger me-1"></i>

{{ $torrent->name }}

</span>

<div class="small text-danger">

Deleted {{ $torrent->deleted_at?->format('M d, Y H:i') }}

<span class="text-muted">({{ $torrent->deleted_at?->diffForHumans() }})</span>

@if($torrent->deletion_reason)

<br>Reason: {{ $torrent->deletion_reason }}

@endif

</div>

@else

<a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}">

<span class="torrent-title">

{{ $torrent->name }}

</span>

</a>

@endif

</div>

</div>

{{-- AGE --}}

<div class="d-none d-md-block col-md-1 text-center small fw-bold">

@if($status === 'trashed')

{{ $torrent->deleted_at?->format('M d, Y') }}

@else

{{ $torrent->created_at->format('M d, Y') }}

@endif

</div>

{{-- COMPLETED --}}

<div class="d-none d-md-block col-md-1 text-center text-success fw-bold">

{{ $torrent->times_completed }}

</div>

{{-- UPLOADER --}}

<div class="col-6 col-md-1 text-center small">

@if($status === 'trashed')

@if($torrent->deletedBy)

<a href="{{ route('profile.show', $torrent->deletedBy->id) }}" class="text-danger">

<i class="bi bi-person-x"></i>

{{ $torrent->deletedBy->name }}

</a>

@else

<span class="text-danger">

<i class="bi bi-person-x"></i>

Unknown

</span>

@endif

@else

@if($torrent->uploader)

<a href="{{ route('profile.show', $torrent->uploader->id) }}">

{{ $torrent->uploader->name }}

</a>

@endif

@endif

</div>

{{-- ACTIONS --}}

<div class="col-12 col-md-2 px-1">

<div class="d-flex justify-content-center gap-1">

    @if($status === 'trashed')

    @else

<a href="{{ route('admin.torrents.show', $torrent->id) }}"

class="btn btn-sm btn-outline-primary torrent-action-btn"

data-bs-toggle="tooltip"

title="Torrent Info">

<i class="bi bi-info-circle"></i>

</a>

@endif

@if($status === 'trashed')

<button

class="btn btn-sm btn-success torrent-action-btn"

data-bs-toggle="modal"

data-bs-target="#restoreTorrentModal"

data-id="{{ $torrent->id }}"

data-name="{{ $torrent->name }}">

<i class="bi bi-arrow-counterclockwise"></i>

</button>

<button

class="btn btn-sm btn-danger torrent-action-btn"

data-bs-toggle="modal"

data-bs-target="#forceDeleteModal"

data-id="{{ $torrent->id }}"

data-name="{{ $torrent->name }}"

title="Delete permanently">

<i class="bi bi-trash3"></i>

</button>

@else

<button

class="btn btn-sm btn-outline-danger torrent-action-btn"

data-bs-toggle="modal"

data-bs-target="#deleteTorrentModal"

data-id="{{ $torrent->id }}"

data-name="{{ $torrent->name }}">

<i class="bi bi-trash"></i>

</button>

@endif

</div>

</div>

</div>

@empty

<div class="text-center py-5 text-muted">

No torrents found.

</div>

@endforelse

</div>

</div>

        <!-- Pagination -->

        <div class="d-flex justify-content-center mt-4">

            {{ $torrents->links('pagination::bootstrap-5') }}

        </div>

    </div>

</div>

<!-- Delete Torrent Modal -->

<div class="modal fade" id="deleteTorrentModal" tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="deleteTorrentForm">

@csrf

@method('DELETE')

<div class="modal-header">

<h5 class="modal-title">Delete Torrent</h5>

<button type="button" class="btn-close" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<p class="mb-3">

Are you sure you want to delete

<strong id="torrentName"></strong> ?

</p>

<label class="form-label">Reason</label>

<select name="deletion_reason" class="form-select" id="reasonSelect">

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

<input

type="text"

name="custom_reason"

class="form-control"

placeholder="Enter custom reason">

</div>

</div>

<div class="modal-footer">

<button

type="button"

class="btn btn-secondary"

data-bs-dismiss="modal">

Cancel

</button>

<button

type="submit"

class="btn btn-danger">

Delete Torrent

</button>

</div>

</form>

</div>

</div>

</div>

<!-- Force Delete Modal -->

<div class="modal fade" id="forceDeleteModal" tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="forceDeleteForm">

@csrf

@method('DELETE')

<div class="modal-header bg-danger text-white">

<h5 class="modal-title">

<i class="bi bi-exclamation-triangle"></i>

Permanent Delete

</h5>

<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<p class="mb-3">

You are about to permanently delete:

<strong id="forceTorrentName"></strong>

</p>

<div class="alert alert-danger mb-0">

<i class="bi bi-exclamation-triangle"></i>

This action cannot be undone.

</div>

</div>

<div class="modal-footer">

<button

type="button"

class="btn btn-secondary"

data-bs-dismiss="modal">

Cancel

</button>

<button

type="submit"

class="btn btn-danger">

<i class="bi bi-trash3"></i>

Permanent Delete

</button>

</div>

</form>

</div>

</div>

</div>

<!-- Restore Torrent Modal -->

<div class="modal fade" id="restoreTorrentModal" tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST" id="restoreTorrentForm">

@csrf

<div class="modal-header bg-success text-white">

<h5 class="modal-title">

<i class="bi bi-arrow-counterclockwise"></i>

Restore Torrent

</h5>

<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<p>

Restore torrent:

<strong id="restoreTorrentName"></strong> ?

</p>

</div>

<div class="modal-footer">

<button

type="button"

class="btn btn-secondary"

data-bs-dismiss="modal">

Cancel

</button>

<button

type="submit"

class="btn btn-success">

Restore Torrent

</button>

</div>

</form>

</div>

</div>

</div>

<style>
    .admin-torrents-page {
        color: #dbe7ef;
        font-size: 14px;
    }

    .admin-torrents-header {
        color: #f3f8fb;
        font-size: 21px;
        font-weight: 700;
        letter-spacing: -.01em;
    }

    .admin-torrents-header i {
        color: var(--ui-accent, #20c997);
    }

    .admin-search {
        position: relative;
        background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
        border: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
        border-radius: .7rem;
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
        overflow: hidden;
    }

    .admin-search::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--ui-accent, #20c997);
        opacity: .85;
    }

    .premium-search-combined {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(7,15,27,.45);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: .55rem;
        padding: 5px 7px;
        transition: .18s ease;
    }

    .premium-search-combined:focus-within {
        border-color: rgba(32,201,151,.4);
        box-shadow: 0 0 0 .15rem rgba(32,201,151,.06);
    }

    .search-icon {
        color: #718596;
        flex: 0 0 auto;
    }

    .search-input-wrapper {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
    }

    .small-input {
        max-width: 160px;
    }

    .premium-input-combined,
    .premium-select,
    .premium-date {
        background: transparent !important;
        border: 0 !important;
        color: #dce8ed !important;
        box-shadow: none !important;
        font-size: 13px;
        min-height: 30px;
    }

    .premium-input-combined::placeholder {
        color: #65798a;
    }

    .premium-select option {
        background: #172234;
        color: #e7eef2;
    }

    .premium-date {
        max-width: 135px;
        color-scheme: dark;
    }

    .search-divider {
        width: 1px;
        height: 25px;
        background: rgba(255,255,255,.08);
        margin: 0 4px;
        flex: 0 0 auto;
    }

    .premium-inline-select {
        display: flex;
        align-items: center;
        gap: 3px;
        flex: 0 0 auto;
    }

    .premium-search-btn,
    .premium-reset-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .45rem;
        font-size: 12px;
        font-weight: 700;
        transition: .18s ease;
        text-decoration: none;
    }

    .premium-search-btn {
        background: rgba(32,201,151,.13);
        border: 1px solid rgba(32,201,151,.28);
        color: #72e3bb;
        padding: 6px 12px;
    }

    .premium-search-btn:hover {
        background: rgba(32,201,151,.2);
        border-color: rgba(32,201,151,.45);
        color: #a2f1d3;
    }

    .premium-reset-btn {
        width: 31px;
        height: 31px;
        background: rgba(255,255,255,.035);
        border: 1px solid rgba(255,255,255,.08);
        color: #9aabb8;
        padding: 0;
    }

    .premium-reset-btn:hover {
        background: rgba(255,255,255,.07);
        color: #fff;
        border-color: rgba(32,201,151,.25);
    }

    .torrent-list-card {
        position: relative;
        background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
        border: 1px solid var(--ui-border, rgba(255,255,255,.08));
        border-radius: .7rem;
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
        overflow: hidden;
    }

    .torrent-header {
        background: rgba(255,255,255,.025);
        border-color: rgba(255,255,255,.07) !important;
    }

    .torrent-header i {
        opacity: .9;
    }

    .torrent-row {
        transition: background .16s ease, border-color .16s ease;
    }

    .torrent-row:hover {
        background: rgba(32,201,151,.025);
    }

    .torrent-trashed {
        background: rgba(220,53,69,.035);
    }

    .torrent-trashed:hover {
        background: rgba(220,53,69,.055);
    }

    .torrent-name-tags {
        min-width: 0;
        width: 100%;
    }

    .torrent-title {
        color: #dce8ed;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.45;
        word-break: break-word;
    }

    a .torrent-title {
        color: #dce8ed;
        text-decoration: none;
        transition: color .16s ease;
    }

    a:hover .torrent-title {
        color: var(--ui-accent, #20c997);
    }

    .torrent-date,
    .torrent-uploader {
        color: #a7bac6;
        font-size: 12px;
    }

    .torrent-uploader a {
        color: #9edcc8;
        text-decoration: none;
    }

    .torrent-uploader a:hover {
        color: #fff;
    }

    .torrent-action-btn {
        min-width: 31px;
    }

    .torrent-list-card .btn {
        border-radius: .4rem;
        font-size: 12px;
    }

    .torrent-list-card .btn-outline-primary {
        color: #7fdcca;
        border-color: rgba(32,201,151,.28);
    }

    .torrent-list-card .btn-outline-primary:hover {
        color: #fff;
        background: rgba(32,201,151,.12);
        border-color: rgba(32,201,151,.45);
    }

    .torrent-list-card .btn-outline-danger {
        color: #ff8e98;
        border-color: rgba(220,53,69,.28);
    }

    .torrent-list-card .btn-outline-danger:hover {
        color: #fff;
        background: rgba(220,53,69,.12);
        border-color: rgba(220,53,69,.45);
    }

    .pagination {
        gap: 3px;
    }

    .pagination .page-link {
        background: rgba(22,32,51,.9);
        border-color: rgba(255,255,255,.08);
        color: #aabcc7;
        font-size: 12px;
        border-radius: .4rem !important;
    }

    .pagination .page-item.active .page-link {
        background: rgba(32,201,151,.14);
        border-color: rgba(32,201,151,.3);
        color: #73e2bb;
    }

    .pagination .page-link:hover {
        background: rgba(32,201,151,.08);
        color: #fff;
        border-color: rgba(32,201,151,.25);
    }

    .modal-content {
        background: linear-gradient(135deg, rgba(22,32,51,.98), rgba(15,23,42,.96));
        border: 1px solid var(--ui-border, rgba(255,255,255,.08));
        border-radius: .7rem;
        color: #dbe7ef;
        box-shadow: 0 18px 50px rgba(0,0,0,.4);
    }

    .modal-header,
    .modal-footer {
        border-color: rgba(255,255,255,.07);
    }

    .modal-header:not(.bg-danger):not(.bg-success) {
        background: rgba(255,255,255,.02);
    }

    .modal-title {
        font-size: 15px;
        font-weight: 700;
    }

    .modal-body {
        font-size: 13px;
    }

    .modal .form-label {
        color: #9fb0bc;
        font-size: 12px;
        font-weight: 600;
    }

    .modal .form-select,
    .modal .form-control {
        background: rgba(7,15,27,.55);
        border: 1px solid rgba(255,255,255,.1);
        color: #dbe7ef;
        font-size: 13px;
    }

    .modal .form-select:focus,
    .modal .form-control:focus {
        background: rgba(7,15,27,.65);
        color: #fff;
        border-color: rgba(32,201,151,.4);
        box-shadow: 0 0 0 .15rem rgba(32,201,151,.07);
    }

    .modal .form-select option {
        background: #172234;
        color: #fff;
    }

    .modal .alert {
        font-size: 12px;
        border-radius: .5rem;
    }

    @media (max-width: 991.98px) {
        .premium-search-combined {
            flex-wrap: wrap;
            padding: 8px;
        }

        .search-divider {
            display: none;
        }

        .search-input-wrapper,
        .small-input,
        .premium-inline-select {
            flex: 1 1 45%;
            max-width: none;
        }

        .premium-date {
            flex: 1 1 30%;
            max-width: none;
        }

        .premium-search-btn {
            flex: 1 1 auto;
        }
    }

    @media (max-width: 575.98px) {
        .admin-torrents-header {
            font-size: 18px;
        }

        .premium-search-combined {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .search-input-wrapper,
        .small-input,
        .premium-inline-select,
        .premium-date {
            width: 100%;
            min-width: 0;
        }

        .premium-search-btn {
            width: 100%;
        }

        .premium-reset-btn {
            width: 100%;
            height: 32px;
        }

        .torrent-title {
            font-size: 13px;
        }

        .torrent-list-card .btn {
            padding: .3rem .45rem;
        }
    }
</style>

<script>

document.addEventListener("DOMContentLoaded", function(){

const modal = document.getElementById("deleteTorrentModal");

const form = document.getElementById("deleteTorrentForm");

const nameField = document.getElementById("torrentName");

const reasonSelect = document.getElementById("reasonSelect");

const customBox = document.getElementById("customReasonBox");

modal.addEventListener("show.bs.modal", function(event){

const button = event.relatedTarget;

const torrentId = button.getAttribute("data-id");

const torrentName = button.getAttribute("data-name");

nameField.textContent = torrentName;

form.action = "/admin/torrents/" + torrentId + "/destroy";

});

reasonSelect.addEventListener("change", function(){

if(this.value === "custom"){

customBox.classList.remove("d-none");

}else{

customBox.classList.add("d-none");

}

});

});

const forceModal = document.getElementById("forceDeleteModal");

const forceForm = document.getElementById("forceDeleteForm");

const forceName = document.getElementById("forceTorrentName");

forceModal.addEventListener("show.bs.modal", function(event){

const button = event.relatedTarget;

const torrentId = button.getAttribute("data-id");

const torrentName = button.getAttribute("data-name");

forceName.textContent = torrentName;

forceForm.action = "/admin/torrents/" + torrentId + "/force";

});

const restoreModal = document.getElementById("restoreTorrentModal");

const restoreForm = document.getElementById("restoreTorrentForm");

const restoreName = document.getElementById("restoreTorrentName");

restoreModal.addEventListener("show.bs.modal", function(event){

const button = event.relatedTarget;

const torrentId = button.getAttribute("data-id");

const torrentName = button.getAttribute("data-name");

restoreName.textContent = torrentName;

restoreForm.action = "/admin/torrents/" + torrentId + "/restore";

});

</script>

@endsection
