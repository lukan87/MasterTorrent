@if($latestNews->isEmpty())

<div class="col-12 mt-3">

    <div class="modern-news-empty">

        <div class="empty-glow"></div>

        <div class="position-relative z-2 text-center">

            <div class="empty-icon">

                <i class="bi bi-newspaper"></i>

            </div>

            <h6 class="mb-1 text-white">

                No news available

            </h6>

            <p class="text-muted small mb-3">

                Check back later for tracker updates.

            </p>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                <a href="{{ route('news.create') }}"
                   class="modern-news-btn">

                    <i class="bi bi-plus-circle-fill me-1"></i>

                    Create News

                </a>

            @endif

        </div>

    </div>

</div>

@else

@foreach($latestNews as $news)

<div class="col-md-12 mt-2">

    <div class="modern-news-card" data-news-card>

        <div class="news-card-glow"></div>

        {{-- IMAGE --}}
        @if($news->image)

        <div class="modern-news-image-wrap">

            <img src="{{ asset('storage/' . $news->image) }}"
                 class="modern-news-image"
                 alt="{{ $news->title }}">

        </div>

        @endif

        {{-- HEADER --}}
        <div class="modern-news-header" data-news-header>

            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">

                <div class="news-icon-box">

                    <i class="bi bi-megaphone-fill"></i>

                </div>

                <div class="flex-grow-1 min-w-0">

                    <h6 class="modern-news-title mb-1">

                        <a href="{{ route('news.show', $news) }}">

                            {{ $news->title }}

                        </a>

                    </h6>

                    <div class="modern-news-meta">

                        <span>

                            <i class="bi bi-person-circle"></i>

                            {{ $news->user->name }}

                        </span>

                        <span>

                            <i class="bi bi-calendar3"></i>

                            {{ $news->created_at->format('M j, Y') }}

                        </span>

                    </div>

                </div>

            </div>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

            <div class="modern-news-actions">

                <a href="{{ route('news.create') }}"
                   class="news-action-btn"
                   data-bs-toggle="tooltip"
                   title="Create News">

                    <i class="bi bi-plus-lg"></i>

                </a>

                <a href="{{ route('news.edit', $news) }}"
                   class="news-action-btn edit-btn"
                   data-bs-toggle="tooltip"
                   title="Edit News">

                    <i class="bi bi-pencil"></i>

                </a>

            </div>

            @endif

        </div>

        {{-- BODY --}}
        <div class="modern-news-body" data-news-body>

            <div class="news-content-preview"
                 data-news-content
                 data-char-threshold="500"
                 data-auto-collapse="10000">

                {!! convertCustomTagsToHtml($news->content) !!}

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="modern-news-footer">

            <a href="{{ route('news.show', $news) }}"
               class="read-more-btn">

                <i class="bi bi-arrow-right-circle me-1"></i>

                More News

            </a>

        </div>

        {{-- Floating toggle inside the card --}}
        <div class="news-expand-row"
             data-expand-row
             hidden>

            <button type="button"
                    class="news-expand-toggle"
                    data-expand-toggle
                    aria-expanded="false">

                <span class="toggle-label">Show more</span>

                <span class="toggle-arrow">

                    <i class="bi bi-chevron-down"></i>

                </span>

            </button>

        </div>

    </div>

</div>

@endforeach

@endif

<style>

/* =========================================================
   FILEIPLAY NEWS
   ========================================================= */

.modern-news-card,
.modern-news-empty {
    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 10px 26px rgba(0, 0, 0, .16),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        transform 160ms ease,
        border-color 160ms ease,
        box-shadow 160ms ease;
}

.modern-news-card::before,
.modern-news-empty::before {
    content: "";

    position: absolute;

    top: 0;
    left: 8%;
    right: 8%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, .07),
            transparent
        );

    pointer-events: none;
}

.modern-news-card::after,
.modern-news-empty::after {
    content: "";

    position: absolute;

    top: 18px;
    bottom: 18px;
    left: 0;

    width: 3px;

    background:
        linear-gradient(
            180deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border-radius: 0 4px 4px 0;

    opacity: .75;

    pointer-events: none;
}

.modern-news-card:hover {
    transform: translateY(-2px);

    border-color: rgba(99, 210, 198, .20);

    box-shadow:
        0 14px 32px rgba(0, 0, 0, .22),
        0 0 0 1px rgba(99, 210, 198, .025);
}


/* IMAGE */
.modern-news-image-wrap {
    position: relative;
    overflow: hidden;

    background: rgba(8, 15, 29, .65);

    border-bottom: 1px solid rgba(148, 163, 184, .09);
}

.modern-news-image {
    display: block;

    width: 100%;
    height: 145px;

    object-fit: cover;

    opacity: .88;

    transition: transform 300ms ease, opacity 300ms ease;
}

.modern-news-card:hover .modern-news-image {
    transform: scale(1.015);
    opacity: .96;
}

.modern-news-image-wrap::after {
    content: "";

    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            180deg,
            rgba(8, 15, 29, .02),
            rgba(8, 15, 29, .30)
        );

    pointer-events: none;
}


/* HEADER */
.modern-news-header {
    position: relative;
    z-index: 2;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    padding: 1rem 1.15rem .9rem;

    border-bottom: 1px solid rgba(148, 163, 184, .08);
}

.news-icon-box {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .075);

    border: 1px solid rgba(99, 210, 198, .14);

    border-radius: .65rem;

    font-size: .95rem;

    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .025);

    transition: background 160ms ease, border-color 160ms ease;
}

.modern-news-card:hover .news-icon-box {
    background: rgba(99, 210, 198, .11);
    border-color: rgba(99, 210, 198, .22);
}

.modern-news-title {
    margin: 0;

    color: #f1f5f9;

    font-size: 1rem;
    font-weight: 800;

    line-height: 1.35;

    overflow-wrap: anywhere;
}

.modern-news-title a {
    color: #f1f5f9;
    text-decoration: none;
    transition: color 160ms ease;
}

.modern-news-title a:hover {
    color: var(--ui-accent);
}

.modern-news-meta {
    display: flex;

    align-items: center;
    flex-wrap: wrap;

    gap: .45rem .9rem;

    margin-top: .35rem;

    color: #71859b;

    font-size: .92rem;
}

.modern-news-meta span {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}

.modern-news-meta i {
    color: #60758b;
    font-size: .7rem;
}


/* ADMIN ACTIONS */
.modern-news-actions {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-shrink: 0;
}

.news-action-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 30px;
    height: 30px;

    color: #8295aa;

    background: rgba(148, 163, 184, .045);

    border: 1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .72rem;

    text-decoration: none;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.news-action-btn:hover {
    color: var(--ui-accent);
    background: rgba(99, 210, 198, .07);
    border-color: rgba(99, 210, 198, .17);
    transform: translateY(-1px);
}

.news-action-btn.edit-btn:hover {
    color: #c9f3ee;
    background: rgba(99, 210, 198, .07);
    border-color: rgba(99, 210, 198, .17);
}


/* BODY */
.modern-news-body {
    position: relative;
    z-index: 2;

    padding: 1rem 1.15rem;
}

.news-content-preview {
    color: #c2cedb;

    font-size: .95rem;

    line-height: 1.7;
}

.news-content-preview p {
    margin-bottom: .65rem;
}

.news-content-preview p:last-child {
    margin-bottom: 0;
}

.news-content-preview strong {
    color: #e5edf7;
}

.news-content-preview a {
    color: var(--ui-accent);
    text-decoration: none;
}

.news-content-preview a:hover {
    text-decoration: underline;
}

.news-content-preview img {
    display: block;

    max-width: 100%;
    height: auto;

    margin: .7rem 0;

    border: 1px solid rgba(148, 163, 184, .12);
    border-radius: .65rem;
}


/* EXPAND / COLLAPSE */
.news-content-preview.is-collapsed {
    position: relative;

    max-height: 17.65em;

    overflow: hidden;

    transition: max-height 1120ms ease;
}

.news-content-preview.is-collapsed::after {
    content: "";

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 2.4em;

    background:
        linear-gradient(
            180deg,
            rgba(15, 23, 42, 0),
            rgba(15, 23, 42, .92)
        );

    pointer-events: none;
}

.news-content-preview.is-expanded {
    max-height: none;
}

.news-content-preview.is-expanded::after {
    display: none;
}


/* =========================================================
   FLOATING-IN-CARD TOGGLE
   Now on the LEFT side, so it never collides with the
   admin Create / Edit buttons on the right.
   ========================================================= */

.news-expand-row {
    position: absolute;

    top: 0;
    left: 1.15rem;

    z-index: 5;

    display: flex;

    align-items: center;

    justify-content: flex-start;

    pointer-events: none;

    transition: top 60ms linear;
}

.news-expand-row[hidden] {
    display: none !important;
}


/* =========================================================
   TOGGLE BUTTON
   ========================================================= */

.news-expand-toggle {
    display: inline-flex;

    align-items: center;
    gap: .3rem;

    padding: .25rem .65rem;

    color: var(--ui-accent);

    background: rgba(15, 23, 42, .95);

    border: 1px solid rgba(99, 210, 198, .25);

    border-radius: 999px;

    font-size: .72rem;
    font-weight: 700;

    letter-spacing: .2px;

    cursor: pointer;

    pointer-events: auto;

    opacity: .95;

    box-shadow:
        0 4px 14px rgba(0, 0, 0, .4),
        0 0 0 1px rgba(0, 0, 0, .2);

    backdrop-filter: blur(6px);

    -webkit-backdrop-filter: blur(6px);

    transition:
        opacity 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease,
        transform 160ms ease;
}

.news-expand-toggle:hover {
    opacity: 1;
    color: #a0f0e8;
    background: rgba(15, 23, 42, .99);
    border-color: rgba(99, 210, 198, .4);
    transform: translateY(-1px);
}

.news-expand-toggle .toggle-arrow {
    display: inline-block;

    font-size: .7rem;

    transition: transform 220ms ease;
}

.news-expand-toggle.is-open .toggle-arrow {
    transform: rotate(180deg);
}


/* FOOTER */
.modern-news-footer {
    position: relative;
    z-index: 2;

    display: flex;

    align-items: center;

    padding: .8rem 1.15rem 1rem;

    border-top: 1px solid rgba(148, 163, 184, .07);
}

.read-more-btn {
    display: inline-flex;

    align-items: center;

    min-height: 30px;

    padding: .35rem .65rem;

    color: #8295aa;

    background: rgba(148, 163, 184, .045);

    border: 1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .72rem;
    font-weight: 700;

    text-decoration: none;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.read-more-btn:hover {
    color: var(--ui-accent);
    background: rgba(99, 210, 198, .07);
    border-color: rgba(99, 210, 198, .17);
    transform: translateY(-1px);
}

.read-more-btn i {
    color: var(--ui-accent);
    font-size: .8rem;
}


/* EMPTY NEWS STATE */
.modern-news-empty {
    padding: 2rem 1.25rem;
}

.modern-news-empty .position-relative {
    position: relative !important;
    z-index: 2;
}

.empty-icon {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 48px;
    height: 48px;

    margin: 0 auto .85rem;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .075);

    border: 1px solid rgba(99, 210, 198, .14);

    border-radius: .75rem;

    font-size: 1.05rem;
}

.modern-news-empty h6 {
    color: #f1f5f9;
    font-size: .95rem;
    font-weight: 800;
}

.modern-news-empty p {
    color: #71859b !important;
    font-size: .78rem;
}


.modern-news-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 34px;

    padding: .42rem .75rem;

    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border: 1px solid rgba(99, 210, 198, .25);

    border-radius: .5rem;

    font-size: .8rem;
    font-weight: 800;

    text-decoration: none;

    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        filter 160ms ease;
}

.modern-news-btn:hover {
    color: #062523;
    transform: translateY(-1px);
    filter: brightness(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, .2);
}


/* GLOW */
.news-card-glow,
.empty-glow {
    position: absolute;

    top: -80px;
    right: -80px;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(99, 210, 198, .055),
            transparent 70%
        );

    pointer-events: none;
}


/* MOBILE */
@media (max-width: 767.98px) {

    .modern-news-header {
        align-items: flex-start;
        padding: .85rem .9rem .75rem;
    }

    .news-icon-box {
        width: 35px;
        height: 35px;
        flex-basis: 35px;
    }

    .modern-news-title {
        font-size: .92rem;
    }

    .modern-news-meta {
        font-size: 1rem;
        gap: .35rem .7rem;
    }

    .modern-news-body {
        padding: .85rem .9rem;
    }

    .news-content-preview {
        font-size: .84rem;
    }

    .modern-news-footer {
        padding: .7rem .9rem .85rem;
    }

    .modern-news-image {
        height: 115px;
    }

    .modern-news-actions {
        gap: .25rem;
    }

    .news-action-btn {
        width: 28px;
        height: 28px;
    }

    .modern-news-empty {
        padding: 1.5rem 1rem;
    }

    .news-expand-row {
        left: .9rem;
    }
}


@media (max-width: 480px) {

    .modern-news-header {
        gap: .65rem;
    }

    .modern-news-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: .2rem;
    }

    .modern-news-actions {
        margin-left: auto;
    }
}

</style>

<script>
(function () {

    /* -------- Detect navbar / sticky header height -------- */

    function getStickyOffset() {

        var candidates = document.querySelectorAll(
            'header, nav, .navbar, .site-header, .app-header, [data-sticky-header]'
        );

        var maxBottom = 0;

        candidates.forEach(function (el) {

            var style = window.getComputedStyle(el);

            var isFixedOrSticky =
                style.position === 'fixed' ||
                style.position === 'sticky';

            if (!isFixedOrSticky) return;

            var rect = el.getBoundingClientRect();

            if (rect.bottom > 0 && rect.top < 100) {
                if (rect.bottom > maxBottom) {
                    maxBottom = rect.bottom;
                }
            }
        });

        return maxBottom + 8;
    }


    function initNewsExpanders() {

        var contents = document.querySelectorAll('[data-news-content]');

        contents.forEach(function (content) {

            if (content.getAttribute('data-expander-ready') === '1') {
                return;
            }

            content.setAttribute('data-expander-ready', '1');

            var threshold = parseInt(
                content.getAttribute('data-char-threshold'),
                10
            ) || 500;

            var autoCollapseMs = parseInt(
                content.getAttribute('data-auto-collapse'),
                10
            ) || 0;

            var plainText = (content.textContent || '')
                .replace(/\s+/g, ' ')
                .trim();

            if (plainText.length <= threshold) {
                return;
            }

            var card = content.closest('[data-news-card]');
            var header = card ? card.querySelector('[data-news-header]') : null;
            var row = card ? card.querySelector('[data-expand-row]') : null;
            var toggle = row ? row.querySelector('[data-expand-toggle]') : null;

            if (!card || !row || !toggle) {
                return;
            }

            row.hidden = false;
            content.classList.add('is-collapsed');

            var collapseTimer = null;

            function collapseNow() {
                content.classList.remove('is-expanded');
                content.classList.add('is-collapsed');

                toggle.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.querySelector('.toggle-label').textContent = 'Show more';

                updateRowPosition();
            }

            function expandNow() {
                content.classList.remove('is-collapsed');
                content.classList.add('is-expanded');

                toggle.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
                toggle.querySelector('.toggle-label').textContent = 'Show less';

                if (autoCollapseMs > 0) {
                    clearTimeout(collapseTimer);
                    collapseTimer = setTimeout(function () {
                        collapseNow();
                    }, autoCollapseMs);
                }

                updateRowPosition();
            }

            toggle.addEventListener('click', function () {
                clearTimeout(collapseTimer);

                if (toggle.classList.contains('is-open')) {
                    collapseNow();
                } else {
                    expandNow();
                }
            });

            /* ---- reposition row inside card, respecting sticky header
                    AND staying below the card's own header row ---- */

            function updateRowPosition() {

                var cardRect = card.getBoundingClientRect();

                var stickyOffset = getStickyOffset();

                /* Minimum offset inside the card: below the card header */
                var minOffset = 8;

                if (header) {
                    var headerRect = header.getBoundingClientRect();
                    /* Distance from card top to bottom of header, plus a gap */
                    var headerBottomInCard = headerRect.bottom - cardRect.top;

                    if (headerBottomInCard > minOffset) {
                        minOffset = headerBottomInCard + 6;
                    }
                }

                /* Visible top of card, clamped to below sticky site header */
                var visibleTop = Math.max(stickyOffset, cardRect.top);

                /* Visible bottom of card */
                var visibleBottom = Math.min(
                    window.innerHeight,
                    cardRect.bottom
                );

                /* Hide if card visible band is too small */
                if (visibleBottom - visibleTop < 30) {
                    row.style.visibility = 'hidden';
                    return;
                }

                /* Offset relative to the card's own top */
                var offset = visibleTop - cardRect.top;

                /* Never go above the card's own header row */
                if (offset < minOffset) offset = minOffset;

                /* Never go past the bottom of the card */
                var maxOffset = cardRect.height - row.offsetHeight - 8;
                if (offset > maxOffset) offset = maxOffset;

                row.style.top = offset + 'px';
                row.style.visibility = 'visible';
            }

            card._updateExpandRow = updateRowPosition;

            updateRowPosition();
        });

    }

    var rafPending = false;

    function onScrollOrResize() {

        if (rafPending) return;

        rafPending = true;

        requestAnimationFrame(function () {
            rafPending = false;

            document.querySelectorAll('[data-news-card]').forEach(function (card) {
                if (typeof card._updateExpandRow === 'function') {
                    card._updateExpandRow();
                }
            });
        });
    }

    window.addEventListener('scroll', onScrollOrResize, { passive: true });
    window.addEventListener('resize', onScrollOrResize);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initNewsExpanders();
            onScrollOrResize();
        });
    } else {
        initNewsExpanders();
        onScrollOrResize();
    }

})();
</script>