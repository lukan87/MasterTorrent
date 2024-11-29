
<div class="row">
    <div class="col-md-12 col-lg-12">
        <!-- Removed card card-custom card-blur classes -->
        <div class="content-overlay mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-1 order-xxl-0 text-center">
                        <img src="{{$torrent->poster}}"
                             style="border-radius: 12px;
                                    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                                    width:100%;
                                    ">
                    </div>

                    <div class="col-sm-9 col-xxl-10 order-1 order-sm-0 order-xxl-1">

                    <dd class="col-lg-12">
                    <p><h4>Name: {{ $steamData[$torrent->steamid]['data']['name'] ?? 'N/A' }}</h4></p>
                    <p><h5>
                         @if(isset($steamData[$torrent->steamid]['data']['release_date']['date']))
         ({{ \Carbon\Carbon::parse($steamData[$torrent->steamid]['data']['release_date']['date'])->format('F j, Y') }})
    @endif</h5></p>
                    </dd>

<dd class="col-lg-12">
@foreach($torrent->genres as $genre)
                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary" title="Search for {{ $genre->name }} torrents">{{ $genre->name }}</a>
            @endforeach
</dd>

@if(isset($steamData[$torrent->steamid]['data']['short_description']))
    <dd class="col-sm-12">
        <b>
            <font size="3">
                <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="Short Description"></i>&nbsp;&nbsp;{!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['short_description']) !!}
            </font>
        </b>
    </dd>
@endif

@if(isset($steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']))
        <a href="#" onmouseover=this.href="{{$steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']}}"
                    onmouseout="this.href='#'"
                    data-lity>
                <h6><i class="bi bi-camera-reels"></i> Trailer for {{ $steamData[$torrent->steamid]['data']['name'] ?? 'N/A' }}</h6>
        </a>
@endif

<!-- @if(isset($steamData[$torrent->steamid]['data']['about_the_game']))
    <dd class="col-sm-12">
        <b>
            <font size="3">
                <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="About the game"></i>&nbsp;&nbsp;
                <div style="max-height: 400px; overflow-y: auto;">
                    {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['about_the_game']) !!}
                </div>
            </font>
        </b>
    </dd>
@endif -->


<div class="row">
@if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']))
<div class="col-md-6">{!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']) !!}</div>
@endif
@if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']))
<div class="col-md-6">{!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']) !!}</div>
@endif
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                                  <div class="col-12 d-xxl-none">
                                         <h4>Images</h4>
                                    </div>

<div class="row justify-content-center">
@php
        $i = 0; // Initialize counter for cast members
    @endphp


</div>

<style>
    /* Style adjustments for background display */
    .content-overlay {
        background: none; /* Remove card background */
        padding: 20px;
    }

    body::before {
        content: '';
        position: fixed;
/* position: absolute; */
top: 55px;
right: 0;
bottom: 0;
left: 0;
background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('{{$torrent->background}}');
background-position-x: center;
background-size: cover;
background-repeat: no-repeat;
opacity: 0.7;

    }

    .videoWrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
    }

    .videoWrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .actor-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
    }

    .select {
        gap: 3px 20px;
        border-radius: 16px;
        padding: 6px 26px 6px 6px;
        overflow: hidden;
    }

    .select:hover {
        backdrop-filter: brightness(130%) blur(10px);
    }
</style>
