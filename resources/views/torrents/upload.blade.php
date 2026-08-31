@extends('layouts.app')

@section('title', 'Last-Torrents Upload')

@section('content')

@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))

<div class="container py-5">

    <div class="modern-upload-wrapper">

        {{-- BACKDROP --}}
        <div class="upload-glow"></div>

        {{-- HEADER --}}
        <div class="modern-upload-header">

            <div class="upload-header-icon">

                <i class="bi bi-cloud-arrow-up-fill"></i>

            </div>

            <div>

                <h1 class="upload-title">

                    Last-Torrents Upload Center

                </h1>

                <div class="upload-subtitle">

                    Precision • Quality • Reputation

                </div>

            </div>

        </div>

        {{-- BODY --}}
        <div class="modern-upload-body">

            {{-- ANNOUNCE --}}
            <div class="announce-card mb-4">

                <div>

                    <div class="announce-label">

                        PERSONAL ANNOUNCE URL

                    </div>

                    <a href="javascript:void(0);"
                       onclick="copyToClipboard('{{ env('APP_URL') }}/announce/{{ $user->passkey }}')"
                       class="announce-link">

                        {{ env('APP_URL') }}/announce/{{ $user->passkey }}

                    </a>

                </div>

                <div class="announce-copy">

                    <i class="bi bi-clipboard-check-fill"></i>

                </div>

            </div>

            <form action="{{ route('torrents.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="row g-4">

                    {{-- LEFT --}}
                    <div class="col-lg-6">

                        <div class="upload-card">

                            {{-- FILE --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Torrent File

                                </label>

                                <input type="file"
                                       class="form-control upload-input"
                                       id="file"
                                       name="torrent"
                                       required
                                       onchange="setTorrentName()">

                            </div>

                            {{-- NAME --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Torrent Name

                                </label>

                                <input type="text"
                                       class="form-control upload-input"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required>

                            </div>

                            {{-- CATEGORY --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Category

                                </label>

                                <select name="category_id"
                                        id="category_id"
                                        class="form-select upload-input"
                                        required
                                        onchange="toggleFieldsByCategory()">

                                    @foreach($categories->sortByDesc(fn($cat) => $cat->id === 49) as $category)

                                        <option value="{{ $category->id }}">

                                            {{ $category->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            {{-- POSTER --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Poster URL

                                </label>

                                <input type="url"
                                       name="poster"
                                       id="poster"
                                       class="form-control upload-input"
                                       value="{{ old('poster') }}">

                            </div>

                            {{-- GENRE --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Genre

                                </label>

                                <input type="text"
                                       class="form-control upload-input"
                                       id="genre"
                                       name="genre"
                                       value="{{ old('genre') }}">

                            </div>

                            {{-- STEAM --}}
                            <div class="mb-3">

                                <label class="upload-label">

                                    Steam ID

                                </label>

                                <input type="text"
                                       class="form-control upload-input"
                                       id="steamid"
                                       name="steamid"
                                       placeholder="Steam App ID only"
                                       value="{{ old('steamid') }}">

                            </div>

                            {{-- CONDITIONAL --}}
                            <div id="conditionalFields" style="display:none;">

                                <div class="mb-4">

                                    <label class="upload-label">

                                        IMDb URL

                                    </label>

                                    <input type="text"
                                           class="form-control upload-input"
                                           id="imdb_url"
                                           name="imdb_url"
                                           value="{{ old('imdb_url') }}">

                                    <button class="btn imdb-fetch-btn mt-3"
                                            type="button"
                                            onclick="fetchIMDBInfo()">

                                        <i class="bi bi-film me-1"></i>

                                        Fetch Info

                                    </button>

                                </div>

                                <div id="imdb-duplicate-warning"
                                     class="alert alert-warning rounded-4 d-none">

                                    <strong>
                                        Duplicate IMDb detected
                                    </strong>

                                    <ul id="existing-torrent-list"
                                        class="mb-0">
                                    </ul>

                                </div>

                                <div class="mb-3">

                                    <label class="upload-label">

                                        Media Info

                                    </label>

                                    <textarea class="form-control upload-input"
                                              id="mediainfo"
                                              name="mediainfo"
                                              oninput="resizeTextarea('mediainfo')"
                                              style="min-height:180px;">{{ old('mediainfo') }}</textarea>

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="col-lg-6">

                        <div class="upload-card">

                            {{-- SCREENSHOTS --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Screenshots (Max 10)

                                </label>

                                <input class="form-control upload-input"
                                       type="file"
                                       id="images"
                                       name="images[]"
                                       accept="image/*"
                                       multiple>

                                <div id="preview-container"
                                     class="mt-3 d-flex flex-wrap gap-3">
                                </div>

                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="mb-4">

                                <label class="upload-label">

                                    Description

                                </label>

                                {{-- TOOLBAR --}}
                                <div class="bbcode-toolbar mb-3">

                                    <button class="toolbar-btn"
                                            type="button"
                                            onclick="insertBBCode('b')">

                                        <i class="bi bi-type-bold"></i>

                                    </button>

                                    <button class="toolbar-btn"
                                            type="button"
                                            onclick="insertBBCode('i')">

                                        <i class="bi bi-type-italic"></i>

                                    </button>

                                    <button class="toolbar-btn"
                                            type="button"
                                            onclick="insertBBCode('u')">

                                        <i class="bi bi-type-underline"></i>

                                    </button>

                                    <button class="toolbar-btn"
                                            type="button"
                                            onclick="insertBBCode('center')">

                                        <i class="bi bi-text-center"></i>

                                    </button>

                                    <button class="toolbar-btn"
                                            type="button"
                                            onclick="insertBBCode('quote')">

                                        <i class="bi bi-chat-left-quote"></i>

                                    </button>

                                    <button class="toolbar-btn youtube-btn"
                                            type="button"
                                            onclick="insertBBCode('youtube')">

                                        <i class="bi bi-youtube"></i>

                                    </button>

                                    <button class="toolbar-btn image-btn"
                                            type="button"
                                            onclick="insertBBCode('img')">

                                        <i class="bi bi-card-image"></i>

                                    </button>

                                </div>

                                <textarea class="form-control upload-input"
                                          id="description"
                                          name="description"
                                          required
                                          oninput="resizeTextarea('description')"
                                          style="min-height:260px;">{{ old('description') }}</textarea>

                            </div>

                            {{-- TAGS --}}
                            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR))

                            <div class="mb-3">

                                <label class="upload-label">

                                    Torrent Tags

                                </label>

                                <div class="tag-switch-grid">

                                    @foreach (['free','double','sticky','seedbox'] as $tag)

                                        <div class="modern-switch">

                                            <input type="checkbox"
                                                   class="form-check-input"
                                                   name="{{ $tag }}"
                                                   id="{{ $tag }}"
                                                   value="1"
                                                   {{ old($tag) ? 'checked' : '' }}>

                                            <label for="{{ $tag }}">

                                                {{ ucfirst($tag) }}

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                            @endif

                        </div>

                    </div>

                </div>

                {{-- SUBMIT --}}
                <div class="text-center mt-5">

                    <button type="submit"
                            class="upload-submit-btn">

                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>

                        Upload Torrent

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@include('torrents.partials.scripts')

<style>

/* =========================================
   WRAPPER
========================================= */

.modern-upload-wrapper{

    position:relative;

    overflow:hidden;

    border-radius:30px;

    background:
        linear-gradient(
            145deg,
            rgba(14,18,28,.95),
            rgba(8,10,18,.98)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(20px);

    box-shadow:
        0 25px 70px rgba(0,0,0,.45);
}

.upload-glow{

    position:absolute;

    top:-150px;
    right:-150px;

    width:350px;
    height:350px;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.18),
            transparent 70%
        );

    pointer-events:none;
}

/* =========================================
   HEADER
========================================= */

.modern-upload-header{

    position:relative;

    display:flex;

    align-items:center;

    gap:20px;

    padding:28px 34px;

    border-bottom:
        1px solid rgba(255,255,255,.05);

    background:
        linear-gradient(
            90deg,
            rgba(59,130,246,.12),
            rgba(124,58,237,.08)
        );
}

.upload-header-icon{

    width:74px;
    height:74px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:22px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:2rem;

    color:#fff;

    box-shadow:
        0 10px 30px rgba(59,130,246,.35);
}

.upload-title{

    margin:0;

    font-size:2rem;

    font-weight:900;

    color:#fff;
}

.upload-subtitle{

    margin-top:4px;

    color:rgba(255,255,255,.55);

    letter-spacing:2px;

    text-transform:uppercase;

    font-size:.8rem;
}

/* =========================================
   BODY
========================================= */

.modern-upload-body{

    padding:34px;
}

/* =========================================
   ANNOUNCE
========================================= */

.announce-card{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    padding:22px 24px;

    border-radius:22px;

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.05);
}

.announce-label{

    font-size:.72rem;

    font-weight:800;

    letter-spacing:2px;

    color:rgba(255,255,255,.45);

    margin-bottom:6px;
}

.announce-link{

    color:#67e8f9;

    text-decoration:none;

    font-weight:700;

    word-break:break-all;
}

.announce-copy{

    width:52px;
    height:52px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:16px;

    background:
        rgba(59,130,246,.12);

    color:#93c5fd;

    font-size:1.2rem;
}

/* =========================================
   CARD
========================================= */

.upload-card{

    height:100%;

    padding:28px;

    border-radius:24px;

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.05);
}

/* =========================================
   LABELS
========================================= */

.upload-label{

    display:block;

    margin-bottom:10px;

    font-size:.78rem;

    font-weight:800;

    letter-spacing:1px;

    text-transform:uppercase;

    color:#94a3b8;
}

/* =========================================
   INPUTS
========================================= */

.upload-input{

    background:
        rgba(255,255,255,.04) !important;

    border:
        1px solid rgba(255,255,255,.06) !important;

    color:#fff !important;

    border-radius:16px;

    padding:14px 16px;

    transition:.2s ease;
}

.upload-input:focus{

    border-color:
        rgba(59,130,246,.55) !important;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.12) !important;
}

.upload-input::placeholder{

    color:rgba(255,255,255,.35);
}

/* SELECT FIX */
select.upload-input{
    background-color: rgba(20,25,35,.95) !important;
    color: #fff !important;
}

/* DROPDOWN OPTIONS */
select.upload-input option{
    background: #111827 !important;
    color: #fff !important;
}

/* HOVER / SELECTED OPTION */
select.upload-input option:hover,
select.upload-input option:checked{
    background: #2563eb !important;
    color: #fff !important;
}

/* =========================================
   TOOLBAR
========================================= */

.bbcode-toolbar{

    display:flex;

    flex-wrap:wrap;

    gap:10px;
}

.toolbar-btn{

    width:42px;
    height:42px;

    border:none;

    border-radius:14px;

    background:
        rgba(255,255,255,.05);

    color:#dbeafe;

    transition:.2s ease;
}

.toolbar-btn:hover{

    transform:translateY(-2px);

    background:
        rgba(59,130,246,.18);
}

.youtube-btn{

    color:#f87171;
}

.image-btn{

    color:#67e8f9;
}

/* =========================================
   BUTTONS
========================================= */

.imdb-fetch-btn{

    border:none;

    padding:10px 18px;

    border-radius:14px;

    font-weight:700;

    background:
        linear-gradient(
            135deg,
            #059669,
            #10b981
        );

    color:#fff;
}

.upload-submit-btn{

    border:none;

    padding:16px 34px;

    border-radius:999px;

    font-size:1rem;

    font-weight:800;

    letter-spacing:.5px;

    color:#fff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    transition:.25s ease;

    box-shadow:
        0 12px 30px rgba(59,130,246,.25);
}

.upload-submit-btn:hover{

    transform:translateY(-3px);

    box-shadow:
        0 18px 40px rgba(59,130,246,.35);
}

/* =========================================
   SWITCHES
========================================= */

.tag-switch-grid{

    display:flex;

    flex-wrap:wrap;

    gap:14px;
}

.modern-switch{

    display:flex;

    align-items:center;

    gap:8px;

    padding:10px 16px;

    border-radius:16px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);
}

.modern-switch label{

    margin:0;

    color:#fff;

    font-weight:600;
}

/* =========================================
   TEXTAREA
========================================= */

textarea{

    resize:none;

    overflow:auto;

    max-height:600px;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-upload-header{

        flex-direction:column;

        text-align:center;

        padding:24px 20px;
    }

    .modern-upload-body{

        padding:20px;
    }

    .upload-card{

        padding:20px;
    }

    .upload-title{

        font-size:1.5rem;
    }

    .announce-card{

        flex-direction:column;

        align-items:flex-start;
    }

    .upload-submit-btn{

        width:100%;
    }
}

</style>

@else

<div class="container mt-5">

    <div class="alert alert-danger rounded-4 shadow-lg p-4">

        <h4 class="fw-bold mb-2">

            Upload Permission Required

        </h4>

        <p class="mb-0">

            You are not authorized to upload torrents. Please contact staff.

        </p>

    </div>

</div>

@endif

@endsection