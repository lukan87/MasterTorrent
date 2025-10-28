<style>
    .badge-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 3px;
        margin: 3px 0;
    }

    .badge-btn {
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 10px;
        font-family: 'Poppins', Helvetica, sans-serif;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.1);
        text-shadow: 0 1px 1px rgba(0,0,0,0.25);
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        line-height: 1;
        white-space: nowrap;
    }

    .badge-btn i {
        margin-right: 3px;
        font-size: 10px;
        line-height: 1;
    }

    .badge-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }

    /* Shine animation */
    .badge-btn::after {
        content: '';
        position: absolute;
        top: -40%;
        left: -40%;
        width: 180%;
        height: 180%;
        background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.15) 50%, transparent 70%);
        transform: translateX(-100%) rotate(25deg);
        transition: 0.6s;
        opacity: 0;
    }

    .badge-btn:hover::after {
        transform: translateX(100%) rotate(25deg);
        opacity: 1;
    }

    /* Color Schemes (kept rich but subtle) */
    .free-btn {
        background: linear-gradient(135deg, #28a745, #38d67a);
    }
    .sticky-btn {
        background: linear-gradient(135deg, #495057, #343a40);
    }
    .double-btn {
        background: linear-gradient(135deg, #7b47cc, #a46bff);
    }
    .new-btn {
        background: linear-gradient(135deg, #0d6efd, #00aaff);
    }
    .recommended-btn {
        background: linear-gradient(135deg, #ffc107, #ffb300);
        color: #1b1b1b;
        text-shadow: none;
    }
    .seedbox-btn {
        background: linear-gradient(135deg, #20c997, #17a2b8);
    }
    .bump-btn {
        background: linear-gradient(135deg, #fd7e14, #ffb300);
    }
</style>

<div class="badge-group">
    @if (isset($newTorrents) && $newTorrents->contains($torrent))
        <div class="badge-btn new-btn" data-bs-toggle="tooltip" title="Newly uploaded torrent">
            <i class="bi bi-star-fill"></i> New
        </div>
    @endif

    @if($torrent->sticky)
        <div class="badge-btn sticky-btn" data-bs-toggle="tooltip" title="Pinned to top">
            <i class="bi bi-pin-angle-fill"></i> Sticky
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
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
    @endif

    @if($torrent->bumped)
        <div class="badge-btn bump-btn" data-bs-toggle="tooltip" title="This torrent was bumped recently">
            <i class="bi bi-arrow-clockwise"></i>
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
