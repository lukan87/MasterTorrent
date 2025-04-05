@extends('layouts.app')

@section('title',  'Upload' )

@section('content')

@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))


<div class="container">
    <h1>Upload a New Torrent</h1>

<div class="alert alert-info">
    <!-- URL-ul Announce -->
    <h2 class="mt-10">
        <strong>Announce URL:</strong>
        <a href="javascript:void(0);" 
           onclick="copyToClipboard('http://last-torrents.org/announce/{{ $user->passkey }}')" 
           title="Click to copy this URL to your clipboard!">
            http://last-torrents.org/announce/{{ $user->passkey }}
        </a>
    </h2>
    <p>Click the announce URL above to copy it automatically when creating a new torrent!</p>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Announce URL copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }
</script>



    <form action="{{ route('torrents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
            <label for="file">Torrent File</label>
            <input type="file" class="form-control" id="file" name="torrent" required onchange="setTorrentName()">
        </div>

        <div class="form-group mb-3">
            <label for="name">Torrent Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="name">Genre (Example: Genre1, Genre2, Genre 3)</label>
            <input type="text" class="form-control" id="genre" name="genre" value="{{ old('genre') }}">
        </div>

        <div class="form-group mb-3">
            <label for="name">Steam ID</label>
            <input type="text" class="form-control" id="steamid" name="steamid" placeholder="Steam ID eg:https://store.steampowered.com/app/310950 ID=310950" value="{{ old('steamid') }}">
        </div>

         <!-- Category Dropdown -->
         <div class="form-group mb-3">
    <label for="category_id">Category</label>
    <select name="category_id" id="category_id" class="form-control" required onchange="toggleFieldsByCategory()">
        <!-- Make sure category with id 10 comes first -->
        @foreach($categories->sortByDesc(fn($cat) => $cat->id === 49) as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>

        <div class="form-group mb-3">
            <label for="poster">Poster</label>
            <input type="url" name="poster" id="poster" class="form-control" value="{{ old('poster') }}">
        </div>

        <div class="form-group mb-3">
    <label for="description">Description</label>
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
    <textarea class="form-control" id="description" oninput="resizeTextarea('description')" style="min-height: 150px; max-height: 500px;" name="description" required>{{ old('description') }}</textarea>

    <div id="conditionalFields" style="display: none;">
        <div class="form-group mb-3">
            <label for="mediainfo">Media Info</label>
            <textarea class="form-control" id="mediainfo" oninput="resizeTextarea('mediainfo')" style="min-height: 150px; max-height: 500px;" name="mediainfo">{{ old('mediainfo') }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="imdb_url">IMDB URL (For movies and series)</label>
            <input type="text" class="form-control" id="imdb_url" name="imdb_url" value="{{ old('imdb_url') }}">
            <button type="button" class="btn btn-success mt-2" onclick="fetchIMDBInfo()">Fetch Movie Info</button>
         </div>
        </div>

         <!-- Torrent Options -->
        <div class="form-group mb-3">
            <label>Torrent Tags:</label>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="free" id="free" class="form-check-input" value="1" {{ old('free') ? 'checked' : '' }}>
                <label for="free" class="form-check-label">Free</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="double" id="double" class="form-check-input" value="1" {{ old('double') ? 'checked' : '' }}>
                <label for="double" class="form-check-label">Double</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="sticky" id="sticky" class="form-check-input" value="1" {{ old('sticky') ? 'checked' : '' }}>
                <label for="sticky" class="form-check-label">Sticky</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="recommended" id="recommended" class="form-check-input" value="1" {{ old('recommended') ? 'checked' : '' }}>
                <label for="recommended" class="form-check-label">Recommended</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="seedbox" id="seedbox" class="form-check-input" value="1" {{ old('seedbox') ? 'checked' : '' }}>
                <label for="seedbox" class="form-check-label">Seedbox</label>
            </div>
            {{-- <div class="form-check form-check-inline">
                <input type="checkbox" name="external" id="external" class="form-check-input" value="1" {{ old('external') ? 'checked' : '' }}>
                <label for="external" class="form-check-label">External</label>
            </div> --}}
        </div>


        <button type="submit" class="btn btn-primary">Upload Torrent</button>
    </form>
</div>


<script>

function toggleFieldsByCategory() {
    const selectedCategory = document.getElementById('category_id').value;
    const conditionalFields = document.getElementById('conditionalFields');

    // Define categories that should display the conditional fields
    const targetCategories = ["1", "2", "5", "6", "9", "10", "11", "12", "13", "14", "20", "21", "24", "25", "31", "32", "54", "55", "81", "82"]; // Replace with the actual category IDs that should display the fields

    if (targetCategories.includes(selectedCategory)) {
        conditionalFields.style.display = 'block';
    } else {
        conditionalFields.style.display = 'none';
    }
}

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

function resizeTextarea(id) {
    const textarea = document.getElementById(id);
    textarea.style.height = 'auto'; // Reset the height to auto
    textarea.style.height = Math.min(textarea.scrollHeight, 500) + 'px'; // Set the height to the minimum of scrollHeight and 500 pixels
    textarea.scrollTop = textarea.scrollHeight; // Scroll to the bottom to keep the most recent content visible
}

function setTorrentName() {
    const fileInput = document.getElementById('file');
    const nameInput = document.getElementById('name');

    if (fileInput.files.length > 0) {
        const fullFileName = fileInput.files[0].name;
        const fileNameWithoutExtension = fullFileName.split('.').slice(0, -1).join('.');
        nameInput.value = fileNameWithoutExtension;
    } else {
        nameInput.value = '';
    }
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



@else
<div class="alert alert-danger mt-5"> <h1>You are not authorized to upload torrents! Speak with a staff member !</h1> </div>
@endif
@endsection
