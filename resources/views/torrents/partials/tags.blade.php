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
    font-size: 9px;
    font-weight: 600;

    line-height: 1.1;

    white-space: nowrap;

    position: relative;
    overflow: hidden;

    color: #f5f5f5;

    border: 1px solid rgba(255,255,255,.08);

    text-shadow: 0 1px 1px rgba(0,0,0,.2);

    transition:
        transform .15s ease,
        box-shadow .2s ease,
        opacity .2s ease;

    backdrop-filter: blur(4px);

    box-shadow:
        inset 0 0 10px rgba(255,255,255,.02),
        0 1px 3px rgba(0,0,0,.15);
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
            rgba(255,255,255,.12) 50%,
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
    background: linear-gradient(135deg, #284233, #52d68d);
}

.double-btn {
    background: linear-gradient(135deg, #40344d, #9567ff);
}

.new-btn {
    background: linear-gradient(135deg, #2d5563, #2496d1);
}

.recommended-btn {
    background: linear-gradient(135deg, #c89500, #ffbf00);
    color: #1b1b1b;
    text-shadow: none;
}

.seedbox-btn {
    background: linear-gradient(135deg, #4d2f39, #dc0c5c);
}

.bump-btn {
    background: linear-gradient(135deg, #35522a, #28c7d9);
}

.subtitles-btn {
    background: linear-gradient(135deg, #38284f, #8a63d2);
}

.happyhour-btn {
    background: linear-gradient(135deg, #7a4b00, #ff9800);
}

.sticky-btn {
    background: linear-gradient(135deg, #4b5258, #8f98a1);
}
</style>

<div class="badge-group">
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
    @if($torrent->subtitles->isNotEmpty())
        <div class="badge-btn subtitles-btn" data-bs-toggle="tooltip" title="This torrent has external subtitles included">
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
