@php($sliderId = 'random-' . \Illuminate\Support\Str::slug($randomTitle))
<div class="col-md-6">

    <div class="modern-trending-wrapper ro-wrapper" data-random-slider>

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="modern-trending-header">

            <div class="d-flex align-items-center gap-3">

                <div class="modern-trending-icon ro-icon">
                    <i class="bi {{ $randomIcon }}"></i>
                </div>

                <div>

                    <h4 class="modern-trending-title">
                        {{ $randomTitle }}
                    </h4>

                    <div class="modern-trending-subtitle">
                        {{ $randomSubtitle }}
                    </div>

                </div>

            </div>

            <div class="ro-controls" aria-label="{{ $randomTitle }} navigation">
                <button type="button" class="ro-slide-button" data-slide="-1" aria-label="Previous {{ $randomTitle }}" aria-controls="{{ $sliderId }}" disabled>
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="ro-slide-button" data-slide="1" aria-label="Next {{ $randomTitle }}" aria-controls="{{ $sliderId }}" disabled>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>


        {{-- =========================================================
             BODY
        ========================================================== --}}
        <div class="modern-trending-body ro-body">

            <div class="ro-row" id="{{ $sliderId }}" tabindex="0" role="region" aria-label="{{ $randomTitle }}">

                @forelse(collect($randomItems)->take(10) as $item)

                    <a href="{{ $item['url'] }}"
                       class="ro-card text-decoration-none"
                       title="{{ $item['title'] }}">

                        <div class="ro-image-wrap">

                            {{-- POSTER --}}
                            <img
                                src="{{ $item['poster'] }}"
                                class="ro-poster"
                                data-bs-toggle="tooltip"
                                title="{{ $item['title'] }}"
                                loading="lazy"
                                alt="{{ $item['title'] }}"
                            >


                            {{-- RATING --}}
                            @if($item['rating'] > 0)

                                <span class="ro-rating">

                                    <i class="bi bi-star-fill"></i>

                                    {{ $item['rating'] }}

                                </span>

                            @endif


                            {{-- INFO OVERLAY --}}
                            <div class="ro-overlay">

                                @if($item['year'])

                                    <div class="ro-year">
                                        {{ $item['year'] }}
                                    </div>

                                @endif

                            </div>

                        </div>

                    </a>

                @empty
                    <p class="text-muted mb-0">No titles available right now.</p>
                @endforelse

            </div>

        </div>

    </div>

</div>


<style>

/* ==========================================================
   RANDOM ONLINE
   Movies / Series panels

   Single row with horizontal scrolling and navigation buttons
========================================================== */


/* ==========================================================
   MAIN WRAPPER
========================================================== */

.ro-wrapper {
    overflow: hidden;

    width: 100%;
    height: 100%;

    display: flex;
    flex-direction: column;

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    background:
        linear-gradient(
            135deg,
            var(--theme-surface, rgba(14,21,33,.94)),
            var(--theme-surface, rgba(10,15,27,.88))
        );

    box-shadow:
        0 12px 32px var(--theme-shadow, rgba(0, 0, 0, .25));
}


/* ==========================================================
   HEADER ICON
========================================================== */

.ro-icon {
    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: .65rem;

    background:
        var(--theme-teal-soft, rgba(45, 212, 191, .12));

    color:
        var(--ui-accent, var(--theme-on-action, #2dd4bf));

    font-size: 1.05rem;
}


/* ==========================================================
   BODY
========================================================== */

.ro-body {
    width: 100%;

    flex: 1;

    padding: 14px 16px 16px;
}


/* ==========================================================
   DESKTOP GRID

   5 posters per row
   10 posters = 2 rows
========================================================== */

.ro-row {
    width: 100%;

    display: grid;

    grid-template-columns:
        repeat(5, minmax(0, 125px));

    justify-content: center;

    gap: 12px;
}


/* ==========================================================
   POSTER CARD
========================================================== */

.ro-card {
    position: relative;

    display: block;

    width: 100%;
    max-width: 125px;

    aspect-ratio: 2 / 3;

    overflow: hidden;

    border-radius: .65rem;

    background:
        var(--theme-surface, rgba(10,15,27,.70));

    border:
        1px solid var(--theme-border, rgba(255, 255, 255, .07));

    text-decoration: none;

    box-shadow:
        0 5px 14px var(--theme-shadow, rgba(0, 0, 0, .20));

    transition:
        transform .22s ease,
        border-color .22s ease,
        box-shadow .22s ease;
}


/* ==========================================================
   CARD HOVER
========================================================== */

.ro-card:hover {
    transform: translateY(-4px);

    border-color:
        var(--theme-teal-border, rgba(45, 212, 191, .38));

    box-shadow:
        0 12px 26px var(--theme-shadow, rgba(0, 0, 0, .38));
}


/* ==========================================================
   IMAGE WRAPPER
========================================================== */

.ro-image-wrap {
    position: absolute;

    inset: 0;

    overflow: hidden;

    background: #0a0f1b;
}


/* ==========================================================
   POSTER IMAGE
========================================================== */

.ro-poster {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
    object-position: center;

    transition:
        transform .30s ease,
        filter .30s ease;
}


.ro-card:hover .ro-poster {
    transform: scale(1.05);
}


/* ==========================================================
   RATING
========================================================== */

.ro-rating {
    position: absolute;

    top: 6px;
    right: 6px;

    z-index: 3;

    display: inline-flex;
    align-items: center;

    gap: 3px;

    padding: 3px 6px;

    border-radius: .4rem;

    background:
        var(--theme-surface, rgba(10,15,27,.90));

    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);

    border:
        1px solid var(--theme-border, rgba(255, 255, 255, .14));

    color: var(--theme-amber-text, #facc15);

    font-size: var(--site-font-small, 13px);
    font-weight: 700;

    line-height: 1;

    box-shadow:
        0 4px 12px var(--theme-shadow, rgba(0, 0, 0, .28));

    pointer-events: none;
}


.ro-rating i {
    font-size: 9px;
}


/* ==========================================================
   POSTER OVERLAY
========================================================== */

.ro-overlay {
    position: absolute;

    inset: 0;

    z-index: 2;

    display: flex;
    flex-direction: column;
    justify-content: flex-end;

    padding: .55rem .6rem;

    background:
        linear-gradient(
            to top,
            rgba(0, 0, 0, .94) 0%,
            rgba(0, 0, 0, .70) 20%,
            rgba(0, 0, 0, .22) 50%,
            rgba(0, 0, 0, .08) 70%,
            transparent 100%
        );

    pointer-events: none;

    transition:
        background .25s ease;
}


.ro-card:hover .ro-overlay {
    background:
        linear-gradient(
            to top,
            rgba(0, 0, 0, .97) 0%,
            rgba(0, 0, 0, .76) 22%,
            rgba(0, 0, 0, .26) 52%,
            rgba(0, 0, 0, .08) 72%,
            transparent 100%
        );
}


/* ==========================================================
   TITLE
========================================================== */

.ro-title {
    color: var(--theme-text, #ffffff);

    font-size: var(--site-font-small, 13px);
    font-weight: 700;

    line-height: 1.2;

    text-shadow:
        0 2px 4px var(--theme-shadow, rgba(0, 0, 0, .90));

    overflow: hidden;

    display: -webkit-box;

    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}


/* ==========================================================
   YEAR
========================================================== */

.ro-year {
    margin-top: 3px;

    color:
        var(--theme-muted, rgba(255, 255, 255, .76));

    font-size: var(--site-font-small, 13px);
    font-weight: 600;

    line-height: 1.2;
}


/* ==========================================================
   LARGE DESKTOP
========================================================== */

@media (min-width: 1600px) {

    .ro-body {
        padding: 16px 18px 18px;
    }

    .ro-row {
        grid-template-columns:
            repeat(5, minmax(0, 130px));

        gap: 14px;
    }

    .ro-card {
        max-width: 130px;
    }

}


/* ==========================================================
   NORMAL DESKTOP
========================================================== */

@media (min-width: 1200px) and (max-width: 1599.98px) {

    .ro-row {
        grid-template-columns:
            repeat(5, minmax(0, 120px));

        gap: 11px;
    }

    .ro-card {
        max-width: 120px;
    }

}


/* ==========================================================
   SMALLER DESKTOP / TABLET

   Switch to 4 columns if the panel becomes too narrow.
========================================================== */

@media (min-width: 768px) and (max-width: 1199.98px) {

    .ro-body {
        padding: 10px;
    }

    .ro-row {
        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 9px;
    }

    .ro-card {
        max-width: none;
    }

    .ro-overlay {
        padding: .45rem;
    }

    .ro-title {
        font-size: var(--site-font-small, 13px);
    }

    .ro-year {
        font-size: var(--site-font-small, 13px);
    }

    .ro-rating {
        top: 4px;
        right: 4px;

        padding: 3px 5px;

        font-size: var(--site-font-small, 13px);
    }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (max-width: 767.98px) {

    .ro-body {
        padding: .65rem;
    }


    .ro-row {
        display: flex;

        width: 100%;

        justify-content: flex-start;

        gap: 10px;

        overflow-x: auto;
        overflow-y: hidden;

        scroll-snap-type: x mandatory;

        -webkit-overflow-scrolling: touch;

        scrollbar-width: thin;

        scrollbar-color:
            var(--theme-teal-border, rgba(45, 212, 191, .25))
            transparent;

        padding-bottom: 7px;
    }


    .ro-row::-webkit-scrollbar {
        height: 5px;
    }


    .ro-row::-webkit-scrollbar-track {
        background: transparent;
    }


    .ro-row::-webkit-scrollbar-thumb {
        background:
            var(--theme-teal-soft, rgba(45, 212, 191, .25));

        border-radius: 10px;
    }


    .ro-card {
        flex: 0 0 120px;

        width: 120px;
        max-width: 120px;

        aspect-ratio: 2 / 3;

        scroll-snap-align: start;
    }


    .ro-title {
        font-size: var(--site-font-small, 13px);
    }


    .ro-year {
        font-size: var(--site-font-small, 13px);
    }

}


/* ==========================================================
   VERY SMALL MOBILE
========================================================== */

@media (max-width: 420px) {

    .ro-card {
        flex-basis: 110px;

        width: 110px;
        max-width: 110px;
    }


    .ro-overlay {
        padding: .5rem;
    }


    .ro-title {
        font-size: var(--site-font-small, 13px);
    }


    .ro-rating {
        top: 5px;
        right: 5px;

        padding: 3px 5px;

        font-size: var(--site-font-small, 13px);
    }

}

/* Keep both panels in a single row at every screen size. */
.ro-wrapper .ro-row {
    display: flex;
    flex-wrap: nowrap;
    justify-content: flex-start;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x proximity;
    scrollbar-width: thin;
    scrollbar-color: var(--theme-teal-border, rgba(45, 212, 191, .35)) transparent;
    padding: 5px 0 10px;
}
.ro-wrapper .ro-card {
    flex: 0 0 clamp(110px, calc((100% - 48px) / 5), 150px);
    width: auto;
    max-width: none;
    scroll-snap-align: start;
}
.ro-wrapper .modern-trending-header { flex-wrap: wrap; gap: .75rem; }
.ro-controls { display: flex; gap: .4rem; margin-left: auto; }
.ro-slide-button {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border: 1px solid var(--theme-teal-border, rgba(99, 210, 198, .2));
    border-radius: 9px;
    color: var(--ui-accent, var(--theme-on-action, #63d2c6));
    background: var(--theme-teal-soft, rgba(99, 210, 198, .08));
}
.ro-slide-button:hover:not(:disabled) { background: var(--theme-teal-soft, rgba(99, 210, 198, .2)); }
.ro-slide-button:disabled { opacity: .3; cursor: default; }
.ro-slide-button:focus-visible, .ro-row:focus-visible { outline: 2px solid var(--ui-accent, var(--theme-teal-border, #63d2c6)); outline-offset: 2px; }
</style>

@once
@push('scripts')
<script>
document.querySelectorAll('[data-random-slider]').forEach(function (slider) {
    const row = slider.querySelector('.ro-row');
    const previous = slider.querySelector('[data-slide="-1"]');
    const next = slider.querySelector('[data-slide="1"]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function updateButtons() {
        previous.disabled = row.scrollLeft <= 1;
        next.disabled = row.scrollLeft + row.clientWidth >= row.scrollWidth - 1;
    }

    slider.querySelectorAll('[data-slide]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = row.querySelector('.ro-card');
            if (!card) return;
            const step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(row).gap);
            const page = Math.max(1, Math.floor(row.clientWidth / step)) * step;
            row.scrollBy({ left: Number(button.dataset.slide) * page, behavior: reducedMotion.matches ? 'instant' : 'smooth' });
        });
    });

    row.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);
    if ('ResizeObserver' in window) new ResizeObserver(updateButtons).observe(row);
    updateButtons();
});
</script>
@endpush
@endonce
