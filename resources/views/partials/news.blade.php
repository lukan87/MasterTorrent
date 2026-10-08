@if($latestNews->isEmpty())

<div class="col-12 mt-3">

    <div class="modern-news-empty">

        <div class="empty-glow"></div>

        <div class="position-relative z-2 text-center">

            <div class="empty-icon">

                <i class="bi bi-newspaper"></i>

            </div>

            <h6 class="mb-1 theme-text">

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
                 id="news-content-{{ $news->id }}"
                 data-news-content
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

        {{-- Expand control stays beside More News --}}
        <div class="news-expand-row"
             data-expand-row
             hidden>

            <button type="button"
                    class="read-more-btn news-expand-toggle"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-container="body"
                    title="Expand or collapse the full news text without leaving this page."
                    data-expand-toggle
                    aria-expanded="false"
                    aria-controls="news-content-{{ $news->id }}">

                <span class="toggle-label">Show more</span>

                <span class="toggle-arrow">

                    <i class="bi bi-chevron-down"></i>

                </span>

            </button>

        </div>

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
            var(--theme-surface, rgba(14,21,33,.95)),
            var(--theme-surface, rgba(10,15,27,.84))
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 10px 26px var(--theme-shadow, rgba(0, 0, 0, .16)),
        inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .025));

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
            var(--theme-surface-alt, rgba(255,255,255,0.049)),
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
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );

    border-radius: 0 4px 4px 0;

    opacity: .75;

    pointer-events: none;
}

.modern-news-card:hover {
    transform: translateY(-2px);

    border-color: var(--theme-teal-border, rgba(99, 210, 198, .20));

    box-shadow:
        0 14px 32px var(--theme-shadow, rgba(0, 0, 0, .22)),
        0 0 0 1px var(--theme-shadow, rgba(99, 210, 198, .025));
}


/* IMAGE */
.modern-news-image-wrap {
    position: relative;
    overflow: hidden;

    background: rgba(5,10,19,.65);

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
            rgba(5,10,19,.02),
            rgba(5,10,19,.30)
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

    border-bottom: 1px solid var(--theme-border, rgba(148, 163, 184, .08));
}

.news-icon-box {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    color: var(--ui-accent);

    background: var(--theme-teal-soft, rgba(99, 210, 198, .075));

    border: 1px solid var(--theme-teal-border, rgba(99, 210, 198, .14));

    border-radius: .65rem;

    font-size: .95rem;

    box-shadow: inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .025));

    transition: background 160ms ease, border-color 160ms ease;
}

.modern-news-card:hover .news-icon-box {
    background: var(--theme-teal-soft, rgba(99, 210, 198, .11));
    border-color: var(--theme-teal-border, rgba(99, 210, 198, .22));
}

.modern-news-title {
    margin: 0;

    color: var(--theme-text, #f1f5f9);

    font-size: 1rem;
    font-weight: 800;

    line-height: 1.35;

    overflow-wrap: anywhere;
}

.modern-news-title a {
    color: var(--theme-text, #f1f5f9);
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

    color: var(--theme-muted, #71859b);

    font-size: var(--site-font-body, 13px);
}

.modern-news-meta span {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}

.modern-news-meta i {
    color: var(--theme-muted, #60758b);
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

    color: var(--theme-muted, #8295aa);

    background: var(--theme-surface-alt, rgba(148,163,184,0.0315));

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .09));

    border-radius: .45rem;

    font-size: var(--site-font-small, 13px);

    text-decoration: none;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.news-action-btn:hover {
    color: var(--ui-accent);
    background: var(--theme-teal-soft, rgba(99, 210, 198, .07));
    border-color: var(--theme-teal-border, rgba(99, 210, 198, .17));
    transform: translateY(-1px);
}

.news-action-btn.edit-btn:hover {
    color:  var(--theme-text, #c9f3ee);
    background: var(--theme-teal-soft, rgba(99, 210, 198, .07));
    border-color: var(--theme-teal-border, rgba(99, 210, 198, .17));
}


/* BODY */
.modern-news-body {
    position: relative;
    z-index: 2;

    padding: 1rem 1.15rem;
}

.news-content-preview {
    color: var(--theme-text, #c2cedb);

    font-size: var(--site-font-body, 13px);

    line-height: 1.7;
}

.news-content-preview p {
    margin-bottom: .65rem;
}

.news-content-preview p:last-child {
    margin-bottom: 0;
}

.news-content-preview strong {
    color: var(--theme-text, #e5edf7);
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

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .12));
    border-radius: .65rem;
}


/* EXPAND / COLLAPSE */
.news-content-preview.is-collapsed {
    position: relative;

    max-height: 17.65em;

    overflow: hidden;


}

.news-content-preview.is-collapsed::after {
    content: "";

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 4em;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    mask-image: linear-gradient(to bottom, transparent, black 45%);
    -webkit-mask-image: linear-gradient(to bottom, transparent, black 45%);

    background:
        linear-gradient(
            180deg,
            rgba(10,15,27,0),
            var(--theme-surface, rgba(10,15,27,.92))
        );

    pointer-events: none;
}

.news-content-preview.is-expanded {
    max-height: none;
}

.news-content-preview.is-expanded::after {
    display: none;
}


/* Footer expand control */
.news-expand-row {
    display: flex;
    align-items: center;
}

.news-expand-row[hidden] {
    display: none !important;
}


/* =========================================================
   TOGGLE BUTTON
   ========================================================= */

.news-expand-toggle {
    gap: .3rem;
    cursor: pointer;
    font-family: inherit;
    line-height: inherit;
}

.news-expand-toggle .toggle-arrow {
    display: inline-block;

    font-size: var(--site-font-small, 13px);

    transition: transform 220ms ease;
}

.news-expand-toggle.is-open .toggle-arrow {
    transform: rotate(180deg);
}


/* FOOTER */
.modern-news-footer {
    flex-wrap: wrap;
    gap: .6rem;
    position: relative;
    z-index: 2;

    display: flex;

    align-items: center;

    padding: .8rem 1.15rem 1rem;

    border-top: 1px solid var(--theme-border, rgba(148, 163, 184, .07));
}

.read-more-btn {
    display: inline-flex;

    align-items: center;

    min-height: 30px;

    padding: .35rem .65rem;

    color: var(--theme-muted, #8295aa);

    background: var(--theme-surface-alt, rgba(148,163,184,0.0315));

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .09));

    border-radius: .45rem;

    font-size: var(--site-font-small, 13px);
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
    background: var(--theme-teal-soft, rgba(99, 210, 198, .07));
    border-color: var(--theme-teal-border, rgba(99, 210, 198, .17));
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

    background: var(--theme-teal-soft, rgba(99, 210, 198, .075));

    border: 1px solid var(--theme-teal-border, rgba(99, 210, 198, .14));

    border-radius: .75rem;

    font-size: 1.05rem;
}

.modern-news-empty h6 {
    color: var(--theme-text, #f1f5f9);
    font-size: var(--site-font-body, 13px);
    font-weight: 800;
}

.modern-news-empty p {
    color: var(--theme-muted, #71859b) !important;
    font-size: var(--site-font-body, 13px);
}


.modern-news-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 34px;

    padding: .42rem .75rem;

    color: var(--theme-on-action, #062523);

    background:
        linear-gradient(
            135deg,
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
        );

    border: 1px solid var(--theme-teal-border, rgba(99, 210, 198, .25));

    border-radius: .5rem;

    font-size: var(--site-font-body, 13px);
    font-weight: 800;

    text-decoration: none;

    box-shadow: 0 4px 12px var(--theme-shadow, rgba(0, 0, 0, .15));

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        filter 160ms ease;
}

.modern-news-btn:hover {
    color: var(--theme-text, #062523);
    transform: translateY(-1px);
    filter: brightness(1.05);
    box-shadow: 0 6px 16px var(--theme-shadow, rgba(0, 0, 0, .2));
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
            var(--theme-teal-soft, rgba(99, 210, 198, .055)),
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
        font-size: var(--site-font-body, 13px);
    }

    .modern-news-meta {
        font-size: 1rem;
        gap: .35rem .7rem;
    }

    .modern-news-body {
        padding: .85rem .9rem;
    }

    .news-content-preview {
        font-size: var(--site-font-body, 13px);
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
    function initNewsExpanders() {
        document.querySelectorAll('[data-news-content]').forEach(function (content) {
            if (content.dataset.expanderReady) return;
            content.dataset.expanderReady = '1';

            const card = content.closest('[data-news-card]');
            const row = card.querySelector('[data-expand-row]');
            const toggle = row.querySelector('[data-expand-toggle]');
            const label = toggle.querySelector('.toggle-label');
            const autoCollapseMs = Number(content.dataset.autoCollapse) || 0;
            let collapseTimer;
            let expanded = false;

            function update() {
                const limit = parseFloat(getComputedStyle(content).fontSize) * 17.65;
                const overflows = content.scrollHeight > limit + 1;
                row.hidden = !overflows;
                content.classList.toggle('is-collapsed', overflows && !expanded);
                content.classList.toggle('is-expanded', expanded);
                toggle.classList.toggle('is-open', expanded);
                toggle.setAttribute('aria-expanded', String(expanded));
                label.textContent = expanded ? 'Show less' : 'Show more';
            }

            toggle.addEventListener('click', function () {
                clearTimeout(collapseTimer);
                expanded = !expanded;
                update();
                if (expanded && autoCollapseMs > 0) {
                    collapseTimer = setTimeout(function () {
                        expanded = false;
                        update();
                    }, autoCollapseMs);
                }
            });

            content.querySelectorAll('img').forEach(function (image) {
                image.addEventListener('load', update);
            });
            window.addEventListener('resize', update);
            update();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNewsExpanders);
    } else {
        initNewsExpanders();
    }
})();
</script>
