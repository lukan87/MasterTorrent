<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Trailer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#trailerModal .modal-content {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.98)), var(--theme-surface, rgba(10,15,27,.96))) !important;
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .1)) !important;
    border-radius: .75rem;
}
#trailerModal .modal-header {
    border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, .1)) !important;
}
#trailerModal .modal-title {
    color: var(--theme-text, #fff);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trailerModal = document.getElementById('trailerModal');
    const trailerIframe = document.getElementById('trailerIframe');

    trailerModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const videoKey = button.getAttribute('data-video-key');
        trailerIframe.src = 'https://www.youtube.com/embed/' + videoKey + '?autoplay=1';
    });

    trailerModal.addEventListener('hidden.bs.modal', function() {
        trailerIframe.src = '';
    });
});
</script>
