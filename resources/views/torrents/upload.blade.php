@extends('layouts.app')

@section('title',  'Upload' )

@section('content')

@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))


<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-12">
            <h1 class="mb-4 text-center">📤 Upload a New Torrent</h1>

            <div class="alert alert-info shadow-sm rounded">
                <h5>
                    <strong>Announce URL:</strong>
                    <a href="javascript:void(0);" 
                       onclick="copyToClipboard('http://last-torrents.org/announce/{{ $user->passkey }}')" 
                       title="Click to copy this URL to your clipboard!">
                        http://last-torrents.org/announce/{{ $user->passkey }}
                    </a>
                </h5>
                <p class="mb-0">Click the announce URL to copy it while creating your torrent file.</p>
            </div>

            <form action="{{ route('torrents.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4 rounded">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Torrent File</label>
                    <input type="file" class="form-control" id="file" name="torrent" required onchange="setTorrentName()">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Torrent Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="genre" class="form-label">Genre <small>(e.g. Action, Drama)</small></label>
                    <input type="text" class="form-control" id="genre" name="genre" value="{{ old('genre') }}">
                </div>

                <div class="mb-3">
                    <label for="steamid" class="form-label">Steam ID</label>
                    <input type="text" class="form-control" id="steamid" name="steamid" placeholder="ONLY ONSERT THE ID FROM - https://store.steampowered.com/app/310950" value="{{ old('steamid') }}">
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select" required onchange="toggleFieldsByCategory()">
                        @foreach($categories->sortByDesc(fn($cat) => $cat->id === 49) as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="poster" class="form-label">Poster URL</label>
                    <input type="url" name="poster" id="poster" class="form-control" value="{{ old('poster') }}">
                </div>

                <div class="mb-3">
    <label for="images" class="form-label fw-bold">Screenshots (max 10)</label>
    <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple>
    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-3"></div>
</div>



                <div class="mb-3">
                 

                   
                        <label for="description" class="form-label"><strong>Description</strong></label>
                    
                        <div class="d-flex flex-wrap gap-3 mb-2 align-items-end">
                            <!-- Font Size -->
                            <div class="d-flex flex-column me-2">
                                <label for="fontSize" class="form-label small">Size</label>
                                <select id="fontSize" class="form-select form-select-sm"
                                    onchange="insertBBCode('size', this.value)" aria-label="Font Size">
                                    <option value="14" style="font-size: 14px;">1 (Small)</option>
                                    <option value="16" style="font-size: 16px;">2 (Normal)</option>
                                    <option value="18" style="font-size: 18px;">3 (Medium)</option>
                                    <option value="20" style="font-size: 20px;">4 (Large)</option>
                                    <option value="22" style="font-size: 22px;">5 (Extra Large)</option>
                                </select>
                            </div>
                        
                            <!-- Font Color -->
                            <div class="d-flex flex-column me-2">
                                <label for="fontColor" class="form-label small">Color</label>
                                <select id="fontColor" class="form-select form-select-sm"
                                    onchange="insertBBCode('color', this.value)" aria-label="Font Color">
                                    <option value="black" style="color: black;">Black</option>
                                    <option value="gray" style="color: gray;">Gray</option>
                                    <option value="red" style="color: red;">Red</option>
                                    <option value="darkred" style="color: darkred;">Dark Red</option>
                                    <option value="orange" style="color: orange;">Orange</option>
                                    <option value="gold" style="color: goldenrod;">Gold</option>
                                    <option value="green" style="color: green;">Green</option>
                                    <option value="darkgreen" style="color: darkgreen;">Dark Green</option>
                                    <option value="blue" style="color: blue;">Blue</option>
                                    <option value="darkblue" style="color: darkblue;">Dark Blue</option>
                                    <option value="purple" style="color: purple;">Purple</option>
                                    <option value="pink" style="color: deeppink;">Pink</option>
                                    <option value="teal" style="color: teal;">Teal</option>
                                    <option value="brown" style="color: brown;">Brown</option>
                                </select>
                            </div>
                        
                            <!-- Font Family -->
                            <div class="d-flex flex-column me-2">
                                <label for="fontFamily" class="form-label small">Font</label>
                                <select id="fontFamily" class="form-select form-select-sm"
                                    onchange="insertBBCode('font', this.value)" aria-label="Font Family">
                                    <option value="Arial" style="font-family: Arial;">Arial</option>
                                    <option value="Verdana" style="font-family: Verdana;">Verdana</option>
                                    <option value="Courier New" style="font-family: 'Courier New';">Courier New</option>
                                    <option value="Georgia" style="font-family: Georgia;">Georgia</option>
                                    <option value="Times New Roman" style="font-family: 'Times New Roman';">Times New Roman</option>
                                    <option value="Comic Sans MS" style="font-family: 'Comic Sans MS';">Comic Sans MS</option>
                                    <option value="Trebuchet MS" style="font-family: 'Trebuchet MS';">Trebuchet MS</option>
                                    <option value="Lucida Console" style="font-family: 'Lucida Console';">Lucida Console</option>
                                    <option value="Tahoma" style="font-family: Tahoma;">Tahoma</option>
                                    <option value="Impact" style="font-family: Impact;">Impact</option>
                                </select>
                            </div>
                        
                            <!-- BBCode Quick Buttons -->
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('center')" data-bs-toggle="tooltip" data-bs-placement="top" title="Center">
                                    <i class="bi bi-text-center"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('b')" data-bs-toggle="tooltip" data-bs-placement="top" title="Bold">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('i')" data-bs-toggle="tooltip" data-bs-placement="top" title="Italic">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('u')" data-bs-toggle="tooltip" data-bs-placement="top" title="Underline">
                                    <i class="bi bi-type-underline"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('quote')" data-bs-toggle="tooltip" data-bs-placement="top" title="Quote">
                                    <i class="bi bi-chat-left-quote"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('youtube')" data-bs-toggle="tooltip" data-bs-placement="top" title="YouTube">
                                    <i class="bi bi-youtube"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="insertBBCode('img')" data-bs-toggle="tooltip" data-bs-placement="top" title="Image">
                                    <i class="bi bi-card-image"></i>
                                </button>
                            </div>
                            
                            
                        </div>
                        
                    
                    

                    <textarea class="form-control" id="description" name="description" oninput="resizeTextarea('description')" style="min-height: 150px;" required>{{ old('description') }}</textarea>
                </div>

                <div id="conditionalFields" style="display: none;">
                    <div class="mb-3">
                        <label for="mediainfo" class="form-label">Media Info</label>
                        <textarea class="form-control" id="mediainfo" name="mediainfo" oninput="resizeTextarea('mediainfo')" style="min-height: 150px;">{{ old('mediainfo') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="imdb_url" class="form-label">IMDB URL</label>
                        <input type="text" class="form-control" id="imdb_url" name="imdb_url" value="{{ old('imdb_url') }}">
                        <button class="btn btn-outline-success mt-2" type="button" onclick="fetchIMDBInfo()">🎬 Fetch Info</button>
                    </div>

                   <div id="imdb-duplicate-warning" class="alert alert-warning mt-2 d-none">
    <strong>Similar torrents already exist with this IMDb URL. Check if your release is already uploaded!</strong>
    <ul id="existing-torrent-list" class="mb-0"></ul>
</div>

<script>
    document.getElementById('imdb_url').addEventListener('input', function () {
        const imdbUrl = this.value.trim();
        const warningBox = document.getElementById('imdb-duplicate-warning');
        const list = document.getElementById('existing-torrent-list');

        if (!imdbUrl) {
            warningBox.classList.add('d-none');
            list.innerHTML = '';
            return;
        }

        fetch(`/torrents/check-imdb?url=${encodeURIComponent(imdbUrl)}`)
            .then(response => response.json())
            .then(data => {
                list.innerHTML = '';
                if (data.exists) {
                   data.torrents.forEach(torrent => {
    const li = document.createElement('li');
    li.innerHTML = `
        <a href="/torrents/${torrent.id}">${torrent.name}</a> 
        — Seeders: ${torrent.seeders} | Leechers: ${torrent.leechers} | Completed: ${torrent.times_completed} 
    `;
    list.appendChild(li);
});
                    warningBox.classList.remove('d-none');
                } else {
                    warningBox.classList.add('d-none');
                }
            });
    });
</script>


                </div>

                                   <!-- Torrent Tags -->
                                   @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))
                                   <div class="mb-4">
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
                                            <input type="checkbox" class="form-check-input" name="recommended" id="recommended" value="1" {{ old('recommended') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="recommended"><span class="badge bg-info">Recommended</span></label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="seedbox" id="seedbox" value="1" {{ old('seedbox') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="seedbox"><span class="badge bg-dark">Seedbox</span></label>
                                        </div>
                                    </div>
                                </div>
                                @endif

                <button type="submit" class="btn btn-primary w-100 py-2">🚀 Upload Torrent</button>
            </form>
        </div>
    </div>
</div>


<script>

const maxImages = 10;
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('preview-container');
    const dataTransfer = new DataTransfer();
    let fileIdCounter = 0;

    fileInput.addEventListener('change', function () {
        const files = Array.from(fileInput.files);

        files.forEach(file => {
            if (dataTransfer.files.length >= maxImages) {
                alert('You can upload a maximum of 10 images.');
                return;
            }

            const uniqueId = 'file_' + (fileIdCounter++);
            file.uniqueId = uniqueId; // attach custom ID

            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                wrapper.dataset.id = uniqueId;

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded border shadow-sm';
                img.style.height = '120px';
                img.style.objectFit = 'cover';

                const removeBtn = document.createElement('button');
                removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = () => {
                    // Remove from DataTransfer by filtering out the one with this uniqueId
                    const newDT = new DataTransfer();
                    Array.from(dataTransfer.files).forEach(f => {
                        if (f.uniqueId !== uniqueId) newDT.items.add(f);
                    });

                    dataTransfer.items.clear();
                    Array.from(newDT.files).forEach(f => dataTransfer.items.add(f));

                    fileInput.files = dataTransfer.files;
                    wrapper.remove();
                };

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });

        // DO NOT clear the value, as it breaks file submission
    fileInput.files = dataTransfer.files;
    });

function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Announce URL copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }

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


    .content-overlay {
        background: none;
        padding: 20px;
    }

    body::before {
        content: '';
        position: fixed;
/* position: absolute; */
top: 55px;
right: 0;
bottom: 0;
left: 0;
background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 1)), url('https://4kwallpapers.com/images/walls/thumbs_3t/8324.png');
background-position-x: center top;
background-size: cover;
background-repeat: no-repeat;
opacity: 0.7;

    }
</style>



@else
<div class="container mt-5">
    <div class="alert alert-danger bg-gradient-danger text-white border-0 shadow-lg">
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
