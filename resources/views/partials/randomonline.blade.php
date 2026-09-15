<div class="col-md-6">

    <div class="modern-trending-wrapper ro-wrapper">

        {{-- HEADER --}}
        <div class="modern-trending-header">

            <div class="d-flex align-items-center gap-3">

                <div class="modern-trending-icon ro-icon">

                    <i class="bi {{ $randomIcon }}"></i>

                </div>

                <div>

                    <h4 class="modern-trending-title">{{ $randomTitle }}</h4>

                    <div class="modern-trending-subtitle">{{ $randomSubtitle }}</div>

                </div>

            </div>

        </div>

        {{-- BODY --}}
        <div class="modern-trending-body ro-body">

            <div class="ro-row">

                @foreach($randomItems as $item)

                    <a href="{{ $item['url'] }}"
                       class="ro-card text-decoration-none">

                        <div class="ro-image-wrap">

                            <img src="{{ $item['poster'] }}"
                                 class="ro-poster"
                                 loading="lazy"
                                 alt="{{ $item['title'] }}">

                            @if($item['rating'] > 0)

                                <span class="ro-rating"><i class="bi bi-star-fill"></i> {{ $item['rating'] }}</span>

                            @endif

                            <div class="ro-overlay">

                                <div class="ro-title">{{ $item['title'] }}</div>

                                @if($item['year'])

                                    <div class="ro-year">{{ $item['year'] }}</div>

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
   RANDOM ONLINE – movies / series panels on home page
   Poster card with info overlaid on the image.
========================================================== */
.ro-wrapper {
    overflow: hidden;
    border: 1px solid var(--ui-border);
    border-radius: 1rem;
    background: linear-gradient(135deg, rgba(22,32,51,.94), rgba(15,23,42,.88));
    box-shadow: 0 12px 32px rgba(0,0,0,.25);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.ro-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .65rem;
    background: rgba(45,212,191,.12);
    color: var(--ui-accent, #2dd4bf);
    font-size: 1.05rem;
    flex-basis: 42px;
}
.ro-body {
    padding: .85rem;
    flex: 1;
}

/* Grid on desktop: 3 columns x 2 rows */
.ro-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

/* Card: full-bleed poster with overlay info */
.ro-card {
    display: block;
    position: relative;
    aspect-ratio: 2 / 3;
    border-radius: .6rem;
    background: rgba(15,23,42,.7);
    border: 1px solid rgba(255,255,255,.06);
    overflow: hidden;
    text-decoration: none;
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.ro-card:hover {
    transform: translateY(-3px);
    border-color: rgba(45,212,191,.25);
    box-shadow: 0 10px 24px rgba(0,0,0,.3);
}

/* Poster */
.ro-image-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
    background: #0f172a;
}
.ro-poster {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    transition: transform .25s ease;
}
.ro-card:hover .ro-poster {
    transform: scale(1.04);
}

/* Rating pill */
.ro-rating {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 6px;
    border-radius: .4rem;
    background: rgba(15,23,42,.82);
    border: 1px solid rgba(255,255,255,.14);
    color: #facc15;
    font-size: 10.5px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,.25);
    pointer-events: none;
}

/* Overlay info */
.ro-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: .55rem .6rem;
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, .82),
        rgba(0, 0, 0, .18) 55%,
        rgba(0, 0, 0, .15) 72%,
        transparent
    );
    pointer-events: none;
}
.ro-title {
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0,0,0,.6);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.ro-year {
    color: rgba(255,255,255,.78);
    font-size: .68rem;
    font-weight: 600;
    margin-top: 2px;
}

/* Mobile: horizontal scroll up to 6 */
@media (max-width: 767.98px) {
    .ro-body {
        padding: .65rem;
    }
    .ro-row {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(45,212,191,.25) transparent;
        gap: 10px;
        padding-bottom: 6px;
    }
    .ro-row::-webkit-scrollbar {
        height: 5px;
    }
    .ro-row::-webkit-scrollbar-thumb {
        background: rgba(45,212,191,.22);
        border-radius: 10px;
    }
    .ro-card {
        flex: 0 0 130px;
        width: 130px;
        scroll-snap-align: start;
    }
}
</style>