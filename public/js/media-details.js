(() => {
    'use strict';
    function init() {
        document.querySelectorAll('.media-detail-hero .md-hero-backdrop').forEach(backdrop => {
            const hero = backdrop.closest('.media-detail-hero');
            function hideArtwork() {
                hero.classList.remove('md-has-backdrop');
                backdrop.hidden = true;
            }
            backdrop.addEventListener('error', hideArtwork);
            if (backdrop.complete && backdrop.naturalWidth === 0) hideArtwork();

            // Sample a separate CORS image so a blocked palette request cannot hide the artwork.
            const sample = new Image();
            sample.crossOrigin = 'anonymous';
            sample.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 40;
                    canvas.height = 24;
                    const context = canvas.getContext('2d', { willReadFrequently: true });
                    if (!context) return;
                    context.drawImage(sample, 0, 0, canvas.width, canvas.height);
                    const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                    const palette = new Map();
                    for (let i = 0; i < pixels.length; i += 4) {
                        const [r, g, b, alpha] = pixels.subarray(i, i + 4);
                        const lightness = (r + g + b) / 3;
                        if (alpha < 128 || lightness < 16 || lightness > 240) continue;
                        const key = `${r >> 5},${g >> 5},${b >> 5}`;
                        const bucket = palette.get(key) || { count: 0, r: 0, g: 0, b: 0 };
                        bucket.count++;
                        bucket.r += r;
                        bucket.g += g;
                        bucket.b += b;
                        palette.set(key, bucket);
                    }
                    const dominant = [...palette.values()].sort((a, b) => b.count - a.count)[0];
                    if (!dominant) return;
                    const rgb = ['r', 'g', 'b'].map(channel => Math.round(dominant[channel] / dominant.count));
                    hero.style.setProperty('--md-backdrop-color', `rgb(${rgb.join(', ')})`);
                } catch {
                    // Keep the theme's default tint if this image does not permit canvas access.
                }
            };
            sample.onerror = () => {}; // Artwork remains usable if color sampling is unavailable.
            sample.src = backdrop.currentSrc || backdrop.src;
        });
        document.querySelectorAll('[data-media-cast] [data-bs-toggle="tooltip"]').forEach(image => {
            if (window.bootstrap?.Tooltip) bootstrap.Tooltip.getOrCreateInstance(image, { container: 'body', html: false });
        });
        document.querySelectorAll('[data-media-cast]').forEach(section => {
            const track = section.querySelector('.md-cast-track');
            const controls = section.querySelector('.md-cast-controls');
            const buttons = [...section.querySelectorAll('[data-media-cast-step]')];
            function update() {
                const overflow = track.scrollWidth > track.clientWidth + 1;
                controls.hidden = !overflow;
                buttons[0].disabled = track.scrollLeft <= 3;
                buttons[1].disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 3;
            }
            function move(direction) {
                track.scrollBy({ left: direction * track.clientWidth * .8, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
            }
            buttons.forEach(button => button.addEventListener('click', () => move(Number(button.dataset.mediaCastStep))));
            track.addEventListener('scroll', update, { passive: true });
            track.addEventListener('keydown', event => {
                if (event.target !== track) return;
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    move(event.key === 'ArrowRight' ? 1 : -1);
                } else if (event.key === 'Home' || event.key === 'End') {
                    event.preventDefault();
                    track.scrollTo({ left: event.key === 'Home' ? 0 : track.scrollWidth, behavior: 'instant' });
                }
            });
            window.addEventListener('resize', update);
            if ('ResizeObserver' in window) new ResizeObserver(update).observe(track);
            update();
        });
        document.querySelectorAll('.media-detail-page .md-image[data-fallback]').forEach(image => {
            function fallback() {
                const source = image.dataset.fallback;
                if (source) {
                    delete image.dataset.fallback;
                    image.src = source;
                }
            }
            image.addEventListener('error', fallback);
            if (image.complete && image.naturalWidth === 0) fallback();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
