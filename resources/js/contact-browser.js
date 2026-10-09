import { draft, jsonRequest, stateIsland, submitForm } from './progressive-ui';
import '../css/contact-support.css';

const root = document.querySelector('[data-contact-app]');
if (root) {
    const state = stateIsland(root);
    const drafts = new WeakMap();
    root.querySelectorAll('[data-contact-submit]').forEach(form => {
        const field = form.querySelector('textarea');
        if (field) drafts.set(form, draft(form, field));
        const time = form.querySelector('[data-contact-time]');
        const token = form.querySelector('[data-contact-token]');
        if (time) time.value = Date.now();
        if (token) token.value = btoa(`${Date.now()}`);
    });
    let timer, refreshing = false, stopped = false, failures = 0;
    let listUrl = new URL(location.href);
    let controller;
    const live = root.hasAttribute('data-contact-list') || root.dataset.contactUpdates;

    function schedule() {
        clearTimeout(timer);
        if (live && !stopped && !document.hidden) timer = setTimeout(() => refresh(true), Math.min(60000, 5000 * (2 ** failures)));
    }

    async function refresh(background = false) {
        if (refreshing || stopped || (background && (document.hidden || state.busy.value))) { schedule(); return; }
        refreshing = true;
        if (!background) { state.error.value = ''; state.loading(true); }
        controller = new AbortController();
        try {
            const url = new URL(root.dataset.contactUpdates || listUrl, location.origin);
            const ids = [...root.querySelectorAll('[data-contact-message]')].map(node => Number(node.dataset.contactMessage));
            if (root.dataset.contactUpdates) url.searchParams.set('after', ids.length ? Math.max(...ids) : 0);
            const data = await jsonRequest(url, { signal: controller.signal });
            if (typeof data.html !== 'string') throw new Error('Could not load contact requests.');
            if (root.hasAttribute('data-contact-list')) {
                const list = root.querySelector('[data-contact-list-content]');
                // Keep pagination keyboard focus stable during automatic updates.
                if (!background || !list.contains(document.activeElement)) {
                    const oldIds = new Set([...list.querySelectorAll('[data-contact-request]')].map(node => node.dataset.contactRequest));
                    const template = document.createElement('template'); template.innerHTML = data.html;
                    const added = [...template.content.querySelectorAll('[data-contact-request]')].filter(node => !oldIds.has(node.dataset.contactRequest)).length;
                    list.replaceChildren(template.content);
                    if (background && added && !listUrl.searchParams.get('page')) {
                        state.message.value = `${added} new ${added === 1 ? 'request' : 'requests'} received.`;
                        window.showNotification?.('info', state.message.value);
                    }
                }
            } else {
                const template = document.createElement('template'); template.innerHTML = data.html;
                const known = new Set(ids);
                let added = 0;
                for (const message of template.content.querySelectorAll('[data-contact-message]')) {
                    if (known.has(Number(message.dataset.contactMessage))) continue;
                    const thread = root.querySelector(`[data-contact-thread="${message.dataset.contactId}"]`);
                    const conversation = thread?.querySelector('[data-contact-messages]');
                    if (!conversation) continue;
                    const nearBottom = conversation.scrollHeight - conversation.scrollTop - conversation.clientHeight < 80;
                    conversation.append(message); added++;
                    if (nearBottom) conversation.scrollTop = conversation.scrollHeight;
                }
                for (const contact of data.contacts || []) {
                    const thread = root.querySelector(`[data-contact-thread="${contact.id}"]`);
                    if (!thread) continue;
                    const badge = thread.querySelector('[data-contact-status]');
                    badge.dataset.resolved = String(contact.resolved); badge.textContent = contact.resolved ? 'Resolved' : 'Open';
                    thread.querySelector('[data-contact-closed]')?.classList.toggle('d-none', !contact.resolved);
                    // Retain drafts in the DOM even when staff closes a conversation.
                    thread.querySelectorAll('[data-contact-composer], [data-contact-resolve]').forEach(form => {
                        form.classList.toggle('d-none', contact.resolved);
                        form.querySelectorAll('[type="submit"]').forEach(button => { button.disabled = contact.resolved; });
                    });
                }
                if (added && background) {
                    state.message.value = `${added} new ${added === 1 ? 'message' : 'messages'} received.`;
                    window.showNotification?.('info', state.message.value);
                }
            }
            failures = 0;
        } catch (error) {
            if (error.name !== 'AbortError') {
                failures = Math.min(failures + 1, 4);
                if ([401, 403, 404].includes(error.status)) { stopped = true; state.error.value = 'This conversation is no longer available. Please reload or check your requests again.'; }
                else if (!background) state.error.value = error.message;
            }
        } finally { refreshing = false; if (!background) state.loading(false); schedule(); }
    }

    root.addEventListener('submit', async event => {
        const form = event.target.closest('[data-contact-submit]');
        if (!form || event.defaultPrevented) return;
        event.preventDefault();
        if (refreshing) { controller?.abort(); }
        const field = form.querySelector('textarea');
        const value = field?.value;
        try {
            const data = await submitForm(form, state);
            if (!data) return;
            drafts.get(form)?.saved(value);
            if (data.redirect) { location.assign(data.redirect); return; }
            state.message.value = data.message || 'Saved.';
            // Wait for an aborted background refresh before fetching the committed reply.
            if (refreshing) await new Promise(resolve => {
                const check = () => refreshing ? setTimeout(check, 25) : resolve(); check();
            });
            await refresh();
        } catch (error) { state.error.value = error.message; }
    });

    root.addEventListener('click', async event => {
        if (!root.hasAttribute('data-contact-list') || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('[data-contact-list-content] .pagination a');
        if (!link) return;
        event.preventDefault();
        if (refreshing) return;
        listUrl = new URL(link.href); await refresh();
        if (!state.error.value) history.replaceState(null, '', listUrl);
    });
    document.addEventListener('visibilitychange', () => {
        clearTimeout(timer);
        if (!document.hidden && live) refresh(true);
    });
    window.addEventListener('pagehide', () => { clearTimeout(timer); controller?.abort(); });
    window.addEventListener('pageshow', schedule);
    schedule();
}
