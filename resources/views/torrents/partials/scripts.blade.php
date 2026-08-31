
<script>

const maxImages = 12;
const fileInput = document.getElementById('images');
const previewContainer = document.getElementById('preview-container');
const dataTransfer = new DataTransfer();
let fileIdCounter = 0;

fileInput.addEventListener('change', function () {
    const files = Array.from(fileInput.files);
    files.forEach(file => {
        if (dataTransfer.files.length >= maxImages) {
            alert('Max 12 images');
            return;
        }

        const uniqueId = 'file_' + (fileIdCounter++);
        file.uniqueId = uniqueId;
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        const reader = new FileReader();
        reader.onload = function (e) {
            
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3 position-relative'; 
            col.dataset.id = uniqueId;

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'rounded border shadow-sm w-100';
            img.style.height = '130px';
            img.style.objectFit = 'cover';

            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => {
                const newDT = new DataTransfer();
                Array.from(dataTransfer.files).forEach(f => { if (f.uniqueId !== uniqueId) newDT.items.add(f); });
                dataTransfer.items.clear();
                Array.from(newDT.files).forEach(f => dataTransfer.items.add(f));
                fileInput.files = dataTransfer.files;
                col.remove();
            };

            col.appendChild(img);
            col.appendChild(removeBtn);

        
            if (!previewContainer.classList.contains('row')) {
                previewContainer.classList.add('row');
            }

            previewContainer.appendChild(col);
        };
        reader.readAsDataURL(file);
    });

    fileInput.files = dataTransfer.files;
});



function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => alert('Announce URL copied!')).catch(err => console.error(err));
}


function toggleFieldsByCategory() {
    const selectedCategory = document.getElementById('category_id').value;
    const conditionalFields = document.getElementById('conditionalFields');
    const targetCategories = ["1","2","5","6","9","10","11","12","13","14","20","21","24","25","31","32","54","55","81","82"];
    conditionalFields.style.display = targetCategories.includes(selectedCategory) ? 'block' : 'none';
}



async function fetchIMDBInfo() {
    const imdbUrl = document.getElementById('imdb_url').value;
    const imdbIdMatch = imdbUrl.match(/tt\d+/);
    if (!imdbIdMatch) return alert('Enter a valid IMDb URL');

    const imdbId = imdbIdMatch[0];
    const tmdbKey = '325f0b42fccd356be82ede4d2be6312c';

    try {
        // 🔎 FIND
        const find = await fetch(
            `https://api.themoviedb.org/3/find/${imdbId}?api_key=${tmdbKey}&external_source=imdb_id`
        ).then(r => r.json());

        let item, type;
        if (find.movie_results.length) {
            item = find.movie_results[0];
            type = 'movie';
        } else if (find.tv_results.length) {
            item = find.tv_results[0];
            type = 'tv';
        } else {
            return alert('Title not found.');
        }

        // 📦 DETAILS + CREDITS
        const [details, credits] = await Promise.all([
            fetch(`https://api.themoviedb.org/3/${type}/${item.id}?api_key=${tmdbKey}`).then(r => r.json()),
            fetch(`https://api.themoviedb.org/3/${type}/${item.id}/credits?api_key=${tmdbKey}`).then(r => r.json())
        ]);

        // 🎬 CORE DATA
        const title = type === 'movie' ? details.title : details.name;
        const year = (type === 'movie'
            ? details.release_date
            : details.first_air_date
        )?.split('-')[0] || 'N/A';

        const poster = details.poster_path
            ? `https://image.tmdb.org/t/p/w342${details.poster_path}`
            : '';

        const genres = details.genres.map(g => g.name).join(', ') || 'N/A';

        const cast = credits.cast
            .slice(0, 6)
            .map(p => p.name)
            .join(', ') || 'N/A';

        const overview = details.overview || 'No description available.';

        const label = type === 'movie' ? 'Movie' : 'TV Series';

        // 🧾 CLEAN BBCode OUTPUT
        const desc = document.getElementById('description');

        const info =
            `[center][img]${poster}[/img][/center]\n\n` +
            `[b]${title} (${year})[/b]\n` +
            `[i]${label}[/i]\n\n` +
            `[b]Genre:[/b] ${genres}\n` +
            `[b]Cast:[/b] ${cast}\n\n` +
            `[b]Overview:[/b]\n${overview}`;

        desc.value += info;

    } catch (e) {
        alert('Error fetching movie info.');
    }
}





  function toggleCustomReason(elem) {
        document.getElementById('custom_reason_div').style.display = (elem.value === 'custom') ? 'block' : 'none';
    }

    function confirmDelete() {
        return confirm('Are you sure you want to delete this torrent?');
    }


function resizeTextarea(id) {
    const ta = document.getElementById(id);
    ta.style.height = 'auto';
    ta.style.height = Math.min(ta.scrollHeight,500)+'px';
    ta.scrollTop = ta.scrollHeight;
}


function setTorrentName() {
    const fileInput = document.getElementById('file');
    const nameInput = document.getElementById('name');
    if(fileInput.files.length > 0){
        const fullName = fileInput.files[0].name;
        nameInput.value = fullName.split('.').slice(0,-1).join('.');
    } else nameInput.value = '';
}


function insertBBCode(tag, option=null) {
    const ta = document.getElementById("description");
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursor = ta.selectionStart;
    const sel = ta.value.substring(ta.selectionStart, ta.selectionEnd);
    ta.value = ta.value.substring(0,cursor)+startTag+sel+endTag+ta.value.substring(ta.selectionEnd);
    const newCursor = cursor+startTag.length;
    ta.selectionStart = newCursor;
    ta.selectionEnd = newCursor;
    ta.focus();
}


// Seedbox JavaScript

document.querySelectorAll('.seedbox-send-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', this.dataset.torrent), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                seedbox_id: this.dataset.seedbox
            })
        })
        .then(r => r.json())
        .then(data => {
            showToast(data.message || 'Sent to seedbox');
        })
        .catch(() => showToast('Seedbox error', 'danger'));
    });
});

 function swalSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 3500,
        showConfirmButton: true,
        timerProgressBar: true
    });
}

function swalError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}



document.addEventListener('click', function (e) {
    const btn = e.target.closest('.seedbox-send-btn');
    if (!btn) return;

    e.preventDefault();

    // Prevent double-click
    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';
    btn.classList.add('disabled');

    // Optional loading alert
    Swal.fire({
        title: 'Sending torrent…',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("{{ route('torrents.sendToSeedbox', '__ID__') }}".replace('__ID__', btn.dataset.torrent), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            seedbox_id: btn.dataset.seedbox
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    })
    .then(data => {
        Swal.close();
        swalSuccess(data.message || 'Torrent sent to seedbox');

        // Optional: mark as sent
        btn.innerHTML = '✔ Sent';
        btn.classList.add('text-success');
    })
    .catch(error => {
        Swal.close();
        swalError(error.message || 'Failed to send torrent');

        btn.dataset.loading = '0';
        btn.classList.remove('disabled');
    });
});

// Seedbox JavaScript

</script>


<script>
document.getElementById('images')?.addEventListener('change', function (event) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';

    Array.from(event.target.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();

        reader.onload = e => {
            const wrapper = document.createElement('div');
            wrapper.classList.add('position-relative');

            const img = document.createElement('img');
            img.src = e.target.result;
            img.classList.add('img-thumbnail');
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';

            wrapper.appendChild(img);
            preview.appendChild(wrapper);
        };

        reader.readAsDataURL(file);
    });
});
</script>

