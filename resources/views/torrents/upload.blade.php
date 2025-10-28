@extends('layouts.app')

@section('title', 'Upload')

@section('content')
@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">

            <!-- Page Header -->
            <h1 class="text-center mb-4 display-6 fw-bold text-gradient">
                📤 Upload a New Torrent
            </h1>

            <!-- Announce URL -->
            <div class="alert alert-info shadow-sm rounded d-flex justify-content-between align-items-center">
                <div>
                    <strong>Announce URL:</strong>
                    <a href="javascript:void(0);" onclick="copyToClipboard('http://last-torrents.org/announce/{{ $user->passkey }}')" class="text-decoration-underline">
                        http://last-torrents.org/announce/{{ $user->passkey }}
                    </a>
                    <p class="mb-0 small">Click to copy the URL while creating your torrent file.</p>
                </div>
                <i class="bi bi-clipboard-fill fs-3 text-primary"></i>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('torrents.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-lg p-4 rounded-4 bg-dark">
                @csrf

                <!-- Torrent File -->
                <div class="mb-3">
                    <label for="file" class="form-label fw-bold">Torrent File</label>
                    <input type="file" class="form-control form-control-lg" id="file" name="torrent" required onchange="setTorrentName()">
                </div>

                <!-- Torrent Name & Genre -->
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label fw-bold">Torrent Name</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="genre" class="form-label fw-bold">Genre <small>(e.g. Action, Drama)</small></label>
                        <input type="text" class="form-control form-control-lg" id="genre" name="genre" value="{{ old('genre') }}">
                    </div>
                </div>

                <!-- Steam ID -->
                <div class="mb-3 mt-3">
                    <label for="steamid" class="form-label fw-bold">Steam ID</label>
                    <input type="text" class="form-control form-control-lg" id="steamid" name="steamid" placeholder="ONLY INSERT THE ID FROM - https://store.steampowered.com/app/310950" value="{{ old('steamid') }}">
                </div>

                <!-- Category & Poster -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-bold">Category</label>
                        <select name="category_id" id="category_id" class="form-select form-select-lg" required onchange="toggleFieldsByCategory()">
                            @foreach($categories->sortByDesc(fn($cat) => $cat->id === 49) as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="poster" class="form-label fw-bold">Poster URL</label>
                        <input type="url" name="poster" id="poster" class="form-control form-control-lg" value="{{ old('poster') }}">
                    </div>
                </div>

                <!-- Screenshots Upload -->
                <div class="mb-3 mt-3">
                    <label for="images" class="form-label fw-bold">Screenshots (max 12)</label>
                    <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple>
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-3"></div>
                </div>

                <!-- Description with BBCode Tools -->
                <div class="mb-3 mt-4">
                    <label for="description" class="form-label fw-bold">Description</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="insertBBCode('b')" title="Bold"><i class="bi bi-type-bold"></i></button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="insertBBCode('i')" title="Italic"><i class="bi bi-type-italic"></i></button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="insertBBCode('u')" title="Underline"><i class="bi bi-type-underline"></i></button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="insertBBCode('center')" title="Center"><i class="bi bi-text-center"></i></button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="insertBBCode('quote')" title="Quote"><i class="bi bi-chat-left-quote"></i></button>
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="insertBBCode('youtube')" title="YouTube"><i class="bi bi-youtube"></i></button>
                        <button class="btn btn-sm btn-outline-success" type="button" onclick="insertBBCode('img')" title="Image"><i class="bi bi-card-image"></i></button>
                    </div>
                    <textarea class="form-control form-control-lg" id="description" name="description" oninput="resizeTextarea('description')" style="min-height: 180px;" required>{{ old('description') }}</textarea>
                </div>

                <!-- Conditional Fields (IMDB, MediaInfo) -->
                <div id="conditionalFields" class="mt-3" style="display: none;">
                     <div class="mb-3">
                        <label for="imdb_url" class="form-label fw-bold">IMDB URL</label>
                        <input type="text" class="form-control form-control-lg" id="imdb_url" name="imdb_url" value="{{ old('imdb_url') }}">
                        <button class="btn btn-outline-success mt-2" type="button" onclick="fetchIMDBInfo()">🎬 Fetch Info</button>
                    </div>
                    <div id="imdb-duplicate-warning" class="alert alert-warning mt-2 d-none rounded-3">
                        <strong>Similar torrents exist with this IMDb URL!</strong>
                        <ul id="existing-torrent-list" class="mb-0"></ul>
                    </div>
                    <div class="mb-3">
                        <label for="mediainfo" class="form-label fw-bold">Media Info</label>
                        <textarea class="form-control form-control-lg" id="mediainfo" name="mediainfo" oninput="resizeTextarea('mediainfo')" style="min-height: 120px;">{{ old('mediainfo') }}</textarea>
                    </div>
                </div>

                <!-- Torrent Tags -->
                @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
                <div class="mb-4 mt-3">
                    <label class="form-label fw-bold">Torrent Tags:</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="free" id="free" value="1" {{ old('free') ? 'checked' : '' }}>
                            <label class="form-check-label" for="free"><span class="badge bg-success">Free</span></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="double" id="double" value="1" {{ old('double') ? 'checked' : '' }}>
                            <label class="form-check-label" for="double"><span class="badge bg-warning text-dark">Double</span></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="sticky" id="sticky" value="1" {{ old('sticky') ? 'checked' : '' }}>
                            <label class="form-check-label" for="sticky"><span class="badge bg-danger">Sticky</span></label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="seedbox" id="seedbox" value="1" {{ old('seedbox') ? 'checked' : '' }}>
                            <label class="form-check-label" for="seedbox"><span class="badge bg-dark">Seedbox</span></label>
                        </div>
                        {{-- <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="external" id="external" value="1" {{ old('external') ? 'checked' : '' }}>
                            <label class="form-check-label" for="external"><span class="badge bg-dark">External</span></label>
                        </div> --}}
                    </div>
                </div>
                @endif

                <button type="submit" class="btn btn-gradient-primary w-100 py-3 fw-bold">🚀 Upload Torrent</button>
            </form>
        </div>
    </div>
</div>

@include('torrents.partials.scripts') <!-- Keep all JS in a partial for cleanliness -->

<style>
body::before {
    content: '';
    position: fixed;
    top:55px; left:0; right:0; bottom:0;
    background-image: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.9)), url('https://4kwallpapers.com/images/walls/thumbs_3t/8324.png');
    background-position: center top;
    background-size: cover;
    opacity:0.7;
    z-index:-1;
}
textarea{
resize: none;
overflow: auto;
max-height: 500px;
min-height: 150px;
}
</style>

@else
<div class="container mt-5">
    <div class="alert alert-danger bg-gradient-danger text-white border-0 shadow-lg rounded-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h1 class="alert-heading mb-2">Upload Permission Required</h1>
                <p class="mb-0">You are not authorized to upload torrents. Please contact staff if you believe this is an error.</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
