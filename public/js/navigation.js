(() => {
    const toggle = document.getElementById('userMenuToggle');
    const menu = document.getElementById('userMenuPanel');
    const container = toggle?.closest('.user-menu');
    if (!toggle || !menu || !container) return;

    const hover = window.matchMedia('(hover: hover) and (pointer: fine)');
    let openTimer;
    let closeTimer;

    function cancelTimers() {
        window.clearTimeout(openTimer);
        window.clearTimeout(closeTimer);
    }

    function positionMenu() {
        const viewportWidth = document.documentElement.clientWidth;
        const bounds = container.getBoundingClientRect();
        const width = Math.min(380, viewportWidth - 24);
        const left = Math.min(Math.max(12, bounds.right - width - 8), viewportWidth - width - 12);
        menu.style.width = `${width}px`;
        menu.style.right = `${bounds.right - left - width}px`;
        menu.style.maxHeight = `${Math.max(0, window.innerHeight - bounds.bottom - 20)}px`;
    }

    function setOpen(open) {
        cancelTimers();
        if (open) positionMenu();
        menu.classList.toggle('show', open);
        toggle.setAttribute('aria-expanded', String(open));
    }

    container.addEventListener('mouseenter', () => {
        if (!hover.matches) return;
        cancelTimers();
        if (!menu.classList.contains('show')) {
            openTimer = window.setTimeout(() => setOpen(true), 120);
        }
    });
    container.addEventListener('mouseleave', () => {
        if (!hover.matches) return;
        cancelTimers();
        if (menu.contains(document.activeElement)) return;
        closeTimer = window.setTimeout(() => setOpen(false), 180);
    });
    menu.addEventListener('mouseenter', cancelTimers);
    menu.addEventListener('mouseleave', () => {
        if (!hover.matches || menu.contains(document.activeElement)) return;
        closeTimer = window.setTimeout(() => setOpen(false), 180);
    });
    container.addEventListener('focusout', event => {
        if (!container.contains(event.relatedTarget)) setOpen(false);
    });
    toggle.addEventListener('click', () => setOpen(!menu.classList.contains('show')));
    toggle.addEventListener('keydown', event => {
        if (event.key !== 'ArrowDown') return;
        event.preventDefault();
        setOpen(true);
        menu.querySelector('a[href], button:not(:disabled)')?.focus();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || !menu.classList.contains('show')) return;
        const restoreFocus = container.contains(document.activeElement);
        setOpen(false);
        if (restoreFocus) toggle.focus();
    });
    document.addEventListener('click', event => {
        if (!container.contains(event.target)) setOpen(false);
    });
    window.addEventListener('resize', () => {
        if (menu.classList.contains('show')) positionMenu();
    });
})();
