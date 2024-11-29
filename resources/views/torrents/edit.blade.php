@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Torrent: {{ $torrent->name }}</h2>

    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success message -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('torrents.update', $torrent->slug) }}" method="POST" onsubmit="return confirm('Are you sure you want to update this torrent?');">
        @csrf
        @method('PUT')

        <!-- Torrent Name -->
        <div class="form-group">
            <label for="name">Torrent Name:</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $torrent->name) }}" required>
        </div>

        <!-- Category Selection -->
        <div class="form-group mb-3">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" class="form-control" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ $category->id == $torrent->category_id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="name">Genre:</label>
            <input type="text" name="genre" id="genre" class="form-control" value="{{ old('genre', $torrent->genre) }}">
            <i>ie (Genre1, Genre2, Genre3)</i>
        </div>

        <div class="form-group mb-3">
            <label for="steamid">Steam ID:</label>
            <input type="text" name="steamid" id="steamid" class="form-control" placeholder="Steam ID eg:https://store.steampowered.com/app/310950 ID=310950" value="{{ old('steamid', $torrent->steamid) }}">
            <i>ie (Genre1, Genre2, Genre3)</i>
        </div>

        <!-- Torrent Options -->
        <div class="form-group mb-3">
            <label>Torrent Tags:</label>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="free" id="free" class="form-check-input" value="1" {{ old('free', $torrent->free) ? 'checked' : '' }}>
                <label for="free" class="form-check-label">Free</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="double" id="double" class="form-check-input" value="1" {{ old('double', $torrent->double) ? 'checked' : '' }}>
                <label for="double" class="form-check-label">Double</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="sticky" id="sticky" class="form-check-input" value="1" {{ old('sticky', $torrent->sticky) ? 'checked' : '' }}>
                <label for="sticky" class="form-check-label">Sticky</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="recommended" id="recommended" class="form-check-input" value="1" {{ old('recommended', $torrent->recommended) ? 'checked' : '' }}>
                <label for="recommended" class="form-check-label">Recommended</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="seedbox" id="seedbox" class="form-check-input" value="1" {{ old('seedbox', $torrent->seedbox) ? 'checked' : '' }}>
                <label for="seedbox" class="form-check-label">Seedbox</label>
            </div>
        </div>

        <!-- Description -->

            <label for="description">Description:</label>

            <div class="form-group">
        <div class="mb-2">
        <!-- Font Size Dropdown -->
        <select id="fontSize" class="form-select form-select-sm d-inline-block" style="width: auto;">
            <option value="14">1 (Small)</option>
            <option value="16">2 (Normal)</option>
            <option value="18">3 (Medium)</option>
            <option value="20">4 (Large)</option>
            <option value="22">5 (Extra Large)</option>
        </select>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('size', document.getElementById('fontSize').value)">Font Size</button>

        <!-- Font Color Dropdown -->
        <select id="fontColor" class="form-select form-select-sm d-inline-block" style="width: auto;">
            <option value="black">Black</option>
            <option value="red">Red</option>
            <option value="blue">Blue</option>
            <option value="green">Green</option>
            <option value="purple">Purple</option>
        </select>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('color', document.getElementById('fontColor').value)">Font Color</button>

        <!-- Font Family Dropdown -->
        <select id="fontFamily" class="form-select form-select-sm d-inline-block" style="width: auto;">
            <option value="Arial">Arial</option>
            <option value="Verdana">Verdana</option>
            <option value="Courier">Courier</option>
            <option value="Georgia">Georgia</option>
            <option value="Times New Roman">Times New Roman</option>
        </select>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('font', document.getElementById('fontFamily').value)">Font Family</button>

        <!-- Center Button -->
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('center')">Center</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('b')">Bold</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('i')">Italic</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('u')">Underline</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('quote')">Quote</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">YouTube</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('img')">Image</button>
        </div>
    </div>
            <textarea name="description" id="description" oninput="resizeTextarea('description')" style="min-height: 150px; max-height: 500px;" class="form-control">{{ old('description', $torrent->description) }}</textarea>


        <!-- Media Info -->
        <div class="form-group">
            <label for="description">Media Info:</label>
            <textarea name="mediainfo" id="mediainfo" class="form-control">{{ old('mediainfo', $torrent->mediainfo) }}</textarea>
        </div>

        <!-- Poster URL -->
        <div class="form-group">
            <label for="poster">Poster URL:</label>
            <input type="text" name="poster" id="poster" class="form-control" value="{{ old('poster', $torrent->poster) }}">
            @if ($torrent->poster)
                <div>
                    <img src="{{ $torrent->poster }}" alt="Poster" class="img-thumbnail" style="max-width: 150px;">
                    <p>Current poster</p>
                </div>
            @endif
        </div>

        <!-- Background URL -->
        <div class="form-group">
            <label for="background">Background URL:</label>
            <input type="text" name="background" id="background" class="form-control" value="{{ old('background', $torrent->background) }}">
            @if ($torrent->background)
                <div>
                    <img src="{{ $torrent->background }}" alt="Background" class="img-thumbnail" style="max-width: 150px;">
                    <p>Current background</p>
                </div>
            @endif
        </div>

        <!-- IMDB URL (Optional) -->
        <div class="form-group">
            <label for="imdb_url">IMDB URL:</label>
            <input type="url" name="imdb_url" id="imdb_url" class="form-control" value="{{ old('imdb_url', $torrent->imdb_url) }}">
            <button type="button" class="btn btn-success mt-2" onclick="fetchIMDBInfo()">Fetch Movie Info</button>
        </div>

        <script>
function fetchIMDBInfo() {
    const imdbUrl = document.getElementById('imdb_url').value;
    const imdbIdMatch = imdbUrl.match(/(?:imdb\.com\/title\/)(tt\d+)/); // Regex to extract IMDb ID
    const imdbId = imdbIdMatch ? imdbIdMatch[1] : null;

    if (imdbId) {
        const apiKey = 'd3eb5201'; // Replace with your OMDb API key
        const apiUrl = `https://www.omdbapi.com/?i=${imdbId}&apikey=${apiKey}&plot=full`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.Response === 'True') {
                    const descriptionField = document.getElementById('description');
                    const poster = data.Poster;
                    const title = data.Title;
                    const year = data.Year;
                    const plot = data.Plot;
                    const genre = data.Genre;

                    // Constructing the string to insert into the description
                    const movieInfo = `[center][img]${poster}[/img]\n\n\n[b]${title} (${year})[/b]\n\n\n[quote]${plot}[/quote]\n[font=Arial][color=grey]Genre: ${genre}[/color][/font][/center]`;

                    // Inserting movie info into the description field
                    descriptionField.value += movieInfo; // Append to existing content
                } else {
                    alert('Movie not found or invalid IMDb ID.');
                }
            })
            .catch(error => {
                console.error('Error fetching IMDb data:', error);
                alert('An error occurred while fetching movie information.');
            });
    } else {
        alert('Please enter a valid IMDb URL.');
    }
}
</script>

        <div class="form-group">
            <label for="trailer">Trailer:</label>
            <input type="url" name="trailer" id="trailer" class="form-control" value="{{ old('trailer', $torrent->trailer) }}">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update Torrent</button>

        <!-- Back Button -->
        <a href="{{ route('torrents.index') }}" class="btn btn-secondary">Back to List</a>
    </form>

    <!-- Delete Button -->

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
</div>



<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this torrent?");
    }

     // Show or hide the custom reason text area based on the dropdown selection
     function toggleCustomReason(selectElement) {
        const customReasonDiv = document.getElementById('custom_reason_div');
        if (selectElement.value === 'custom') {
            customReasonDiv.style.display = 'block';
        } else {
            customReasonDiv.style.display = 'none';
        }
    }
</script>

<script>

function resizeTextarea(id) {
    const textarea = document.getElementById(id);
    textarea.style.height = 'auto'; // Reset the height to auto
    textarea.style.height = Math.min(textarea.scrollHeight, 500) + 'px'; // Set the height to the minimum of scrollHeight and 500 pixels
    textarea.scrollTop = textarea.scrollHeight; // Scroll to the bottom to keep the most recent content visible
}

function insertBBCode(tag, option = null) {
    const textarea = document.getElementById("description"); // Make sure this matches your textarea ID
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursorPosition = textarea.selectionStart; // Store current cursor position
    const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

    // Insert the BBCode tags with the selected text in the middle
    const newText = startTag + selectedText + endTag;
    textarea.value = textarea.value.substring(0, cursorPosition) + newText + textarea.value.substring(textarea.selectionEnd);

    // Set the cursor position in the middle of the tags, right after the opening tag
    const newCursorPosition = cursorPosition + startTag.length;
    textarea.selectionStart = newCursorPosition;
    textarea.selectionEnd = newCursorPosition;

    // Focus back on the textarea
    textarea.focus();
}
</script>

<style>
    textarea {
        resize: none; /* Disable the default textarea resizing */
        overflow: auto; /* Enable scrollbar when necessary */
        max-height: 500px; /* Maximum height for the textarea */
        min-height: 150px; /* Minimum height for the textarea */
    }
</style>
@endsection
