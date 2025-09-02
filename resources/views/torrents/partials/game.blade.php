<div style="display:block; height:50px"></div>

<div class="row">
    <!-- LEFT COLUMN: Poster + Trailer Button -->
    <div class="col-md-4 text-center mb-3">
        <img src="{{$torrent->poster}}" 
             style="border-radius:12px; box-shadow:0 14px 28px rgba(0,0,0,0.25),0 10px 10px rgba(0,0,0,0.22); width:100%;">

        @if(isset($steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']))
            <button class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#trailerModal">
                <i class="bi bi-play-circle-fill"></i> Watch Trailer
            </button>
        @endif

        @if(isset($steamData[$torrent->steamid]['data']['screenshots']))
            <h5 class="mt-3">Screenshots</h5>
            <div id="screenshotsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($steamData[$torrent->steamid]['data']['screenshots'] as $key => $shot)
                        <div class="carousel-item @if($key==0) active @endif">
                            <img src="{{ $shot['path_thumbnail'] }}" class="d-block w-100 rounded">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#screenshotsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#screenshotsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        @endif
    </div>

    <!-- RIGHT COLUMN: Game Info -->
    <div class="col-md-8">
        <div class="tagss mb-2">Games</div>

        <h4>{{ $steamData[$torrent->steamid]['data']['name'] ?? 'N/A' }}
            @if(isset($steamData[$torrent->steamid]['data']['release_date']['date']))
                ({{ \Carbon\Carbon::parse($steamData[$torrent->steamid]['data']['release_date']['date'])->format('F j, Y') }})
            @endif
        </h4>

        <!-- Genres -->
        <div class="mb-2">
            @foreach($torrent->genres as $genre)
                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary">{{ $genre->name }}</a>
            @endforeach
        </div>

        <!-- Short Description -->
        @if(isset($steamData[$torrent->steamid]['data']['short_description']))
            <p><i class="bi bi-info-circle-fill"></i> {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['short_description']) !!}</p>
        @endif

        <!-- Developer & Publisher -->
        @if(!empty($steamData[$torrent->steamid]['data']['developers']))
            <p><b>Developer:</b> {{ implode(', ', $steamData[$torrent->steamid]['data']['developers']) }}</p>
        @endif
        @if(!empty($steamData[$torrent->steamid]['data']['publishers']))
            <p><b>Publisher:</b> {{ implode(', ', $steamData[$torrent->steamid]['data']['publishers']) }}</p>
        @endif

        <!-- Metacritic -->
        @if(isset($steamData[$torrent->steamid]['data']['metacritic']['score']))
            <p><b>Metacritic:</b> {{ $steamData[$torrent->steamid]['data']['metacritic']['score'] }}/100</p>
        @endif

        <!-- Price -->
        @if(isset($steamData[$torrent->steamid]['data']['price_overview']['final_formatted']))
            <p><b>Price:</b> {{ $steamData[$torrent->steamid]['data']['price_overview']['final_formatted'] }}</p>
        @endif

        <!-- Languages -->
        @if(isset($steamData[$torrent->steamid]['data']['supported_languages']))
            <p><b>Languages:</b> {!! $steamData[$torrent->steamid]['data']['supported_languages'] !!}</p>
        @endif

        <!-- Categories -->
        @if(isset($steamData[$torrent->steamid]['data']['categories']))
            <p><b>Categories:</b>
                @foreach($steamData[$torrent->steamid]['data']['categories'] as $cat)
                    <span class="badge bg-info">{{ $cat['description'] }}</span>
                @endforeach
            </p>
        @endif

        <!-- About the game -->
        @if(isset($steamData[$torrent->steamid]['data']['about_the_game']))
            <div style="max-height:300px; overflow-y:auto; border-left: 3px solid #dc3545; padding-left:10px; margin-bottom:10px;">
                {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['about_the_game']) !!}
            </div>
        @endif

        <!-- System Requirements -->
        <div class="row">
            @if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']))
                <div class="col-md-6">
                    <h6>Minimum Requirements</h6>
                    {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']) !!}
                </div>
            @endif
            @if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']))
                <div class="col-md-6">
                    <h6>Recommended Requirements</h6>
                    {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']) !!}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Trailer Modal -->
@if(isset($steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']))
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">{{ $steamData[$torrent->steamid]['data']['name'] ?? 'Trailer' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="videoWrapper">
                    <video id="trailerVideo" controls preload="none" style="width:100%;">
                        <source src="{{ $steamData[$torrent->steamid]['data']['movies'][0]['webm']['max'] }}" type="video/webm">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const trailerModal = document.getElementById('trailerModal');
    const trailerVideo = document.getElementById('trailerVideo');

    trailerModal.addEventListener('shown.bs.modal', () => {
        trailerVideo.play();
    });

    trailerModal.addEventListener('hidden.bs.modal', () => {
        trailerVideo.pause();
        trailerVideo.currentTime = 0;
    });
</script>
@endif

<style>
    .tagss {
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .videoWrapper {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
    }

    .videoWrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    body::before {
        content: '';
        position: fixed;
        top: 55px;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('{{$torrent->background}}');
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0.7;
        z-index: -1;
    }

   .carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(220, 53, 69, 0.8); /* Red circle background */
    border-radius: 50%;                      /* Make it circular */
    width: 50px;                             /* Bigger size */
    height: 50px;
    background-size: 50%, 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6); /* Subtle shadow */
}

.carousel-control-prev,
.carousel-control-next {
    width: 60px;     /* Ensure clickable area is larger */
    height: 60px;
    top: 50%;
    transform: translateY(-50%);
}

.carousel-control-prev:hover .carousel-control-prev-icon,
.carousel-control-next:hover .carousel-control-next-icon {
    background-color: rgba(220, 53, 69, 1); /* Fully opaque on hover */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.7); /* Slightly stronger on hover */
}
</style>
