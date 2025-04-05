<style>
    .badge-btn {
        cursor: pointer;
        padding: 1px 4px; /* Reduced padding for smaller size */
        border-radius: 15px; /* Slightly smaller border radius */
        font-family: Poppins, Helvetica, 'sans-serif';
        font-size: 10px; /* Reduced font size */
        font-weight: 400;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px; /* Reduced margin for closer grouping */
        transition: transform 0.2s ease-in-out;
    }

    .free-btn {
        background-color: #28a745;
        border: 1px solid #1EC621;
        color: white;
    }

    .free-btn:hover {
        background-color: #218838;
        transform: scale(1.05);
    }

    .sticky-btn {
        background-color: #343a40;
        border: 1px solid #dc3545;
        color: white;
    }

    .sticky-btn:hover {
        background-color: #23272b;
        transform: scale(1.05);
    }

    .double-btn {
        background-color: #6f42c1;
        border: 1px solid #9200FF;
        color: white;
    }

    .double-btn:hover {
        background-color: #5a32a3;
        transform: scale(1.05);
    }

    .new-btn {
        background-color: grey;
        border: 1px solid #FFC90E;
        color: white;
    }

    .new-btn:hover {
        background-color: #e0a800;
        transform: scale(1.05);
    }

    

    .recommended-btn {
        background-color: #343a40;
        border: 1px solid #FFC90E;
        color: white;
    }
    .recommended-btn:hover {
        background-color: #23272b;
        transform: scale(1.05);
    }

    .seedbox-btn {
        background-color: #343a40;
        border: 1px solid #FFC90E;
        color: white;
    }

    .seedbox-btn:hover {
        background-color: #23272b;
        transform: scale(1.05);
    }
</style>


<!-- Sticky Badge -->

@if (isset($newTorrents) && $newTorrents->contains($torrent))
    <div class="badge-btn new-btn">New</div>
@endif
@if($torrent->sticky)
    <div class="badge-btn sticky-btn" data-bs-toggle="tooltip" title="This torrent will stay on top of every other torrent">Sticky</div>
@endif

<!-- Double Badge -->
@if($torrent->double)
    <div class="badge-btn double-btn" data-bs-toggle="tooltip" title="This torrent is counted as double upload!">Double</div>
@endif

<!-- Free Badge -->
@if($torrent->free)
    <div class="badge-btn free-btn" data-bs-toggle="tooltip" title="This torrentis free. Download does not count!">Free</div>
@endif

<!-- Recommended Badge -->
@if($torrent->recommended)
    <div class="badge-btn recommended-btn" data-bs-toggle="tooltip" title="This torrent is recommended by staff!">Recommended</div>
@endif

<!-- Seedbox Badge -->
@if($torrent->seedbox)
    <div class="badge-btn seedbox-btn" data-bs-toggle="tooltip" title="This torrent was upload with a seedbox. Downloading speeds are higher!">Seedbox</div>
@endif

<!-- Bumped Badge -->
@if($torrent->bumped)
    <div class="badge-btn bump-btn" data-bs-toggle="tooltip" title="This torrent was bumped to an earlier date. Download it while it's here!"><i class="fa fa-refresh" style="font-size:16px" aria-hidden="true"></i></div>
@endif