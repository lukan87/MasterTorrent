@if (!empty($mediainfo))
@php
    $sectionCount = 0;
    if (!empty($mediainfo['general'])) $sectionCount++;
    if (!empty($mediainfo['video'])) $sectionCount++;
    if (!empty($mediainfo['audio'])) $sectionCount++;
    if (!empty($mediainfo['text'])) $sectionCount++;

    $colClass = 'col-12';
    if ($sectionCount >= 2) $colClass .= ' col-sm-6';
    if ($sectionCount >= 3) $colClass .= ' col-md-4';
    if ($sectionCount >= 4) $colClass .= ' col-xxl-3';
@endphp

<div class="media-info-card card mb-4 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-film me-2"></i>Media Information</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">

            <!-- General Card -->
            @if (!empty($mediainfo['general']))
            <div class="{{ $colClass }}">
                <div class="glass-card h-100">
                    <div class="card-header">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>General
                    </div>
                    <div class="card-body">
                        <ul class="info-list">
                            @isset($mediainfo['general']['format'])
                            <li><i class="bi bi-file-earmark-text text-primary me-2"></i><strong>Format:</strong> {{ $mediainfo['general']['format'] }}</li>
                            @endisset
                            @isset($mediainfo['general']['file_size'])
                            <li><i class="bi bi-hdd text-primary me-2"></i><strong>Size:</strong> {{ number_format($mediainfo['general']['file_size'] / (1024*1024*1024),2) }} GB</li>
                            @endisset
                            @isset($mediainfo['general']['duration'])
                            <li><i class="bi bi-clock text-primary me-2"></i><strong>Duration:</strong> {{ $mediainfo['general']['duration'] }}</li>
                            @endisset
                            @isset($mediainfo['general']['bit_rate'])
                            <li><i class="bi bi-speedometer2 text-primary me-2"></i><strong>Bit Rate:</strong> {{ $mediainfo['general']['bit_rate'] }}</li>
                            @endisset
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Video Card -->
            @if (!empty($mediainfo['video']))
            <div class="{{ $colClass }}">
                <div class="glass-card h-100">
                    <div class="card-header">
                        <i class="bi bi-camera-reels-fill text-danger me-2"></i>Video
                    </div>
                    <div class="card-body">
                        @foreach($mediainfo['video'] as $video)
                        <ul class="info-list">
                            @isset($video['format'])
                            <li><i class="bi bi-filetype-mp4 text-danger me-2"></i><strong>Format:</strong> {{ $video['format'] }}</li>
                            @endisset
                            @isset($video['bit_rate'])
                            <li><i class="bi bi-lightning-charge text-danger me-2"></i><strong>Bit Rate:</strong> {{ $video['bit_rate'] }}</li>
                            @endisset
                            @isset($video['width'])
                            <li><i class="bi bi-aspect-ratio text-danger me-2"></i><strong>Resolution:</strong> {{ $video['width'] }}×{{ $video['height'] }} px</li>
                            @endisset
                            @isset($video['frame_rate'])
                            <li><i class="bi bi-film text-danger me-2"></i><strong>Frame Rate:</strong> {{ $video['frame_rate'] }}</li>
                            @endisset
                        </ul>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Audio Card -->
            @if (!empty($mediainfo['audio']))
            <div class="{{ $colClass }}">
                <div class="glass-card h-100">
                    <div class="card-header">
                        <i class="bi bi-speaker-fill text-success me-2"></i>Audio
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs audio-tabs mb-3" id="audioInfoTabs" role="tablist">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                            <li class="nav-item">
                                <a class="nav-link {{ $key===0?'active':'' }}" id="audio-tab-{{ $key }}-tab" data-bs-toggle="tab" href="#audio-tab-{{ $key }}" role="tab">{{ 'Track '.($key+1) }}</a>
                            </li>
                            @endforeach
                        </ul>
                        <div class="tab-content">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                            <div class="tab-pane fade {{ $key===0?'show active':'' }}" id="audio-tab-{{ $key }}" role="tabpanel">
                                <ul class="info-list">
                                    @isset($audio['format'])
                                    <li><i class="bi bi-file-earmark-music text-success me-2"></i><strong>Format:</strong> {{ $audio['format'] }}</li>
                                    @endisset
                                    @isset($audio['bit_rate'])
                                    <li><i class="bi bi-volume-up text-success me-2"></i><strong>Bit Rate:</strong> {{ $audio['bit_rate'] }}</li>
                                    @endisset
                                    @isset($audio['channels'])
                                    <li><i class="bi bi-soundwave text-success me-2"></i><strong>Channels:</strong> {{ $audio['channels'] }}</li>
                                    @endisset
                                    @isset($audio['language'])
                                    <li><i class="bi bi-translate text-success me-2"></i><strong>Language:</strong> {{ $audio['language'] }}</li>
                                    @endisset
                                </ul>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Subtitles Card -->
            @if (!empty($mediainfo['text']))
            @php
                $languageCounts = [];
                foreach ($mediainfo['text'] as $text) {
                    $lang = $text['language'] ?? 'Unknown';
                    $languageCounts[$lang] = ($languageCounts[$lang] ?? 0) + 1;
                }
            @endphp
            <div class="{{ $colClass }}">
                <div class="glass-card h-100">
                    <div class="card-header"><i class="bi bi-translate text-warning me-2"></i>Subtitles</div>
                   <div class="card-body subtitle-body flex-column">
    <div class="subtitle-languages mb-2">
        @foreach($languageCounts as $language => $count)
            <span class="badge subtitle-badge">{{ $language }} @if($count>1) ({{ $count }}×) @endif</span>
        @endforeach
    </div>

    @if(in_array($torrent->category_id,[2,6,10,12,17,19,25,32,55,57,81]))
        <div class="mt-1">
            <span class="badge romanian-badge">Romanian Subtitle Available</span>
        </div>
    @endif
</div>

                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif
<style>

.media-info-card {
    border-radius: 16px;
    overflow: hidden;
    background: rgba(31, 31, 31, 0.7);
    backdrop-filter: blur(6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.4);
}

.glass-card {
    border: none;
    border-radius: 12px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(4px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.glass-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.5);
}

.glass-card .card-header {
    font-weight: 600;
    font-size: 0.95rem;
    background: transparent;
    border-bottom: none;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: #eee;
}

.info-list i {
    min-width: 20px;
}

.subtitle-body {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.subtitle-badge {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.romanian-badge {
    background: linear-gradient(90deg,#00bfff,#1e90ff);
    color: #fff;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 14px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.subtitle-body {
    display: flex;
    flex-direction: column; /* ensures badges are stacked vertically if needed */
    gap: 6px; /* space between rows */
}

.subtitle-languages {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.mt-1 {
    margin-top: 0.5rem;
}



</style>