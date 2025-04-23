@if ($torrent->images->isNotEmpty())
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3 text-primary">
                <i class="bi bi-images me-2"></i> Screenshots
            </h5>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @foreach ($torrent->images as $image)
                        <div class="swiper-slide">
                            <img
                                src="{{ asset('storage/' . $image->path) }}"
                                alt="Screenshot"
                                class="img-fluid rounded cursor-pointer border shadow-sm"
                                style="height: 220px; object-fit: cover; width: 100%;"
                                data-bs-toggle="modal"
                                data-bs-target="#fullscreenImageModal"
                                data-bs-image="{{ asset('storage/' . $image->path) }}">
                        </div>
                    @endforeach
                </div>

                <!-- Navigation -->
                <div class="swiper-button-next text-primary"></div>
                <div class="swiper-button-prev text-primary"></div>
                <div class="swiper-pagination mt-2"></div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Image Modal -->
    <div class="modal fade" id="fullscreenImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <img id="fullscreenModalImage" src="" class="img-fluid rounded shadow" style="max-height: 90vh;">
                </div>
            </div>
        </div>
    </div>
@endif
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Swiper setup
        new Swiper(".mySwiper", {
            slidesPerView: 1.5,
            spaceBetween: 15,
            loop: true,
            speed: 600,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                992: { slidesPerView: 4 },
                1200: { slidesPerView: 5 },
            },
        });

        // Modal image viewer
        const modalImage = document.getElementById('fullscreenModalImage');
        const imageTriggers = document.querySelectorAll('[data-bs-target="#fullscreenImageModal"]');

        imageTriggers.forEach(img => {
            img.addEventListener('click', function () {
                const imageUrl = this.getAttribute('data-bs-image');
                modalImage.src = imageUrl;
            });
        });
    });
</script>
