import { createApp, h, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { setTorrentLoading } from './torrent-loading';

const root = document.querySelector('[data-torrent-browser]');
const filters = document.querySelector('[data-torrent-filters]');

if (root && filters) {
    const initialHtml = root.innerHTML;
    const browseUrl = new URL(root.dataset.browseUrl, window.location.origin);
    const dispose = element => element.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
        window.bootstrap?.Tooltip.getInstance(node)?.dispose();
    });
    const enhance = element => element.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
        window.bootstrap?.Tooltip.getOrCreateInstance(node);
    });
    dispose(root);

    createApp({
        setup() {
            const html = ref(initialHtml);
            const busy = ref(false);
            watch(busy, value => setTorrentLoading('browse', value), { flush: 'sync' });
            const error = ref('');
            const retryUrl = ref(window.location.href);
            let pending;
            let sequence = 0;

            async function navigate(url, { history = true, scroll = false } = {}) {
                pending?.abort();
                const controller = new AbortController();
                pending = controller;
                const requestId = ++sequence;
                busy.value = true;
                error.value = '';
                retryUrl.value = url.href;
                try {
                    const response = await fetch(url, {
                        signal: controller.signal,
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-Torrent-Browse': '1' },
                    });
                    if (!response.ok || response.redirected) throw new Error('navigation');
                    const data = await response.json();
                    if (typeof data.html !== 'string' || typeof data.filters !== 'string') throw new Error('response');
                    if (requestId !== sequence || controller.signal.aborted) return;

                    // Keep keyboard focus and the open category chooser across updates.
                    const active = filters.contains(document.activeElement) ? document.activeElement : null;
                    const focus = active ? { id: active.id, start: active.selectionStart, end: active.selectionEnd } : null;
                    const categoriesOpen = filters.querySelector('#torrent-search-categories')?.classList.contains('show');
                    dispose(root);
                    dispose(filters);
                    html.value = data.html;
                    filters.innerHTML = data.filters;
                    if (categoriesOpen && history) {
                        filters.querySelector('#torrent-search-categories')?.classList.add('show');
                        filters.querySelector('[data-bs-target="#torrent-search-categories"]')?.setAttribute('aria-expanded', 'true');
                    }
                    if (history && url.href !== window.location.href) {
                        window.history.pushState(null, '', url);
                    }
                    await nextTick();
                    document.dispatchEvent(new CustomEvent('torrent:updated'));
                    enhance(root);
                    enhance(filters);
                    const target = focus?.id ? filters.querySelector(`#${CSS.escape(focus.id)}`) : null;
                    target?.focus({ preventScroll: true });
                    if (target && focus.start !== null && typeof target.setSelectionRange === 'function') {
                        target.setSelectionRange(focus.start, focus.end);
                    }
                    if (scroll) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (exception) {
                    if (exception.name !== 'AbortError' && requestId === sequence) {
                        if (!history) {
                            window.location.assign(url);
                            return;
                        }
                        error.value = 'Could not update the list. Your previous results are still shown.';
                    }
                } finally {
                    if (requestId === sequence) busy.value = false;
                }
            }

            function click(event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                const link = event.target.closest('a[href]');
                if (!link || !(root.contains(link) || filters.contains(link)) || link.hasAttribute('download') || link.target) return;
                const url = new URL(link.href);
                if (url.origin !== browseUrl.origin || url.pathname !== browseUrl.pathname || url.hash) return;
                // The error link intentionally performs a normal navigation.
                if (link.dataset.browseFallback !== undefined) return;
                event.preventDefault();
                navigate(url, { scroll: link.closest('.tx-pagination') !== null });
            }

            function submit(event) {
                const form = event.target.closest('[data-torrent-search]');
                if (!form || !filters.contains(form)) return;
                event.preventDefault();
                const url = new URL(form.action);
                url.search = new URLSearchParams(new FormData(form)).toString();
                navigate(url);
            }

            function input(event) {
                if (!filters.contains(event.target) || !busy.value) return;
                // An older request must never replace a search the member is still editing.
                pending?.abort();
                ++sequence;
                busy.value = false;
            }

            function popstate() {
                const url = new URL(window.location.href);
                if (url.pathname === browseUrl.pathname) navigate(url, { history: false });
                else window.location.reload();
            }

            onMounted(() => {
                enhance(root);
                document.addEventListener('click', click);
                document.addEventListener('submit', submit);
                filters.addEventListener('input', input);
                filters.addEventListener('change', input);
                window.addEventListener('popstate', popstate);
            });
            onBeforeUnmount(() => {
                setTorrentLoading('browse', false);
                pending?.abort();
                document.removeEventListener('click', click);
                document.removeEventListener('submit', submit);
                filters.removeEventListener('input', input);
                filters.removeEventListener('change', input);
                window.removeEventListener('popstate', popstate);
                dispose(root);
            });

            return () => h('div', { 'aria-busy': String(busy.value) }, [
                h('div', { role: 'status', 'aria-live': 'polite', class: error.value ? 'alert alert-info py-2' : 'visually-hidden' },
                    error.value ? [error.value, ' ', h('a', { href: retryUrl.value, 'data-browse-fallback': '' }, 'Reload results')] : busy.value ? 'Updating torrents…' : 'Torrent list ready.'),
                // Only trusted, escaped Blade fragments from this authenticated route enter here.
                h('div', { innerHTML: html.value }),
            ]);
        },
    }).mount(root);
}
