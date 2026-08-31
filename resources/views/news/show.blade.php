@extends('layouts.app')

@section('content')

<div class="container py-5 news-show-page">

    {{-- =========================================
        HERO HEADER
    ========================================= --}}
    <div class="news-show-hero mb-4">

        <div class="hero-glow"></div>

        <div class="position-relative">

            <div class="news-badge">

                <i class="bi bi-newspaper me-2"></i>

                LASTFILES NEWS

            </div>

            <h1 class="news-title">

                {{ $news->title }}

            </h1>

            <div class="news-meta">

                <span>

                    <i class="bi bi-person-circle"></i>

                    {{ $news->user->name }}

                </span>

                <span>

                    <i class="bi bi-calendar3"></i>

                    {{ $news->created_at->format('F d, Y') }}

                </span>

            </div>

        </div>

    </div>

    {{-- =========================================
        ARTICLE CONTENT
    ========================================= --}}
    <div class="news-article-card mb-4">

        <div class="article-glow"></div>

        <div class="news-content">

            {!! convertCustomTagsToHtml($news->content) !!}

        </div>

    </div>

    {{-- =========================================
        FOOTER ACTIONS
    ========================================= --}}
    <div class="news-actions">

        <a href="{{ route('news.index') }}"
           class="modern-btn secondary-btn">

            <i class="bi bi-arrow-left-circle me-2"></i>

            Back to News

        </a>

        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

            <a href="{{ route('news.edit', $news) }}"
               class="modern-btn edit-btn">

                <i class="bi bi-pencil-square me-2"></i>

                Edit Article

            </a>

        @endif

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

.news-show-hero{

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

    backdrop-filter:blur(16px);

    box-shadow:
        0 25px 60px rgba(0,0,0,.35);
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

.news-badge{

    display:inline-flex;

    align-items:center;

    padding:10px 16px;

    border-radius:999px;

    background:
        rgba(59,130,246,.12);

    border:
        1px solid rgba(59,130,246,.18);

    color:#93c5fd;

    font-size:.82rem;

    font-weight:800;

    letter-spacing:1px;

    margin-bottom:20px;
}

.news-title{

    color:white;

    font-size:clamp(2rem,5vw,3.5rem);

    line-height:1.15;

    font-weight:900;

    margin-bottom:20px;

    overflow-wrap:anywhere;
}

.news-meta{

    display:flex;

    flex-wrap:wrap;

    gap:18px;

    color:rgba(255,255,255,.6);

    font-size:.95rem;
}

.news-meta span{

    display:flex;

    align-items:center;

    gap:8px;
}

/* =========================================
   ARTICLE CARD
========================================= */

.news-article-card{

    position:relative;

    overflow:hidden;

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

.article-glow{

    position:absolute;

    bottom:-120px;
    left:-120px;

    width:260px;
    height:260px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(124,58,237,.18),
            transparent 70%
        );
}

/* =========================================
   CONTENT
========================================= */

.news-content{

    position:relative;

    z-index:2;

    padding:42px;

    color:rgba(255,255,255,.88);

    font-size:1rem;

    line-height:1.9;

    overflow-wrap:anywhere;
}

/* TYPOGRAPHY */

.news-content h1,
.news-content h2,
.news-content h3,
.news-content h4,
.news-content h5,
.news-content h6{

    color:white;

    margin-top:32px;
    margin-bottom:18px;

    font-weight:800;
}

.news-content p{

    margin-bottom:22px;
}

.news-content img{

    max-width:100%;

    height:auto;

    border-radius:18px;

    margin:20px 0;

    box-shadow:
        0 10px 30px rgba(0,0,0,.35);
}

.news-content a{

    color:#60a5fa;

    text-decoration:none;
}

.news-content a:hover{

    color:#93c5fd;

    text-decoration:underline;
}

.news-content blockquote{

    margin:24px 0;

    padding:18px 24px;

    border-left:4px solid #3b82f6;

    background:
        rgba(255,255,255,.04);

    border-radius:14px;

    color:rgba(255,255,255,.78);

    font-style:italic;
}

.news-content code{

    background:
        rgba(255,255,255,.08);

    padding:2px 8px;

    border-radius:8px;

    color:#93c5fd;
}

.news-content pre{

    background:
        rgba(0,0,0,.4);

    padding:20px;

    border-radius:18px;

    overflow:auto;

    border:
        1px solid rgba(255,255,255,.06);
}

/* =========================================
   ACTIONS
========================================= */

.news-actions{

    display:flex;

    flex-wrap:wrap;

    gap:14px;
}

.modern-btn{

    display:inline-flex;

    align-items:center;
    justify-content:center;

    padding:14px 22px;

    border-radius:18px;

    text-decoration:none;

    font-weight:700;

    transition:.25s ease;
}

.secondary-btn{

    background:
        rgba(255,255,255,.06);

    border:
        1px solid rgba(255,255,255,.08);

    color:white;
}

.edit-btn{

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    box-shadow:
        0 14px 30px rgba(59,130,246,.3);
}

.modern-btn:hover{

    transform:translateY(-3px);

    color:white;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .news-show-hero{

        padding:28px 24px;
    }

    .news-content{

        padding:28px 22px;

        font-size:.96rem;
    }

    .news-actions{

        flex-direction:column;
    }

    .modern-btn{

        width:100%;
    }
}

</style>

@endsection