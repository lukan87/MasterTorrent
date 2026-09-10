@extends('layouts.app')

@section('content')

<div class="container py-5 news-page">

    {{-- =========================================
        HERO HEADER
    ========================================= --}}
    <div class="news-hero mb-5">

        <div class="hero-glow"></div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative">

            <div>

                <div class="news-kicker">

                    FileIplay UPDATES

                </div>

                <h1 class="news-title">

                    News Center

                </h1>

                <p class="news-subtitle">

                    Tracker updates, announcements and community news

                </p>

            </div>

            <a href="{{ route('news.create') }}"
               class="create-news-btn">

                <i class="bi bi-plus-circle-fill me-2"></i>

                Create News

            </a>

        </div>

    </div>

    {{-- =========================================
        SUCCESS ALERT
    ========================================= --}}
    @if(session('success'))

        <div class="modern-alert success-alert mb-4">

            <div class="d-flex align-items-center gap-3">

                <div class="alert-icon">

                    <i class="bi bi-check-circle-fill"></i>

                </div>

                <div>

                    {{ session('success') }}

                </div>

            </div>

            <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif

    {{-- =========================================
        NEWS LIST
    ========================================= --}}
    <div class="news-grid">

        @forelse($newsItems as $news)

            <div class="news-card">

                <div class="news-card-glow"></div>

                <div class="news-card-body">

                    {{-- DATE --}}
                    <div class="news-date">

                        <i class="bi bi-calendar3 me-2"></i>

                        {{ $news->created_at->format('F j, Y') }}

                    </div>

                    {{-- TITLE --}}
                    <h3 class="news-card-title">

                        <a href="{{ route('news.show', $news) }}">

                            {{ $news->title }}

                        </a>

                    </h3>

                    {{-- AUTHOR --}}
                    <div class="news-author">

                        <i class="bi bi-person-circle me-2"></i>

                        Posted by

                        <span>

                            {{ $news->user->name }}

                        </span>

                    </div>

                    {{-- ACTIONS --}}
                    <div class="news-footer">

                        <a href="{{ route('news.show', $news) }}"
                           class="read-more-btn">

                            Read Article

                            <i class="bi bi-arrow-right-short"></i>

                        </a>

                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                            <div class="admin-actions">

                                <a href="{{ route('news.edit', $news) }}"
                                   class="admin-btn edit-btn">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <form action="{{ route('news.destroy', $news) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="admin-btn delete-btn"
                                            onclick="return confirm('Are you sure?')">

                                        <i class="bi bi-trash-fill"></i>

                                    </button>

                                </form>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="empty-news-card">

                <div class="empty-icon">

                    <i class="bi bi-newspaper"></i>

                </div>

                <h3>

                    No news articles available

                </h3>

                <p>

                    Be the first to create a news article.

                </p>

                <a href="{{ route('news.create') }}"
                   class="create-news-btn mt-2">

                    <i class="bi bi-plus-circle-fill me-2"></i>

                    Create News

                </a>

            </div>

        @endforelse

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

.news-hero{

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

    top:-100px;
    right:-100px;

    width:300px;
    height:300px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.28),
            transparent 70%
        );
}

.news-kicker{

    color:#60a5fa;

    font-size:.8rem;

    font-weight:800;

    letter-spacing:2px;

    margin-bottom:10px;
}

.news-title{

    color:white;

    font-size:3rem;

    font-weight:900;

    margin:0;
}

.news-subtitle{

    margin-top:10px;

    color:rgba(255,255,255,.62);

    font-size:1rem;
}

/* =========================================
   BUTTONS
========================================= */

.create-news-btn{

    display:inline-flex;

    align-items:center;
    justify-content:center;

    padding:14px 22px;

    border-radius:18px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    text-decoration:none;

    font-weight:800;

    transition:.25s ease;

    box-shadow:
        0 14px 30px rgba(59,130,246,.3);
}

.create-news-btn:hover{

    transform:translateY(-3px);

    color:white;
}

/* =========================================
   ALERT
========================================= */

.modern-alert{

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:18px 22px;

    border-radius:20px;

    backdrop-filter:blur(14px);

    border:
        1px solid rgba(255,255,255,.08);

    color:white;
}

.success-alert{

    background:
        rgba(34,197,94,.14);
}

.alert-icon{

    font-size:1.2rem;

    color:#4ade80;
}

/* =========================================
   NEWS GRID
========================================= */

.news-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fill,minmax(320px,1fr));

    gap:24px;
}

/* =========================================
   NEWS CARD
========================================= */

.news-card{

    position:relative;

    overflow:hidden;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(16px);

    transition:.3s ease;

    box-shadow:
        0 15px 40px rgba(0,0,0,.28);
}

.news-card:hover{

    transform:
        translateY(-8px);

    box-shadow:
        0 25px 50px rgba(0,0,0,.42);
}

.news-card-glow{

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.18),
            transparent 70%
        );
}

.news-card-body{

    position:relative;

    z-index:2;

    padding:28px;
}

/* =========================================
   CARD CONTENT
========================================= */

.news-date{

    color:#93c5fd;

    font-size:.82rem;

    font-weight:700;

    margin-bottom:16px;
}

.news-card-title{

    margin-bottom:18px;

    font-size:1.4rem;

    line-height:1.35;

    font-weight:800;
}

.news-card-title a{

    color:white;

    text-decoration:none;

    transition:.2s ease;
}

.news-card-title a:hover{

    color:#93c5fd;
}

.news-author{

    color:rgba(255,255,255,.55);

    font-size:.92rem;

    margin-bottom:28px;
}

.news-author span{

    color:white;

    font-weight:700;
}

/* =========================================
   FOOTER
========================================= */

.news-footer{

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:12px;

    flex-wrap:wrap;
}

.read-more-btn{

    display:inline-flex;

    align-items:center;

    gap:4px;

    text-decoration:none;

    color:#60a5fa;

    font-weight:700;

    transition:.2s ease;
}

.read-more-btn:hover{

    gap:8px;

    color:#93c5fd;
}

.admin-actions{

    display:flex;

    gap:10px;
}

.admin-btn{

    width:42px;
    height:42px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:14px;

    border:none;

    text-decoration:none;

    transition:.2s ease;
}

.edit-btn{

    background:
        rgba(59,130,246,.14);

    color:#93c5fd;
}

.delete-btn{

    background:
        rgba(239,68,68,.14);

    color:#f87171;
}

.admin-btn:hover{

    transform:translateY(-2px);
}

/* =========================================
   EMPTY
========================================= */

.empty-news-card{

    grid-column:1/-1;

    text-align:center;

    padding:70px 30px;

    border-radius:30px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(16px);
}

.empty-icon{

    font-size:4rem;

    color:rgba(255,255,255,.2);

    margin-bottom:20px;
}

.empty-news-card h3{

    color:white;

    font-weight:800;

    margin-bottom:10px;
}

.empty-news-card p{

    color:rgba(255,255,255,.6);

    margin-bottom:25px;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .news-hero{

        padding:30px 24px;
    }

    .news-title{

        font-size:2rem;
    }

    .news-subtitle{

        font-size:.92rem;
    }

    .news-card-body{

        padding:24px;
    }

    .news-grid{

        grid-template-columns:1fr;
    }

    .create-news-btn{

        width:100%;
    }
}

</style>

@endsection