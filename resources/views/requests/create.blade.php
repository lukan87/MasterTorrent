@extends('layouts.app')

@section('content')

@php $defaults = $defaults ?? []; @endphp

<div class="request-page">

    <div class="container py-5">

        {{-- =========================================

            HERO HEADER

        ========================================= --}}

        <div class="request-hero mb-4">

            <div>

                <div class="request-kicker">

                    COMMUNITY • REQUEST SYSTEM

                </div>

                <h1 class="request-title">

                    <i class="bi bi-megaphone-fill me-2"></i>

                    Create Torrent Request

                </h1>

                <div class="request-subtitle">

                    Request movies, TV shows, games or content from uploaders.

                </div>

            </div>

            <div class="hero-icon">

                <i class="bi bi-cloud-plus-fill"></i>

            </div>

        </div>

        {{-- =========================================

            ERRORS

        ========================================= --}}

        @if ($errors->any())

            <div class="modern-alert modern-alert-danger mb-4">

                <div>

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    Please fix the errors below.

                </div>

            </div>

        @endif

        {{-- =========================================

            FORM CARD

        ========================================= --}}

        <div class="modern-card">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-pencil-square text-info me-2"></i>

                    Request Information

                </h5>

            </div>

            <div class="modern-card-body">

                <form action="{{ route('requests.store') }}"

                      method="POST">

                    @csrf

                    <input type="hidden"

                           name="requested_by"

                           value="{{ auth()->id() }}">

                    <div class="row g-4">

                        {{-- REQUEST NAME --}}

                        <div class="col-12">

                            <label class="modern-label">

                                Request Name

                            </label>

                            <input type="text"

                                   name="name"

                                   id="name"

                                   class="form-control modern-input"

                                   value="{{ old('name', $defaults['name'] ?? null) }}"

                                   placeholder="Movie / TV Show / Game name..."

                                   required>

                        </div>

                        {{-- CATEGORY --}}

                        <div class="col-md-6">

                            <label class="modern-label">

                                Category

                            </label>

                            <select name="category_id"

                                    id="category_id"

                                    class="form-select modern-input"

                                    required>

                                <option value=""

                                    >

                                    Select Category

                                </option>

                                @foreach ($categories as $category)

                                    <option value="{{ $category->id }}"

                                        {{ old('category_id', $defaults['category_id'] ?? null) == $category->id ? 'selected' : '' }}>

                                        {{ $category->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- IMAGE --}}

                        <div class="col-md-6">

                            <label class="modern-label">

                                Poster / Image URL

                            </label>

                            <input type="url"

                                   name="image"

                                   id="image"

                                   class="form-control modern-input"

                                   value="{{ old('image', $defaults['image'] ?? null) }}"

                                   placeholder="https://..."

                                   required>

                        </div>

                        {{-- IMDB --}}

                        <div class="col-md-4">

                            <label class="modern-label">

                                IMDb URL

                            </label>

                            <input type="url"

                                   name="imdb_url"

                                   id="imdb_url"

                                   class="form-control modern-input"

                                   value="{{ old('imdb_url', $defaults['imdb_url'] ?? null) }}"

                                   placeholder="https://imdb.com/title/...">

                        </div>

                        {{-- TMDB --}}

                        <div class="col-md-4">

                            <label class="modern-label">

                                TMDB URL

                            </label>

                            <input type="url"

                                   name="tmdb_url"

                                   id="tmdb_url"

                                   class="form-control modern-input"

                                   value="{{ old('tmdb_url', $defaults['tmdb_url'] ?? null) }}"

                                   placeholder="https://themoviedb.org/...">

                        </div>

                        {{-- STEAM --}}

                        <div class="col-md-4">

                            <label class="modern-label">

                                Steam URL

                            </label>

                            <input type="url"

                                   name="steam_url"

                                   id="steam_url"

                                   class="form-control modern-input"

                                   value="{{ old('steam_url') }}"

                                   placeholder="https://store.steampowered.com/...">

                        </div>

                        {{-- DESCRIPTION --}}

                        <div class="col-12">

                            <label class="modern-label">

                                Description

                            </label>

                            <textarea name="description"

                                      id="description"

                                      rows="7"

                                      class="form-control modern-input modern-textarea"

                                      placeholder="Add extra details about the request...">{{ old('description') }}</textarea>

                            <div class="input-hint">

                                Maximum 500 words.

                            </div>

                        </div>

                    </div>

                    {{-- SUBMIT --}}

                    <div class="text-center mt-5">

                        <button type="submit"

                                class="btn modern-submit-btn">

                            <i class="bi bi-send-fill me-2"></i>

                            Create Request

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<style>
/* =========================================
   FILEIPLAY CREATE TORRENT REQUEST
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.request-page {
    min-height: 100vh;
    color: #e2e8f0;
}

.request-hero,
.modern-card {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px rgba(0,0,0,.18);
}

.request-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 1.2rem;
    border-left: 3px solid var(--ui-accent, #22d3ee);
    border-radius: .85rem;
}

.request-hero::after {
    content: "";
    position: absolute;
    top: -100px;
    right: -100px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(
        circle,
        rgba(34,211,238,.10),
        transparent 70%
    );
    pointer-events: none;
}

.request-kicker {
    margin-bottom: .35rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.request-title {
    position: relative;
    z-index: 1;
    margin: 0;
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.25;
}

.request-subtitle {
    position: relative;
    z-index: 1;
    margin-top: .4rem;
    color: rgba(226,232,240,.62);
    font-size: 13px;
    line-height: 1.45;
}

.hero-icon {
    position: relative;
    z-index: 1;
    width: 52px;
    height: 52px;
    flex: 0 0 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .75rem;
    background: rgba(34,211,238,.08);
    color: var(--ui-accent, #22d3ee);
    font-size: 1.25rem;
}

/* =========================================
   CARD
========================================= */

.modern-card {
    overflow: hidden;
    border-radius: .85rem;
}

.modern-card-header {
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--ui-border, rgba(255,255,255,.08));
}

.modern-card-header h5 {
    margin: 0;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
}

.modern-card-header .text-info {
    color: var(--ui-accent, #22d3ee) !important;
}

.modern-card-body {
    padding: 1rem;
}

/* =========================================
   ALERT
========================================= */

.modern-alert {
    padding: .75rem .9rem;
    border-radius: .7rem;
    font-size: 13px;
    line-height: 1.45;
}

.modern-alert-danger {
    border: 1px solid rgba(239,68,68,.22);
    border-left: 3px solid rgba(239,68,68,.65);
    background: rgba(239,68,68,.08);
    color: #fecaca;
}

/* =========================================
   LABELS
========================================= */

.modern-label {
    display: block;
    margin-bottom: .4rem;
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .4px;
}

/* =========================================
   INPUTS
========================================= */

.modern-input {
    min-height: 40px;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
    border-radius: .6rem !important;
    background: rgba(255,255,255,.035) !important;
    color: #f8fafc !important;
    font-size: 13px;
    box-shadow: none !important;
    transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
}

.modern-input:focus {
    border-color: rgba(34,211,238,.45) !important;
    background: rgba(34,211,238,.035) !important;
    box-shadow: 0 0 0 3px rgba(34,211,238,.08) !important;
}

.modern-input::placeholder {
    color: rgba(226,232,240,.36);
}

select.modern-input option {
    background: #0f172a;
    color: #f8fafc;
}

.modern-textarea {
    min-height: 160px;
    resize: vertical;
    line-height: 1.5;
}

.input-hint {
    margin-top: .35rem;
    color: rgba(226,232,240,.45);
    font-size: 12px;
}

/* =========================================
   SUBMIT
========================================= */

.modern-submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .55rem .9rem;
    border: 1px solid rgba(34,211,238,.28);
    border-radius: .55rem;
    background: rgba(34,211,238,.10);
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
    transition: all .2s ease;
}

.modern-submit-btn:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.16);
    color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 7px 18px rgba(0,0,0,.18);
}

/* =========================================
   MOBILE
========================================= */

@media (max-width: 768px) {
    .request-page .container {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .request-hero {
        padding: .9rem;
        border-radius: .75rem;
    }

    .request-title {
        font-size: 18px;
    }

    .request-subtitle {
        font-size: 12px;
    }

    .hero-icon {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
        font-size: 1.1rem;
    }

    .modern-card-body {
        padding: .85rem;
    }

    .modern-input {
        font-size: 13px;
    }

    .modern-submit-btn {
        font-size: 12px;
    }
}
</style>

@endsection