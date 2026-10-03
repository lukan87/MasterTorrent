(() => {
    'use strict';
    const dialog = document.createElement('dialog');
    if (typeof dialog.showModal !== 'function') return;
    dialog.className = 'site-image-lightbox';
    dialog.setAttribute('aria-labelledby', 'sil-title');
    dialog.innerHTML = `
        <header class="sil-toolbar">
            <h2 class="sil-heading" id="sil-title">Image preview</h2>
            <button type="button" data-zoom aria-label="View actual size" aria-pressed="false" disabled>1:1</button>
            <a data-original target="_blank" rel="noopener noreferrer" aria-label="Open original image in a new tab" title="Open original image"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
            <button type="button" data-close aria-label="Close image viewer" autofocus><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <div class="sil-stage"><img class="sil-image" alt="" hidden><div class="sil-status" role="status" aria-live="polite"></div></div>
        <footer class="sil-navigation">
            <button type="button" data-previous aria-label="Previous image"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <span class="sil-caption" aria-live="polite"></span>
            <button type="button" data-next aria-label="Next image"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        </footer>`;
    document.body.append(dialog);
    const stage = dialog.querySelector('.sil-stage');
    const picture = dialog.querySelector('.sil-image');
    const status = dialog.querySelector('.sil-status');
    const zoom = dialog.querySelector('[data-zoom]');
    const original = dialog.querySelector('[data-original]');
    const previous = dialog.querySelector('[data-previous]');
    const next = dialog.querySelector('[data-next]');
    let items = [], index = 0, revision = 0, opener, touchStart, scrollLocks = [];

    function setZoom(value) {
        stage.classList.toggle('is-zoomed', value);
        zoom.setAttribute('aria-pressed', String(value));
        zoom.setAttribute('aria-label', value ? 'Fit image to screen' : 'View actual size');
        zoom.textContent = value ? 'Fit' : '1:1';
        stage.scrollTo(0, 0);
    }
    function showImage() {
        const item = items[index];
        const request = ++revision;
        setZoom(false);
        picture.hidden = true;
        zoom.disabled = true;
        status.textContent = 'Loading image…';
        original.href = item.src;
        previous.disabled = items.length < 2;
        next.disabled = items.length < 2;
        dialog.querySelector('.sil-caption').textContent = `${index + 1} / ${items.length}`;
        const loader = new Image();
        loader.onload = () => {
            if (!dialog.open || request !== revision) return;
            picture.src = loader.src;
            picture.alt = item.alt;
            original.href = loader.src;
            picture.hidden = false;
            status.textContent = '';
            zoom.disabled = false;
        };
        loader.onerror = () => {
            if (!dialog.open || request !== revision) return;
            if (item.fallback && loader.src !== item.fallback) { loader.src = item.fallback; return; }
            status.textContent = 'This image could not be loaded. Try opening the original.';
        };
        loader.src = item.src;
    }
    function move(direction) {
        index = (index + direction + items.length) % items.length;
        showImage();
    }
    function open(target) {
        const gallery = target.closest('[data-screenshot-gallery]');
        const scope = gallery || target.closest('.description-expand-content, .content, .reply-content, .news-content-preview, .bbcode-content') || target.parentElement;
        const nodes = gallery ? [...gallery.querySelectorAll('[data-image-lightbox]')] : [...scope.querySelectorAll('img.bbcode-image, .torrent-description img')].filter(image => !image.closest('a'));
        index = nodes.indexOf(target);
        if (index < 0) { nodes.push(target); index = nodes.length - 1; }
        items = nodes.map(node => ({
            src: node.dataset.imageSrc || (node.matches('a') ? node.href : node.currentSrc || node.src),
            alt: node.querySelector('img')?.alt || node.alt || 'Image preview',
            fallback: node.dataset.imageFallback || ''
        }));
        opener = target;
        dialog.showModal();
        scrollLocks = [document.documentElement, document.querySelector('.app-main')].filter(Boolean).map(node => ({ node, overflow: node.style.overflow }));
        scrollLocks.forEach(({ node }) => { node.style.overflow = 'hidden'; });
        showImage();
    }
    dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        revision++;
        scrollLocks.forEach(({ node, overflow }) => { node.style.overflow = overflow; });
        picture.removeAttribute('src');
        opener?.focus({ preventScroll: true });
    });
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    zoom.addEventListener('click', () => setZoom(!stage.classList.contains('is-zoomed')));
    stage.addEventListener('click', event => { if (event.target === stage) dialog.close(); });
    dialog.addEventListener('keydown', event => {
        if (stage.classList.contains('is-zoomed')) return;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            move(event.key === 'ArrowRight' ? 1 : -1);
        }
    });
    stage.addEventListener('touchstart', event => {
        touchStart = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null;
    }, { passive: true });
    stage.addEventListener('touchend', event => {
        if (!touchStart || stage.classList.contains('is-zoomed')) return;
        const dx = event.changedTouches[0].clientX - touchStart.x;
        const dy = event.changedTouches[0].clientY - touchStart.y;
        if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) move(dx < 0 ? 1 : -1);
        touchStart = null;
    }, { passive: true });
    function targetFor(event) {
        const target = event.target.closest('[data-image-lightbox], img.bbcode-image, .torrent-description img');
        if (!target || dialog.contains(target)) return null;
        if (target.matches('img') && target.closest('a')) return null;
        return target;
    }
    document.addEventListener('click', event => {
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const target = targetFor(event);
        if (!target) return;
        event.preventDefault();
        open(target);
    });
    document.addEventListener('keydown', event => {
        const target = targetFor(event);
        if (!target?.matches('img') || !['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        open(target);
    });
    document.querySelectorAll('[data-screenshot-gallery]').forEach(gallery => {
        const track = gallery.querySelector('.torrent-screens-track');
        const buttons = gallery.querySelectorAll('[data-screenshot-step]');
        function update() {
            buttons[0].disabled = track.scrollLeft <= 1;
            buttons[1].disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
        }
        buttons.forEach(button => button.addEventListener('click', () => track.scrollBy({
            left: Number(button.dataset.screenshotStep) * track.clientWidth * .85,
            behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'
        })));
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        if ('ResizeObserver' in window) new ResizeObserver(update).observe(track);
        update();
    });
})();
