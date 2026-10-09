import './page-browser';

const cache = new Map();
let generation = 0;

async function trackers(root) {
    if (!root?.dataset.seedbox) return;
    const current = ++generation;
    const cells = [...root.querySelectorAll('.torrent-trackers')];
    async function worker() {
        while (cells.length && current === generation) {
            const cell = cells.shift();
            const hash = cell.dataset.hash;
            try {
                let saved = cache.get(hash);
                if (!saved || saved.until < Date.now()) {
                    const response = await fetch(`/seedboxes/${root.dataset.seedbox}/torrent/${encodeURIComponent(hash)}/trackers`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                    if (!response.ok || response.redirected) throw new Error('trackers');
                    const data = await response.json();
                    const hosts = [...new Set((data.trackers || []).map(url => {
                        try { return new URL(url).hostname.replace(/^(tracker\.|www\.)/i, '').toLowerCase(); } catch { return ''; }
                    }).filter(Boolean))];
                    saved = { hosts, until: Date.now() + 300000 }; cache.set(hash, saved);
                }
                if (!cell.isConnected || current !== generation) continue;
                cell.textContent = saved.hosts.join(', ') || 'N/A';
                const upload = cell.closest('tr')?.querySelector('.upload-button');
                if (upload) upload.style.display = saved.hosts.some(host => ['fileiplay.org', 'fileiplay.ro'].some(domain => host === domain || host.endsWith('.' + domain))) ? 'none' : 'inline-block';
            } catch { if (cell.isConnected) cell.textContent = 'Unavailable'; }
        }
    }
    await Promise.all([worker(), worker(), worker(), worker()]);
}

document.addEventListener('page:updated', event => trackers(event.detail.root));
trackers(document.querySelector('[data-seedbox]'));
