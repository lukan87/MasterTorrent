
<script>

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => alert('Announce URL copied!')).catch(err => console.error(err));
}


function toggleFieldsByCategory() {
    const selectedCategory = document.getElementById('category_id').value;
    const conditionalFields = document.getElementById('conditionalFields');
    const targetCategories = ["1","2","5","6","9","10","11","12","13","14","20","21","24","25","31","32","54","55","81","82"];
    conditionalFields.style.display = targetCategories.includes(selectedCategory) ? 'block' : 'none';
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
        nameInput.value = fullName
            .replace(/\.torrent$/i, '')
            .replace(/[^A-Za-z0-9.\-]+/g, '.')
            .replace(/\.{2,}/g, '.')
            .replace(/^\.+|\.+$/g, '')
            .replace(/(?:\.(?:torrent|mkv|mp4|avi|mov|m2ts|ts|webm|wmv|mpg|mpeg|m4v|vob))+$/i, '');
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


<script src="{{ asset('js/torrent-image-previews.js') }}?v=2" defer></script>

<style>
.torrent-upload-preview { position: relative; flex: 0 0 150px; width: 150px; height: 110px; overflow: hidden; border: 1px solid var(--ui-border, var(--theme-border, #334155)); border-radius: 8px; background: var(--theme-surface, rgba(10,15,27,.65)); }
.torrent-upload-preview img { width: 100%; height: 100%; object-fit: contain; }
.torrent-upload-preview-status { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 10px; color: var(--theme-muted, #94a3b8); font-size: var(--site-font-small, 13px); text-align: center; }
.torrent-upload-preview-status[hidden], .torrent-upload-preview img[hidden] { display: none; }
.torrent-upload-preview-remove { position: absolute; top: 4px; right: 4px; width: 25px; height: 25px; border: 0; border-radius: 50%; background: var(--theme-red-soft, #842029); color: var(--theme-text, #fff); line-height: 1; }
</style>
