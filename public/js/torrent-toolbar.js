(() => {
    function initializeToolbar() {
        const toolbar = document.querySelector('[data-torrent-sticky-toolbar]');
        const showbar = document.querySelector('[data-torrent-showbar]') || document.querySelector('.modern-showbar');
        const navbar = document.querySelector('.app-header');
        const main = document.querySelector('.app-main');
        if (!toolbar || !showbar || !navbar || !main || toolbar.dataset.initialized) return;
        toolbar.dataset.initialized = 'true';

        // Escape glass panels and all nested scrolling/stacking contexts.
        document.body.appendChild(toolbar);
        let scheduled = false;
        let intersection = null;
        let observer = null;
        let observedTop = -1;

        function schedule() {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame(update);
        }

        function observeVisibility(top) {
            if (!('IntersectionObserver' in window) || observedTop === top) return;
            observedTop = top;
            intersection = null;
            observer?.disconnect();
            observer = new IntersectionObserver(entries => {
                intersection = entries[0].isIntersecting;
                schedule();
            }, { root: null, rootMargin: `-${top}px 0px 0px 0px`, threshold: 0 });
            observer.observe(showbar);
        }

        function update() {
            scheduled = false;
            const navbarBottom = Math.max(0, Math.min(window.innerHeight, Math.round(navbar.getBoundingClientRect().bottom)));
            const bounds = main.getBoundingClientRect();
            const left = Math.max(0, bounds.left);
            const right = Math.min(document.documentElement.clientWidth, bounds.right);
            const showbarBounds = showbar.getBoundingClientRect();
            observeVisibility(navbarBottom);

            // A showbar below the viewport must not activate the toolbar.
            const passedShowbar = showbarBounds.bottom <= navbarBottom
                || (intersection === false && showbarBounds.top < navbarBottom);
            const visible = passedShowbar && right > left;
            toolbar.style.top = `${navbarBottom}px`;
            toolbar.style.left = `${left}px`;
            toolbar.style.width = `${Math.max(0, right - left)}px`;
            showbar.style.scrollMarginTop = `${navbarBottom + 16}px`;
            if (!visible && !toolbar.hidden) {
                const toggle = toolbar.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle) window.bootstrap?.Dropdown?.getInstance(toggle)?.hide();
                if (toolbar.contains(document.activeElement)) document.activeElement.blur();
            }
            toolbar.hidden = !visible;
        }

        // Capture scrolls from any ancestor, including AdminLTE's inner viewport.
        document.addEventListener('scroll', schedule, { capture: true, passive: true });
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule);
        window.addEventListener('pageshow', schedule);
        window.addEventListener('load', schedule, { once: true });
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(schedule);
            [navbar, main, showbar].forEach(element => resizeObserver.observe(element));
        }
        toolbar.querySelector('[data-torrent-return]')?.addEventListener('click', () => {
            showbar.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'start',
            });
        });
        update();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeToolbar, { once: true });
    } else {
        initializeToolbar();
    }
})();
