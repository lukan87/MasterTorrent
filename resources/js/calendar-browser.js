import { createApp, h, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { setTorrentLoading } from './torrent-loading';

const root = document.querySelector('[data-calendar-browser]');

if (root) {
    const initialHtml = root.innerHTML;
    const browseUrl = new URL(root.dataset.browseUrl, window.location.origin);

    createApp({
        setup() {
            const html = ref(initialHtml);
            const busy = ref(false);
            const error = ref('');
            const notice = ref('');
            const mutating = ref(false);
            let desiredUrl = window.location.href;
            watch(mutating, value => setTorrentLoading('calendar-action', value), { flush: 'sync' });
            const retryUrl = ref(window.location.href);
            let pending;
            let timer;
            let sequence = 0;
            watch(busy, value => setTorrentLoading('calendar-navigation', value), { flush: 'sync' });

            function cancel() {
                window.clearTimeout(timer);
                pending?.abort();
                sequence++;
                busy.value = false;
            }

            async function navigate(url, { history = true, scroll = false } = {}) {
                cancel();
                desiredUrl = url.href;
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
                        headers: { Accept: 'application/json', 'X-Calendar-Browse': '1' },
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
                    const optionsOpen = root.querySelector('.tvc-filter-options')?.open;
                    const boardScroll = root.querySelector('.tvc-board')?.scrollLeft || 0;
                    html.value = data.html;
                    if (history && url.href !== window.location.href) window.history.pushState(null, '', url);
                    await nextTick();
                    if (optionsOpen) root.querySelector('.tvc-filter-options')?.setAttribute('open', '');
                    const board = root.querySelector('.tvc-board');
                    if (board) board.scrollLeft = boardScroll;
                    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                        window.bootstrap?.Tooltip.getOrCreateInstance(node);
                    });
                    const target = focus?.name ? root.querySelector(`[name="${CSS.escape(focus.name)}"]:not([type="hidden"])`) : null;
                    target?.focus({ preventScroll: true });
                    if (target && focus.start != null && typeof target.setSelectionRange === 'function') {
                        target.setSelectionRange(focus.start, focus.end);
                    }
                    if (scroll && url.hash) document.getElementById(decodeURIComponent(url.hash.slice(1)))?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (exception) {
                    if (exception.name !== 'AbortError' && requestId === sequence) {
                        if (!history) {
                            window.location.assign(url);
                            return;
                        }
                        error.value = 'Could not update the calendar. Your previous schedule is still shown.';
                    }
                } finally {
                    if (requestId === sequence) busy.value = false;
                }
            }

            function search(form = root.querySelector('.tvc-filters')) {
                if (!form?.checkValidity()) return;
                const url = new URL(form.action);
                url.search = new URLSearchParams(new FormData(form)).toString();
                navigate(url);
            }

            async function action(form) {
                if (mutating.value || !form.checkValidity()) return;
                mutating.value = true;
                error.value = '';
                notice.value = '';
                const buttons = [...root.querySelectorAll('form[method="POST"] button[type="submit"]:not(:disabled)')];
                buttons.forEach(button => { button.disabled = true; });
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json' },
                        body: new FormData(form),
                    });
                    const data = await response.json();
                    if (!response.ok || response.redirected) {
                        error.value = data.message || 'Could not update your watchlist. Refresh the calendar to check its current state.';
                        retryUrl.value = desiredUrl;
                        return;
                    }
                    notice.value = data.message;
                    await navigate(new URL(desiredUrl));
                } catch {
                    error.value = 'Could not confirm the watchlist update. Refresh the calendar to check its current state.';
                    retryUrl.value = desiredUrl;
                } finally {
                    mutating.value = false;
                    buttons.forEach(button => { button.disabled = false; });
                }
            }

            function submit(event) {
                const form = event.target;
                if (form.matches('.tvc-filters, .tvc-quick-options')) {
                    event.preventDefault();
                    search(form);
                } else if (form.method.toLowerCase() === 'post') {
                    const url = new URL(form.action);
                    if (url.origin !== browseUrl.origin || !url.pathname.startsWith(browseUrl.pathname.replace(/\/$/, '') + '/shows/')) return;
                    event.preventDefault();
                    action(form);
                }
            }

            function input(event) {
                if (!event.target.matches('.tvc-filters input[name="q"]')) return;
                cancel();
                if (!event.isComposing) timer = window.setTimeout(() => search(), 650);
            }

            function change(event) {
                if (event.target.closest('.tvc-quick-options') && event.target.type === 'checkbox') {
                    search(event.target.form);
                } else if (event.target.matches('.tvc-filters select, .tvc-filters input[type="date"]')) {
                    search();
                }
            }

            function click(event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                const link = event.target.closest('a[href]');
                if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-calendar-fallback')) return;
                const url = new URL(link.href);
                if (url.origin !== browseUrl.origin || url.pathname !== browseUrl.pathname) return;
                if (url.search === window.location.search && url.hash) return;
                event.preventDefault();
                navigate(url, { scroll: !!url.hash });
            }

            const popstate = () => navigate(new URL(window.location.href), { history: false });
            window.addEventListener('popstate', popstate);
            onBeforeUnmount(() => {
                cancel();
                setTorrentLoading('calendar-navigation', false);
                setTorrentLoading('calendar-action', false);
                window.removeEventListener('popstate', popstate);
            });

            return () => h('div', {
                'aria-busy': String(busy.value || mutating.value),
                onClick: click, onSubmit: submit, onInput: input, onChange: change,
                onCompositionend: input,
            }, [
                h('div', { role: 'status', 'aria-live': 'polite', class: error.value || notice.value ? 'alert alert-info py-2' : 'visually-hidden' },
                    error.value ? [error.value, ' ', h('a', { href: retryUrl.value, 'data-calendar-fallback': '' }, 'Reload results')]
                        : notice.value || (busy.value ? 'Updating calendar…' : 'Calendar ready.')),
                // These fragments are rendered and escaped by the existing Laravel views.
                h('div', { innerHTML: html.value }),
            ]);
        },
    }).mount(root);
}
