<div class="col-md-6">

    <div class="modern-trending-wrapper ro-wrapper">

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

        </div>


        {{-- =========================================================
             BODY
        ========================================================== --}}
        <div class="modern-trending-body ro-body">

            <div class="ro-row">

                @foreach($randomItems as $item)

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

                @endforeach

            </div>

        </div>

    </div>

</div>


<style>

/* ==========================================================
   RANDOM ONLINE
   Movies / Series panels

   Desktop: 5 posters x 2 rows
   Mobile: horizontal scrolling
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
            rgba(22, 32, 51, .94),
            rgba(15, 23, 42, .88)
        );

    box-shadow:
        0 12px 32px rgba(0, 0, 0, .25);
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
        rgba(45, 212, 191, .12);

    color:
        var(--ui-accent, #2dd4bf);

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
        rgba(15, 23, 42, .70);

    border:
        1px solid rgba(255, 255, 255, .07);

    text-decoration: none;

    box-shadow:
        0 5px 14px rgba(0, 0, 0, .20);

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
        rgba(45, 212, 191, .38);

    box-shadow:
        0 12px 26px rgba(0, 0, 0, .38);
}


/* ==========================================================
   IMAGE WRAPPER
========================================================== */

.ro-image-wrap {
    position: absolute;

    inset: 0;

    overflow: hidden;

    background: #0f172a;
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
        rgba(15, 23, 42, .90);

    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);

    border:
        1px solid rgba(255, 255, 255, .14);

    color: #facc15;

    font-size: 10px;
    font-weight: 700;

    line-height: 1;

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .28);

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
    color: #ffffff;

    font-size: .72rem;
    font-weight: 700;

    line-height: 1.2;

    text-shadow:
        0 2px 4px rgba(0, 0, 0, .90);

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
        rgba(255, 255, 255, .76);

    font-size: .62rem;
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
        font-size: .68rem;
    }

    .ro-year {
        font-size: .60rem;
    }

    .ro-rating {
        top: 4px;
        right: 4px;

        padding: 3px 5px;

        font-size: 9px;
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
            rgba(45, 212, 191, .25)
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
            rgba(45, 212, 191, .25);

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
        font-size: .72rem;
    }


    .ro-year {
        font-size: .62rem;
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
        font-size: .68rem;
    }


    .ro-rating {
        top: 5px;
        right: 5px;

        padding: 3px 5px;

        font-size: 9px;
    }

}

</style>