import { ref } from 'vue';
import { jsonRequest } from './progressive-ui';
for (const root of document.querySelectorAll('[data-home-widget]')) {
    const content = document.createElement('div');
    while (root.firstChild) content.append(root.firstChild);
    root.append(content);
    const tooltipNodes = () => content.querySelectorAll('[data-bs-toggle="tooltip"]');
    const disposeTooltips = () => tooltipNodes().forEach(node => window.bootstrap?.Tooltip.getInstance(node)?.dispose());
    const initTooltips = () => tooltipNodes().forEach(node => window.bootstrap?.Tooltip.getOrCreateInstance(node, { animation: false }));
    disposeTooltips();
    initTooltips();
    const busy = ref(false);
    let stopped = false;
    function sameInput(candidate, original) {
        return candidate.type === original.type && candidate.name === original.name &&
            candidate.value === original.value && candidate.form?.action === original.form?.action;
    }
    function filterMembers() {
        const search = content.querySelector('#online-member-search');
        if (!search) return;
        const term = search.value.trim().toLocaleLowerCase(); let visible = 0;
        content.querySelectorAll('[data-member-name]').forEach(member => {
            const match = member.dataset.memberName.toLocaleLowerCase().includes(term);
            member.hidden = !match; if (match) visible++;
        });
        const empty = content.querySelector('#online-member-empty'); if (empty) empty.hidden = visible > 0;
    }
    async function refresh() {
        if (busy.value || stopped || document.hidden) return;
        busy.value = true;
        try {
            const data = await jsonRequest(root.dataset.widgetUrl, { headers: { 'X-Home-Widget': root.dataset.homeWidget } });
            if (typeof data.html !== 'string') throw new Error('Could not refresh this widget.');
            const open = content.querySelector('details')?.open;
            const expanded = content.querySelector('#ttTrending')?.classList.contains('show');
            // Capture current input after the request, including edits made while it was loading.
            const search = content.querySelector('#online-member-search');
            const active = content.contains(document.activeElement) ? document.activeElement : null;
            const selection = active === search && search ? [search.selectionStart, search.selectionEnd, search.selectionDirection] : null;
            const checked = [...content.querySelectorAll('input:checked')];
            disposeTooltips();
            content.innerHTML = data.html;
            initTooltips();
            const inputs = [...content.querySelectorAll('input')];
            checked.forEach(original => {
                const replacement = inputs.find(input => sameInput(input, original));
                if (replacement) replacement.checked = true;
            });
            const newSearch = content.querySelector('#online-member-search');
            if (search && newSearch) newSearch.value = search.value;
            filterMembers();
            const focus = active === search ? newSearch : active?.matches('input') ? inputs.find(input => sameInput(input, active)) : null;
            if (focus) {
                focus.focus({ preventScroll: true });
                if (selection && selection[0] !== null) focus.setSelectionRange(...selection);
            }
            if (open !== undefined && content.querySelector('details')) content.querySelector('details').open = open;
            if (expanded !== undefined) {
                content.querySelector('#ttTrending')?.classList.toggle('show', expanded);
                content.querySelector('[data-bs-target="#ttTrending"]')?.setAttribute('aria-expanded', String(expanded));
            }
            document.dispatchEvent(new CustomEvent('home:updated', { detail: { root } }));
        } catch (failure) { if ([401, 403].includes(failure.status)) stopped = true; }
        finally { busy.value = false; }
    }
    root.addEventListener('input', event => {
        if (event.target.id !== 'online-member-search') return;
        filterMembers();
    });
    root.addEventListener('shown.bs.collapse', () => { try { localStorage.setItem('trendingAccordionState', 'open'); } catch (_) {} });
    root.addEventListener('hidden.bs.collapse', () => { try { localStorage.setItem('trendingAccordionState', 'closed'); } catch (_) {} });
    const refreshInterval = root.dataset.homeWidget === 'trending' ? 300000 : root.dataset.homeWidget === 'online' ? 5000 : 30000;
    const interval = setInterval(() => refresh(), refreshInterval);
    window.addEventListener('pagehide', event => { if (!event.persisted) { clearInterval(interval); disposeTooltips(); } });
}
