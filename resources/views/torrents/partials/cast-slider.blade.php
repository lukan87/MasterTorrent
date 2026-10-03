@if(!empty($display['cast']))
<section class="media-cast mt-3" data-cast-slider aria-label="Featured cast">
    <div class="media-cast-header">
        <h6 class="mb-0"><i class="bi bi-people me-2" aria-hidden="true"></i>Featured Cast</h6>
        <div class="media-cast-controls">
            <button type="button" data-cast-step="-1" aria-label="Previous cast members" aria-controls="media-cast-{{ $torrent->id }}" disabled><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button type="button" data-cast-step="1" aria-label="Next cast members" aria-controls="media-cast-{{ $torrent->id }}" disabled><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="media-cast-track" id="media-cast-{{ $torrent->id }}" tabindex="0" role="region" aria-label="Cast members">
        @foreach($display['cast'] as $actor)
            <div class="media-cast-person"
                 @if(!empty($actor['character']))
                     data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body"
                     data-bs-html="true" data-bs-custom-class="media-cast-tooltip"
                     title="{{ '<strong>' . e($actor['name']) . '</strong><br>Playing as ' . e($actor['character']) }}"
                     tabindex="0"
                 @endif>
                @if(!empty($actor['id']))
                    <a href="{{ route('actors.show', $actor['id']) }}" class="media-cast-profile">
                @else
                    <div class="media-cast-profile">
                @endif
                    <img src="{{ ($actor['photo'] ?? null) ?: '/images/not-found.jpg' }}" alt="{{ $actor['name'] }}" loading="lazy" width="72" height="72">
                    <span class="media-cast-name">{{ $actor['name'] }}</span>
                @if(!empty($actor['id']))
                    </a>
                @else
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</section>

@once
<style>
.media-cast { min-width: 0; max-width: 100%; padding-top: 1rem; border-top: 1px solid rgba(148,163,184,.12); }
.media-cast-header { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .75rem; }
.media-cast-header h6 { color: #dce7f2; font-size: 13px; font-weight: 700; }
.media-cast-header h6 i { color: var(--ui-accent, #63d2c6); }
.media-cast-controls { display: flex; gap: .35rem; }
.media-cast-controls button { display: grid; place-items: center; width: 30px; height: 30px; border: 1px solid rgba(99,210,198,.2); border-radius: 8px; background: rgba(99,210,198,.07); color: var(--ui-accent, #63d2c6); }
.media-cast-controls button:hover:not(:disabled) { background: rgba(99,210,198,.18); }
.media-cast-controls button:disabled { opacity: .3; }
.media-cast-track { display: flex; flex-wrap: nowrap; gap: 12px; overflow-x: auto; padding: 3px 2px 10px; scroll-snap-type: x proximity; scrollbar-width: thin; scrollbar-color: rgba(99,210,198,.3) transparent; }
.media-cast-person { flex: 0 0 84px; min-width: 0; text-align: center; scroll-snap-align: start; }
.media-cast-profile { display: flex; flex-direction: column; align-items: center; gap: .45rem; color: #dce7f2; text-decoration: none; }
.media-cast-profile:hover { color: var(--ui-accent, #63d2c6); }
.media-cast-profile img { width: 72px; height: 72px; object-fit: cover; object-position: center 25%; border-radius: 50%; border: 1px solid rgba(148,163,184,.16); }
.media-cast-name { font-size: 12px; font-weight: 600; line-height: 1.35; overflow-wrap: anywhere; }
.media-cast-tooltip { --bs-tooltip-bg: #0a0f1b; --bs-tooltip-opacity: 1; }
.media-cast-tooltip .tooltip-inner { max-width: 240px; padding: .65rem .85rem; border: 1px solid rgba(99,210,198,.25); border-radius: 10px; color: #cbd5e1; font-size: 12px; line-height: 1.6; box-shadow: 0 8px 24px rgba(0,0,0,.3); }
.media-cast-tooltip strong { color: var(--ui-accent, #63d2c6); }

.media-cast :focus-visible { outline: 2px solid var(--ui-accent, #63d2c6); outline-offset: 2px; }
@media (max-width: 575.98px) {
    .media-cast-person { flex-basis: 76px; }
    .media-cast-profile img { width: 64px; height: 64px; }
}
</style>
@push('scripts')
<script>
document.querySelectorAll('[data-cast-slider]').forEach(function (slider) {
    const track = slider.querySelector('.media-cast-track');
    const previous = slider.querySelector('[data-cast-step="-1"]');
    const next = slider.querySelector('[data-cast-step="1"]');
    function update() {
        previous.disabled = track.scrollLeft <= 1;
        next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
    }
    slider.querySelectorAll('[data-cast-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = track.querySelector('.media-cast-person');
            if (!card) return;
            const step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap);
            track.scrollBy({
                left: Number(button.dataset.castStep) * Math.max(1, Math.floor(track.clientWidth / step)) * step,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'
            });
        });
    });
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    if ('ResizeObserver' in window) new ResizeObserver(update).observe(track);
    update();
});
</script>
@endpush
@endonce
@endif
