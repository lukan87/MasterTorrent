@if (!empty($mediainfo))
<div class="card mb-4">
    <div class="card-header">
        <h5>Media Info</h5>
    </div>
    <div class="card-body">
        <div class="row">

            <!-- General Information Card -->
            @if (!empty($mediainfo['general']))
            <div class="col-12 col-sm-6 col-xxl-3 mb-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5>General Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            @isset($mediainfo['general']['format'])
                                <li><strong>Format:</strong> {{ $mediainfo['general']['format'] }}</li>
                            @endisset
                            @isset($mediainfo['general']['file_size'])
                                <li><strong>File Size:</strong> {{ number_format($mediainfo['general']['file_size'] / (1024 * 1024 * 1024), 2) }} GB</li>
                            @endisset
                            @isset($mediainfo['general']['duration'])
                                <li><strong>Duration:</strong> {{ $mediainfo['general']['duration'] }}</li>
                            @endisset
                            @isset($mediainfo['general']['bit_rate'])
                                <li><strong>Bit Rate:</strong> {{ $mediainfo['general']['bit_rate'] }}</li>
                            @endisset
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Video Information Card -->
            @if (!empty($mediainfo['video']))
            <div class="col-12 col-sm-6 col-xxl-3 mb-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5>Video Information</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($mediainfo['video'] as $video)
                            <ul class="list-unstyled">
                                @isset($video['format'])
                                    <li><strong>Format:</strong> {{ $video['format'] }}</li>
                                @endisset
                                @isset($video['bit_rate'])
                                    <li><strong>Bit Rate:</strong> {{ $video['bit_rate'] }}</li>
                                @endisset
                                @isset($video['width'])
                                    <li><strong>Width:</strong> {{ trim($video['width']) }} pixels</li>
                                @endisset
                                @isset($video['height'])
                                    <li><strong>Height:</strong> {{ trim($video['height']) }} pixels</li>
                                @endisset
                                @isset($video['frame_rate'])
                                    <li><strong>Frame Rate:</strong> {{ $video['frame_rate'] }}</li>
                                @endisset
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Audio Information Card -->
            @if (!empty($mediainfo['audio']))
            <div class="col-12 col-sm-6 col-xxl-3 mb-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5>Audio Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="audioInfoTabs" role="tablist">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                                <li class="nav-item">
                                    <a class="nav-link {{ $key === 0 ? 'active' : '' }}" id="audio-tab-{{ $key }}-tab" data-bs-toggle="tab" href="#audio-tab-{{ $key }}" role="tab" aria-controls="audio-tab-{{ $key }}" aria-selected="{{ $key === 0 ? 'true' : 'false' }}">Audio {{ $key + 1 }}</a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="audioInfoTabContent">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                                <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}" id="audio-tab-{{ $key }}" role="tabpanel" aria-labelledby="audio-tab-{{ $key }}-tab">
                                    <ul class="list-unstyled">
                                        @isset($audio['format'])
                                            <li><strong>Format:</strong> {{ $audio['format'] }}</li>
                                        @endisset
                                        @isset($audio['bit_rate'])
                                            <li><strong>Bit Rate:</strong> {{ $audio['bit_rate'] }}</li>
                                        @endisset
                                        @isset($audio['channels'])
                                            <li><strong>Channels:</strong> {{ $audio['channels'] }}</li>
                                        @endisset
                                        @isset($audio['language'])
                                            <li><strong>Language:</strong> {{ $audio['language'] }}</li>
                                        @endisset
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Subtitle Information -->
            @if (!empty($mediainfo['text']))
            <div class="col-12 col-sm-6 col-xxl-3 mb-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5>Subtitles</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Languages:</strong>
                            <i>{{ implode(', ', array_column($mediainfo['text'], 'language')) }}</i>
                        </p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif
