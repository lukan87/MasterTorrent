(() => {
    'use strict';
    const root = document.getElementById('community-chat');
    const form = document.getElementById('shoutbox-form');
    if (!root || !form || root.dataset.initialized) return;
    root.dataset.initialized = 'true';
    const box = document.getElementById('shoutbox-messages');
    const container = document.getElementById('shoutbox-container');
    const pinned = document.getElementById('shoutbox-pinned');
    let followingLatest = true;
    const input = form.querySelector('[name="content"]');
    const search = document.getElementById('chat-search');
    const filter = document.getElementById('chat-filter');
    const status = document.getElementById('chat-connection');
    const feedback = document.getElementById('chat-feedback');
    const draftStatus = document.getElementById('chat-draft-status');
    const jump = document.getElementById('shoutbox-jump-bottom');
    const csrf = form.querySelector('[name="_token"]').value;
    const draftKey = 'shoutbox-draft:' + root.dataset.user;
    let pending = null;
    let polling = false;
    let mutation = false;
    let timer;
    let typingTimer;
    let lastTyping = 0;
    let lastRevision = null;
    let generation = 0;
    let stopped = false;

    function notice(text, error = false) {
        feedback.textContent = text;
        feedback.classList.toggle('text-danger', error);
    }

    async function request(url, options = {}) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        let response;
        let data;
        try {
            response = await fetch(url, {
                ...options, signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf, ...options.headers },
            });
            data = await response.json().catch(() => ({}));
        } catch (error) {
            throw new Error(error.name === 'AbortError'
                ? 'The request timed out. Check chat before retrying; your draft has been kept.'
                : 'Unable to connect. Your draft has been kept.');
        } finally { clearTimeout(timeout); }
        if (!response.ok || response.redirected) {
            if ([401, 419].includes(response.status) || response.redirected ||
                (response.status === 403 && !options.method)) {
                stopped = true;
                status.textContent = 'Chat unavailable — refresh the page';
            }
            throw new Error(Object.values(data.errors || {}).flat()[0] || data.error || data.message ||
                (response.status === 429 ? 'Please wait before trying again.' : 'Request failed. Your text has been kept. Please try again.'));
        }
        return data;
    }

    function applyFilter() {
        let visible = 0;
        const term = search.value.trim().toLocaleLowerCase();
        box.querySelectorAll('.message').forEach(message => {
            const text = [...message.querySelectorAll('.username, .reply-username, .content, .reply-content')]
                .map(el => el.textContent).join(' ').toLocaleLowerCase();
            const mine = message.dataset.user === root.dataset.user ||
                [...message.querySelectorAll('.reply-card')].some(reply => reply.dataset.user === root.dataset.user);
            const matches = text.includes(term) && (filter.value !== 'mine' || mine) &&
                (filter.value !== 'pinned' || message.dataset.sticky === '1');
            message.hidden = !matches;
            if (matches) visible++;
        });
        document.getElementById('chat-no-results').hidden = visible > 0 || (filter.value === 'pinned' && pinned.querySelector('.message')) || (!term && filter.value === 'all');
    }

    function editing() {
        return [...root.querySelectorAll('.edit-form, .reply-form')].some(el => el.style.display === 'block');
    }

    function nearBottom() {
        return container.scrollHeight - container.scrollTop - container.clientHeight < 150;
    }

    function scrollToLatest() {
        followingLatest = true;
        container.scrollTop = container.scrollHeight;
        showJump();
    }

    function showJump() {
        jump.style.display = pending || !nearBottom() ? 'flex' : 'none';
        jump.classList.toggle('has-count', Boolean(pending));
        document.getElementById('shoutbox-jump-count').textContent = pending ? '•' : '';
        jump.title = pending ? 'Chat updated — show latest messages' : 'Show latest messages';
    }

    function render(snapshot) {
        if (!snapshot || snapshot.revision === lastRevision) return;
        root.querySelectorAll('#shoutbox-messages [data-bs-toggle="tooltip"], #shoutbox-pinned [data-bs-toggle="tooltip"]').forEach(el => window.bootstrap?.Tooltip.getInstance(el)?.dispose());
        box.innerHTML = snapshot.html;
        pinned.innerHTML = snapshot.pinned_html || '';
        lastRevision = snapshot.revision;
        pending = null;
        applyFilter();
        scrollToLatest();
    }

    async function refresh(force = false) {
        if (polling || stopped || document.hidden || (!force && mutation)) return;
        polling = true;
        const requestGeneration = generation;
        try {
            const data = await request(root.dataset.pollUrl + '?snapshot=1');
            if (requestGeneration !== generation) return;
            if (typeof data.html !== 'string') throw new Error('Unable to refresh chat.');
            // Never replace a reply or edit being composed, even if it opened during this request.
            if (editing() || (!force && (!nearBottom() || mutation))) {
                if (data.revision !== lastRevision) pending = data;
            } else {
                render(data);
            }
            status.textContent = 'Live · updates every 8s';
            showJump();
        } catch (error) {
            if (!stopped) status.textContent = 'Reconnecting…';
            if (force) notice(error.message, true);
        } finally { polling = false; }
    }

    function saveDraft() {
        try {
            if (input.value) sessionStorage.setItem(draftKey, input.value);
            else sessionStorage.removeItem(draftKey);
            draftStatus.textContent = input.value ? 'Draft saved in this tab' : '';
        } catch (_) { draftStatus.textContent = ''; }
        const hasText = Boolean(input.value.trim());
        const send = document.getElementById('chat-send');
        send.hidden = !hasText;
        send.disabled = mutation || !hasText;
        form.classList.toggle('has-draft', hasText);
    }

    try {
        const draft = sessionStorage.getItem(draftKey);
        if (draft && !input.value) {
            input.value = draft;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            draftStatus.textContent = 'Draft restored';
        }
    } catch (_) { /* Storage may be disabled. Chat still works. */ }

    function typing(stop = false) {
        request(stop ? root.dataset.stopUrl : root.dataset.typingUrl, { method: 'POST' }).catch(() => {});
    }
    input.addEventListener('input', () => {
        saveDraft();
        if (!input.value.trim()) { typing(true); return; }
        if (Date.now() - lastTyping > 3000) { typing(); lastTyping = Date.now(); }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => typing(true), 2000);
    });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            if (!mutation && input.value.trim()) form.requestSubmit();
        }
    });

    async function submit(target) {
        if (mutation) return;
        const text = target.querySelector('textarea');
        if (text && !text.value.trim()) return;
        const original = text?.value;
        if (text) text.readOnly = true;
        mutation = true;
        generation++;
        const buttons = [...target.querySelectorAll('button')];
        buttons.forEach(button => button.disabled = true);
        notice('Saving…');
        try {
            await request(target.action, { method: 'POST', body: new FormData(target) });
            if (target === form) {
                if (input.value === original) input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                typing(true);
            } else {
                if (text) text.value = '';
                const editor = target.closest('.edit-form') || target;
                editor.style.display = 'none';
            }
            notice('Saved.');
            // Ignore any snapshot fetched before this mutation completed.
            pending = null;
            lastRevision = null;
        } catch (error) {
            notice(error.message, true);
        } finally {
            mutation = false;
            if (text) text.readOnly = false;
            buttons.forEach(button => button.disabled = false);
            saveDraft();
            await refresh(true);
        }
    }

    root.addEventListener('submit', event => {
        const target = event.target;
        if (!(target instanceof HTMLFormElement)) return;
        event.preventDefault();
        if (target.matches('.shoutbox-delete-form, .reply-delete-form') && !confirm('Delete this message?')) return;
        submit(target);
    });
    window.handleReplyDelete = (event, target) => {
        event.preventDefault();
        target.requestSubmit();
    };

    function toggleEditor(id, reply, open) {
        const editor = document.getElementById((reply ? 'reply-edit-form-' : 'edit-form-') + id);
        if (!editor) return;
        editor.style.display = open ? 'block' : 'none';
        if (open) editor.querySelector('textarea')?.focus();
    }
    window.openEdit = id => toggleEditor(id, false, true);
    window.closeEdit = id => toggleEditor(id, false, false);
    window.openReplyEdit = id => toggleEditor(id, true, true);
    window.closeReplyEdit = id => toggleEditor(id, true, false);
    window.toggleReplyForm = id => {
        const reply = document.getElementById('reply-form-' + id);
        if (!reply) return;
        reply.style.display = reply.style.display === 'block' ? 'none' : 'block';
        if (reply.style.display === 'block') reply.querySelector('textarea').focus();
    };
    root.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        event.target.closest('.edit-form, .reply-form')?.style.setProperty('display', 'none');
    });
    document.getElementById('chat-expand').addEventListener('click', event => {
        const expanded = root.classList.toggle('chat-expanded');
        event.currentTarget.setAttribute('aria-pressed', String(expanded));
        event.currentTarget.textContent = expanded ? 'Collapse' : 'Expand';
    });
    search.addEventListener('input', applyFilter);
    filter.addEventListener('change', applyFilter);
    container.addEventListener('scroll', () => {
        followingLatest = nearBottom();
        showJump();
    });
    jump.addEventListener('click', () => {
        if (editing()) { notice('Finish or cancel your open reply or edit to show updates.'); return; }
        search.value = '';
        filter.value = 'all';
        if (pending) render(pending);
        applyFilter();
        scrollToLatest();
        refresh(true);
    });

    async function cycle() {
        clearTimeout(timer);
        if (!document.hidden && !stopped) {
            await refresh();
            try {
                const names = await request(root.dataset.typingUsersUrl);
                const indicator = document.getElementById('typing-indicator');
                document.getElementById('typing-users').textContent = names.length ? names.join(', ') + (names.length === 1 ? ' is typing' : ' are typing') : '';
                indicator.style.opacity = names.length ? '1' : '0';
            } catch (_) { /* Message polling reports connection status. */ }
        }
        clearTimeout(timer);
        timer = setTimeout(cycle, 8000);
    }
    document.addEventListener('visibilitychange', () => {
        clearTimeout(timer);
        if (!document.hidden) cycle();
    });
    window.addEventListener('online', () => { if (!stopped) cycle(); });
    window.addEventListener('offline', () => { status.textContent = 'Offline · draft kept'; });
    saveDraft();
    applyFilter();
    scrollToLatest();
    // Images and other embedded content can grow after the initial render.
    if ('ResizeObserver' in window) {
        new ResizeObserver(() => {
            if (followingLatest && !editing()) scrollToLatest();
        }).observe(box);
    }
    window.addEventListener('load', () => {
        if (followingLatest && !editing()) scrollToLatest();
    }, { once: true });
    cycle();
})();
