(() => {
    'use strict';
    const root = document.getElementById('community-chat');
    const form = document.getElementById('shoutbox-form');
    if (!root || !form || root.dataset.initialized) return;
    root.dataset.initialized = 'true';
    const box = document.getElementById('shoutbox-messages');
    const container = document.getElementById('shoutbox-container');
    const pinned = document.getElementById('shoutbox-pinned');
    let viewingMention = Boolean(root.dataset.highlightShout);
    let followingLatest = !viewingMention;
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
    let loadingOlder = false;
    let historyLoaded = false;
    let hasOlder = true;
    let latestIds = new Set();
    const olderButton = document.getElementById('chat-load-older');
    const historyStatus = document.getElementById('chat-history-status');

    const actionDialog = document.createElement('dialog');
    actionDialog.className = 'chat-action-dialog';
    actionDialog.setAttribute('aria-labelledby', 'chat-action-title');
    actionDialog.innerHTML = `<header><div><span>Community chat</span><h2 id="chat-action-title"></h2></div><button type="button" data-action-close aria-label="Close dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="chat-action-context"></div><div class="chat-action-body"></div><p class="chat-action-feedback" role="status" aria-live="polite"></p>`;
    root.append(actionDialog);
    const actionBody = actionDialog.querySelector('.chat-action-body');
    const actionFeedback = actionDialog.querySelector('.chat-action-feedback');
    let actionEditor;
    let actionPlaceholder;
    let actionOpener;
    let actionParentId;
    function restoreEditor() {
        if (actionEditor && actionPlaceholder?.isConnected) {
            actionEditor.style.display = 'none';
            actionPlaceholder.replaceWith(actionEditor);
        }
        actionEditor = null;
        actionPlaceholder = null;
        actionBody.replaceChildren();
    }
    function closeAction() {
        if (mutation) return;
        restoreEditor();
        actionDialog.close();
    }
    actionDialog.querySelector('[data-action-close]').addEventListener('click', closeAction);
    actionDialog.addEventListener('cancel', event => {
        if (mutation) event.preventDefault();
    });
    actionDialog.addEventListener('close', () => {
        // A queued close event must not clear a dialog reopened in the meantime.
        if (actionDialog.open) return;
        restoreEditor();
        if (actionOpener?.isConnected) actionOpener.focus({ preventScroll: true });
    });
    actionDialog.addEventListener('click', event => {
        if (event.target.closest('[data-action-cancel]')) closeAction();
        if (event.target !== actionDialog) return;
        const rect = actionDialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) closeAction();
    });
    function openAction(title, source, isDelete = false) {
        if (mutation || loadingOlder || actionDialog.open || !source) return;
        restoreEditor();
        actionOpener = document.activeElement;
        const message = source.closest('.reply-card, .message');
        actionParentId = message?.closest('.message')?.dataset.id;
        const content = message?.querySelector('.reply-content, .content');
        actionDialog.querySelector('h2').textContent = title;
        const context = actionDialog.querySelector('.chat-action-context');
        const preview = content?.cloneNode(true);
        preview?.querySelectorAll('.chat-youtube').forEach(node => node.replaceWith(' [YouTube video] '));
        preview?.querySelectorAll('.chat-embedded-image').forEach(node => node.replaceWith(' [Image] '));
        preview?.querySelectorAll('.chat-spoiler').forEach(node => node.replaceWith(' [Spoiler] '));
        context.textContent = preview?.textContent.trim().slice(0, 240) || '';
        context.hidden = !context.textContent;
        actionFeedback.textContent = '';
        actionFeedback.classList.remove('text-danger');
        if (isDelete) {
            const confirmation = source.cloneNode(true);
            confirmation.removeAttribute('id');
            confirmation.className = 'chat-delete-confirmation';
            confirmation.dataset.confirmDelete = 'true';
            // Preserve the original route, CSRF token and DELETE method override.
            confirmation.querySelectorAll('button').forEach(button => button.remove());
            const explanation = document.createElement('p');
            explanation.textContent = 'Delete this message? This cannot be undone.';
            const actions = document.createElement('div');
            actions.className = 'chat-action-buttons';
            actions.innerHTML = '<button type="button" data-action-cancel>Cancel</button><button type="submit" class="chat-action-delete">Delete message</button>';
            confirmation.append(explanation, actions);
            actionBody.append(confirmation);
        } else {
            actionEditor = source;
            actionPlaceholder = document.createComment('Chat editor location');
            source.replaceWith(actionPlaceholder);
            source.style.display = 'block';
            actionBody.append(source);
            source.querySelector('textarea')?.setAttribute('aria-label', title);
        }
        actionDialog.showModal();
        if (isDelete) actionBody.querySelector('[data-action-cancel]').focus();
        else source.querySelector('textarea')?.focus();
    }

    function notice(text, error = false) {
        feedback.textContent = text;
        feedback.classList.toggle('text-danger', error);
        if (actionDialog.open) {
            actionFeedback.textContent = text;
            actionFeedback.classList.toggle('text-danger', error);
        }
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
        return actionDialog.open || [...root.querySelectorAll('.edit-form, .reply-form')].some(el => el.style.display === 'block');
    }

    function nearBottom() {
        return container.scrollHeight - container.scrollTop - container.clientHeight < 150;
    }

    function scrollToLatest() {
        viewingMention = false;
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
        const incoming = document.createElement('template');
        incoming.innerHTML = snapshot.html;
        const recent = [...incoming.content.querySelectorAll('.message')];
        latestIds = new Set(recent.map(node => node.dataset.id));
        const pins = document.createElement('template');
        pins.innerHTML = snapshot.pinned_html || '';
        const pinnedIds = new Set([...pins.content.querySelectorAll('.message')].map(node => node.dataset.id));
        const first = recent[0];
        const historical = historyLoaded ? [...box.children].filter(node => node.matches('.message') &&
            !latestIds.has(node.dataset.id) && !pinnedIds.has(node.dataset.id) && first &&
            (node.dataset.created < first.dataset.created ||
                (node.dataset.created === first.dataset.created && Number(node.dataset.id) < Number(first.dataset.id)))) : [];
        box.replaceChildren(...historical, incoming.content);
        pinned.innerHTML = snapshot.pinned_html || '';
        lastRevision = snapshot.revision;
        pending = null;
        applyFilter();
        scrollToLatest();
    }

    async function refresh(force = false) {
        if (polling || loadingOlder || stopped || document.hidden || (!force && mutation)) return;
        polling = true;
        const requestGeneration = generation;
        try {
            const data = await request(root.dataset.pollUrl + '?snapshot=1');
            if (requestGeneration !== generation) return;
            if (typeof data.html !== 'string') throw new Error('Unable to refresh chat.');
            // Never replace a reply or edit being composed, even if it opened during this request.
            if (loadingOlder || editing() || (!force && (viewingMention || !nearBottom() || mutation))) {
                if (data.revision !== lastRevision) pending = data;
            } else {
                render(data);
            }
            status.textContent = 'Live · updates every 5s';
            showJump();
        } catch (error) {
            if (!stopped) status.textContent = 'Reconnecting…';
            if (force) notice(error.message, true);
        } finally { polling = false; }
    }

    function restoreAnchor(anchor, top) {
        if (anchor?.isConnected) container.scrollTop += anchor.getBoundingClientRect().top - top;
    }

    olderButton?.addEventListener('click', async () => {
        if (loadingOlder || !hasOlder || mutation || stopped) return;
        if (editing()) { notice('Finish or cancel your reply or edit before loading older messages.'); return; }
        loadingOlder = true;
        followingLatest = false;
        generation++;
        olderButton.disabled = true;
        historyStatus.textContent = 'Loading older messages…';
        const first = box.querySelector(':scope > .message');
        const top = first?.getBoundingClientRect().top;
        const params = new URLSearchParams();
        if (first) {
            params.set('before', first.dataset.id);
            params.set('before_time', first.dataset.created);
        }
        try {
            const data = await request(root.dataset.olderUrl + '?' + params);
            if (typeof data.html !== 'string' || typeof data.has_more !== 'boolean') throw new Error('Unable to load older messages.');
            const fragment = document.createElement('template');
            fragment.innerHTML = data.html;
            const known = new Set([...root.querySelectorAll('.message')].map(node => node.dataset.id));
            const nodes = [...fragment.content.querySelectorAll('.message')].filter(node => !known.has(node.dataset.id));
            box.querySelector('.shoutbox-empty')?.remove();
            box.prepend(...nodes);
            historyLoaded = true;
            hasOlder = data.has_more;
            applyFilter();
            restoreAnchor(first, top);
            olderButton.hidden = !hasOlder;
            historyStatus.textContent = hasOlder ? `${nodes.length} older messages loaded.` : 'You’ve reached the oldest message.';
        } catch (error) {
            historyStatus.textContent = error.message;
        } finally {
            loadingOlder = false;
            olderButton.disabled = false;
            showJump();
        }
    });

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
        if (loadingOlder) { notice('Wait for older messages to finish loading.'); return; }
        const text = target.querySelector('textarea');
        if (text && !text.value.trim()) return;
        const original = text?.value;
        const parentId = actionDialog.contains(target) ? actionParentId : target.closest('.message')?.dataset.id;
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
            let historyRefreshFailed = false;
            if (historyLoaded && parentId && !latestIds.has(parentId)) {
                try {
                    const oldThread = document.getElementById('shout-' + parentId);
                    const top = oldThread?.getBoundingClientRect().top;
                    const data = await request(root.dataset.pollUrl + '?thread=' + parentId);
                    if (typeof data.html !== 'string') throw new Error('Unable to refresh this conversation.');
                    const replacement = document.createElement('template');
                    replacement.innerHTML = data.html;
                    oldThread?.replaceWith(replacement.content);
                    restoreAnchor(document.getElementById('shout-' + parentId), top);
                } catch (_) { historyRefreshFailed = true; }
            }
            notice(historyRefreshFailed ? 'Saved. Refresh the page to see the updated conversation.' : (target.dataset.confirmDelete ? 'Message deleted.' : 'Saved.'), historyRefreshFailed);
            if (actionDialog.contains(target)) {
                restoreEditor();
                actionDialog.close();
            }
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
        if (target.matches('.shoutbox-delete-form, .reply-delete-form')) {
            openAction('Delete message', target, true);
            return;
        }
        submit(target);
    });
    window.handleReplyDelete = (event, target) => {
        event.preventDefault();
        target.requestSubmit();
    };

    function toggleEditor(id, reply, open) {
        if (!open) { closeAction(); return; }
        const editor = document.getElementById((reply ? 'reply-edit-form-' : 'edit-form-') + id);
        openAction(reply ? 'Edit reply' : 'Edit message', editor);
    }
    window.openEdit = id => toggleEditor(id, false, true);
    window.closeEdit = id => toggleEditor(id, false, false);
    window.openReplyEdit = id => toggleEditor(id, true, true);
    window.closeReplyEdit = id => toggleEditor(id, true, false);
    window.toggleReplyForm = id => {
        const reply = document.getElementById('reply-form-' + id);
        if (actionDialog.open && actionEditor === reply) closeAction();
        else openAction('Reply to message', reply);
    };
    document.getElementById('chat-expand').addEventListener('click', event => {
        const expanded = root.classList.toggle('chat-expanded');
        event.currentTarget.setAttribute('aria-pressed', String(expanded));
        event.currentTarget.textContent = expanded ? 'Collapse' : 'Expand';
    });
    search.addEventListener('input', applyFilter);
    filter.addEventListener('change', applyFilter);
    container.addEventListener('scroll', () => {
        followingLatest = !loadingOlder && !viewingMention && nearBottom();
        showJump();
    });
    jump.addEventListener('click', () => {
        if (loadingOlder) return;
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
        timer = setTimeout(cycle, 5000);
    }
    document.addEventListener('visibilitychange', () => {
        clearTimeout(timer);
        if (!document.hidden) cycle();
    });
    window.addEventListener('online', () => { if (!stopped) cycle(); });
    window.addEventListener('offline', () => { status.textContent = 'Offline · draft kept'; });
    saveDraft();
    applyFilter();
    if (viewingMention) {
        const shout = document.getElementById('shout-' + root.dataset.highlightShout);
        if (shout) {
            shout.scrollIntoView({ block: 'center', behavior: 'instant' });
            shout.classList.add('shout-mention-flash');
            setTimeout(() => shout.classList.remove('shout-mention-flash'), 2000);
            // Keep the target in view when avatars and embedded images finish loading.
            window.addEventListener('load', () => {
                if (viewingMention) shout.scrollIntoView({ block: 'center', behavior: 'instant' });
            }, { once: true });
        } else {
            scrollToLatest();
        }
        showJump();
    } else {
        scrollToLatest();
    }
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
