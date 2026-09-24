@extends('layouts.app')

@section('title', 'FileIplay External Upload')

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

                    FileIplay External Upload

                </h1>

                <div class="upload-subtitle">

                    Precision • Quality • Reputation

                </div>

            </div>

        </div>

        {{-- BODY --}}

        <div class="modern-upload-body">

            <form action="{{ route('external-torrents.store') }}"
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

                        </div>

                    </div>

                    {{-- RIGHT --}}

                    <div class="col-lg-6">

                        <div class="upload-card">

                            {{-- DESCRIPTION --}}

                            <div class="mb-4">

                                <label class="upload-label">
                                    Description
                                </label>

                                <textarea class="form-control upload-input"
                                          id="description"
                                          name="description"
                                          required
                                          oninput="resizeTextarea('description')"
                                          style="min-height:260px;">{{ old('description') }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- SUBMIT --}}

                <div class="text-center mt-5">

                    <button type="submit"
                            class="upload-submit-btn">

                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>

                        Upload External Torrent

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@include('torrents.partials.scripts')


<style>
/* =========================================================
   FILEIPLAY — UPLOAD CENTER
   Dark navy glass + teal forum style
   ========================================================= */

.modern-upload-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: .85rem;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .96),
        rgba(15, 23, 42, .88)
    );

    border: 1px solid var(--ui-border);
    box-shadow: 0 18px 45px rgba(0,0,0,.32);
    backdrop-filter: blur(14px);
}

.upload-glow {
    position: absolute;
    top: -160px;
    right: -160px;
    width: 340px;
    height: 340px;

    background: radial-gradient(
        circle,
        rgba(45,212,191,.10),
        transparent 70%
    );

    pointer-events: none;
}

/* Header */

.modern-upload-header {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;

    padding: 20px 24px;

    background: rgba(45,212,191,.045);
    border-bottom: 1px solid var(--ui-border);
}

.upload-header-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 52px;

    border-radius: .65rem;

    background: rgba(45,212,191,.08);
    border: 1px solid rgba(45,212,191,.22);

    color: var(--ui-accent);

    font-size: 22px;
}

.upload-title {
    margin: 0;

    color: #fff;

    font-size: 16px;
    font-weight: 700;
}

.upload-subtitle {
    margin-top: 3px;

    color: rgba(255,255,255,.45);

    font-size: 12px;
    letter-spacing: .5px;
}

/* Body */

.modern-upload-body {
    padding: 22px;
}

/* Announce */

.announce-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;

    padding: 14px 16px;

    border-radius: .65rem;

    background: rgba(9,16,29,.55);
    border: 1px solid var(--ui-border);
}

.announce-label {
    margin-bottom: 5px;

    color: rgba(255,255,255,.43);

    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
}

.announce-link {
    color: var(--ui-accent);

    font-size: 13px;
    font-weight: 600;

    text-decoration: none;
    word-break: break-all;
}

.announce-link:hover {
    color: #99f6e4;
}

.announce-copy {
    width: 40px;
    height: 40px;

    flex: 0 0 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: .55rem;

    background: rgba(45,212,191,.08);
    border: 1px solid rgba(45,212,191,.16);

    color: var(--ui-accent);

    font-size: 16px;
}

/* Inner cards */

.upload-card {
    height: 100%;

    padding: 20px;

    border-radius: .75rem;

    background: rgba(9,16,29,.48);
    border: 1px solid var(--ui-border);
}

/* Labels */

.upload-label {
    display: block;

    margin-bottom: 7px;

    color: rgba(255,255,255,.68);

    font-size: 13px;
    font-weight: 600;
}

/* Inputs */

.upload-input {
    min-height: 40px;

    background: rgba(7,13,24,.75) !important;

    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: .55rem !important;

    color: #fff !important;

    font-size: 14px;

    padding: 9px 11px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.upload-input:focus {
    background: rgba(7,13,24,.92) !important;

    border-color: rgba(45,212,191,.42) !important;

    box-shadow:
        0 0 0 3px rgba(45,212,191,.08) !important;

    outline: none;
}

.upload-input::placeholder {
    color: rgba(255,255,255,.34);
}

select.upload-input {
    background-color: rgba(10,17,30,.95) !important;
}

select.upload-input option {
    background: #111b2d !important;
    color: #fff !important;
}

select.upload-input option:checked {
    background: #164e63 !important;
    color: #fff !important;
}

input[type="file"].upload-input {
    padding: 7px 9px;
}

input[type="file"].upload-input::file-selector-button {
    margin-right: 10px;

    padding: 6px 10px;

    border: 1px solid rgba(45,212,191,.18);
    border-radius: .4rem;

    background: rgba(45,212,191,.08);
    color: var(--ui-accent);

    font-size: 12px;
    font-weight: 600;

    cursor: pointer;
}

/* BBCode toolbar */

.bbcode-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.toolbar-btn {
    width: 34px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--ui-border);
    border-radius: .45rem;

    background: rgba(255,255,255,.035);
    color: rgba(255,255,255,.65);

    font-size: 13px;

    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.toolbar-btn:hover {
    transform: translateY(-1px);

    background: rgba(45,212,191,.08);
    border-color: rgba(45,212,191,.28);

    color: var(--ui-accent);
}

.youtube-btn {
    color: #ff7b84;
}

.youtube-btn:hover {
    color: #ff9da4;
    border-color: rgba(239,68,68,.28);
    background: rgba(239,68,68,.07);
}

.image-btn {
    color: var(--ui-accent);
}

/* IMDb */

.imdb-fetch-btn {
    border: 1px solid rgba(45,212,191,.24);

    padding: 8px 13px;

    border-radius: .5rem;

    background: rgba(45,212,191,.08);
    color: var(--ui-accent);

    font-size: 13px;
    font-weight: 600;

    transition: .15s ease;
}

.imdb-fetch-btn:hover {
    background: rgba(45,212,191,.14);
    border-color: rgba(45,212,191,.38);
    color: #99f6e4;
}

/* Duplicate warning */

#imdb-duplicate-warning {
    margin-top: 10px;

    background: rgba(245,158,11,.07);
    border: 1px solid rgba(245,158,11,.20);
    color: #fcd34d;

    font-size: 13px;
}

/* Preview */

#preview-container {
    gap: 8px !important;
}

#preview-container img {
    border-radius: .5rem;
    border: 1px solid var(--ui-border);
}

/* Tags */

.tag-switch-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.modern-switch {
    display: flex;
    align-items: center;
    gap: 7px;

    padding: 7px 10px;

    border-radius: .5rem;

    background: rgba(255,255,255,.035);
    border: 1px solid var(--ui-border);
}

.modern-switch label {
    margin: 0;

    color: rgba(255,255,255,.70);

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;
}

.modern-switch .form-check-input {
    margin: 0;

    background-color: rgba(255,255,255,.05);
    border-color: rgba(255,255,255,.18);

    cursor: pointer;
}

.modern-switch .form-check-input:checked {
    background-color: var(--ui-accent);
    border-color: var(--ui-accent);
}

/* Submit */

.upload-submit-btn {
    border: 1px solid rgba(45,212,191,.28);

    padding: 10px 22px;

    border-radius: .6rem;

    background: rgba(45,212,191,.10);
    color: var(--ui-accent);

    font-size: 14px;
    font-weight: 700;

    transition:
        background .15s ease,
        border-color .15s ease,
        transform .15s ease,
        box-shadow .15s ease;
}

.upload-submit-btn:hover {
    transform: translateY(-1px);

    background: rgba(45,212,191,.16);

    border-color: rgba(45,212,191,.42);

    color: #99f6e4;

    box-shadow: 0 8px 22px rgba(0,0,0,.25);
}

/* Textareas */

textarea.upload-input {
    resize: vertical;

    min-height: 180px;

    max-height: 600px;

    line-height: 1.5;
}

/* Permission message */

.upload-permission-card {
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );

    border: 1px solid rgba(239,68,68,.25) !important;
    border-radius: .85rem !important;

    color: rgba(255,255,255,.75);
}

.upload-permission-card h4 {
    color: #fff;
    font-size: 15px;
}

.upload-permission-card p {
    font-size: 13px;
}

/* Mobile */

@media (max-width: 768px) {

    .modern-upload-wrapper {
        border-radius: .75rem;
    }

    .modern-upload-header {
        padding: 16px;
        gap: 12px;
    }

    .upload-header-icon {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
        font-size: 18px;
    }

    .upload-title {
        font-size: 14px;
    }

    .upload-subtitle {
        font-size: 11px;
    }

    .modern-upload-body {
        padding: 14px;
    }

    .upload-card {
        padding: 15px;
    }

    .announce-card {
        align-items: flex-start;
        padding: 12px;
    }

    .announce-copy {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .upload-input {
        font-size: 14px;
    }

    .upload-submit-btn {
        width: 100%;
    }
}
</style>


@else

<div class="container mt-5">

    <div class="alert alert-danger shadow-lg p-4 upload-permission-card">

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
