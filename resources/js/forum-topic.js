import { setTorrentLoading } from './torrent-loading';

const root = document.querySelector('[data-page-browser]');
const pending = new WeakSet();
const config = JSON.parse(document.getElementById('forum-config')?.textContent || '{}');
const draftKey = id => `forum_draft_v2:${config.user}:edit:${id}`;

async function send(url, options = {}) {
    const source = Symbol('forum-action');
    setTorrentLoading(source, true);
    try {
    const response = await fetch(url, { credentials: 'same-origin', ...options,
        headers: { Accept: 'application/json', ...options.headers } });
    const data = await response.json();
    if (!response.ok || response.redirected) throw new Error(data.message || 'Could not complete the request. Your draft is still available.');
    return data;
    } finally { setTorrentLoading(source, false); }
}

document.addEventListener('submit', async event => {
    const form = event.target;
    if (!form.matches('[data-forum-reply]')) return;
    event.preventDefault();
    if (pending.has(form)) return;
    pending.add(form);
    const button = form.querySelector('button[type="submit"]');
    const textarea = form.querySelector('textarea');
    const body = textarea.value;
    const status = form.querySelector('.forum-editor-status');
    if (button) button.disabled = true;
    try {
        const data = await send(form.action, { method: 'POST', body: new FormData(form) });
        if (textarea.value === body) {
            textarea.value = '';
            document.dispatchEvent(new CustomEvent('forum:reply-saved'));
        }
        if (status) status.textContent = data.message;
        document.dispatchEvent(new CustomEvent('page:navigate', { detail: { root, url: data.url } }));
    } catch (error) {
        if (status) status.textContent = error.message;
    } finally {
        pending.delete(form);
        if (button) { button.disabled = false; button.removeAttribute('aria-busy'); }
    }
});

document.addEventListener('click', async event => {
    const link = event.target.closest('[data-post-edit]');
    if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    const article = link.closest('article');
    const content = article.querySelector('.forum-post-body');
    if (content.querySelector('form') || pending.has(article)) return;
    pending.add(article);
    const original = content.innerHTML;
    try {
        const data = await send(link.href);
        const form = document.createElement('form');
        const textarea = document.createElement('textarea');
        textarea.name = 'body'; textarea.value = data.body;
        textarea.className = 'form-control forum-textarea';
        textarea.rows = 8; textarea.required = true; textarea.minLength = 3; textarea.maxLength = 10000;
        textarea.setAttribute('aria-label', 'Edit post');
        const key = draftKey(article.id.replace('post-', ''));
        const status = document.createElement('p'); status.setAttribute('role', 'status');
        try {
            const draft = JSON.parse(localStorage.getItem(key) || 'null');
            if (draft?.body) { textarea.value = draft.body; status.textContent = 'Your saved edit draft was restored.'; }
        } catch { /* Storage may be disabled. */ }
        textarea.addEventListener('input', () => {
            try { localStorage.setItem(key, JSON.stringify({ body: textarea.value, updated: Date.now() })); } catch { /* Keep the editor usable. */ }
        });
        const save = document.createElement('button'); save.type = 'submit'; save.className = 'forum-submit-btn mt-2'; save.textContent = 'Save changes';
        const cancel = document.createElement('button'); cancel.type = 'button'; cancel.className = 'btn btn-secondary mt-2 ms-2'; cancel.textContent = 'Cancel';
        cancel.addEventListener('click', () => { content.innerHTML = original; });
        form.append(textarea, save, cancel, status);
        form.addEventListener('submit', async submit => {
            submit.preventDefault();
            if (save.disabled) return;
            save.disabled = true; textarea.readOnly = true; cancel.disabled = true;
            const body = new FormData(); body.set('body', textarea.value); body.set('_method', 'PUT');
            body.set('_token', document.querySelector('input[name="_token"]')?.value || '');
            try {
                const updated = await send(data.update_url, { method: 'POST', body });
                const template = document.createElement('template'); template.innerHTML = updated.html;
                article.replaceWith(template.content);
                try { localStorage.removeItem(key); } catch { /* Storage may be disabled. */ }
                document.dispatchEvent(new CustomEvent('page:updated', { detail: { root } }));
            } catch (error) { status.textContent = error.message; }
            finally { save.disabled = false; textarea.readOnly = false; cancel.disabled = false; }
        });
        content.replaceChildren(form); textarea.focus();
    } catch (error) {
        const status = document.createElement('p'); status.setAttribute('role', 'alert'); status.textContent = error.message;
        content.append(status);
    } finally { pending.delete(article); }
});

document.addEventListener('page:updated', event => {
    if (event.detail?.data?.replyCount !== undefined) {
        const count = document.querySelector('[data-reply-count]');
        if (count) count.textContent = Number(event.detail.data.replyCount).toLocaleString();
    }
});
