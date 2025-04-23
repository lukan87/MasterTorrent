<style>
    .badge-btn {
        cursor: pointer;
        padding: 3px 8px;
        border-radius: 12px;
        font-family: 'Poppins', Helvetica, sans-serif;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        letter-spacing: 0.5px;
        line-height: 1.2;
        position: relative;
        overflow: hidden;
    }

    .badge-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(30deg);
        transition: all 0.3s ease;
        opacity: 0;
    }

    .badge-btn:hover::after {
        opacity: 1;
        top: -20%;
        left: -20%;
    }

    .badge-btn i {
        margin-right: 3px;
        font-size: 10px;
    }

    /* Free Badge */
    .free-btn {
        background: linear-gradient(135deg, #28a745 0%, #1EC621 100%);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .free-btn:hover {
        background: linear-gradient(135deg, #218838 0%, #1EC621 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    /* Sticky Badge */
    .sticky-btn {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        border: 1px solid #dc3545;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .sticky-btn:hover {
        background: linear-gradient(135deg, #23272b 0%, #343a40 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    /* Double Badge */
    .double-btn {
        background: linear-gradient(135deg, #6f42c1 0%, #9200FF 100%);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .double-btn:hover {
        background: linear-gradient(135deg, #5a32a3 0%, #7B00D4 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(111, 66, 193, 0.3);
    }

    /* New Badge */
    .new-btn {
        background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
        border: 1px solid #FFC90E;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .new-btn:hover {
        background: linear-gradient(135deg, #5a6268 0%, #868e96 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 201, 14, 0.3);
    }

    /* Recommended Badge */
    .recommended-btn {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        border: 1px solid #FFC90E;
        color: #FFC90E;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .recommended-btn:hover {
        background: linear-gradient(135deg, #23272b 0%, #343a40 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 201, 14, 0.3);
    }

    /* Seedbox Badge */
    .seedbox-btn {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        border: 1px solid #17a2b8;
        color: #17a2b8;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .seedbox-btn:hover {
        background: linear-gradient(135deg, #23272b 0%, #343a40 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
    }

    /* Bump Badge */
    .bump-btn {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .bump-btn:hover {
        background: linear-gradient(135deg, #e36209 0%, #ffb700 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(253, 126, 20, 0.3);
    }

    /* Badge Group Container */
    .badge-group {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        margin: 4px 0;
    }
</style>

<div class="badge-group">
    <!-- Sticky Badge -->
    @if (isset($newTorrents) && $newTorrents->contains($torrent))
        <div class="badge-btn new-btn" data-bs-toggle="tooltip" title="Newly uploaded torrent">
            <i class="bi bi-star-fill"></i> New
        </div>
    @endif
    
    @if($torrent->sticky)
        <div class="badge-btn sticky-btn" data-bs-toggle="tooltip" title="This torrent will stay on top of every other torrent">
            <i class="bi bi-pin-angle-fill"></i> Sticky
        </div>
    @endif

    <!-- Double Badge -->
    @if($torrent->double)
        <div class="badge-btn double-btn" data-bs-toggle="tooltip" title="This torrent is counted as double upload!">
            <i class="bi bi-arrow-up-circle-fill"></i> Double
        </div>
    @endif

    <!-- Free Badge -->
    @if($torrent->free)
        <div class="badge-btn free-btn" data-bs-toggle="tooltip" title="This torrent is free. Download does not count!">
            <i class="bi bi-gift-fill"></i> Free
        </div>
    @endif

    <!-- Recommended Badge -->
    @if($torrent->recommended)
        <div class="badge-btn recommended-btn" data-bs-toggle="tooltip" title="This torrent is recommended by staff!">
            <i class="bi bi-award-fill"></i> Recommended
        </div>
    @endif

    <!-- Seedbox Badge -->
    @if($torrent->seedbox)
        <div class="badge-btn seedbox-btn" data-bs-toggle="tooltip" title="This torrent was uploaded with a seedbox. Downloading speeds are higher!">
            <i class="bi bi-lightning-charge-fill"></i> Seedbox
        </div>
    @endif

    <!-- Bumped Badge -->
    @if($torrent->bumped)
        <div data-bs-toggle="tooltip" title="This torrent was bumped to an earlier date. Download it while it's here!">
            <i class="bi bi-arrow-clockwise"></i>
        </div>
    @endif
</div>