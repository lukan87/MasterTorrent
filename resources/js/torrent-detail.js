import { createApp, h, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { setTorrentLoading } from './torrent-loading';
import { jsonRequest } from './progressive-ui';
import '../../public/js/torrent-media-hero.js';
import '../../public/js/media-cast-slider.js';
import '../../public/js/torrent-game.js';
import '../../public/js/torrent-backdrops.js';

const subscription = document.querySelector('[data-torrent-subscription]');
if (subscription) {
    let busy = false;
    const source = Symbol('torrent-subscription');
    function notify(icon, title, text) {
        if (window.Swal?.fire) {
            window.Swal.fire({ icon, title, text, confirmButtonText: 'OK', returnFocus: false });
        } else {
            const message = document.createElement('span');
            message.dataset.subscriptionFeedback = '';
            message.setAttribute('role', icon === 'error' ? 'alert' : 'status');
            message.textContent = text;
            subscription.append(message);
        }
    }
    subscription.addEventListener('submit', async event => {
        const form = event.target;
        if (!form.matches('[data-torrent-subscription-action]')) return;
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        const button = form.querySelector('button[type="submit"]');
        const original = button.innerHTML;
        const removing = new URL(form.action).pathname.endsWith('/unsubscribe');
        const body = new FormData(form);
        subscription.querySelectorAll('[data-subscription-feedback]').forEach(node => node.remove());
        busy = true;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>${removing ? 'Unsubscribing…' : 'Subscribing…'}`;
        subscription.setAttribute('aria-busy', 'true');
        setTorrentLoading(source, true);
        let saved = false;
        try {
            const data = await jsonRequest(form.action, { method: 'POST', body });
            saved = true;
            if (typeof data.html !== 'string' || typeof data.subscribed !== 'boolean') throw new Error('Invalid subscription response.');
            subscription.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                window.bootstrap?.Tooltip.getInstance(node)?.dispose();
            });
            // Refresh only the button and subscribers; players and comment drafts stay intact.
            subscription.innerHTML = data.html;
            subscription.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                window.bootstrap?.Tooltip.getOrCreateInstance(node);
            });
            subscription.querySelector('button[type="submit"]')?.focus({ preventScroll: true });
            notify(data.status === 'info' ? 'info' : 'success', data.subscribed ? 'Subscribed' : 'Unsubscribed', data.message || 'Subscription updated.');
        } catch (error) {
            notify(saved ? 'warning' : 'error', saved ? 'Subscription updated' : 'Could not update subscription', saved
                ? 'Your subscription was saved, but the controls could not refresh. Reload the page to see the update.'
                : error.message);
        } finally {
            button.innerHTML = original;
            button.disabled = false;
            busy = false;
            subscription.setAttribute('aria-busy', 'false');
            setTorrentLoading(source, false);
        }
    });
    window.addEventListener('pagehide', () => setTorrentLoading(source, false));
}

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
