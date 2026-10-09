(() => {
    const setup = () => {
        const element = document.getElementById('libraryPlayer');
        if (!element || !window.bootstrap?.Modal) return;
        document.body.appendChild(element);
        const modal = bootstrap.Modal.getOrCreateInstance(element);
        const frame = element.querySelector('[data-library-frame]');
        let trigger;
        document.addEventListener('click', (event) => {
            const link = event.target.closest('[data-library-play]');
            if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
            const url = new URL(link.href);
            if (url.origin !== 'https://v2.vidsrc.me' || !url.pathname.startsWith('/embed/')) return;
            event.preventDefault();
            trigger = link;
            const open = () => {
                element.querySelector('#libraryPlayerTitle').textContent = link.dataset.playTitle || 'Watch online';
                element.querySelector('[data-library-external]').href = url.href;
                frame.src = url.href;
                modal.show();
            };
            const season = document.getElementById('seasonModal');
            if (season?.classList.contains('show')) {
                season.addEventListener('hidden.bs.modal', open, {once: true});
                bootstrap.Modal.getInstance(season)?.hide();
            } else open();
        });
        element.addEventListener('hide.bs.modal', () => { frame.src = 'about:blank'; });
        element.addEventListener('hidden.bs.modal', () => { trigger?.focus(); });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup, {once: true});
    else setup();
})();
