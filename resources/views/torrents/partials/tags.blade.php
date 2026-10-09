<style>
.badge-group {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

/* Base badge */
.badge-btn {
    cursor: pointer;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;

    padding: 2px 7px;

    border-radius: 6px;

    font-family: 'Poppins', Helvetica, sans-serif;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;

    line-height: 1.1;

    white-space: nowrap;

    position: relative;
    overflow: hidden;

    color: var(--theme-text, #f5f5f5);

    border: 1px solid var(--theme-border, rgba(255,255,255,.08));

    text-shadow: 0 1px 1px var(--theme-shadow, rgba(0,0,0,.2));

    transition:
        transform .15s ease,
        box-shadow .2s ease,
        opacity .2s ease;

    backdrop-filter: blur(4px);

    box-shadow:
        inset 0 0 10px var(--theme-shadow, rgba(255,255,255,.02)),
        0 1px 3px var(--theme-shadow, rgba(0,0,0,.15));
}

/* Icons */
.badge-btn i {
    font-size: 9px;
    line-height: 1;
}

/* Hover */
.badge-btn:hover {
    transform: translateY(-1px);
    opacity: .95;
}

/* Shine effect */
.badge-btn::after {
    content: '';

    position: absolute;

    top: -50%;
    left: -60%;

    width: 180%;
    height: 180%;

    background:
        linear-gradient(
            120deg,
            transparent 35%,
            var(--theme-surface-alt, rgba(255,255,255,0.084)) 50%,
            transparent 65%
        );

    transform: translateX(-100%) rotate(25deg);

    transition: .6s ease;

    opacity: 0;
}

.badge-btn:hover::after {
    transform: translateX(100%) rotate(25deg);
    opacity: 1;
}

/* Variants */

.free-btn {
    background: linear-gradient(135deg, var(--theme-surface, #1a2b21), var(--theme-green-action, #52d68d));
}

.double-btn {
    background: linear-gradient(135deg, var(--theme-surface, #2a2232), var(--theme-purple-action, #9567ff));
}

.new-btn {
    background: linear-gradient(135deg, var(--theme-surface, #1d3740), var(--theme-blue-action, #2496d1));
}

.recommended-btn {
    background: linear-gradient(135deg, var(--theme-amber-action, #c89500), var(--theme-amber-action, #ffbf00));
    color: var(--theme-on-action, #1b1b1b);
    text-shadow: none;
}

.seedbox-btn {
    background: linear-gradient(135deg, var(--theme-surface, #321f25), var(--theme-pink-action, #dc0c5c));
}

.bump-btn {
    background: linear-gradient(135deg, var(--theme-surface, #22351b), var(--theme-teal-action, #28c7d9));
}

.subtitles-btn {
    background: linear-gradient(135deg, var(--theme-surface, #241a33), var(--theme-purple-action, #8a63d2));
}

.happyhour-btn {
    background: linear-gradient(135deg, var(--theme-amber-action, #7a4b00), var(--theme-amber-action, #ff9800));
}

.sticky-btn {
    background: linear-gradient(135deg, var(--theme-surface, #313539), var(--theme-surface, #8f98a1));
}

/* Light-mode tags use tinted surfaces rather than dark action gradients. */
html[data-bs-theme="light"] .torrent-tags .badge-btn {
    background: var(--theme-surface-alt);
    color: var(--theme-text);
    border-color: var(--theme-border);
    text-shadow: none;
    box-shadow: none;
    backdrop-filter: none;
}

html[data-bs-theme="light"] .torrent-tags :is(.free-btn, .bg-success) {
    background: var(--theme-green-soft) !important;
    color: var(--theme-green-text);
    border: 1px solid var(--theme-green-border);
}

html[data-bs-theme="light"] .torrent-tags :is(.double-btn, .subtitles-btn) {
    background: var(--theme-purple-soft) !important;
    color: var(--theme-purple-text);
    border: 1px solid var(--theme-purple-border);
}

html[data-bs-theme="light"] .torrent-tags :is(.new-btn) {
    background: var(--theme-blue-soft) !important;
    color: var(--theme-blue-text);
    border: 1px solid var(--theme-blue-border);
}

html[data-bs-theme="light"] .torrent-tags :is(.recommended-btn, .happyhour-btn) {
    background: var(--theme-amber-soft) !important;
    color: var(--theme-amber-text);
    border: 1px solid var(--theme-amber-border);
}

html[data-bs-theme="light"] .torrent-tags :is(.seedbox-btn) {
    background: var(--theme-pink-soft) !important;
    color: var(--theme-pink-text);
    border: 1px solid var(--theme-pink-border);
}

html[data-bs-theme="light"] .torrent-tags :is(.bump-btn) {
    background: var(--theme-teal-soft) !important;
    color: var(--theme-teal-text);
    border: 1px solid var(--theme-teal-border);
}

</style>

<div class="badge-group torrent-tags">
    @if($torrent->isHot())
        <span class="badge-btn recommended-btn" title="Hot: recent download activity"><i class="bi bi-fire" aria-hidden="true"></i> Hot</span>
    @endif
    @if (isset($newTorrents) && $newTorrents->contains($torrent))
        <div class="badge-btn new-btn" data-bs-toggle="tooltip" title="Newly uploaded torrent">
            <i class="bi bi-star-fill"></i> New
        </div>
    @endif

    @if($torrent->double)
        <div class="badge-btn double-btn" data-bs-toggle="tooltip" title="Double upload credit">
            <i class="bi bi-arrow-up-circle-fill"></i> Double
        </div>
    @endif

    @if($torrent->free)
        <div class="badge-btn free-btn" data-bs-toggle="tooltip" title="Free torrent — download doesn’t count">
            <i class="bi bi-gift-fill"></i> Free
        </div>
    @endif

    @if($torrent->recommended)
        <div class="badge-btn recommended-btn" data-bs-toggle="tooltip" title="Recommended by staff">
            <i class="bi bi-award-fill"></i> Rec
        </div>
    @endif

    @if($torrent->seedbox)
        <div class="badge-btn seedbox-btn" data-bs-toggle="tooltip" title="Uploaded from a seedbox (fast speeds)">
            <i class="bi bi-lightning-charge-fill"></i> Seedbox
        </div>
    @endif

@if($torrent->bumped_at)
    <div class="badge-btn bump-btn"
         data-bs-toggle="tooltip"
         title="Bumped by {{ optional($torrent->bumper)->name ?? 'Unknown' }}">
        <i class="bi bi-arrow-clockwise"></i>Bumped
    </div>
@endif




    @if ($torrent->external)
    <span class="badge bg-success" data-bs-toggle="tooltip" title="For this torrent the download is not registered. The upload is registered only 5%">
        🌍 Hybrid · Ratio Free · DHT Enabled
    </span>
@endif


    {{-- Subtitles badge --}}
    @if($torrent->subtitles_exists)
    <div class="badge-btn subtitles-btn"
         data-bs-toggle="tooltip"
         title="This torrent has external subtitles included">
        <i class="bi bi-badge-cc"></i> Subtitles
    </div>
@endif

    {{-- Happy Hour badge --}}
    @if(!empty($currentHappyHour))
        <div class="badge-btn happyhour-btn" data-bs-toggle="tooltip" 
             title="{{ $currentHappyHour->theme }} Happy Hour — 
             {{ $currentHappyHour->upload_multiplier }}x Upload @if($currentHappyHour->free_download) + Free Download @endif">
            <i class="bi bi-lightning-fill"></i> Happy Hour
        </div>
    @endif
</div>
