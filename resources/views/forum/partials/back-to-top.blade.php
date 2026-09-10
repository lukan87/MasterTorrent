{{-- Back-to-top button partial. Self-contained: renders the button and wires up the JS.
       Safe to include multiple times on one page (id collision guarded). --}}

<button type="button" id="forumBackToTop" aria-label="Back to top" title="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>

@once('forum-back-to-top-js')
    <script>
        (function () {
            if (window.__forumBackToTopInitialized) {
                return;
            }
            window.__forumBackToTopInitialized = true;

            const el = document.getElementById('forumBackToTop');
            if (!el) {
                return;
            }

            const onScroll = function () {
                el.classList.toggle('visible', window.scrollY > 400);
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            el.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();
    </script>
@endonce