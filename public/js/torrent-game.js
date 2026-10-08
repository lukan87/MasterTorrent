(() => {
    let scrollController;
    function init() {
        const root = document.querySelector('.game-page');
        if (!root || root.dataset.gameInitialized) return;
        root.dataset.gameInitialized = '1';
        scrollController?.abort();
        scrollController = new AbortController();
        /* Sticky header */
        window.addEventListener(
            'scroll',
            () => {
                document
                    .querySelector('.game-mini-header')
                    ?.classList.toggle('visible', window.scrollY > 300);
            },
            { signal: scrollController.signal },
        );

        /* Screenshot modal */
        document.querySelectorAll('.screenshot-img').forEach((img) => {
            img.addEventListener('click', () => {
                const modalImg = document.getElementById('screenshotModalImg');

                modalImg.src = img.dataset.full;

                const modal = new bootstrap.Modal(
                    document.getElementById('screenshotModal'),
                );

                modal.show();
            });
        });

        /* Trailer */
        const trailerModal = document.getElementById('trailerModal');

        const trailerVideo = document.getElementById('trailerVideo');

        if (trailerModal) {
            trailerModal.addEventListener('shown.bs.modal', () => {
                trailerVideo.play()?.catch(() => {});
            });

            trailerModal.addEventListener('hidden.bs.modal', () => {
                trailerVideo.pause();

                trailerVideo.currentTime = 0;
            });
        }
    }
    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', init);
    else init();
    document.addEventListener('torrent:metadata-ready', init);
})();
