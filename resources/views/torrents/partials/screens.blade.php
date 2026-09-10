@if ($torrent->images->isNotEmpty())

<div class="card glass screenshot-panel mb-4 border-0 shadow-sm">

    <div class="card-body">

        <div class="screenshot-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

            <div>
                <h5 class="screenshot-title mb-1">
                    <i class="bi bi-images me-2"></i>
                    Screenshots
                </h5>

                <div class="screenshot-subtitle">
                    Preview images from this torrent
                </div>
            </div>

            <div class="screenshot-count">
                <i class="bi bi-image"></i>
                {{ $torrent->images->count() }}
                {{ $torrent->images->count() === 1 ? 'Image' : 'Images' }}
            </div>

        </div>

        <div class="swiper screenshot-swiper">

            <div class="swiper-wrapper">

                @foreach ($torrent->images as $image)

                    <div class="swiper-slide">

                        <div class="screenshot-card">

                            <img
                                src="{{ asset('storage/' . $image->path) }}"
                                class="screenshot-img"
                                loading="lazy"
                                data-full="{{ asset('storage/' . $image->path) }}"
                                alt="Torrent screenshot {{ $loop->iteration }}"
                            >

                            <div class="screenshot-overlay">
                                <div class="screenshot-view-icon">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            <div class="swiper-button-next screenshot-nav"></div>
            <div class="swiper-button-prev screenshot-nav"></div>
            <div class="swiper-pagination screenshot-pagination"></div>

        </div>

    </div>

</div>

@endif

<style>

/* =========================================================
   FILEIPLAY — SCREENSHOTS
   Dark navy glass + teal forum style
   ========================================================= */

.screenshot-panel {
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );

    border: 1px solid var(--ui-border) !important;
    border-radius: .85rem !important;

    box-shadow: 0 14px 35px rgba(0, 0, 0, .28) !important;

    backdrop-filter: blur(14px);
}

.screenshot-panel .card-body {
    padding: 18px;
}

/* Header */

.screenshot-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.screenshot-title i {
    color: var(--ui-accent);
}

.screenshot-subtitle {
    color: rgba(255, 255, 255, .45);
    font-size: 12px;
}

.screenshot-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 6px 9px;

    border-radius: .5rem;

    background: rgba(45, 212, 191, .06);
    border: 1px solid rgba(45, 212, 191, .18);

    color: var(--ui-accent);

    font-size: 12px;
    font-weight: 600;
}

.screenshot-count i {
    font-size: 12px;
}

/* Swiper */

.screenshot-swiper {
    width: 100%;
    overflow: hidden;
    padding-bottom: 24px;
}

.screenshot-swiper .swiper-wrapper {
    align-items: stretch;
}

.screenshot-swiper .swiper-slide {
    height: auto;
}

/* Screenshot card */

.screenshot-card {
    position: relative;

    overflow: hidden;

    border-radius: .65rem;

    background: #0f172a;

    border: 1px solid rgba(255, 255, 255, .07);

    cursor: pointer;

    box-shadow: 0 8px 20px rgba(0, 0, 0, .28);

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        transform .2s ease;
}

.screenshot-card:hover {
    border-color: rgba(45, 212, 191, .28);

    box-shadow:
        0 12px 26px rgba(0, 0, 0, .35),
        0 0 0 1px rgba(45, 212, 191, .05);

    transform: translateY(-2px);
}

.screenshot-img {
    display: block;

    width: 100%;
    height: 210px;

    object-fit: cover;

    transition: transform .3s ease;
}

.screenshot-card:hover .screenshot-img {
    transform: scale(1.035);
}

/* Hover overlay */

.screenshot-overlay {
    position: absolute;
    inset: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(5, 10, 18, .45);

    opacity: 0;

    transition: opacity .2s ease;
}

.screenshot-card:hover .screenshot-overlay {
    opacity: 1;
}

.screenshot-view-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: rgba(15, 23, 42, .88);

    border: 1px solid rgba(45, 212, 191, .35);

    color: var(--ui-accent);

    box-shadow: 0 8px 20px rgba(0, 0, 0, .35);
}

.screenshot-view-icon i {
    font-size: 17px;
}

/* Swiper navigation */

.screenshot-nav {
    width: 30px;
    height: 30px;

    margin-top: -15px;

    border-radius: 50%;

    background: rgba(10, 17, 30, .88);

    border: 1px solid rgba(45, 212, 191, .20);

    color: var(--ui-accent);
}

.screenshot-nav::after {
    font-size: 11px;
    font-weight: 700;
}

.screenshot-nav:hover {
    background: rgba(45, 212, 191, .10);
    border-color: rgba(45, 212, 191, .38);
}

.screenshot-pagination .swiper-pagination-bullet {
    width: 6px;
    height: 6px;

    background: rgba(255, 255, 255, .35);

    opacity: 1;
}

.screenshot-pagination .swiper-pagination-bullet-active {
    width: 18px;

    border-radius: 5px;

    background: var(--ui-accent);
}

/* =========================================================
   FULLSCREEN VIEWER
   ========================================================= */

.image-viewer {
    position: fixed;

    inset: 0;

    width: 100%;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(3, 7, 18, .96);

    backdrop-filter: blur(8px);

    z-index: 9999;
}

.viewer-img {
    max-width: 92%;
    max-height: 88%;

    border-radius: .65rem;

    border: 1px solid rgba(255, 255, 255, .10);

    box-shadow: 0 20px 60px rgba(0, 0, 0, .55);

    transition:
        opacity .25s ease,
        transform .25s ease;

    opacity: 0;

    transform: scale(.96);

    object-fit: contain;
}

.image-viewer.show .viewer-img {
    opacity: 1;
    transform: scale(1);
}

.viewer-arrow {
    position: absolute;

    top: 50%;

    transform: translateY(-50%);

    display: flex;
    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    border-radius: 50%;

    color: var(--ui-accent);

    background: rgba(15, 23, 42, .88);

    border: 1px solid rgba(45, 212, 191, .22);

    cursor: pointer;

    user-select: none;

    opacity: .8;

    transition:
        opacity .15s ease,
        background .15s ease,
        border-color .15s ease;
}

.viewer-arrow:hover {
    opacity: 1;

    background: rgba(45, 212, 191, .10);

    border-color: rgba(45, 212, 191, .40);
}

.viewer-prev {
    left: 22px;
}

.viewer-next {
    right: 22px;
}

.viewer-counter {
    position: absolute;

    top: 18px;
    right: 22px;

    color: rgba(255, 255, 255, .78);

    font-size: 13px;
    font-weight: 600;

    background: rgba(15, 23, 42, .88);

    border: 1px solid var(--ui-border);

    padding: 6px 10px;

    border-radius: .5rem;
}

.viewer-close {
    position: absolute;

    top: 16px;
    left: 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    border-radius: 50%;

    color: rgba(255, 255, 255, .78);

    background: rgba(15, 23, 42, .88);

    border: 1px solid var(--ui-border);

    font-size: 25px;
    line-height: 1;

    cursor: pointer;

    opacity: .85;

    transition:
        color .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.viewer-close:hover {
    color: var(--ui-accent);

    border-color: rgba(45, 212, 191, .35);

    transform: scale(1.04);
}

/* Mobile */

@media (max-width: 768px) {

    .screenshot-panel .card-body {
        padding: 14px;
    }

    .screenshot-title {
        font-size: 14px;
    }

    .screenshot-img {
        height: 190px;
    }

    .screenshot-nav {
        width: 27px;
        height: 27px;
    }

    .viewer-img {
        max-width: 94%;
        max-height: 82%;
    }

    .viewer-prev {
        left: 10px;
    }

    .viewer-next {
        right: 10px;
    }

    .viewer-counter {
        top: 12px;
        right: 12px;
        font-size: 11px;
    }

    .viewer-close {
        top: 10px;
        left: 10px;
        width: 34px;
        height: 34px;
        font-size: 22px;
    }
}

</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const cards = Array.from(document.querySelectorAll(".screenshot-card"));
    const images = Array.from(document.querySelectorAll(".screenshot-img"));

    let current = 0;
    let viewer = null;

    function openViewer(index) {

        if (!images.length) return;

        current = index;

        viewer = document.createElement("div");
        viewer.className = "image-viewer";

        const img = document.createElement("img");
        img.className = "viewer-img";
        img.alt = "Torrent screenshot";

        const counter = document.createElement("div");
        counter.className = "viewer-counter";

        const close = document.createElement("div");
        close.className = "viewer-close";
        close.innerHTML = "&times;";
        close.setAttribute("aria-label", "Close image viewer");

        const left = document.createElement("div");
        left.className = "viewer-arrow viewer-prev";
        left.innerHTML = "❮";
        left.setAttribute("aria-label", "Previous image");

        const right = document.createElement("div");
        right.className = "viewer-arrow viewer-next";
        right.innerHTML = "❯";
        right.setAttribute("aria-label", "Next image");

        close.onclick = function (e) {
            e.stopPropagation();
            closeViewer();
        };

        left.onclick = function (e) {
            e.stopPropagation();
            prev();
        };

        right.onclick = function (e) {
            e.stopPropagation();
            next();
        };

        viewer.appendChild(close);
        viewer.appendChild(counter);
        viewer.appendChild(left);
        viewer.appendChild(img);
        viewer.appendChild(right);

        document.body.appendChild(viewer);

        document.body.style.overflow = "hidden";

        requestAnimationFrame(() => {
            viewer.classList.add("show");
        });

        updateImage();
    }

    function updateImage() {

        if (!viewer || !images.length) return;

        const img = viewer.querySelector(".viewer-img");
        const counter = viewer.querySelector(".viewer-counter");

        if (!img || !counter) return;

        img.style.opacity = 0;

        const newSrc = images[current].dataset.full;

        setTimeout(() => {

            if (!viewer) return;

            img.src = newSrc;
            img.style.opacity = 1;

            counter.textContent =
                (current + 1) + " / " + images.length;

            const nextIndex =
                (current + 1) % images.length;

            const prevIndex =
                (current - 1 + images.length) % images.length;

            const preloadNext = new Image();
            preloadNext.src =
                images[nextIndex].dataset.full;

            const preloadPrev = new Image();
            preloadPrev.src =
                images[prevIndex].dataset.full;

        }, 100);
    }

    function closeViewer() {

        if (viewer) {

            viewer.remove();

            viewer = null;

            document.body.style.overflow = "";
        }
    }

    function next() {

        if (!viewer || !images.length) return;

        current =
            (current + 1) % images.length;

        updateImage();
    }

    function prev() {

        if (!viewer || !images.length) return;

        current =
            (current - 1 + images.length) % images.length;

        updateImage();
    }

    cards.forEach((card, i) => {

        card.addEventListener("click", function (e) {

            if (
                e.target.closest(".swiper-button-next") ||
                e.target.closest(".swiper-button-prev")
            ) {
                return;
            }

            openViewer(i);
        });

    });

    document.addEventListener("keydown", function (e) {

        if (!viewer) return;

        if (e.key === "Escape") {
            closeViewer();
        }

        if (e.key === "ArrowRight") {
            next();
        }

        if (e.key === "ArrowLeft") {
            prev();
        }

    });

    document.addEventListener("click", function (e) {

        if (e.target.classList.contains("image-viewer")) {
            closeViewer();
        }

    });

    const swiperElement =
        document.querySelector(".screenshot-swiper");

    if (swiperElement && typeof Swiper !== "undefined") {

        new Swiper(".screenshot-swiper", {

            slidesPerView: 1.2,

            spaceBetween: 12,

            speed: 500,

            preventClicks: false,

            preventClicksPropagation: false,

            pagination: {
                el: ".screenshot-pagination",
                clickable: true
            },

            navigation: {
                nextEl: ".screenshot-swiper .swiper-button-next",
                prevEl: ".screenshot-swiper .swiper-button-prev"
            },

            breakpoints: {
                476: {
                    slidesPerView: 1
                },

                576: {
                    slidesPerView: 2
                },

                768: {
                    slidesPerView: 3
                },

                992: {
                    slidesPerView: 4
                },

                1200: {
                    slidesPerView: 5
                }
            }

        });

    }

});
</script>
