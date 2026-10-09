import '../css/torrent-preview.css';

const roots = new WeakSet();

export function disposeTorrentTooltips(root) {
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
        window.bootstrap?.Tooltip.getInstance(element)?.dispose();
    });
}

export function initTorrentTooltips(root) {
    if (!roots.has(root)) {
        roots.add(root);
        root.addEventListener('shown.bs.tooltip', event => {
            const id = event.target.getAttribute('aria-describedby');
            const poster = id ? document.getElementById(id)?.querySelector('[data-poster-fallback]') : null;
            if (!poster) return;
            const fallback = () => {
                if (poster.src !== poster.dataset.posterFallback) poster.src = poster.dataset.posterFallback;
            };
            poster.addEventListener('error', fallback, { once: true });
            if (poster.complete && !poster.naturalWidth) fallback();
        });
    }
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
        const preview = element.dataset.torrentPreview;
        const template = preview ? root.querySelector(`[data-preview-content="${preview}"]`) : null;
        // Avoid delayed fade callbacks after a Vue fragment has been replaced.
        window.bootstrap?.Tooltip.getOrCreateInstance(element, {
            animation: false,
            ...(template ? { html: true, title: () => template.content.firstElementChild.cloneNode(true) } : {}),
        });
    });
}
