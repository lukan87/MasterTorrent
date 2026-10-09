import { createApp, h, ref } from 'vue';
import { setTorrentLoading } from './torrent-loading';
import { jsonRequest, stateIsland } from './progressive-ui';
import { applyTitleMetadata } from './torrent-form-metadata';
const form = document.querySelector('[data-torrent-form]');
if (form) {
    const state = stateIsland(form);
    let metadataPending = false;
    const poster = form.querySelector('[name="poster"]');
    if (poster) {
        const host = document.createElement('div'); poster.after(host);
        const url = ref(poster.value), failed = ref(false);
        const update = () => { failed.value = false; url.value = poster.value; };
        poster.addEventListener('input', update); poster.addEventListener('change', update);
        createApp({ setup: () => () => {
            let valid = false;
            try { valid = ['http:', 'https:'].includes(new URL(url.value, location.origin).protocol); } catch (_) {}
            return url.value && valid && !failed.value ? h('img', { src: url.value, alt: 'Poster preview', class: 'mt-2 rounded', style: 'max-width:140px;max-height:210px', referrerpolicy: 'no-referrer', onError: () => { failed.value = true; } }) : null;
        } }).mount(host);
    }
    if (form.dataset.metadataUrl) {
        const host = document.createElement('div'); host.className = 'mb-3';
        form.querySelector('[name="imdb_url"]')?.closest('.mb-3, .mb-4')?.before(host);
        if (!host.isConnected) form.prepend(host);
        const imdbInput = form.querySelector('[name="imdb_url"]');
        const initialType = form.dataset.metadataType === 'tv' ? 'tv' : 'movie';
        const query = ref(imdbInput?.value || ''), type = ref(initialType), results = ref([]), searching = ref(false), lookupError = ref('');
        const replaceDescription = ref(false);
        let previousDescription = '';
        imdbInput?.addEventListener('change', () => { if (imdbInput.value.trim()) query.value = imdbInput.value.trim(); });
        const cache = new Map(); let pending;
        const loadingSource = Symbol('title-lookup');
        async function lookup(params) {
            pending?.abort(); const controller = new AbortController(); pending = controller;
            const url = new URL(form.dataset.metadataUrl, location.origin);
            Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
            searching.value = true; metadataPending = true; setTorrentLoading(loadingSource, true); lookupError.value = '';
            try {
                const data = cache.get(url.href) || await jsonRequest(url, { signal: controller.signal });
                if (controller.signal.aborted) return null;
                if (cache.size >= 20) cache.delete(cache.keys().next().value);
                cache.set(url.href, data); return data;
            } finally { if (pending === controller) { searching.value = false; metadataPending = false; setTorrentLoading(loadingSource, false); } }
        }
        async function choose(item) {
            try {
                const data = await lookup({ id: item.id, type: item.type });
                if (!data?.metadata) return;
                previousDescription = applyTitleMetadata(form, data.metadata, {
                    previous: previousDescription, replace: replaceDescription.value,
                });
                type.value = item.type;
                results.value = []; state.error.value = '';
                state.message.value = 'Title information added to the form and description. Review it before saving.';
            } catch (error) { if (error.name !== 'AbortError') lookupError.value = error.message; }
        }
        async function search(value = query.value) {
            value = value.trim() || imdbInput?.value.trim() || '';
            if (value.length < 2) { lookupError.value = 'Enter a title or IMDb link.'; return; }
            query.value = value; results.value = []; state.message.value = '';
            try {
                const data = await lookup({ q: value.trim(), type: type.value });
                if (!data) return;
                results.value = data.results || [];
                if (!results.value.length) lookupError.value = 'No matching titles found.';
                else if (/tt[0-9]+/.test(value) && results.value.length === 1) await choose(results.value[0]);
            } catch (error) { if (error.name !== 'AbortError') lookupError.value = error.message; }
        }
        createApp({ setup: () => () => h('section', { 'aria-label': 'Title lookup' }, [
            h('label', { for: 'torrent-title-search', class: 'form-label' }, 'Title information'),
            h('div', { class: 'd-flex flex-wrap gap-2' }, [
                h('input', { id: 'torrent-title-search', type: 'search', class: 'form-control', style: 'flex:1;min-width:180px', maxlength: 160, value: query.value, placeholder: 'Title or IMDb link', onInput: event => { query.value = event.target.value; }, onKeydown: event => { if (event.key === 'Enter') { event.preventDefault(); search(); } } }),
                h('select', { class: 'form-select w-auto', 'aria-label': 'Title type', value: type.value, onChange: event => { type.value = event.target.value; } }, [h('option', { value: 'movie' }, 'Movie'), h('option', { value: 'tv' }, 'TV series')]),
                h('button', { type: 'button', class: 'btn btn-outline-primary', disabled: searching.value, onClick: () => search() }, searching.value ? 'Fetching…' : 'Fetch title information'),
            ]),
            h('p', { class: 'form-text mt-2 mb-2' }, 'Search by title or IMDb link to fill the poster, genres and description. Existing description text is kept.'),
            h('label', { class: 'form-check d-flex align-items-center gap-2' }, [
                h('input', { type: 'checkbox', class: 'form-check-input m-0', checked: replaceDescription.value, onChange: event => { replaceDescription.value = event.target.checked; } }),
                h('span', { class: 'form-check-label' }, 'Replace existing description instead of adding to it'),
            ]),
            lookupError.value ? h('p', { role: 'alert', class: 'text-warning mt-2' }, lookupError.value) : null,
            h('div', { class: 'd-flex flex-wrap gap-2 mt-2', 'aria-live': 'polite' }, results.value.map(item => h('button', { type: 'button', class: 'btn btn-outline-secondary btn-sm', disabled: searching.value, onClick: () => choose(item) }, `${item.title}${item.year ? ` (${item.year})` : ''}`))),
        ]) }).mount(host);
        window.addEventListener('pagehide', () => { pending?.abort(); setTorrentLoading(loadingSource, false); }, { once: true });
    }
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return; // Preserve the edit form's confirmation.
        event.preventDefault();
        if (metadataPending) { state.error.value = 'Wait for title information to finish loading before saving.'; return; }
        if (state.busy.value || !form.reportValidity()) return;
        state.error.value = ''; state.message.value = ''; state.progress.value = 0; state.loading(true);
        const body = new FormData(form);
        const buttons = [...form.querySelectorAll('[type="submit"]')].filter(button => !button.disabled);
        buttons.forEach(button => { button.disabled = true; });
        const request = new XMLHttpRequest();
        request.open('POST', form.action); request.setRequestHeader('Accept', 'application/json');
        request.upload.onprogress = event => { if (event.lengthComputable) state.progress.value = Math.round(event.loaded / event.total * 100); };
        request.upload.onload = () => { state.progress.value = null; state.message.value = 'Upload received. Processing torrent…'; };
        request.onload = () => {
            let data;
            try { data = JSON.parse(request.responseText); } catch (_) { state.error.value = 'Could not confirm the upload. Your form is retained; check your uploads before retrying.'; return; }
            if (request.status >= 200 && request.status < 300 && data.url) {
                const target = new URL(data.url, location.origin);
                if (target.origin === location.origin) location.assign(target.href);
            } else {
                state.message.value = '';
                state.error.value = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Could not save this torrent. Your form is retained.';
            }
        };
        request.onerror = () => { state.message.value = ''; state.error.value = 'Connection interrupted. Your form is retained; check your uploads before retrying.'; };
        request.onloadend = () => { state.loading(false); state.progress.value = null; buttons.forEach(button => { button.disabled = false; }); };
        request.send(body);
    });
}
