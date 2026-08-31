@extends('layouts.app')

@section('content')

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
                                   value="{{ old('name') }}"
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

                                <option value="">
                                    Select Category
                                </option>

                                @foreach ($categories as $category)

                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>

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
                                   value="{{ old('image') }}"
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
                                   value="{{ old('imdb_url') }}"
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
                                   value="{{ old('tmdb_url') }}"
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
   BACKGROUND
========================================= */

body{

    background:
        radial-gradient(
            circle at top,
            #172033,
            #0f172a 45%,
            #020617
        );

    min-height:100vh;
}

/* =========================================
   HERO
========================================= */

.request-hero{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding:34px;

    border-radius:30px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.07),
            rgba(255,255,255,.03)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(18px);

    box-shadow:
        0 25px 60px rgba(0,0,0,.45);

    position:relative;

    overflow:hidden;
}

.request-hero::before{

    content:'';

    position:absolute;

    top:-120px;
    right:-120px;

    width:280px;
    height:280px;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.22),
            transparent 70%
        );

    pointer-events:none;
}

.request-kicker{

    color:#60a5fa;

    font-size:.78rem;

    font-weight:800;

    letter-spacing:2px;

    margin-bottom:10px;
}

.request-title{

    color:white;

    font-size:2.4rem;

    font-weight:900;

    margin:0;
}

.request-subtitle{

    margin-top:10px;

    color:rgba(255,255,255,.65);

    font-size:1rem;
}

.hero-icon{

    width:90px;
    height:90px;

    border-radius:24px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-size:2rem;

    box-shadow:
        0 15px 35px rgba(59,130,246,.35);
}

/* =========================================
   CARD
========================================= */

.modern-card{

    border-radius:28px;

    overflow:hidden;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.03)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(16px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.35);
}

.modern-card-header{

    padding:22px 28px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    color:white;
}

.modern-card-body{

    padding:30px;
}

/* =========================================
   LABELS
========================================= */

.modern-label{

    display:block;

    margin-bottom:10px;

    color:#cbd5e1;

    font-size:.78rem;

    font-weight:800;

    letter-spacing:1px;

    text-transform:uppercase;
}

/* =========================================
   INPUTS
========================================= */

.modern-input{

    background:
        rgba(255,255,255,.04) !important;

    border:
        1px solid rgba(255,255,255,.08) !important;

    color:white !important;

    border-radius:18px !important;

    padding:14px 18px !important;

    transition:.2s ease;
}

.modern-input:focus{

    border-color:
        rgba(59,130,246,.35) !important;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.15) !important;

    background:
        rgba(255,255,255,.06) !important;
}

.modern-input::placeholder{

    color:rgba(255,255,255,.35);
}

/* SELECT OPTIONS */
select.modern-input option{

    background:#111827;

    color:white;
}

.modern-textarea{

    min-height:180px;

    resize:vertical;
}

/* =========================================
   HINT
========================================= */

.input-hint{

    margin-top:8px;

    font-size:.8rem;

    color:rgba(255,255,255,.45);
}

/* =========================================
   BUTTON
========================================= */

.modern-submit-btn{

    border:none;

    padding:15px 32px;

    border-radius:18px;

    font-weight:800;

    letter-spacing:.5px;

    color:white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 12px 30px rgba(59,130,246,.35);

    transition:.25s ease;
}

.modern-submit-btn:hover{

    transform:translateY(-3px);

    color:white;

    box-shadow:
        0 18px 40px rgba(59,130,246,.45);
}

/* =========================================
   ALERT
========================================= */

.modern-alert{

    padding:16px 18px;

    border-radius:18px;

    color:white;

    backdrop-filter:blur(10px);
}

.modern-alert-danger{

    background:
        rgba(239,68,68,.14);

    border:
        1px solid rgba(239,68,68,.25);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .request-hero{

        padding:24px;
    }

    .request-title{

        font-size:1.8rem;
    }

    .hero-icon{

        width:72px;
        height:72px;

        font-size:1.6rem;
    }

    .modern-card-body{

        padding:22px;
    }
}

</style>

@endsection