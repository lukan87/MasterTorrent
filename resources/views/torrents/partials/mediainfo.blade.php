@if (!empty($mediainfo))
@php
    // Count how many sections we have
    $sectionCount = 0;
    if (!empty($mediainfo['general'])) $sectionCount++;
    if (!empty($mediainfo['video'])) $sectionCount++;
    if (!empty($mediainfo['audio'])) $sectionCount++;
    if (!empty($mediainfo['text'])) $sectionCount++;
    
    // Determine column class based on section count
    $colClass = 'col-12';
    if ($sectionCount >= 2) $colClass .= ' col-sm-6';
    if ($sectionCount >= 3) $colClass .= ' col-md-4';
    if ($sectionCount >= 4) $colClass .= ' col-xxl-3';
@endphp

<div class="card mb-4 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-film me-2"></i>Media Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">

            <!-- General Information Card -->
            @if (!empty($mediainfo['general']))
            <div class="{{ $colClass }} mb-4 d-flex">
                <div class="card flex-fill h-100 border-primary">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i>General
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @isset($mediainfo['general']['format'])
                                <li class="mb-2">
                                    <span class="badge bg-light text-dark me-2"><i class="bi bi-file-earmark-text"></i></span>
                                    <strong>Format:</strong> {{ $mediainfo['general']['format'] }}
                                </li>
                            @endisset
                            @isset($mediainfo['general']['file_size'])
                                <li class="mb-2">
                                    <span class="badge bg-light text-dark me-2"><i class="bi bi-hdd"></i></span>
                                    <strong>Size:</strong> {{ number_format($mediainfo['general']['file_size'] / (1024 * 1024 * 1024), 2) }} GB
                                </li>
                            @endisset
                            @isset($mediainfo['general']['duration'])
                                <li class="mb-2">
                                    <span class="badge bg-light text-dark me-2"><i class="bi bi-clock"></i></span>
                                    <strong>Duration:</strong> {{ $mediainfo['general']['duration'] }}
                                </li>
                            @endisset
                            @isset($mediainfo['general']['bit_rate'])
                                <li class="mb-2">
                                    <span class="badge bg-light text-dark me-2"><i class="bi bi-speedometer2"></i></span>
                                    <strong>Bit Rate:</strong> {{ $mediainfo['general']['bit_rate'] }}
                                </li>
                            @endisset
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Video Information Card -->
            @if (!empty($mediainfo['video']))
            <div class="{{ $colClass }} mb-4 d-flex">
                <div class="card flex-fill h-100 border-danger">
                    <div class="card-header bg-danger bg-opacity-10">
                        <h5 class="mb-0">
                            <i class="bi bi-camera-reels-fill text-danger me-2"></i>Video
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach ($mediainfo['video'] as $video)
                            <ul class="list-unstyled mb-0">
                                @isset($video['format'])
                                    <li class="mb-2">
                                        <span class="badge bg-light text-dark me-2"><i class="bi bi-filetype-mp4"></i></span>
                                        <strong>Format:</strong> {{ $video['format'] }}
                                    </li>
                                @endisset
                                @isset($video['bit_rate'])
                                    <li class="mb-2">
                                        <span class="badge bg-light text-dark me-2"><i class="bi bi-lightning-charge"></i></span>
                                        <strong>Bit Rate:</strong> {{ $video['bit_rate'] }}
                                    </li>
                                @endisset
                                @isset($video['width'])
                                    <li class="mb-2">
                                        <span class="badge bg-light text-dark me-2"><i class="bi bi-aspect-ratio"></i></span>
                                        <strong>Resolution:</strong> {{ trim($video['width']) }}×{{ trim($video['height']) }} px
                                    </li>
                                @endisset
                                @isset($video['frame_rate'])
                                    <li class="mb-2">
                                        <span class="badge bg-light text-dark me-2"><i class="bi bi-film"></i></span>
                                        <strong>Frame Rate:</strong> {{ $video['frame_rate'] }}
                                    </li>
                                @endisset
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Audio Information Card -->
            @if (!empty($mediainfo['audio']))
            <div class="{{ $colClass }} mb-4 d-flex">
                <div class="card flex-fill h-100 border-success">
                    <div class="card-header bg-success bg-opacity-10">
                        <h5 class="mb-0">
                            <i class="bi bi-speaker-fill text-success me-2"></i>Audio
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="audioInfoTabs" role="tablist">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                                <li class="nav-item">
                                    <a class="nav-link {{ $key === 0 ? 'active' : '' }}" id="audio-tab-{{ $key }}-tab" data-bs-toggle="tab" href="#audio-tab-{{ $key }}" role="tab" aria-controls="audio-tab-{{ $key }}" aria-selected="{{ $key === 0 ? 'true' : 'false' }}">
                                        <i class="bi bi-music-note-beamed me-1"></i> Track {{ $key + 1 }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3" id="audioInfoTabContent">
                            @foreach ($mediainfo['audio'] as $key => $audio)
                                <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}" id="audio-tab-{{ $key }}" role="tabpanel" aria-labelledby="audio-tab-{{ $key }}-tab">
                                    <ul class="list-unstyled mb-0">
                                        @isset($audio['format'])
                                            <li class="mb-2">
                                                <span class="badge bg-light text-dark me-2"><i class="bi bi-file-earmark-music"></i></span>
                                                <strong>Format:</strong> {{ $audio['format'] }}
                                            </li>
                                        @endisset
                                        @isset($audio['bit_rate'])
                                            <li class="mb-2">
                                                <span class="badge bg-light text-dark me-2"><i class="bi bi-volume-up"></i></span>
                                                <strong>Bit Rate:</strong> {{ $audio['bit_rate'] }}
                                            </li>
                                        @endisset
                                        @isset($audio['channels'])
                                            <li class="mb-2">
                                                <span class="badge bg-light text-dark me-2"><i class="bi bi-soundwave"></i></span>
                                                <strong>Channels:</strong> {{ $audio['channels'] }}
                                            </li>
                                        @endisset
                                        @isset($audio['language'])
                                            <li class="mb-2">
                                                <span class="badge bg-light text-dark me-2"><i class="bi bi-translate"></i></span>
                                                <strong>Language:</strong> {{ $audio['language'] }}
                                            </li>
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
            @php
                // Count language occurrences and prepare display text
                $languageCounts = [];
                foreach ($mediainfo['text'] as $text) {
                    $lang = $text['language'] ?? 'Unknown';
                    $languageCounts[$lang] = ($languageCounts[$lang] ?? 0) + 1;
                }
                
                // Create display strings with counts
                $displayLanguages = [];
                foreach ($languageCounts as $language => $count) {
                    $displayLanguages[] = $count > 1 ? $language.' ('.$count.'×)' : $language;
                }
            @endphp
            
            <div class="{{ $colClass }} mb-4 d-flex">
                <div class="card flex-fill h-100 border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0">
                            <i class="bi bi-subtract text-warning me-2"></i>Subtitles
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li>
                                <span class="badge bg-light text-dark me-2"><i class="bi bi-translate"></i></span>
                                <strong>Languages:</strong> 
                                <span class="text-muted">
                                    {{ implode(', ', $displayLanguages) }}
                                </span>
                            </li>
                            @if(in_array($torrent->category_id, [2, 6, 10, 12, 17, 19, 25, 32, 55, 57, 81]))
                            <li class="mt-2">
                                <span class="badge bg-light text-dark me-2"><i class="bi bi-info-circle"></i></span>
                                <span class="text-info">Romanian Subtitle Available</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif