import { createApp, h, ref } from 'vue';
import { jsonRequest, stateIsland, submitForm } from './progressive-ui';
const subscription = document.querySelector('[data-library-subscription]');
if (subscription) {
    const state = stateIsland(subscription);
    function notify(icon, title, text) {
        if (window.Swal?.fire) {
            window.Swal.fire({ icon, title, text, confirmButtonText: 'OK' });
        } else if (icon === 'success') state.message.value = text;
        else state.error.value = text;
    }
    subscription.addEventListener('submit', async event => {
        if (!event.target.matches('[data-library-action]')) return;
        event.preventDefault();
        const removing = new URL(event.target.action).pathname.endsWith('/unsubscribe');
        let saved = false;
        try {
            const data = await submitForm(event.target, state);
            if (!data) return;
            saved = true;
            const updated = await jsonRequest(location.href, { headers: { 'X-Library-Detail': 'subscription' } });
            const row = subscription.querySelector('.container');
            const template = document.createElement('template'); template.innerHTML = updated.html;
            row.replaceWith(template.content);
            notify('success', removing ? 'Unsubscribed' : 'Subscribed', data.message || 'Subscription updated.');
        } catch (error) {
            notify(saved ? 'warning' : 'error', saved ? 'Subscription updated' : 'Could not update subscription',
                saved ? 'Your subscription was saved, but the controls could not refresh. Reload the page to see the update.' : error.message);
        }
    });
}
const seasonCards = document.querySelectorAll('[data-season-url]');
if (seasonCards.length) {
    const parent = document.getElementById('seasonModalLoading');
    parent.replaceChildren();
    const state = stateIsland(parent);
    const cache = new Map();
    let pending;
    document.addEventListener('click', async event => {
        const card = event.target.closest('[data-season-url]');
        if (!card) return;
        event.preventDefault();
        pending?.abort(); const controller = new AbortController(); pending = controller;
        const url = card.dataset.seasonUrl;
        parent.classList.remove('d-none'); document.getElementById('seasonModalContent').classList.add('d-none');
        window.bootstrap.Modal.getOrCreateInstance(document.getElementById('seasonModal')).show();
        state.loading(true); state.error.value = '';
        try {
            const data = cache.get(url) || await jsonRequest(url, { signal: controller.signal });
            if (controller.signal.aborted) return;
            cache.set(url, data); document.dispatchEvent(new CustomEvent('library:season', { detail: data }));
        } catch (error) { if (error.name !== 'AbortError') state.error.value = 'Could not load this season. Select it again to retry.'; }
        finally { if (pending === controller) state.loading(false); }
    });
    document.getElementById('seasonModal').addEventListener('hidden.bs.modal', () => { pending?.abort(); state.loading(false); });
}
// Filtering leaves downloads, resolution accordions and the video player intact.
const rows = [...document.querySelectorAll('.torrent-item')];
if (rows.length) {
    const host = document.createElement('div'); rows[0].closest('.res-panels')?.before(host);
    if (host.isConnected) createApp({ setup() {
        const query = ref('');
        return () => h('label', { class: 'd-block mb-3' }, ['Filter available torrents', h('input', {
            type: 'search', class: 'form-control mt-1', placeholder: 'Title, episode or release…', value: query.value,
            onInput(event) { query.value = event.target.value; const term = query.value.trim().toLowerCase(); rows.forEach(row => { const match = row.textContent.toLowerCase().includes(term); row.hidden = !match; row.style.display = match ? '' : 'none'; }); },
        })]);
    } }).mount(host);
}
const recommendations = document.querySelector('[data-library-recommendations]');
if (recommendations) {
    const state = stateIsland(recommendations);
    const content = document.createElement('div');
    recommendations.querySelector('p')?.replaceWith(content);
    let loaded = false;
    async function load() {
        if (loaded || state.busy.value) return;
        state.loading(true); state.error.value = '';
        try {
            const data = await jsonRequest(location.href, { headers: { 'X-Library-Detail': 'recommendations' } });
            if (typeof data.html !== 'string') throw new Error('Could not load recommendations.');
            content.innerHTML = data.html || 'No recommendations available.'; loaded = true;
            retry.remove();
        } catch (error) { state.error.value = error.message; retry.hidden = false; }
        finally { state.loading(false); }
    }
    const retry = document.createElement('button'); retry.type = 'button'; retry.className = 'btn btn-outline-secondary btn-sm'; retry.textContent = 'Load recommendations'; retry.hidden = true;
    retry.addEventListener('click', load); recommendations.append(retry);
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => { if (entries.some(entry => entry.isIntersecting)) { observer.disconnect(); load(); } }, { rootMargin: '300px' });
        observer.observe(recommendations); window.addEventListener('pagehide', event => { if (!event.persisted) observer.disconnect(); });
    } else load();
}
