import { createApp, h, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { setTorrentLoading } from './torrent-loading';

const root = document.querySelector('[data-page-browser]');

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
            let polling;
            let actionPending = false;
            watch(busy, value => setTorrentLoading('page', value), { flush: 'sync' });

            function cancel() {
                window.clearTimeout(timer);
                pending?.abort();
                sequence++;
                busy.value = false;
            }

            async function navigate(url, { history = true, scroll = false, live = false } = {}) {
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
                        headers: { Accept: 'application/json', 'X-Page-Browse': '1' },
                    });
                    if (!response.ok || response.redirected) throw new Error('navigation');
                    const data = await response.json();
                    if (data.redirect) {
                        const target = new URL(data.redirect, location.origin);
                        if (target.origin !== browseUrl.origin || target.pathname !== browseUrl.pathname) throw new Error('redirect');
                        return navigate(target, { history, scroll });
                    }
                    if (typeof data.html !== 'string') throw new Error('response');
                    if (requestId !== sequence || controller.signal.aborted) return;

                    const active = root.contains(document.activeElement) ? document.activeElement : null;
                    const focus = active ? { name: active.name, start: active.selectionStart, end: active.selectionEnd } : null;
                    const uploads = root.dataset.seedbox ? [...root.querySelectorAll('input[type="file"]')]
                        .filter(input => input.files.length).map(input => input.closest('form')).filter(Boolean) : [];
                    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                        window.bootstrap?.Tooltip.getInstance(node)?.dispose();
                    });
                    if (live && root.dataset.seedbox) {
                        const template = document.createElement('template'); template.innerHTML = data.html;
                        const tbody = root.querySelector('tbody');
                        const incoming = template.content.querySelector('tbody');
                        if (tbody && incoming) {
                            const oldRows = new Map([...tbody.querySelectorAll('[data-seedbox-row]')].map(row => [row.dataset.seedboxRow, row]));
                            const rows = [...incoming.querySelectorAll('[data-seedbox-row]')];
                            if (!rows.length) tbody.innerHTML = incoming.innerHTML;
                            else {
                                tbody.querySelectorAll('tr:not([data-seedbox-row])').forEach(row => row.remove());
                                for (const next of rows) {
                                    const existing = oldRows.get(next.dataset.seedboxRow);
                                    if (existing) {
                                        const tracker = existing.querySelector('.torrent-trackers');
                                        if (tracker && next.querySelector('.torrent-trackers')) next.querySelector('.torrent-trackers').textContent = tracker.textContent;
                                        const upload = existing.querySelector('.upload-button');
                                        if (upload && next.querySelector('.upload-button')) next.querySelector('.upload-button').style.display = upload.style.display;
                                        [...next.cells].forEach((cell, index) => {
                                            if (existing.cells[index].innerHTML !== cell.innerHTML) existing.cells[index].innerHTML = cell.innerHTML;
                                        });
                                        tbody.append(existing); oldRows.delete(next.dataset.seedboxRow);
                                    } else tbody.append(next);
                                }
                                oldRows.forEach(row => row.remove());
                            }
                        }
                        ['.stats-grid', '.connection-badge', '.torrent-count'].forEach(selector => {
                            const current = root.querySelector(selector), next = template.content.querySelector(selector);
                            if (current && next) { current.innerHTML = next.innerHTML; current.className = next.className; }
                        });
                        const pagination = root.querySelector('[data-seedbox-pagination]');
                        const nextPagination = template.content.querySelector('[data-seedbox-pagination]');
                        if (pagination && nextPagination) pagination.innerHTML = nextPagination.innerHTML;
                    } else html.value = data.html;
                    if (history && url.href !== window.location.href) window.history.pushState(null, '', url);
                    await nextTick();
                    for (const upload of uploads) {
                        const replacement = [...root.querySelectorAll('form')].find(form => form.action === upload.action);
                        if (replacement && replacement !== upload) replacement.replaceWith(upload);
                    }
                    document.dispatchEvent(new CustomEvent('page:updated', { detail: { root, data } }));
                    if (url.hash) document.getElementById(decodeURIComponent(url.hash.slice(1)))?.scrollIntoView({ block: 'center' });
                    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
                        window.bootstrap?.Tooltip.getOrCreateInstance(node);
                    });
                    const target = focus?.name ? root.querySelector(`[name="${CSS.escape(focus.name)}"]:not([type="hidden"])`) : null;
                    target?.focus({ preventScroll: true });
                    if (target && focus.start != null && typeof target.setSelectionRange === 'function') {
                        target.setSelectionRange(focus.start, focus.end);
                    }
                    if (scroll) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (exception) {
                    if (exception.name !== 'AbortError' && requestId === sequence) {
                        if (!history && !live) {
                            window.location.assign(url);
                            return;
                        }
                        error.value = 'Could not update this page. Your previous results are still shown.';
                    }
                } finally {
                    if (requestId === sequence) busy.value = false;
                }
            }

            function search(formOverride) {
                const form = formOverride || root.querySelector('form[method="GET"], form[method="get"]');
                if (!form?.checkValidity()) return;
                const url = new URL(form.action);
                const params = new URLSearchParams(new FormData(form));
                for (const [key, value] of [...params]) {
                    if (!value || (key === 'sort' && value === 'latest') || (key === 'availability' && value === 'all')) params.delete(key);
                }
                url.search = params.toString();
                navigate(url);
            }

            async function mutation(form) {
                if (actionPending) return;
                actionPending = true;
                setTorrentLoading('page-action', true);
                const buttons = [...form.querySelectorAll('button:not(:disabled)')];
                buttons.forEach(button => { button.disabled = true; });
                try {
                    const response = await fetch(form.action, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json' }, body: new FormData(form) });
                    const data = await response.json();
                    if (!response.ok || response.redirected) throw new Error(data.message || 'Could not complete this action.');
                    if (data.unreadCount !== undefined) {
                        document.querySelectorAll('[data-notification-count]').forEach(badge => { badge.textContent = data.unreadCount; badge.hidden = data.unreadCount === 0; });
                    }
                    await navigate(new URL(location.href), { history: false });
                } catch (exception) { error.value = exception.message; retryUrl.value = location.href; }
                finally { actionPending = false; buttons.forEach(button => { button.disabled = false; }); setTorrentLoading('page-action', false); }
            }

            onMounted(() => {
                document.dispatchEvent(new CustomEvent('page:updated', { detail: { root } }));
                if (root.dataset.poll) polling = window.setInterval(() => {
                    if (document.hidden || busy.value || actionPending || root.contains(document.activeElement) || root.querySelector('details[open], .modal.show') || [...root.querySelectorAll('input[type="file"]')].some(input => input.files.length)) return;
                    navigate(new URL(location.href), { history: false, live: true });
                }, Number(root.dataset.poll));
            });

            function submit(event) {
                if (event.defaultPrevented) return;
                if (event.target.matches('[data-page-action]')) { event.preventDefault(); mutation(event.target); return; }
                if (event.target.method.toLowerCase() !== 'get' || new URL(event.target.action).pathname !== browseUrl.pathname) return;
                event.preventDefault();
                search(event.target);
            }

            function input(event) {
                if (!event.target.matches('input[name="q"], input[name="search"], input[name="category_q"]') || event.target.form?.method.toLowerCase() !== 'get' || new URL(event.target.form.action).pathname !== browseUrl.pathname) return;
                cancel();
                if (!event.isComposing) timer = window.setTimeout(() => search(event.target.form), 500);
            }

            function change(event) {
                if (event.target.form?.method.toLowerCase() === 'get' && event.target.matches('select, input[type="checkbox"]')) search(event.target.form);
            }

            function click(event) {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                const link = event.target.closest('a[href]');
                if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-page-fallback')) return;
                const url = new URL(link.href);
                if (url.origin !== browseUrl.origin || url.pathname !== browseUrl.pathname) return;
                if (url.search === location.search && url.hash) return;
                event.preventDefault();
                navigate(url, { scroll: url.searchParams.has('page') });
            }

            const external = event => { if (!event.detail.root || event.detail.root === root) navigate(new URL(event.detail.url, location.origin), event.detail.options); };
            document.addEventListener('page:navigate', external);
            const popstate = () => navigate(new URL(window.location.href), { history: false });
            window.addEventListener('popstate', popstate);
            onBeforeUnmount(() => {
                window.clearInterval(polling);
                setTorrentLoading('page-action', false);
                document.removeEventListener('page:navigate', external);
                cancel();
                setTorrentLoading('page', false);
                window.removeEventListener('popstate', popstate);
            });

            return () => h('div', {
                'aria-busy': String(busy.value),
                onClick: click, onSubmit: submit, onInput: input, onChange: change,
                onCompositionend: input,
            }, [
                h('div', { role: 'status', 'aria-live': 'polite', class: error.value ? 'alert alert-info py-2' : 'visually-hidden' },
                    error.value ? [error.value, ' ', h('a', { href: retryUrl.value, 'data-page-fallback': '' }, 'Reload results')]
                        : busy.value ? 'Updating page…' : 'Page ready.'),
                // These fragments are rendered and escaped by the existing Laravel views.
                h('div', { innerHTML: html.value }),
            ]);
        },
    }).mount(root);
}
