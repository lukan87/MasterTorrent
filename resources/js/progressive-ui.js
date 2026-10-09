import { createApp, h, ref } from 'vue';
import { setTorrentLoading } from './torrent-loading';

// Mount only a small status island. Existing editors, file inputs and players keep their DOM.
export function stateIsland(parent) {
    const host = document.createElement('div');
    if (parent.matches('[data-messenger]')) parent.before(host);
    else parent.prepend(host);
    const busy = ref(false), error = ref(''), message = ref(''), progress = ref(null);
    const source = Symbol('progressive-page');
    const app = createApp({ setup: () => () => h('div', {
        role: error.value ? 'alert' : 'status', 'aria-live': 'polite',
        class: error.value ? 'alert alert-warning py-2' : message.value ? 'alert alert-info py-2' : busy.value ? 'small py-2' : 'visually-hidden',
    }, error.value || message.value || (busy.value ? [h('span', { class: 'spinner-border spinner-border-sm me-2', 'aria-hidden': 'true' }), progress.value === null ? 'Updating…' : `Uploading ${progress.value}%…`] : 'Ready')) });
    app.mount(host);
    function loading(value) { busy.value = value; setTorrentLoading(source, value); }
    window.addEventListener('pagehide', event => { loading(false); if (!event.persisted) app.unmount(); });
    return { busy, error, message, progress, loading };
}

export async function jsonRequest(url, options = {}) {
    const target = new URL(url, location.origin);
    if (target.origin !== location.origin) throw new Error('Please reload this page before continuing.');
    const response = await fetch(target, {
        credentials: 'same-origin', cache: 'no-store', ...options,
        headers: { Accept: 'application/json', ...options.headers },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || response.redirected) {
        const error = new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Could not update this page. Please try again.');
        error.status = response.status;
        throw error;
    }
    return data;
}

export function draft(form, field) {
    if (!form || !field) return { saved() {}, remember() {}, dispose() {} };
    const user = form.closest('[data-draft-user]')?.dataset.draftUser;
    const key = `fileiplay:draft:${user}:${form.action}:${field.name}`;
    try { if (!field.value) field.value = sessionStorage.getItem(key) || ''; } catch (_) { /* Storage may be disabled. */ }
    const remember = () => { try { if (field.value) sessionStorage.setItem(key, field.value); else sessionStorage.removeItem(key); } catch (_) {} };
    field.addEventListener('input', remember);
    window.addEventListener('pagehide', remember);
    return { remember, dispose() { field.removeEventListener('input', remember); window.removeEventListener('pagehide', remember); }, saved(value) { if (field.value === value) { field.value = ''; remember(); } } };
}

export async function submitForm(form, state) {
    if (state.busy.value || !form.reportValidity()) return null;
    const body = new FormData(form);
    const buttons = [...form.querySelectorAll('button[type="submit"], input[type="submit"]')].filter(button => !button.disabled);
    state.error.value = ''; state.message.value = ''; state.loading(true);
    buttons.forEach(button => { button.disabled = true; });
    try { return await jsonRequest(form.action, { method: 'POST', body }); }
    finally { buttons.forEach(button => { button.disabled = false; }); state.loading(false); }
}
