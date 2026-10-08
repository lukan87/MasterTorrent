(() => {
    const cleanup = new Map();
    function init() {
        cleanup.forEach((dispose, slider) => {
            if (!slider.isConnected) {
                dispose();
                cleanup.delete(slider);
            }
        });
        document
            .querySelectorAll('[data-cast-slider]')
            .forEach(function (slider) {
                if (slider.dataset.castInitialized) return;
                slider.dataset.castInitialized = '1';
                const track = slider.querySelector('.media-cast-track');
                const controls = slider.querySelector('.media-cast-controls');
                const previous = slider.querySelector('[data-cast-step="-1"]');
                const next = slider.querySelector('[data-cast-step="1"]');
                function update() {
                    const overflows = track.scrollWidth > track.clientWidth + 1;
                    controls.hidden = !overflows;
                    previous.disabled = !overflows || track.scrollLeft <= 1;
                    next.disabled =
                        !overflows ||
                        track.scrollLeft + track.clientWidth >=
                            track.scrollWidth - 1;
                }
                slider
                    .querySelectorAll('[data-cast-step]')
                    .forEach(function (button) {
                        button.addEventListener('click', function () {
                            const card =
                                track.querySelector('.media-cast-person');
                            if (!card) return;
                            const step =
                                card.getBoundingClientRect().width +
                                parseFloat(getComputedStyle(track).gap);
                            track.scrollBy({
                                left:
                                    Number(button.dataset.castStep) *
                                    Math.max(
                                        1,
                                        Math.floor(track.clientWidth / step),
                                    ) *
                                    step,
                                behavior: window.matchMedia(
                                    '(prefers-reduced-motion: reduce)',
                                ).matches
                                    ? 'instant'
                                    : 'smooth',
                            });
                        });
                    });
                track.addEventListener('scroll', update, { passive: true });
                const controller = new AbortController();
                window.addEventListener('resize', update, {
                    signal: controller.signal,
                });
                const observer =
                    'ResizeObserver' in window
                        ? new ResizeObserver(update)
                        : null;
                observer?.observe(track);
                cleanup.set(slider, () => {
                    controller.abort();
                    observer?.disconnect();
                });
                update();
            });
    }
    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', init);
    else init();
    document.addEventListener('torrent:metadata-ready', init);
})();
