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

<style>

/* =========================================
   WRAPPER
========================================= */

.modern-mediainfo-wrapper{

    position:relative;

    background:
        linear-gradient(
            145deg,
            rgba(20,25,40,.72),
            rgba(10,14,24,.92)
        );

    border-radius:24px;

    border:1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(18px);

    overflow:hidden;

    box-shadow:
        0 20px 50px rgba(0,0,0,.45);

    color:#fff;
}

/* =========================================
   HEADER
========================================= */

.modern-mediainfo-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    flex-wrap:wrap;

    gap:20px;

    padding:22px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.mediainfo-icon-box{

    width:60px;
    height:60px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    font-size:1.4rem;

    box-shadow:
        0 10px 25px rgba(59,130,246,.35);
}

.mediainfo-title{

    font-size:1.45rem;

    font-weight:800;

    margin:0;
}

.mediainfo-subtitle{

    color:rgba(255,255,255,.58);

    font-size:.92rem;
}

.mediainfo-raw-btn{

    border:none;

    background:rgba(255,255,255,.06);

    color:#fff;

    border-radius:14px;

    padding:10px 16px;

    font-weight:700;

    transition:.25s ease;
}

.mediainfo-raw-btn:hover{

    background:rgba(255,255,255,.1);

    transform:translateY(-2px);

    color:#fff;
}

/* =========================================
   BODY
========================================= */

.modern-mediainfo-body{
    padding:24px;
}

/* =========================================
   CARD
========================================= */

.modern-media-card{

    background:rgba(255,255,255,.045);

    border:1px solid rgba(255,255,255,.06);

    border-radius:20px;

    padding:18px;

    backdrop-filter:blur(10px);

    transition:
        transform .25s ease,
        border-color .25s ease,
        background .25s ease;
}

.modern-media-card:hover{

    transform:translateY(-4px);

    background:rgba(255,255,255,.065);

    border-color:rgba(124,58,237,.22);
}

/* =========================================
   SECTION HEADERS
========================================= */

.modern-media-card-header{

    display:flex;

    align-items:center;

    gap:8px;

    font-size:1rem;

    font-weight:800;

    margin-bottom:16px;
}

.general-header{
    color:#93c5fd;
}

.video-header{
    color:#f87171;
}

.audio-header{
    color:#4ade80;
}

.subtitle-header{
    color:#fde047;
}

/* =========================================
   LIST
========================================= */

.modern-media-list{

    list-style:none;

    margin:0;

    padding:0;
}

.modern-media-list li{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:12px;

    padding:10px 0;

    border-bottom:
        1px solid rgba(255,255,255,.05);

    font-size:.9rem;
}

.modern-media-list li:last-child{
    border-bottom:none;
}

.modern-media-list span{

    color:rgba(255,255,255,.58);

    font-weight:600;
}

.modern-media-list strong{

    color:#fff;

    text-align:right;
}

/* =========================================
   PILLS
========================================= */

.pill-badge{

    background:rgba(255,255,255,.1);

    padding:4px 10px;

    border-radius:999px;
}

.modern-audio-pills{

    gap:8px;

    flex-wrap:wrap;
}

.modern-audio-pills .nav-link{

    border:none;

    border-radius:999px;

    background:rgba(255,255,255,.06);

    color:#fff;

    font-size:.78rem;

    padding:6px 12px;

    font-weight:700;
}

.modern-audio-pills .nav-link.active{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:#fff;
}

/* =========================================
   SUBTITLE PILLS
========================================= */

.subtitle-pill-grid{

    display:flex;

    flex-wrap:wrap;

    gap:8px;
}

.subtitle-pill-modern{

    background:rgba(255,255,255,.08);

    border:1px solid rgba(255,255,255,.06);

    padding:7px 12px;

    border-radius:999px;

    font-size:.75rem;

    font-weight:700;
}

.romanian-pill{

    margin-top:14px;

    padding:8px 14px;

    border-radius:999px;

    text-align:center;

    font-size:.78rem;

    font-weight:800;

    background:
        linear-gradient(
            90deg,
            #1e3a8a,
            #facc15,
            #b91c1c
        );

    color:#111;
}

/* =========================================
   RAW
========================================= */

.modern-raw-card{

    background:rgba(10,10,10,.75);

    border:1px solid rgba(255,255,255,.06);

    border-radius:20px;

    backdrop-filter:blur(12px);

    padding:20px;
}

.modern-raw-card pre{

    margin:0;

    color:#e5e7eb;

    font-size:.78rem;

    line-height:1.5;

    max-height:500px;

    overflow:auto;

    white-space:pre-wrap;

    word-break:break-word;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-mediainfo-header{

        padding:18px;
    }

    .modern-mediainfo-body{

        padding:18px;
    }

    .mediainfo-title{

        font-size:1.2rem;
    }

    .mediainfo-subtitle{

        font-size:.82rem;
    }

    .modern-media-list li{

        font-size:.82rem;
    }

    .modern-media-card{

        padding:15px;
    }

    .modern-media-card-header{

        font-size:.92rem;
    }
}

</style>

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