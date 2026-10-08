(() => {
    'use strict';
    const config = JSON.parse(document.getElementById('forum-config')?.textContent || '{}');
    const storageKey = (draft) => `forum_draft_v2:${config.user}:${draft}`;
    const csrf = () => document.querySelector('input[name="_token"]')?.value || '';
    const message = (element, text) => { if (element) element.textContent = text; };
    async function request(url, body) {
        const response = await fetch(url, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        let data;
        try { data = await response.json(); } catch (_) { throw new Error('The server could not complete the request. Please try again.'); }
        if (!response.ok) {
            throw new Error(response.status === 419 ? 'Your session expired. Save your draft and refresh the page.'
                : response.status === 429 ? 'Please wait a moment before trying again.'
                : data.message || 'The request failed. Please try again.');
        }
        return data;
    }
    try { if (config.saved) localStorage.removeItem(storageKey(config.saved)); } catch (_) { /* Storage can be disabled. */ }

    const textarea = document.querySelector('textarea[name="body"]');
    if (textarea) {
        const form = textarea.closest('form');
        const title = form.querySelector('input[name="title"]');
        const status = document.createElement('p');
        status.className = 'forum-editor-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        textarea.insertAdjacentElement('afterend', status);
        textarea.maxLength = 10000;
        if (!textarea.labels?.length) textarea.setAttribute('aria-label', 'Post content');
        const key = storageKey(config.draft);
        let timer;
        let dirty = false;
        const snapshot = () => ({ body: textarea.value, title: title?.value || '', updated: Date.now() });
        function saveDraft() {
            if (!dirty) return;
            clearTimeout(timer);
            try {
                const draft = snapshot();
                if (draft.body.trim() || draft.title.trim()) localStorage.setItem(key, JSON.stringify(draft));
                else localStorage.removeItem(key);
                message(status, 'Draft saved in this browser.');
            } catch (_) { message(status, 'Draft storage is unavailable. Keep a copy before leaving.'); }
        }
        try {
            const saved = JSON.parse(localStorage.getItem(key) || 'null');
            if (saved && (saved.body !== textarea.value || (title && saved.title !== title.value))) {
                const notice = document.createElement('div');
                notice.className = 'forum-draft-notice';
                notice.setAttribute('role', 'status');
                const text = document.createElement('span');
                text.textContent = 'A saved draft is available. ';
                const restore = document.createElement('button');
                restore.type = 'button'; restore.textContent = 'Restore draft';
                const discard = document.createElement('button');
                discard.type = 'button'; discard.textContent = 'Discard draft';
                restore.addEventListener('click', () => {
                    textarea.value = saved.body || '';
                    if (title) title.value = saved.title || '';
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    textarea.focus(); notice.remove();
                });
                discard.addEventListener('click', () => {
                    try { localStorage.removeItem(key); } catch (_) {}
                    notice.remove();
                });
                notice.append(text, restore, discard);
                textarea.insertAdjacentElement('beforebegin', notice);
            }
        } catch (_) { message(status, 'Draft storage is unavailable.'); }
        form.addEventListener('input', () => {
            dirty = true;
            message(status, 'Saving draft…');
            clearTimeout(timer);
            timer = setTimeout(saveDraft, 500);
        });
        window.addEventListener('pagehide', saveDraft);
        form.addEventListener('submit', () => {
            dirty = true; saveDraft();
            const submit = form.querySelector('button[type="submit"]');
            if (submit) { submit.disabled = true; submit.setAttribute('aria-busy', 'true'); }
        });
        window.addEventListener('pageshow', () => {
            const submit = form.querySelector('button[type="submit"]');
            if (submit) { submit.disabled = false; submit.removeAttribute('aria-busy'); }
        });
        const previewButton = form.querySelector('#forum-preview-btn, #forum-create-preview-btn, #forum-edit-preview-btn');
        const previewPane = form.querySelector('.forum-post-preview-pane');
        if (previewButton && previewPane) {
            previewButton.setAttribute('aria-controls', previewPane.id);
            previewButton.setAttribute('aria-expanded', 'false');
            previewButton.addEventListener('click', async () => {
                if (previewPane.style.display !== 'none') {
                    previewPane.style.display = 'none';
                    previewButton.textContent = 'Preview';
                    previewButton.setAttribute('aria-expanded', 'false');
                    return;
                }
                previewButton.disabled = true;
                message(status, 'Loading preview…');
                try {
                    const data = await request(config.preview, { body: textarea.value });
                    // Only trusted, escaped markup returned by the shared server renderer.
                    previewPane.innerHTML = data.html || '<em>Nothing to preview.</em>';
                    previewPane.style.display = '';
                    previewButton.textContent = 'Edit';
                    previewButton.setAttribute('aria-expanded', 'true');
                    message(status, 'Preview loaded.');
                } catch (error) { message(status, error.message); }
                finally { previewButton.disabled = false; }
            });
        }
        const selected = new Map();
        function quote(button) {
            const name = (button.dataset.username || 'Former member').replace(/["\r\n]/g, '');
            return `[quote="${name}"]\n${JSON.parse(button.dataset.body).trim()}\n[/quote]\n\n`;
        }
        function insert(text) {
            const next = textarea.value.trimEnd() + (textarea.value.trim() ? '\n\n' : '') + text;
            if (next.length > 10000) { message(status, 'This quote would exceed the 10,000 character limit. Shorten your reply first.'); return false; }
            textarea.value = next;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.focus(); textarea.setSelectionRange(next.length, next.length);
            textarea.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
            return true;
        }
        const bar = document.getElementById('forum-multiquote-bar');
        function updateQuotes() {
            if (bar) bar.style.display = selected.size ? '' : 'none';
            message(document.getElementById('mq-count'), String(selected.size));
            document.querySelectorAll('[data-multi-quote]').forEach(button => {
                const active = selected.has(button.dataset.postId);
                button.classList.toggle('active', active);
                button.setAttribute('aria-pressed', String(active));
            });
        }
        document.querySelectorAll('.quote-post-btn').forEach(button => {
            button.addEventListener('click', () => { try { insert(quote(button)); } catch (_) { message(status, 'This post could not be quoted.'); } });
            if (!bar) return;
            const multi = document.createElement('button');
            multi.type = 'button'; multi.className = 'forum-post-action'; multi.textContent = '+ Quote';
            multi.dataset.multiQuote = ''; multi.dataset.postId = button.dataset.postId;
            multi.setAttribute('aria-label', `Add post ${button.dataset.postId} to multi-quote`);
            multi.setAttribute('aria-pressed', 'false');
            multi.addEventListener('click', () => {
                if (selected.has(button.dataset.postId)) selected.delete(button.dataset.postId);
                else selected.set(button.dataset.postId, button);
                updateQuotes();
            });
            button.insertAdjacentElement('afterend', multi);
        });
        document.getElementById('mq-insert-btn')?.addEventListener('click', () => {
            try { if (insert([...selected.values()].map(quote).join(''))) { selected.clear(); updateQuotes(); } }
            catch (_) { message(status, 'A selected post could not be quoted.'); }
        });
        document.getElementById('mq-clear-btn')?.addEventListener('click', () => { selected.clear(); updateQuotes(); });
        document.getElementById('forum-multiquote-toggle')?.addEventListener('click', () => {
            message(status, 'Use “+ Quote” beside posts, then “Insert all” to add your selection.');
        });
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('.forum-reactions form');
        if (!form) return;
        event.preventDefault();
        const section = form.closest('[data-reactions]');
        if (section.dataset.pending === 'true') return;
        section.dataset.pending = 'true'; section.setAttribute('aria-busy', 'true');
        section.querySelectorAll('button').forEach(button => { button.disabled = true; });
        const status = section.querySelector('.forum-reaction-status');
        message(status, 'Updating reaction…');
        try {
            const open = !!section.querySelector('details[open]');
            const data = await request(form.action, { reaction: form.querySelector('[name="reaction"]').value });
            const template = document.createElement('template');
            template.innerHTML = data.html;
            const replacement = template.content.firstElementChild;
            if (!replacement) throw new Error('Reaction updated. Refresh to see the result.');
            if (open && replacement.querySelector('details')) replacement.querySelector('details').open = true;
            section.replaceWith(replacement);
            message(replacement.querySelector('.forum-reaction-status'), 'Reaction updated.');
        } catch (error) { message(status, error.message); }
        finally {
            section.dataset.pending = 'false'; section.removeAttribute('aria-busy');
            section.querySelectorAll('button').forEach(button => { button.disabled = false; });
        }
    });
    if (/^#post-\d+$/.test(location.hash)) {
        const post = document.getElementById(location.hash.slice(1));
        if (post) { post.classList.add('post-flash-highlight'); setTimeout(() => post.classList.remove('post-flash-highlight'), 3200); }
    }
})();
