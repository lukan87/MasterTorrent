@extends('layouts.app')

@section('content')

@php
$status = request('status', 'active');
@endphp

<div class="min-vh-100 py-5">
    <div class="container">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h2 class="fw-bold mb-3 mb-md-0">🎬 Manage Torrents</h2>
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

            <option value="active" {{ request('status','active')=='active'?'selected':'' }}>
            Active
            </option>

            <option value="dead" {{ request('status')=='dead'?'selected':'' }}>
            Dead
            </option>

            <option value="trashed" {{ request('status')=='trashed'?'selected':'' }}>
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
            <option value="yes" {{ request('free')=='yes'?'selected':'' }}>Free</option>
            <option value="no" {{ request('free')=='no'?'selected':'' }}>Normal</option>
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
<div class="card rounded-lg shadow-sm">
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

<div class="row g-0 align-items-center border-bottom py-3 {{ $status === 'trashed' ? 'torrent-trashed' : '' }}">

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
class="btn btn-sm btn-outline-primary"
data-bs-toggle="tooltip"
title="Torrent Info">
<i class="bi bi-info-circle"></i>
</a>

@endif

@if($status === 'trashed')

<button
class="btn btn-sm btn-success"
data-bs-toggle="modal"
data-bs-target="#restoreTorrentModal"
data-id="{{ $torrent->id }}"
data-name="{{ $torrent->name }}">
<i class="bi bi-arrow-counterclockwise"></i>
</button>

<button
class="btn btn-sm btn-danger"
data-bs-toggle="modal"
data-bs-target="#forceDeleteModal"
data-id="{{ $torrent->id }}"
data-name="{{ $torrent->name }}"
title="Delete permanently">
<i class="bi bi-trash3"></i>
</button>

@else

<button
class="btn btn-sm btn-outline-danger"
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
This action **cannot be undone**.

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

.torrent-trashed {
    background: rgba(255,0,0,0.03);
}

/* Search Bar */

/* Admin search container */
.admin-search {
    background: linear-gradient(145deg,#1b1b1b90,#252525);
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}

/* Shared bar */
.premium-search-combined {
    display:flex;
    align-items:center;
    gap:6px;
    background:linear-gradient(145deg,#111,#1c1c1c);
    border:1px solid rgba(255,255,255,.12);
    border-radius:14px;
    padding:6px 10px;
    transition:.25s ease;
}

.premium-search-combined:focus-within {
    border-color:#7c8cff;
    box-shadow:0 0 0 3px rgba(124,140,255,.2);
}

/* icons */
.search-icon {
    color:#888;
}

/* input wrapper */
.search-input-wrapper {
    display:flex;
    align-items:center;
    flex:1;
}

.small-input{
    max-width:160px;
}

/* inputs */
.premium-input-combined {
    background:transparent;
    border:none;
    color:#fff;
}

.premium-input-combined::placeholder {
    color:#777;
}

/* divider */
.search-divider {
    width:1px;
    height:26px;
    background:rgba(255,255,255,.12);
    margin:0 6px;
}

/* select */
.premium-inline-select {
    display:flex;
    align-items:center;
    gap:4px;
}

.premium-select {
    background:transparent;
    border:none;
    color:#ccc;
    outline:none;
}

/* date */
.premium-date{
    background:transparent;
    border:none;
    color:#ccc;
}

/* button */
.premium-search-btn{
    background:#7c8cff;
    border:none;
    border-radius:8px;
    color:#fff;
    padding:6px 14px;
    font-weight:600;
    transition:.2s ease;
}

.premium-search-btn:hover{
    background:#6a78ff;
}

.premium-reset-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    background:#2a2a2a;
    border:1px solid rgba(255,255,255,.15);
    border-radius:8px;
    color:#ccc;
    padding:6px 10px;
    transition:.2s ease;
}

.premium-reset-btn:hover{
    background:#3a3a3a;
    color:#fff;
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
