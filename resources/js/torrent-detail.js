import { createApp, h, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { setTorrentLoading } from './torrent-loading';
import '../../public/js/torrent-media-hero.js';
import '../../public/js/media-cast-slider.js';
import '../../public/js/torrent-game.js';
import '../../public/js/torrent-backdrops.js';

async function content(url, section, signal) {
    const response = await fetch(url, {
        signal,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Torrent-Content': section },
    });
    if (!response.ok || response.redirected) throw new Error('content');
    return response.json();
}

const files = document.querySelector('[data-torrent-files]');
if (files) {
    let loaded = false;
    let loading = false;
    const load = async () => {
        if (loaded || loading) return;
        loading = true;
        files.setAttribute('aria-busy', 'true');
        files.textContent = 'Loading files…';
        try {
            const data = await content(files.dataset.url, 'files');
            if (typeof data.html !== 'string') throw new Error('response');
            files.innerHTML = data.html;
            loaded = true;
        } catch {
            const message = document.createElement('p');
            message.textContent = 'Could not load the file list.';
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'btn btn-outline-info btn-sm';
            retry.textContent = 'Try again';
            retry.addEventListener('click', load);
            files.replaceChildren(message, retry);
        } finally {
            loading = false;
            files.setAttribute('aria-busy', 'false');
        }
    };
    document.getElementById('torrentFilesModal')?.addEventListener('show.bs.modal', load);
}

const metadataRoot = document.querySelector('[data-torrent-metadata]');
if (metadataRoot) {
    const initialHtml = metadataRoot.innerHTML;
    const url = metadataRoot.dataset.url;
    createApp({
        setup() {
            const html = ref(initialHtml);
            const status = ref('Loading title details…');
            let timer;
            let controller;
            let attempts = 0;
            setTorrentLoading('metadata', true);

            function unavailable() {
                if (html.value !== initialHtml) return;
                html.value = '';
                status.value = 'Additional title details are temporarily unavailable.';
                metadataRoot.setAttribute('aria-busy', 'false');
                setTorrentLoading('metadata', false);
            }

            async function load() {
                controller = new AbortController();
                try {
                    const data = await content(url, 'metadata', controller.signal);
                    if (typeof data.html === 'string' && data.html) {
                        // Keep an open trailer or screenshot modal intact during enrichment.
                        if (metadataRoot.querySelector('.modal.show')) return;
                        // Render the same escaped Blade templates used on a warm page.
                        metadataRoot.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                            window.bootstrap?.Tooltip.getInstance(node)?.dispose();
                        });
                        metadataRoot.querySelectorAll('[data-bs-ride="carousel"]').forEach(node => {
                            window.bootstrap?.Carousel.getInstance(node)?.dispose();
                        });
                        metadataRoot.querySelectorAll('.modal').forEach(node => {
                            window.bootstrap?.Modal.getInstance(node)?.dispose();
                        });
                        html.value = data.html;
                        status.value = '';
                        metadataRoot.setAttribute('aria-busy', 'false');
                        setTorrentLoading('metadata', false);
                        await nextTick();
                        metadataRoot.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                            window.bootstrap?.Tooltip.getOrCreateInstance(node);
                        });
                        metadataRoot.querySelectorAll('[data-bs-ride="carousel"]').forEach(node => {
                            window.bootstrap?.Carousel.getOrCreateInstance(node);
                        });
                        document.dispatchEvent(new CustomEvent('torrent:metadata-ready'));
                        if (!data.pending) return;
                    }
                    if (++attempts < 5) {
                        timer = window.setTimeout(load, Math.min(2000 * attempts, 8000));
                    } else {
                        unavailable();
                    }
                } catch (exception) {
                    if (exception.name !== 'AbortError') unavailable();
                }
            }
            onMounted(() => { timer = window.setTimeout(load, 1500); });
            onBeforeUnmount(() => {
                setTorrentLoading('metadata', false);
                window.clearTimeout(timer);
                controller?.abort();
            });

            return () => h('div', [
                h('div', { innerHTML: html.value }),
                status.value && html.value !== initialHtml ? h('p', { role: 'status', 'aria-live': 'polite', class: 'text-muted small' }, status.value) : null,
            ]);
        },
    }).mount(metadataRoot);
}
