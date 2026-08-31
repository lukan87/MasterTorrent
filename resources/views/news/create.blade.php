@extends('layouts.app')

@section('content')

<div class="container py-5 news-create-page">

    {{-- =========================================
        HEADER
    ========================================= --}}
    <div class="news-create-hero mb-4">

        <div class="hero-glow"></div>

        <div class="position-relative z-2">

            <div class="hero-badge">

                <i class="bi bi-megaphone-fill me-2"></i>

                STAFF NEWS PANEL

            </div>

            <h1 class="hero-title">

                Create News Article

            </h1>

            <p class="hero-subtitle">

                Publish announcements, updates and tracker news in a modern format.

            </p>

        </div>

    </div>

    {{-- =========================================
        FORM CARD
    ========================================= --}}
    <div class="news-form-card">

        <div class="form-glow"></div>

        <div class="position-relative z-2">

            <form action="{{ route('news.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                {{-- TITLE --}}
                <div class="mb-4">

                    <label for="title"
                           class="modern-label">

                        <i class="bi bi-type me-2"></i>

                        Article Title

                    </label>

                    <input type="text"
                           name="title"
                           id="title"
                           class="form-control modern-input"
                           placeholder="Enter a catchy news title..."
                           required>

                </div>

                {{-- TOOLBAR --}}
                <div class="mb-2">

                    <label class="modern-label">

                        <i class="bi bi-pencil-square me-2"></i>

                        Article Content

                    </label>

                    <div class="editor-toolbar">

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertTag('b')">

                            <i class="bi bi-type-bold"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertTag('i')">

                            <i class="bi bi-type-italic"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertTag('u')">

                            <i class="bi bi-type-underline"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertTag('center')">

                            <i class="bi bi-text-center"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn"
                                onclick="insertTag('quote')">

                            <i class="bi bi-chat-left-quote"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn danger-btn"
                                onclick="insertTag('youtube')">

                            <i class="bi bi-youtube"></i>

                        </button>

                        <button type="button"
                                class="toolbar-btn success-btn"
                                onclick="insertTag('img')">

                            <i class="bi bi-image"></i>

                        </button>

                    </div>

                </div>

                {{-- CONTENT --}}
                <div class="mb-4">

                    <textarea name="content"
                              id="content"
                              rows="12"
                              class="form-control modern-textarea"
                              placeholder="Write your news article here..."
                              required></textarea>

                </div>

                {{-- ACTIONS --}}
                <div class="d-flex flex-wrap gap-3">

                    <button type="submit"
                            class="publish-btn">

                        <i class="bi bi-send-fill me-2"></i>

                        Publish News

                    </button>

                    <a href="{{ route('news.index') }}"
                       class="cancel-btn">

                        <i class="bi bi-arrow-left-circle me-2"></i>

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<style>

/* =========================================
   PAGE
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

.news-create-hero{

    position:relative;

    overflow:hidden;

    padding:42px;

    border-radius:30px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.06),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(18px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.35);
}

.hero-glow{

    position:absolute;

    top:-120px;
    right:-120px;

    width:320px;
    height:320px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.22),
            transparent 70%
        );
}

.hero-badge{

    display:inline-flex;

    align-items:center;

    padding:10px 16px;

    border-radius:999px;

    background:
        rgba(59,130,246,.14);

    border:
        1px solid rgba(59,130,246,.2);

    color:#93c5fd;

    font-size:.82rem;

    font-weight:800;

    letter-spacing:1px;

    margin-bottom:20px;
}

.hero-title{

    color:white;

    font-size:clamp(2rem,5vw,3.2rem);

    font-weight:900;

    margin-bottom:12px;
}

.hero-subtitle{

    color:rgba(255,255,255,.65);

    font-size:1rem;

    margin:0;
}

/* =========================================
   FORM CARD
========================================= */

.news-form-card{

    position:relative;

    overflow:hidden;

    padding:38px;

    border-radius:30px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(16px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.3);
}

.form-glow{

    position:absolute;

    bottom:-140px;
    left:-140px;

    width:320px;
    height:320px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(124,58,237,.16),
            transparent 70%
        );
}

/* =========================================
   LABELS
========================================= */

.modern-label{

    display:flex;

    align-items:center;

    margin-bottom:12px;

    color:#dbeafe;

    font-size:.85rem;

    font-weight:800;

    text-transform:uppercase;

    letter-spacing:1px;
}

/* =========================================
   INPUTS
========================================= */

.modern-input,
.modern-textarea{

    background:
        rgba(15,23,42,.75) !important;

    border:
        1px solid rgba(255,255,255,.08) !important;

    color:white !important;

    border-radius:18px;

    padding:16px 18px;

    box-shadow:none !important;
}

.modern-input::placeholder,
.modern-textarea::placeholder{

    color:rgba(255,255,255,.4);
}

.modern-input:focus,
.modern-textarea:focus{

    border-color:
        rgba(59,130,246,.45) !important;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.12) !important;
}

.modern-textarea{

    min-height:320px;

    resize:vertical;

    line-height:1.7;
}

/* =========================================
   TOOLBAR
========================================= */

.editor-toolbar{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    margin-bottom:14px;
}

.toolbar-btn{

    width:44px;
    height:44px;

    border:none;

    border-radius:14px;

    background:
        rgba(255,255,255,.06);

    color:white;

    transition:.25s ease;
}

.toolbar-btn:hover{

    transform:translateY(-2px);

    background:
        rgba(59,130,246,.18);

    color:#93c5fd;
}

.danger-btn:hover{

    background:
        rgba(220,38,38,.18);

    color:#f87171;
}

.success-btn:hover{

    background:
        rgba(34,197,94,.18);

    color:#4ade80;
}

/* =========================================
   BUTTONS
========================================= */

.publish-btn{

    border:none;

    padding:14px 24px;

    border-radius:18px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-weight:800;

    transition:.25s ease;

    box-shadow:
        0 16px 35px rgba(59,130,246,.28);
}

.publish-btn:hover{

    transform:translateY(-3px);
}

.cancel-btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    padding:14px 22px;

    border-radius:18px;

    text-decoration:none;

    background:
        rgba(255,255,255,.06);

    border:
        1px solid rgba(255,255,255,.08);

    color:white;

    font-weight:700;

    transition:.25s ease;
}

.cancel-btn:hover{

    color:white;

    transform:translateY(-3px);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .news-create-hero,
    .news-form-card{

        padding:24px;
    }

    .hero-title{

        font-size:2rem;
    }

    .publish-btn,
    .cancel-btn{

        width:100%;
    }
}

</style>

<script>

function insertTag(tag){

    const textarea = document.getElementById('content');

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    const selected = textarea.value.substring(start, end);

    const openTag = `[${tag}]`;
    const closeTag = `[/${tag}]`;

    const replacement = openTag + selected + closeTag;

    textarea.value =
        textarea.value.substring(0, start)
        + replacement
        + textarea.value.substring(end);

    textarea.focus();

    textarea.selectionStart = start + openTag.length;
    textarea.selectionEnd = end + openTag.length;
}

</script>

@endsection