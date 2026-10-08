(() => {
    const input = document.getElementById('images');
    const preview = document.getElementById('preview-container') || document.getElementById('image-preview');
    if (!input || !preview) return;
    const items = [];
    preview.classList.remove('row');
    preview.classList.add('d-flex', 'flex-wrap', 'gap-2');
    const summary = document.createElement('div');
    summary.className = 'small text-muted mt-2';
    summary.setAttribute('role', 'status');
    summary.setAttribute('aria-live', 'polite');
    preview.after(summary);

    function sync() {
        const transfer = new DataTransfer();
        items.filter(item => !item.failed).forEach(item => transfer.items.add(item.file));
        input.files = transfer.files;
        const loading = items.filter(item => item.loading).length;
        const failed = items.filter(item => item.failed).length;
        preview.setAttribute('aria-busy', String(loading > 0));
        summary.textContent = loading ? `Loading ${loading} image${loading === 1 ? '' : 's'}…`
            : failed ? `${failed} image${failed === 1 ? '' : 's'} could not be loaded and will not be uploaded.`
            : items.length ? `${items.length} image${items.length === 1 ? '' : 's'} ready to upload.` : '';
    }
    input.addEventListener('change', () => {
        const files = [...input.files];
        let rejected = false;
        files.forEach(file => {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 10 * 1024 * 1024 || items.length >= 10) { rejected = true; return; }
            if (items.some(item => item.file.name === file.name && item.file.size === file.size && item.file.lastModified === file.lastModified)) return;
            const item = { file, loading: true, failed: false };
            items.push(item);
            const card = document.createElement('div');
            card.className = 'torrent-upload-preview';
            const status = document.createElement('div');
            status.className = 'torrent-upload-preview-status';
            status.innerHTML = '<span class="spinner-border spinner-border-sm text-info" aria-hidden="true"></span><span>Loading image…</span>';
            const img = document.createElement('img');
            img.hidden = true;
            img.alt = file.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'torrent-upload-preview-remove';
            remove.textContent = '×';
            remove.setAttribute('aria-label', 'Remove ' + file.name);
            card.append(img, status, remove);
            preview.append(card);
            const reader = new FileReader();
            function finish(failed) {
                if (!items.includes(item)) return;
                item.loading = false;
                item.failed = failed;
                img.hidden = failed;
                status.hidden = !failed;
                if (failed) status.textContent = 'Could not load image';
                sync();
            }
            img.onload = () => finish(false);
            img.onerror = () => finish(true);
            reader.onload = () => { if (items.includes(item)) img.src = reader.result; };
            reader.onerror = () => finish(true);
            remove.addEventListener('click', () => {
                items.splice(items.indexOf(item), 1);
                if (reader.readyState === FileReader.LOADING) reader.abort();
                img.removeAttribute('src');
                card.remove();
                sync();
            });
            reader.readAsDataURL(file);
        });
        sync();
        if (rejected) summary.textContent += ' Select up to 10 JPG, PNG or WebP images, 10 MB each.';
    });
})();
