@once
@if(count($display['backdrops'] ?? []) > 1)
<script>
(() => {
    const backdrops = @json($display['backdrops']);
    const initial = @json($fanartBackground ?? ($torrent->background ?: ($display['backdrop'] ?? null)));
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function startBackdropSlideshow() {
        const container = document.createElement('div');
        container.className = 'torrent-backdrop-slideshow';
        container.setAttribute('aria-hidden', 'true');
        const slides = [0, 1].map(() => {
            const slide = document.createElement('div');
            slide.className = 'torrent-backdrop-slide';
            container.appendChild(slide);
            return slide;
        });
        const background = url => `linear-gradient(to bottom, rgba(5,10,18,.38), rgba(5,10,18,.94)), url(${JSON.stringify(url)})`;
        let active = 0;
        let queue = [];
        let lastBackdrop = initial;
        const imageKey = url => (url || '').split('/').pop();

        function nextBackdrop() {
            if (queue.length === 0) {
                queue = [...backdrops];
                // Fisher–Yates: visit every backdrop once in a random order.
                for (let i = queue.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [queue[i], queue[j]] = [queue[j], queue[i]];
                }
                // Original and resized TMDB URLs can refer to the same image.
                if (imageKey(queue[0]) === imageKey(lastBackdrop)) {
                    [queue[0], queue[1]] = [queue[1], queue[0]];
                }
            }
            return queue.shift();
        }
        let pending = false;
        let stopped = false;
        let mounted = false;

        const timer = window.setInterval(() => {
            if (document.hidden || motion.matches || pending) return;
            pending = true;
            const url = nextBackdrop();
            const image = new Image();
            image.onload = () => {
                pending = false;
                if (stopped || document.hidden || motion.matches) return;
                if (!mounted) {
                    slides[active].style.backgroundImage = background(initial || url);
                    slides[active].classList.add('is-visible');
                    document.body.prepend(container);
                    document.documentElement.classList.add('has-torrent-backdrops');
                    // Establish the first layer before starting the crossfade.
                    container.getBoundingClientRect();
                    mounted = true;
                }
                const next = 1 - active;
                slides[next].style.backgroundImage = background(url);
                slides[next].classList.add('is-visible');
                slides[active].classList.remove('is-visible');
                active = next;
                lastBackdrop = url;
            };
            image.onerror = () => { pending = false; };
            image.src = url;
        }, 5000);

        window.addEventListener('pagehide', event => {
            if (!event.persisted) {
                stopped = true;
                window.clearInterval(timer);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startBackdropSlideshow, { once: true });
    } else {
        startBackdropSlideshow();
    }
})();
</script>
@endif
@endonce
