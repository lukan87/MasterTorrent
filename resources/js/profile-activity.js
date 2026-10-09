import { jsonRequest, stateIsland } from './progressive-ui';
const root = document.querySelector('[data-profile-activity]');
if (root) {
    const state = stateIsland(root), content = root.querySelector('[data-activity-content]');
    const cache = new Map();
    let pending, currentUrl;
    async function load(url) {
        pending?.abort(); const controller = new AbortController(); pending = controller;
        state.loading(true); state.error.value = '';
        try {
            const data = cache.get(url) || await jsonRequest(url, { signal: controller.signal, headers: { 'X-Page-Browse': '1' } });
            if (controller.signal.aborted) return;
            if (typeof data.html !== 'string') throw new Error('Could not load activity. Use the activity link to open the full page.');
            if (cache.size >= 12) cache.delete(cache.keys().next().value);
            cache.set(url, data); currentUrl = url;
            content.innerHTML = data.html; content.hidden = false;
            root.querySelectorAll('[data-activity-tab]').forEach(link => {
                const selected = new URL(link.href).pathname === new URL(url).pathname;
                link.classList.toggle('active', selected);
                if (selected) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
            });
        } catch (error) { if (error.name !== 'AbortError') state.error.value = error.message; }
        finally { if (pending === controller) state.loading(false); }
    }
    root.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href);
        if (!link.matches('[data-activity-tab]') && (!currentUrl || url.origin !== location.origin || url.pathname !== new URL(currentUrl).pathname)) return;
        event.preventDefault(); load(link.href);
    });
    window.addEventListener('pagehide', () => pending?.abort(), { once: true });
}
const achievementOverview = document.getElementById('achievements');
if (achievementOverview) {
    const state = stateIsland(achievementOverview.querySelector('.modal-body'));
    const pending = new Map();
    async function loadAchievement(key) {
        if (document.getElementById(`achievementModal-${key}`)) return;
        if (!pending.has(key)) pending.set(key, (async () => {
            state.loading(true); state.error.value = '';
            try {
                const data = await jsonRequest(location.href, { headers: { 'X-Profile-Achievement': key } });
                if (typeof data.html !== 'string') throw new Error('Could not load milestones. Select the achievement again to retry.');
                const template = document.createElement('template'); template.innerHTML = data.html;
                document.body.append(template.content);
            } catch (error) { state.error.value = error.message; throw error; }
            finally { state.loading(false); pending.delete(key); }
        })());
        await pending.get(key);
    }
    document.addEventListener('click', async event => {
        const trigger = event.target.closest('[data-achievement-target]');
        if (!trigger || document.querySelector(trigger.dataset.achievementTarget)) return;
        const key = trigger.dataset.achievementTarget.replace('#achievementModal-', '');
        try { await loadAchievement(key); trigger.click(); } catch (_) { /* The visible status allows retry. */ }
    });
    async function reveal() {
        const match = location.hash.match(/^#achievement-([a-z_]+)-[0-9]+$/);
        if (!match || document.getElementById(location.hash.slice(1))) return;
        try { await loadAchievement(match[1]); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (_) {}
    }
    window.addEventListener('hashchange', reveal);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', reveal, { once: true }); else reveal();
}
