(() => {
    const setup = () => {
        const modalElement = document.getElementById('movieWatchModal');
        const trigger = document.querySelector('[data-movie-watch]');
        const frame = modalElement?.querySelector('[data-movie-watch-frame]');
        if (!modalElement || !trigger || !frame || !window.bootstrap?.Modal) return;

        // Match the series player: keep the modal outside transformed page panels.
        document.body.appendChild(modalElement);
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        const container = modalElement.querySelector('[data-movie-watch-container]');
        const fullscreenButton = modalElement.querySelector('[data-movie-watch-fullscreen]');
        const fullscreenElement = () => document.fullscreenElement || document.webkitFullscreenElement;
        const isFullscreen = () => modalElement.classList.contains('movie-watch-expanded') ||
            Boolean(fullscreenElement() && modalElement.contains(fullscreenElement()));
        const updateFullscreenButton = () => {
            const active = isFullscreen();
            const label = active ? 'Exit fullscreen' : 'Enter fullscreen';
            fullscreenButton?.setAttribute('aria-pressed', String(active));
            fullscreenButton?.setAttribute('aria-label', label);
            fullscreenButton?.setAttribute('title', label);
        };
        const expand = () => {
            modalElement.classList.add('movie-watch-expanded');
            updateFullscreenButton();
        };
        const exitFullscreen = () => {
            modalElement.classList.remove('movie-watch-expanded');
            const exit = document.exitFullscreen || document.webkitExitFullscreen;
            if (fullscreenElement() && modalElement.contains(fullscreenElement()) && exit) {
                Promise.resolve(exit.call(document)).catch(() => {}).finally(updateFullscreenButton);
            } else {
                updateFullscreenButton();
            }
        };

        fullscreenButton?.addEventListener('click', () => {
            if (isFullscreen()) {
                exitFullscreen();
                return;
            }
            const request = container?.requestFullscreen || container?.webkitRequestFullscreen;
            if (!request) {
                expand();
                return;
            }
            try {
                Promise.resolve(request.call(container)).then(updateFullscreenButton).catch(expand);
            } catch (error) {
                expand();
            }
        });
        document.addEventListener('fullscreenchange', updateFullscreenButton);
        document.addEventListener('webkitfullscreenchange', updateFullscreenButton);

        trigger.addEventListener('click', (event) => {
            // Preserve normal new-tab and modifier-key link behavior.
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
            event.preventDefault();
            frame.src = trigger.href;
            modal.show();
        });

        modalElement.addEventListener('hide.bs.modal', () => {
            exitFullscreen();
            // Unload the iframe immediately so closing the modal stops playback.
            frame.src = 'about:blank';
        });
        modalElement.addEventListener('hidden.bs.modal', () => trigger.focus());
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup, { once: true });
    } else {
        setup();
    }
})();
