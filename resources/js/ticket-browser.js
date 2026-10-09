import { draft, jsonRequest, stateIsland, submitForm } from './progressive-ui';
const root = document.querySelector('[data-ticket-browser]');
if (root) {
    const state = stateIsland(root);
    let form = root.querySelector('form textarea[name="message"]')?.form;
    let field = form?.querySelector('[name="message"]');
    let savedDraft = draft(form, field);
    let refreshing = false, stopped = false, composerState;
    async function refresh(background = false) {
        if (refreshing) return;
        refreshing = true;
        try {
            const url = new URL(location.href);
            const ids = [...root.querySelectorAll('[data-reply-id]')].map(node => Number(node.dataset.replyId));
            if (ids.length) url.searchParams.set('after', Math.max(...ids));
            const data = await jsonRequest(url, { headers: { 'X-Ticket-Content': '1' } });
            if (typeof data.html !== 'string') throw new Error('Could not load ticket activity.');
            const replies = document.getElementById('ticket-replies');
            const template = document.createElement('template'); template.innerHTML = data.html;
            const known = new Set([...replies.querySelectorAll('[data-reply-id]')].map(node => node.dataset.replyId));
            for (const article of template.content.querySelectorAll('[data-reply-id]')) {
                if (known.has(article.dataset.replyId)) continue;
                document.getElementById('latest-reply')?.removeAttribute('id'); article.id = 'latest-reply';
                replies.append(article);
                document.getElementById('ticket-new-replies')?.classList.remove('d-none');
            }
            root.querySelectorAll('.support-badge[data-ticket-status]').forEach(badge => { badge.textContent = data.status; badge.dataset.value = data.status; });
            const nextState = `${data.status}:${data.locked}`;
            if (composerState !== nextState) {
                composerState = nextState;
                // Preserve the actual textarea and FileList when a ticket changes while composing.
                const template = document.createElement('template'); template.innerHTML = data.composer;
                const nextForm = template.content.querySelector('form textarea[name="message"]')?.form;
                const current = root.querySelector('[data-ticket-composer]');
                if (form && !nextForm) {
                    let notice = current.querySelector('[data-ticket-composer-state]');
                    if (!notice) { notice = document.createElement('div'); notice.dataset.ticketComposerState = ''; current.prepend(notice); }
                    const next = template.content.querySelector('[data-ticket-composer]');
                    notice.replaceChildren(...next.children);
                    form.querySelector('[type="submit"]').disabled = true;
                    state.message.value = 'This ticket is locked or closed. Your draft is retained.';
                } else if (form && nextForm) {
                    current.querySelector('[data-ticket-composer-state]')?.remove();
                    if (state.message.value === 'This ticket is locked or closed. Your draft is retained.') state.message.value = 'Replies reopened. Your draft is ready.';
                    form.querySelector('[type="submit"]').disabled = false;
                } else if (current) {
                    current.replaceWith(template.content);
                    form = root.querySelector('form textarea[name="message"]')?.form;
                    field = form?.querySelector('[name="message"]'); savedDraft = draft(form, field);
                }
            }
            const details = root.querySelector('[data-ticket-details]');
            if (details && (!background || !details.contains(document.activeElement)) && !details.dataset.dirty) {
                const template = document.createElement('template'); template.innerHTML = data.details;
                details.replaceWith(template.content);
            }
        } catch (error) {
            if ([401, 403, 404].includes(error.status)) stopped = true;
            if (!background) state.error.value = error.message;
        } finally { refreshing = false; }
    }
    root.addEventListener('change', event => {
        const details = event.target.closest('[data-ticket-details]'); if (details) details.dataset.dirty = '1';
        if (event.target.id !== 'staff-note') return;
        document.getElementById('reply-audience').textContent = event.target.checked ? 'Only staff can see this note' : 'Visible to the member and support team';
        document.getElementById('send-reply').textContent = event.target.checked ? 'Add internal note' : 'Send reply';
    });
    root.addEventListener('submit', async event => {
        const submitted = event.target;
        if (!(submitted instanceof HTMLFormElement) || event.defaultPrevented) return;
        event.preventDefault();
        const value = submitted === form ? field.value : null;
        const attachments = [...submitted.querySelectorAll('input[type="file"]')].map(input => ({ input, files: [...input.files] }));
        try {
            const data = await submitForm(submitted, state);
            if (!data) return;
            if (value !== null) {
                savedDraft.saved(value);
                attachments.forEach(({ input, files }) => { if (input.files.length === files.length && [...input.files].every((file, i) => file === files[i])) { input.value = ''; input.dispatchEvent(new Event('change', { bubbles: true })); } });
            }
            const details = submitted.closest('[data-ticket-details]'); if (details) delete details.dataset.dirty;
            state.message.value = data.message;
            await refresh();
        } catch (error) { state.error.value = error.message; }
    });
    const polling = setInterval(() => { if (!document.hidden && !state.busy.value && !stopped) refresh(true); }, 15000);
    window.addEventListener('pagehide', event => { if (!event.persisted) clearInterval(polling); });
}
