import { draft, jsonRequest, stateIsland, submitForm } from './progressive-ui';
const root = document.querySelector('[data-messenger]');
if (root) {
    const state = stateIsland(root);
    let chat, form, field, savedDraft;
    let refreshing = false, stopped = false, sending = false, sequence = 0;
    let navigation, readPending;
    const indexUrl = new URL(root.dataset.indexUrl || '/messages', location.origin);
    function initialize() {
        chat = document.getElementById('chatBody');
        form = document.getElementById('chatForm');
        field = form?.querySelector('[name="body"]');
        savedDraft = draft(form, field);
        field?.dispatchEvent(new Event('input', { bubbles: true }));
        if (chat) {
            const current = chat;
            const bottom = () => { if (chat === current) current.scrollTop = current.scrollHeight; };
            bottom();
            current.querySelectorAll('img').forEach(image => { if (!image.complete) image.addEventListener('load', bottom, { once: true }); });
        }
        document.dispatchEvent(new CustomEvent('messenger:updated'));
    }
    function updateSidebar(data) {
        updateCount(data);
        if (typeof data.sidebar === 'string') {
            root.querySelector('[data-conversation-results]').innerHTML = data.sidebar;
            document.getElementById('conversationSearch')?.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    async function navigate(url, push = true) {
        if (sending) return;
        savedDraft?.remember();
        navigation?.abort(); readPending?.abort(); refreshing = false;
        const id = ++sequence;
        const controller = new AbortController(); navigation = controller;
        state.loading(true); state.error.value = ''; state.message.value = '';
        try {
            const target = new URL(url, location.origin);
            const data = await jsonRequest(target, { signal: controller.signal, headers: { 'X-Messenger-Pane': '1' } });
            if (id !== sequence || controller.signal.aborted) return;
            if (typeof data.pane !== 'string') throw new Error('Could not open this conversation. Please try again.');
            savedDraft?.dispose();
            const dialog = document.getElementById('messageDialog'); if (dialog?.open) dialog.close();
            root.querySelector('.messenger-chat').innerHTML = data.pane;
            root.classList.toggle('has-active-chat', !!root.querySelector('#chatBody'));
            updateSidebar(data);
            if (push && target.href !== location.href) history.pushState(null, '', target);
            stopped = false; initialize();
        } catch (error) {
            if (error.name !== 'AbortError' && id === sequence) {
                if (!push) { location.assign(url); return; }
                state.error.value = error.message;
            }
        } finally { if (id === sequence) state.loading(false); }
    }
    const updateCount = data => {
        if (data.unreadCount === undefined) return;
        document.querySelectorAll('[data-message-count]').forEach(badge => {
            badge.textContent = data.unreadCount;
            badge.classList.toggle('bg-danger', data.unreadCount > 0); badge.classList.toggle('bg-success', data.unreadCount === 0);
        });
    };
    async function refresh(url = location.href, older = false, background = false) {
        if (!chat || refreshing || state.busy.value) return;
        refreshing = true;
        const id = sequence; const currentChat = chat;
        const controller = new AbortController(); readPending = controller;
        if (!background) state.loading(true);
        try {
            const target = new URL(url, location.origin);
            if (!older) {
                target.searchParams.delete('before');
                const ids = [...chat.querySelectorAll('[data-id]')].map(node => Number(node.dataset.id));
                if (ids.length) target.searchParams.set('after', Math.max(...ids));
            }
            const data = await jsonRequest(target, { signal: controller.signal, headers: { 'X-Messenger-Thread': '1' } });
            if (id !== sequence || currentChat !== chat) return;
            updateCount(data);
            if (typeof data.html !== 'string') throw new Error('Could not load messages.');
            const template = document.createElement('template'); template.innerHTML = data.html;
            const ids = new Set([...chat.querySelectorAll('[data-id]')].map(node => node.dataset.id));
            const fragment = document.createDocumentFragment();
            let divider;
            for (const node of [...template.content.children]) {
                if (node.matches('.ms-day-divider')) { divider = node; continue; }
                const bubble = node.querySelector('[data-id]');
                if (!bubble || ids.has(bubble.dataset.id)) continue;
                if (divider) { fragment.append(divider); divider = null; }
                fragment.append(node); ids.add(bubble.dataset.id);
            }
            const height = chat.scrollHeight, scroll = chat.scrollTop;
            const atBottom = height - scroll - chat.clientHeight < 100;
            if (older) {
                chat.querySelector('.ms-load-older')?.remove();
                const nextOlder = template.content.querySelector('.ms-load-older');
                chat.prepend(fragment);
                if (nextOlder) chat.prepend(nextOlder);
                chat.scrollTop = scroll + chat.scrollHeight - height;
            } else {
                chat.append(fragment);
                if (atBottom) chat.scrollTop = chat.scrollHeight;
            }
            updateSidebar(data);
        } catch (error) {
            if (id === sequence && error.name !== 'AbortError') {
                if ([401, 403, 404].includes(error.status)) stopped = true;
                if (!background) state.error.value = error.message;
            }
        } finally { if (id === sequence) { refreshing = false; if (!background) state.loading(false); } }
    }

    async function sidebar(url, background = false) {
        if (state.busy.value || refreshing) return;
        if (!background) state.loading(true);
        refreshing = true;
        const id = sequence; const controller = new AbortController(); readPending = controller;
        try {
            const data = await jsonRequest(url, { signal: controller.signal, headers: { [chat ? 'X-Messenger-Thread' : 'X-Messenger-Sidebar']: '1' } });
            if (id !== sequence) return;
            updateCount(data);
            const results = root.querySelector('[data-conversation-results]');
            if (typeof data.sidebar !== 'string') throw new Error('Could not load conversations.');
            results.innerHTML = data.sidebar;
            document.getElementById('conversationSearch')?.dispatchEvent(new Event('input', { bubbles: true }));
            if (!background) history.replaceState(null, '', url);
        } catch (error) { if (id === sequence && error.name !== 'AbortError' && !background) state.error.value = error.message; }
        finally { if (id === sequence) { refreshing = false; if (!background) state.loading(false); } }
    }
    root.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const conversation = event.target.closest('.ms-conv-item[href], .ms-mobile-back[href]');
        if (conversation && !conversation.target) {
            const url = new URL(conversation.href);
            if (url.origin === indexUrl.origin) { event.preventDefault(); navigate(url); }
            return;
        }
        const smilies = event.target.closest('#smiliesToggle');
        if (smilies) { document.getElementById('smiliesPanel')?.classList.toggle('d-none'); return; }
        const pagination = event.target.closest('.ms-pagination a[href]');
        if (pagination) { event.preventDefault(); sidebar(pagination.href); return; }
        const older = event.target.closest('.ms-older-link');
        if (older) { event.preventDefault(); refresh(older.href, true); }
    });
    root.addEventListener('input', event => {
        if (event.target.id === 'body') { event.target.style.height = 'auto'; event.target.style.height = `${event.target.scrollHeight}px`; }
        if (event.target.id === 'conversationSearch') {
            const query = event.target.value.toLowerCase();
            root.querySelectorAll('.ms-conv-item').forEach(link => { link.style.display = (link.dataset.name || '').includes(query) ? '' : 'none'; });
        }
    });
    root.addEventListener('keydown', event => {
        if (event.target.id === 'body' && event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault(); if (event.target.value.trim()) event.target.form.requestSubmit();
        }
    });
    root.addEventListener('submit', async event => {
        if (event.target.id !== 'chatForm') return;
        event.preventDefault();
        if (sending) return;
        const submitted = form, value = field.value, submittedDraft = savedDraft;
        sending = true;
        try {
            const data = await submitForm(submitted, state);
            if (!data) return;
            submittedDraft.saved(value); field.dispatchEvent(new Event('input', { bubbles: true }));
            state.message.value = data.message;
            if (new URL(data.url, location.origin).pathname !== location.pathname) {
                sending = false; await navigate(data.url);
            } else await refresh(data.url);
        } catch (error) { state.error.value = error.message; }
        finally { sending = false; }
    });
    window.addEventListener('popstate', () => {
        if (sending) { savedDraft?.remember(); location.assign(location.href); return; }
        navigate(location.href, false);
    });
    initialize();
    const polling = setInterval(() => { if (!document.hidden && !state.busy.value && !stopped && !document.getElementById('messageDialog')?.open) chat ? refresh(location.href, false, true) : sidebar(location.href, true); }, 15000);
    window.addEventListener('pagehide', event => { if (!event.persisted) clearInterval(polling); });
}
