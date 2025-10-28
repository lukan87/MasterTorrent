@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Edit Torrent: {{ $torrent->name }}</h4>
            <a href="{{ route('torrents.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('torrents.update', $torrent->slug) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to update this torrent?');">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label"><i class="bi bi-card-text me-1"></i>Torrent Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $torrent->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label"><i class="bi bi-tags me-1"></i>Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->id == $torrent->category_id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="genre" class="form-label"><i class="bi bi-collection me-1"></i>Genre</label>
                    <input type="text" name="genre" id="genre" class="form-control" value="{{ old('genre', $torrent->genre) }}" placeholder="Genre1, Genre2">
                </div>

                <div class="mb-3">
                    <label for="steamid" class="form-label"><i class="bi bi-controller me-1"></i>Steam ID EG: https://store.steampowered.com/app/310950 Use ONLY 310950</label>
                    <input type="text" name="steamid" id="steamid" class="form-control" placeholder="STEAM ID - EX: 310950" value="{{ old('steamid', $torrent->steamid) }}">
                </div>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-bookmark-star me-1"></i>Torrent Tags</label><br>
                    @foreach (['free' => 'Free', 'double' => 'Double', 'sticky' => 'Sticky', 'seedbox' => 'Seedbox'] as $field => $label)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="{{ $field }}" id="{{ $field }}" class="form-check-input" value="1" {{ old($field, $torrent->$field) ? 'checked' : '' }}>
                            <label for="{{ $field }}" class="form-check-label">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                @endif

                <hr class="my-4">

                <div class="mb-3">
                    <label for="imdb_url" class="form-label"><i class="bi bi-film me-1"></i>IMDB URL</label>
                    <input type="url" name="imdb_url" id="imdb_url" class="form-control" value="{{ old('imdb_url', $torrent->imdb_url) }}">
                    <button type="button" class="btn btn-outline-primary mt-2" onclick="fetchIMDBInfo()">
                        <i class="bi bi-cloud-download me-1"></i>Fetch Info
                    </button>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label"><i class="bi bi-info-square me-1"></i>Description</label>
                    @include('torrents.partials.description_editor')
                    <textarea name="description" id="description" class="form-control" rows="6" oninput="adjustTextareaHeight(this)">{{ old('description', $torrent->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="mediainfo" class="form-label"><i class="bi bi-music-note-list me-1"></i>Media Info</label>
                    <textarea name="mediainfo" id="mediainfo" class="form-control" rows="6" oninput="adjustTextareaHeight(this)">{{ old('mediainfo', $torrent->mediainfo) }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="poster" class="form-label"><i class="bi bi-image me-1"></i>Poster URL</label>
                        <input type="text" name="poster" id="poster" class="form-control" value="{{ old('poster', $torrent->poster) }}">
                        @if ($torrent->poster)
                            <img src="{{ $torrent->poster }}" alt="Poster" class="img-thumbnail mt-2" style="max-width: 150px;">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="background" class="form-label"><i class="bi bi-card-image me-1"></i>Background URL</label>
                        <input type="url" name="background" id="background" class="form-control" value="{{ old('background', $torrent->background) }}">
                        @if ($torrent->background)
                            <img src="{{ $torrent->background }}" alt="Background" class="img-thumbnail mt-2" style="max-width: 350px;">
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="images" class="form-label"><i class="bi bi-images me-1"></i>Upload New Images</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                </div>

                @if ($torrent->images && $torrent->images->count())
                    <div class="mb-3">
                        <label class="form-label">Current Images</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($torrent->images as $image)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $image->path) }}" class="img-thumbnail" style="max-width: 150px;">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="form-check-input" id="delete_image_{{ $image->id }}">
                                        <label for="delete_image_{{ $image->id }}" class="form-check-label">Delete</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <label for="trailer" class="form-label"><i class="bi bi-play-btn me-1"></i>Trailer</label>
                    <input type="url" name="trailer" id="trailer" class="form-control" value="{{ old('trailer', $torrent->trailer) }}">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Update Torrent
                    </button>
                    <a href="{{ route('torrents.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
    <div class="card mt-5 shadow-lg">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">Delete Torrent</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('torrents.destroy', $torrent->slug) }}" method="POST" class="d-inline-block" onsubmit="return confirmDelete()">
                @csrf
                @method('DELETE')

                <!-- Reason for Deletion -->
                <div class="form-group mb-4">
                    <label class="form-label">Reason for Deletion:</label>

                    <!-- Radio buttons for predefined reasons -->
                    <div class="form-check">
                        <input type="radio" name="deletion_reason" id="dead" value="dead" class="form-check-input" onchange="toggleCustomReason(this)" required>
                        <label class="form-check-label" for="dead">Torrent has 0 seeders and 0 leechers</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" name="deletion_reason" id="custom" value="custom" class="form-check-input" onchange="toggleCustomReason(this)">
                        <label class="form-check-label" for="custom">Custom Reason</label>
                    </div>

                    <!-- Custom Reason Text Area (will show when "Custom Reason" is selected) -->
                    <div class="form-group mb-4" id="custom_reason_div" style="display:none;">
                        <textarea name="custom_reason" id="custom_reason" class="form-control" placeholder="Enter a valid reason for deleting the torrent." rows="4" cols="200"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-danger px-4 py-2">Delete Torrent</button>
                    <a href="{{ route('torrents.index') }}" class="btn btn-secondary px-4 py-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endif
</div>

@include('torrents.partials.scripts')
@endsection
