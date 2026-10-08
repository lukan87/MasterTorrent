(() => {
    const storageKey = 'fileiplay.theme';
    const choices = ['light', 'dark', 'system'];
    const root = document.documentElement;
    const system = window.matchMedia('(prefers-color-scheme: dark)');
    const icons = { light: 'bi-sun', dark: 'bi-moon-stars', system: 'bi-circle-half' };
    const labels = { light: 'Light', dark: 'Dark', system: 'System / Auto' };
    let preference = 'dark';

    const normalize = value => choices.includes(value) ? value : 'dark';
    try {
        preference = normalize(window.localStorage.getItem(storageKey));
    } catch (_) {
        // The selector still works when browser storage is unavailable.
    }

    function applyTheme() {
        const theme = preference === 'system' ? (system.matches ? 'dark' : 'light') : preference;
        root.setAttribute('data-bs-theme', theme);
        root.setAttribute('data-theme-preference', preference);
        window.dispatchEvent(new CustomEvent('fileiplay:themechange', { detail: { theme, preference } }));
        document.querySelectorAll('[data-theme-choice]').forEach(button => {
            button.setAttribute('aria-pressed', String(button.dataset.themeChoice === preference));
        });
        const toggle = document.getElementById('themeMenuToggle');
        if (toggle) {
            const label = `Appearance: ${labels[preference]}`;
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', label);
            toggle.querySelector('[data-theme-icon]').className = `bi ${icons[preference]} fs-4`;
        }
    }

    // Runs in the head, before styles render, to avoid flashing the wrong theme.
    applyTheme();
    document.addEventListener('DOMContentLoaded', applyTheme, { once: true });
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-theme-choice]');
        if (!button || !choices.includes(button.dataset.themeChoice)) return;
        preference = button.dataset.themeChoice;
        try {
            window.localStorage.setItem(storageKey, preference);
        } catch (_) {}
        applyTheme();
    });
    system.addEventListener('change', () => {
        if (preference === 'system') applyTheme();
    });
    window.addEventListener('storage', event => {
        if (event.key !== storageKey && event.key !== null) return;
        preference = normalize(event.newValue);
        applyTheme();
    });
})();
