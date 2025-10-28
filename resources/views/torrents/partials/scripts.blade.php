
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


function fetchIMDBInfo() {
    const imdbUrl = document.getElementById('imdb_url').value;
    const imdbIdMatch = imdbUrl.match(/(?:imdb\.com\/title\/)(tt\d+)/);
    const imdbId = imdbIdMatch ? imdbIdMatch[1] : null;
    if (!imdbId) { alert('Enter valid IMDb URL'); return; }

    fetch(`https://www.omdbapi.com/?i=${imdbId}&apikey=d3eb5201&plot=full`)
        .then(res => res.json()).then(data => {
            if (data.Response === 'True') {
                const desc = document.getElementById('description');
                const info = `[center][img]${data.Poster}[/img]\n\n[b]${data.Title} (${data.Year})[/b]\n[quote]${data.Plot}[/quote]\n[font=Arial][color=grey]Genre: ${data.Genre}[/color][/font][/center]`;
                desc.value += info;
            } else alert('Movie not found.');
        }).catch(e => alert('Error fetching movie info.'));
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
</script>