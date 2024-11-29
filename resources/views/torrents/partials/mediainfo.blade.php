
<div class="card mb-4">
    <div class="card-header">
        <h5>Media Info</h5>
    </div>
    <div class="card-body">
<div class="row">

    <!-- General Information Card -->
    <div class="col-12 col-sm-6 col-xxl-3 order-0 order-sm-0 order-xxl-0 mb-4 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5>General Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                <ul class="list-unstyled">
    <!-- @if(isset($mediainfo['general']['file_name']))
        <li><strong>File Name:</strong> {{ $mediainfo['general']['file_name'] }}</li>
    @endif -->
    @if(isset($mediainfo['general']['format']))
        <li><strong>Format:</strong> {{ $mediainfo['general']['format'] }}</li>
    @endif
    @if(isset($mediainfo['general']['file_size']))
        <li><strong>File Size:</strong> {{ number_format($mediainfo['general']['file_size'] / (1024 * 1024 * 1024), 2) }} GB</li>
    @endif
    @if(isset($mediainfo['general']['duration']))
        <li><strong>Duration:</strong> {{ $mediainfo['general']['duration'] }}</li>
    @endif
    @if(isset($mediainfo['general']['bit_rate']))
        <li><strong>Bit Rate:</strong> {{ $mediainfo['general']['bit_rate'] }}</li>
    @endif
</ul>
                </ul>
            </div>
        </div>
    </div>

    <!-- Video Information Card -->
    <div class="col-12 col-sm-6 col-xxl-3 order-0 order-sm-1 order-xxl-1 mb-4 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5>Video Information</h5>
            </div>
            <div class="card-body">
                @if(isset($mediainfo['video']) && count($mediainfo['video']) > 0)
                    @foreach($mediainfo['video'] as $video)

                        <ul class="list-unstyled">
    @if(isset($video['format']))
        <li><strong>Format:</strong> {{ $video['format'] }}</li>
    @endif
    @if(isset($video['format_profile']))
        <li><strong>Format Profile:</strong> {{ $video['format_profile'] }}</li>
    @endif
    @if(isset($video['codec']))
        <li><strong>Codec:</strong> {{ $video['codec'] }}</li>
    @endif
    @if(isset($video['bit_rate']))
        <li><strong>Bit Rate:</strong> {{ $video['bit_rate'] }}</li>
    @endif
    @if(isset($video['width']))
        <li><strong>Width:</strong> {{ trim($video['width']) }} pixels</li>
    @endif
    @if(isset($video['height']))
        <li><strong>Height:</strong> {{ trim($video['height']) }} pixels</li>
    @endif
    @if(isset($video['aspect_ratio']))
        <li><strong>Aspect Ratio:</strong> {{ $video['aspect_ratio'] }}</li>
    @endif
    @if(isset($video['frame_rate']))
        <li><strong>Frame Rate:</strong> {{ $video['frame_rate'] }}</li>
    @endif
    <!-- @if(isset($video['language']))
        <li><strong>Language:</strong> {{ $video['language'] }}</li>
    @endif -->
</ul>
                    @endforeach
                @else
                    <p>No video information available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Audio Information Card -->
    <div class="col-12 col-sm-6 col-xxl-3 order-0 order-sm-2 order-xxl-2 mb-4 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5>Audio Information</h5>
            </div>
            <div class="card-body">
                @if(isset($mediainfo['audio']) && count($mediainfo['audio']) > 0)
                    <!-- Nav Tabs for Audio -->
                    <ul class="nav nav-tabs" id="audioInfoTabs" role="tablist">
                        @foreach($mediainfo['audio'] as $key => $audio)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $key === 0 ? 'active' : '' }}" id="audio-tab-{{ $key }}-tab" data-bs-toggle="tab" href="#audio-tab-{{ $key }}" role="tab" aria-controls="audio-tab-{{ $key }}" aria-selected="{{ $key === 0 ? 'true' : 'false' }}">Audio {{ $key + 1 }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab Content for Audio -->
                    <div class="tab-content" id="audioInfoTabContent">
                        @foreach($mediainfo['audio'] as $key => $audio)
                            <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}" id="audio-tab-{{ $key }}" role="tabpanel" aria-labelledby="audio-tab-{{ $key }}-tab">
                            <ul class="list-unstyled">
    @if(isset($audio['format']))
        <li><strong>Format:</strong> {{ $audio['format'] }}</li>
    @endif
    @if(isset($audio['codec']))
        <li><strong>Codec:</strong> {{ $audio['codec'] }}</li>
    @endif
    @if(isset($audio['bit_rate']))
        <li><strong>Bit Rate:</strong> {{ $audio['bit_rate'] }}</li>
    @endif
    @if(isset($audio['channels']))
        <li><strong>Channels:</strong> {{ $audio['channels'] }}</li>
    @endif
    @if(isset($audio['language']))
        <li><strong>Language:</strong> {{ $audio['language'] }}</li>
    @endif
</ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>No audio information available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Text Information Card -->
    <div class="col-12 col-sm-6 col-xxl-3 order-0 order-sm-3 order-xxl-3 mb-4 d-flex">
    <div class="card flex-fill">
        <div class="card-header">
            <h5>Subtitles</h5>
        </div>
        <div class="card-body">
            @if(isset($mediainfo['text']) && count($mediainfo['text']) > 0)
                <!-- Collect and Display All Languages -->
                <p><strong>Languages:</strong>
                    <i>{{ implode(', ', array_column($mediainfo['text'], 'language')) }}</i>
                </p>
            @else
                <p>No subtitle information available.</p>
            @endif
        </div>
    </div>
</div>

</div>
</div>
</div>
