import { createApp, h, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { setTorrentLoading } from './torrent-loading';

const root = document.querySelector('[data-library-browser]');

if (root) {
    const initialHtml = root.innerHTML;
    const browseUrl = new URL(root.dataset.browseUrl, window.location.origin);

    createApp({
        setup() {
            const html = ref(initialHtml);
            const busy = ref(false);
            const error = ref('');
            const retryUrl = ref(window.location.href);
            let pending;
            let timer;
            let sequence = 0;
            watch(busy, value => setTorrentLoading('library', value), { flush: 'sync' });

            function cancel() {
                window.clearTimeout(timer);
                pending?.abort();
                sequence++;
                busy.value = false;
            }

            async function navigate(url, { history = true, scroll = false } = {}) {
                cancel();
                const controller = new AbortController();
                pending = controller;
                const requestId = sequence;
                busy.value = true;
                error.value = '';
                retryUrl.value = url.href;
                try {
                    const response = await fetch(url, {
                        signal: controller.signal,
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-Library-Browse': '1' },
                    });
                    if (!response.ok || response.redirected) throw new Error('navigation');
                    const data = await response.json();
                    if (typeof data.html !== 'string') throw new Error('response');
                    if (requestId !== sequence || controller.signal.aborted) return;

                    const active = root.contains(document.activeElement) ? document.activeElement : null;
                    const focus = active ? { name: active.name, start: active.selectionStart, end: active.selectionEnd } : null;
                    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                        window.bootstrap?.Tooltip.getInstance(node)?.dispose();
                    });
                    html.value = data.html;
                    if (history && url.href !== window.location.href) window.history.pushState(null, '', url);
                    await nextTick();
                    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                        window.bootstrap?.Tooltip.getOrCreateInstance(node);
                    });
                    const target = focus?.name ? root.querySelector(`[name="${CSS.escape(focus.name)}"]`) : null;
                    target?.focus({ preventScroll: true });
                    if (target && focus.start != null && typeof target.setSelectionRange === 'function') {
                        target.setSelectionRange(focus.start, focus.end);
                    }
                    if (scroll) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (exception) {
                    if (exception.name !== 'AbortError' && requestId === sequence) {
                        if (!history) {
                            window.location.assign(url);
                            return;
                        }
                        error.value = 'Could not update the library. Your previous results are still shown.';
                    }
                } finally {
                    if (requestId === sequence) busy.value = false;
                }
            }

            function search() {
                const form = root.querySelector('[data-library-search]');
                if (!form?.checkValidity()) return;
                const url = new URL(form.action);
                const params = new URLSearchParams(new FormData(form));
                for (const [key, value] of [...params]) {
                    if (!value || (key === 'sort' && value === 'latest') || (key === 'availability' && value === 'all')) params.delete(key);
                }
                url.search = params.toString();
                navigate(url);
            }

            function submit(event) {
                if (!event.target.matches('[data-library-search]')) return;
                event.preventDefault();
                search();
            }

            function input(event) {
                if (!event.target.matches('input[name="q"], input[name="year"]')) return;
                cancel();
                if (!event.isComposing) timer = window.setTimeout(search, 450);
            }

            function change(event) {
                if (event.target.matches('select[name="sort"], select[name="availability"]')) search();
            }

            function click(event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                const link = event.target.closest('a[href]');
                if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-library-fallback')) return;
                const url = new URL(link.href);
                if (url.origin !== browseUrl.origin || url.pathname !== browseUrl.pathname || url.hash) return;
                event.preventDefault();
                navigate(url, { scroll: !!link.closest('.pagination') });
            }

            const popstate = () => navigate(new URL(window.location.href), { history: false });
            window.addEventListener('popstate', popstate);
            onBeforeUnmount(() => {
                cancel();
                setTorrentLoading('library', false);
                window.removeEventListener('popstate', popstate);
            });

            return () => h('div', {
                'aria-busy': String(busy.value),
                onClick: click, onSubmit: submit, onInput: input, onChange: change,
                onCompositionend: input,
            }, [
                h('div', { role: 'status', 'aria-live': 'polite', class: error.value ? 'alert alert-info py-2' : 'visually-hidden' },
                    error.value ? [error.value, ' ', h('a', { href: retryUrl.value, 'data-library-fallback': '' }, 'Reload results')]
                        : busy.value ? 'Updating library…' : 'Library ready.'),
                // These fragments are rendered and escaped by the existing Laravel views.
                h('div', { innerHTML: html.value }),
            ]);
        },
    }).mount(root);
}
