(() => {
    'use strict';
    function init() {
        document.querySelectorAll('[data-torrent-media-hero]').forEach(hero => {
            if (hero.dataset.heroInitialized) return;
            hero.dataset.heroInitialized = '1';
            const wrapper = hero.querySelector('.premium-poster-wrapper');
            const poster = wrapper?.querySelector('.premium-poster');
            const backdrop = hero.querySelector('.premium-backdrop');
            if (backdrop) {
                backdrop.addEventListener('error', () => { backdrop.hidden = true; });
                if (backdrop.complete && !backdrop.naturalWidth) backdrop.hidden = true;
            }
            if (!poster) return;
            const originalSource = poster.currentSrc || poster.src;
            function fallback() {
                if (!poster.dataset.posterFallback) return;
                const source = poster.dataset.posterFallback;
                delete poster.dataset.posterFallback;
                poster.src = source;
            }
            poster.addEventListener('error', fallback);
            if (poster.complete && !poster.naturalWidth) fallback();
            // A separate sample preserves the displayed poster if its host blocks CORS.
            const sample = new Image();
            sample.crossOrigin = 'anonymous';
            sample.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 32;
                    canvas.height = 48;
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
                    const rgb = ['r', 'g', 'b'].map(channel => Math.round(dominant[channel] / dominant.count)).join(', ');
                    [hero, wrapper].forEach(element => {
                        element.style.setProperty('--premium-image-color', `rgb(${rgb})`);
                        element.style.setProperty('--premium-image-rgb', rgb);
                    });
                } catch {
                    // Keep the theme colors when a host does not allow canvas sampling.
                }
            };
            sample.onerror = () => {};
            sample.src = originalSource;
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
    document.addEventListener('torrent:metadata-ready', init);
})();
