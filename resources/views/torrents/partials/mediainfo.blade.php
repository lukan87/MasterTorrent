@if (!empty($mediainfo))

@php

    $sections = collect([

        'general'  => !empty($mediainfo['general']),

        'video'    => !empty($mediainfo['video']),

        'audio'    => !empty($mediainfo['audio']),

        'text'     => !empty($mediainfo['text']),

    ])->filter()->count();

    $colClass = 'col-12';

    if ($sections >= 2) $colClass .= 'col-md-4 col-xl-4 col-sm-6';

    if ($sections >= 3) $colClass .= 'col-lg-3 col-xl-4  col-md-6 col-sm-3';

    if ($sections >= 4) $colClass .= 'col-xl-3 col-xxl-3';

@endphp

<div class="modern-mediainfo-wrapper mb-4">

    {{-- HEADER --}}

    <div class="modern-mediainfo-header">

        <div class="d-flex align-items-center gap-3 flex-wrap">

            <div class="mediainfo-icon-box">

                <i class="bi bi-film"></i>

            </div>

            <div>

                <h4 class="mediainfo-title mb-1">

                    Media Information

                </h4>

                <div class="mediainfo-subtitle">

                    Technical details about this release

                </div>

            </div>

        </div>

        <button class="btn mediainfo-raw-btn"

                type="button"

                data-bs-toggle="collapse"

                data-bs-target="#mediainfo-raw">

            <i class="bi bi-code-slash me-1"></i>

            Raw Data

        </button>

    </div>

    {{-- CONTENT --}}

    <div class="modern-mediainfo-body">

        <div class="row g-4">

            {{-- GENERAL --}}

            @if (!empty($mediainfo['general']))

            <div class="{{ $colClass }}">

                <div class="modern-media-card h-100">

                    <div class="modern-media-card-header general-header">

                        <i class="bi bi-info-circle-fill"></i>

                        General

                    </div>

                    <ul class="modern-media-list">

                        @isset($mediainfo['general']['format'])

                        <li>

                            <span>Format</span>

                            <strong>{{ $mediainfo['general']['format'] }}</strong>

                        </li>

                        @endisset

                        @isset($mediainfo['general']['file_size'])

                        <li>

                            <span>Size</span>

                            <strong>

                                {{ number_format($mediainfo['general']['file_size'] / (1024*1024*1024),2) }} GB

                            </strong>

                        </li>

                        @endisset

                        @isset($mediainfo['general']['duration'])

                        <li>

                            <span>Duration</span>

                            <strong>{{ $mediainfo['general']['duration'] }}</strong>

                        </li>

                        @endisset

                        @isset($mediainfo['general']['bit_rate'])

                        <li>

                            <span>Bit Rate</span>

                            <strong>{{ $mediainfo['general']['bit_rate'] }}</strong>

                        </li>

                        @endisset

                        @if (!empty($mediainfo['chapters']))

                        <li>

                            <span>Chapters</span>

                            <strong class="pill-badge">

                                {{ count($mediainfo['chapters']) }}

                            </strong>

                        </li>

                        @endif

                    </ul>

                </div>

            </div>

            @endif

            {{-- VIDEO --}}

            @if (!empty($mediainfo['video']))

            <div class="{{ $colClass }}">

                <div class="modern-media-card h-100">

                    <div class="modern-media-card-header video-header">

                        <i class="bi bi-camera-reels-fill"></i>

                        Video

                    </div>

                    @foreach($mediainfo['video'] as $video)

                    <ul class="modern-media-list">

                        @isset($video['format'])

                        <li>

                            <span>Format</span>

                            <strong>{{ $video['format'] }}</strong>

                        </li>

                        @endisset

                        @isset($video['bit_rate'])

                        <li>

                            <span>Bit Rate</span>

                            <strong>{{ $video['bit_rate'] }}</strong>

                        </li>

                        @endisset

                        @isset($video['width'])

                        <li>

                            <span>Resolution</span>

                            <strong>

                                {{ $video['width'] }}×{{ $video['height'] }}

                            </strong>

                        </li>

                        @endisset

                        @isset($video['frame_rate'])

                        <li>

                            <span>Frame Rate</span>

                            <strong>{{ $video['frame_rate'] }}</strong>

                        </li>

                        @endisset

                    </ul>

                    @endforeach

                </div>

            </div>

            @endif

            {{-- AUDIO --}}

            @if (!empty($mediainfo['audio']))

            <div class="{{ $colClass }}">

                <div class="modern-media-card h-100">

                    <div class="modern-media-card-header audio-header">

                        <i class="bi bi-speaker-fill"></i>

                        Audio

                    </div>

                    <ul class="nav nav-pills modern-audio-pills mb-3">

                        @foreach ($mediainfo['audio'] as $i => $audio)

                        <li class="nav-item">

                            <button class="nav-link {{ $i===0?'active':'' }}"

                                    data-bs-toggle="pill"

                                    data-bs-target="#audio-{{ $i }}">

                                Track {{ $i+1 }}

                            </button>

                        </li>

                        @endforeach

                    </ul>

                    <div class="tab-content">

                        @foreach ($mediainfo['audio'] as $i => $audio)

                        <div class="tab-pane fade {{ $i===0?'show active':'' }}"

                             id="audio-{{ $i }}">

                            <ul class="modern-media-list">

                                @isset($audio['format'])

                                <li>

                                    <span>Format</span>

                                    <strong>{{ $audio['format'] }}</strong>

                                </li>

                                @endisset

                                @isset($audio['bit_rate'])

                                <li>

                                    <span>Bit Rate</span>

                                    <strong>{{ $audio['bit_rate'] }}</strong>

                                </li>

                                @endisset

                                @isset($audio['channels'])

                                <li>

                                    <span>Channels</span>

                                    <strong>{{ $audio['channels'] }}</strong>

                                </li>

                                @endisset

                                @isset($audio['language'])

                                <li>

                                    <span>Language</span>

                                    <strong>{{ $audio['language'] }}</strong>

                                </li>

                                @endisset

                            </ul>

                        </div>

                        @endforeach

                    </div>

                </div>

            </div>

            @endif

            {{-- SUBTITLES --}}

            @if (!empty($mediainfo['text']))

            @php

                $languageCounts = [];

                foreach ($mediainfo['text'] as $t) {

                    $lang = $t['language'] ?? 'Unknown';

                    $languageCounts[$lang] =

                        ($languageCounts[$lang] ?? 0) + 1;

                }

            @endphp

            <div class="{{ $colClass }}">

                <div class="modern-media-card h-100">

                    <div class="modern-media-card-header subtitle-header">

                        <i class="bi bi-translate"></i>

                        Subtitles

                    </div>

                    <div class="subtitle-pill-grid">

                        @foreach($languageCounts as $lang => $count)

                            <span class="subtitle-pill-modern">

                                {{ $lang }}

                                @if($count > 1)

                                    ×{{ $count }}

                                @endif

                            </span>

                        @endforeach

                    </div>

                    @if(in_array($torrent->category_id,[2,6,10,12,17,19,25,32,55,57,81]))

                        <div class="romanian-pill">

                            Romanian Subtitle Available

                        </div>

                    @endif

                </div>

            </div>

            @endif

        </div>

    </div>

</div>

{{-- RAW --}}

<div class="collapse mt-3 mb-5" id="mediainfo-raw">

    <div class="modern-raw-card">

<pre>{{ json_encode($mediainfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

    </div>

</div>

@endif



<script>

document.getElementById('mediainfo-raw')

?.addEventListener(

    'shown.bs.collapse',

    () => localStorage.setItem('mi_raw', 1)

);

document.getElementById('mediainfo-raw')

?.addEventListener(

    'hidden.bs.collapse',

    () => localStorage.removeItem('mi_raw')

);

</script>


<style>
/* =========================================================
   FileIplay MediaInfo — Forum Style
   ========================================================= */

.modern-mediainfo-wrapper {
    position: relative;
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
    border: 1px solid var(--ui-border);
    border-left: 3px solid var(--ui-accent);
    border-radius: .9rem;
    backdrop-filter: blur(14px);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.22);
    color: #e6edf3;
}

.modern-mediainfo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding: 15px 18px;
    border-bottom: 1px solid var(--ui-border);
}

.mediainfo-icon-box {
    width: 44px;
    height: 44px;
    border-radius: .65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(45,212,191,.10);
    border: 1px solid rgba(45,212,191,.22);
    color: var(--ui-accent);
    font-size: 1.1rem;
    box-shadow: none;
}

.mediainfo-title {
    font-size: 14px;
    line-height: 1.4;
    font-weight: 700;
    color: #f1f5f9;
}

.mediainfo-subtitle {
    color: rgba(255,255,255,.58);
    font-size: 13px;
}

.mediainfo-raw-btn {
    border: 1px solid var(--ui-border);
    background: rgba(255,255,255,.045);
    color: #cbd5e1;
    border-radius: .55rem;
    padding: 7px 11px;
    font-size: 13px;
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}

.mediainfo-raw-btn:hover {
    background: rgba(45,212,191,.09);
    border-color: rgba(45,212,191,.25);
    color: var(--ui-accent);
}

.modern-mediainfo-body {
    padding: 16px 18px;
}

.modern-media-card {
    background: linear-gradient(135deg, rgba(22,32,51,.78), rgba(15,23,42,.68));
    border: 1px solid var(--ui-border);
    border-radius: .75rem;
    padding: 14px;
    backdrop-filter: blur(8px);
    transition: transform .15s ease, border-color .15s ease, background .15s ease;
}

.modern-media-card:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, rgba(24,38,58,.84), rgba(15,23,42,.72));
    border-color: rgba(45,212,191,.20);
}

.modern-media-card-header {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 12px;
}

.general-header,
.video-header,
.audio-header,
.subtitle-header {
    color: var(--ui-accent);
}

.modern-media-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.modern-media-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,.055);
    font-size: 13px;
}

.modern-media-list li:last-child {
    border-bottom: none;
}

.modern-media-list span {
    color: rgba(255,255,255,.58);
    font-weight: 600;
}

.modern-media-list strong {
    color: #edf2f7;
    text-align: right;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.pill-badge {
    background: rgba(45,212,191,.09);
    color: var(--ui-accent);
    border: 1px solid rgba(45,212,191,.18);
    padding: 3px 8px;
    border-radius: 999px;
}

.modern-audio-pills {
    gap: 6px;
    flex-wrap: wrap;
}

.modern-audio-pills .nav-link {
    border: 1px solid var(--ui-border);
    border-radius: .5rem;
    background: rgba(255,255,255,.045);
    color: #bfcbd6;
    font-size: 12px;
    padding: 5px 9px;
    font-weight: 600;
}

.modern-audio-pills .nav-link:hover {
    background: rgba(45,212,191,.08);
    color: var(--ui-accent);
}

.modern-audio-pills .nav-link.active {
    background: rgba(45,212,191,.12);
    border-color: rgba(45,212,191,.28);
    color: var(--ui-accent);
}

.subtitle-pill-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.subtitle-pill-modern {
    background: rgba(45,212,191,.07);
    border: 1px solid rgba(45,212,191,.16);
    color: #b9eee8;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.romanian-pill {
    margin-top: 12px;
    padding: 7px 10px;
    border-radius: .55rem;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    background: rgba(45,212,191,.08);
    border: 1px solid rgba(45,212,191,.18);
    color: var(--ui-accent);
}

.modern-raw-card {
    background: rgba(8,15,28,.78);
    border: 1px solid var(--ui-border);
    border-radius: .75rem;
    padding: 14px;
}

.modern-raw-card pre {
    margin: 0;
    color: #d7e0e8;
    font-size: 12px;
    line-height: 1.5;
    max-height: 500px;
    overflow: auto;
    white-space: pre-wrap;
    word-break: break-word;
}

@media (max-width: 768px) {
    .modern-mediainfo-header {
        padding: 13px 14px;
    }

    .modern-mediainfo-body {
        padding: 13px 14px;
    }

    .mediainfo-icon-box {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .mediainfo-title {
        font-size: 14px;
    }

    .mediainfo-subtitle {
        font-size: 12px;
    }

    .modern-media-card {
        padding: 12px;
    }

    .modern-media-card-header {
        font-size: 14px;
    }

    .modern-media-list li {
        font-size: 13px;
    }
}
</style>

