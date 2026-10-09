import { createApp, h, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import '../css/torrent-featured.css';
import { disposeTorrentTooltips, initTorrentTooltips } from './torrent-tooltips';

for (const root of document.querySelectorAll('[data-torrent-featured]')) {
    const initial = root.innerHTML;
    const disposeTooltips = () => disposeTorrentTooltips(root);
    const initTooltips = () => initTorrentTooltips(root);
    // Dispose any instances attached to the server-rendered nodes before Vue replaces them.
    disposeTooltips();
    createApp({
        setup() {
            const html = ref(initial);
            const busy = ref(false);
            const error = ref('');
            onMounted(initTooltips);
            onBeforeUnmount(disposeTooltips);
            async function navigate(event) {
                const link = event.target.closest('[data-featured-nav]');
                if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                if (busy.value) return;
                busy.value = true;
                error.value = '';
                try {
                    const response = await fetch(link.href, {
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-Torrent-Featured': root.dataset.torrentFeatured },
                    });
                    if (!response.ok || response.redirected) throw new Error('request');
                    const data = await response.json();
                    if (typeof data.html !== 'string') throw new Error('response');
                    disposeTooltips();
                    html.value = data.html;
                    await nextTick();
                    initTooltips();
                    const focus = root.querySelector(`[data-featured-nav="${link.dataset.featuredNav}"]`)
                        ?? root.querySelector('[data-featured-nav]');
                    focus?.focus({ preventScroll: true });
                } catch (_) {
                    error.value = 'Could not load this page. Please try the arrow again.';
                } finally {
                    busy.value = false;
                }
            }
            return () => h('div', { class: 'torrent-featured-content', 'aria-busy': String(busy.value) }, [
                h('div', { innerHTML: html.value, onClick: navigate }),
                h('span', { class: 'visually-hidden', role: 'status' }, busy.value ? 'Loading torrents' : ''),
                error.value ? h('p', { class: 'torrent-featured-error', role: 'alert' }, error.value) : null,
            ]);
        },
    }).mount(root);
}
