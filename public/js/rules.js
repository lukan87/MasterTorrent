(() => {
    const root = document.getElementById('fileiplay-rules');
    if (!root) return;
    const tablist = root.querySelector('.rules-tabs');
    const tabs = [...root.querySelectorAll('[data-rules-tab]')];
    const panels = [...root.querySelectorAll('[data-rules-panel]')];
    tablist.setAttribute('role', 'tablist');
    tabs.forEach(tab => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', tab.dataset.rulesTab);
    });
    panels.forEach(panel => {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', `tab-${panel.id}`);
        panel.tabIndex = 0;
    });
    const select = (tab, updateHash = false) => {
        tabs.forEach(item => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
        });
        panels.forEach(panel => { panel.hidden = panel.id !== tab.dataset.rulesTab; });
        if (updateHash) history.replaceState(null, '', `#${tab.dataset.rulesTab}`);
    };
    const selectHash = () => select(tabs.find(tab => tab.hash === location.hash) || tabs[0]);
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', event => {
            // Preserve normal link behavior for opening a section in another tab.
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            select(tab, true);
        });
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (event.key === ' ') next = index;
            if (next === undefined) return;
            event.preventDefault();
            select(tabs[next], true);
            tabs[next].focus();
        });
    });
    window.addEventListener('hashchange', selectHash);
    selectHash();
})();
