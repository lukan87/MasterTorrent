@if($latestNews->isEmpty())

<div class="col-12 mt-3">

    <div class="modern-news-empty">

        <div class="empty-glow"></div>

        <div class="position-relative z-2 text-center">

            <div class="empty-icon">

                <i class="bi bi-newspaper"></i>

            </div>

            <h6 class="mb-1 text-white">

                No news available

            </h6>

            <p class="text-muted small mb-3">

                Check back later for tracker updates.

            </p>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                <a href="{{ route('news.create') }}"
                   class="modern-news-btn">

                    <i class="bi bi-plus-circle-fill me-1"></i>

                    Create News

                </a>

            @endif

        </div>

    </div>

</div>

@else

@foreach($latestNews as $news)

<div class="col-12 mt-2">

    <div class="modern-news-card">

        {{-- Glow --}}
        <div class="news-card-glow"></div>

        {{-- IMAGE --}}
        @if($news->image)

        <div class="modern-news-image-wrap">

            <img src="{{ asset('storage/' . $news->image) }}"
                 class="modern-news-image"
                 alt="{{ $news->title }}">

        </div>

        @endif

        {{-- HEADER --}}
        <div class="modern-news-header">

            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">

                <div class="news-icon-box">

                    <i class="bi bi-megaphone-fill"></i>

                </div>

                <div class="flex-grow-1 min-w-0">

                    <h6 class="modern-news-title mb-1">

                        <a href="{{ route('news.show', $news) }}">

                            {{ $news->title }}

                        </a>

                    </h6>

                    <div class="modern-news-meta">

                        <span>

                            <i class="bi bi-person-circle"></i>

                            {{ $news->user->name }}

                        </span>

                        <span>

                            <i class="bi bi-calendar3"></i>

                            {{ $news->created_at->format('M j, Y') }}

                        </span>

                    </div>

                </div>

            </div>

            {{-- ACTIONS --}}
            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

            <div class="modern-news-actions">

                <a href="{{ route('news.create') }}"
                   class="news-action-btn"
                   data-bs-toggle="tooltip"
                   title="Create News">

                    <i class="bi bi-plus-lg"></i>

                </a>

                <a href="{{ route('news.edit', $news) }}"
                   class="news-action-btn edit-btn"
                   data-bs-toggle="tooltip"
                   title="Edit News">

                    <i class="bi bi-pencil"></i>

                </a>

            </div>

            @endif

        </div>

        {{-- BODY --}}
        <div class="modern-news-body">

            <div class="news-content-preview">

                {!! convertCustomTagsToHtml($news->content) !!}

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="modern-news-footer">

            <a href="{{ route('news.show', $news) }}"
               class="read-more-btn">

                <i class="bi bi-arrow-right-circle me-1"></i>

                Read More

            </a>

        </div>

    </div>

</div>

@endforeach

@endif

<style>

/* =========================================
   NEWS CARD
========================================= */

.modern-news-card{

    position:relative;

    overflow:hidden;

    border-radius:16px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.045),
            rgba(255,255,255,.018)
        );

    border:
        1px solid rgba(255,255,255,.05);

    backdrop-filter:blur(12px);

    box-shadow:
        0 8px 22px rgba(0,0,0,.18);

    transition:.22s ease;
}

.modern-news-card:hover{

    transform:translateY(-2px);

    box-shadow:
        0 12px 28px rgba(0,0,0,.24);
}

.news-card-glow{

    position:absolute;

    top:-60px;
    right:-60px;

    width:140px;
    height:140px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.10),
            transparent 70%
        );
}

/* =========================================
   IMAGE
========================================= */

.modern-news-image-wrap{

    position:relative;

    overflow:hidden;

    max-height:130px;
}

.modern-news-image{

    width:100%;

    height:130px;

    object-fit:cover;

    transition:transform .3s ease;
}

.modern-news-card:hover .modern-news-image{

    transform:scale(1.01);
}

/* =========================================
   HEADER
========================================= */

.modern-news-header{

    position:relative;

    z-index:2;

    padding:12px 14px 10px;

    display:flex;

    align-items:flex-start;

    justify-content:space-between;

    gap:10px;

    border-bottom:
        1px solid rgba(255,255,255,.04);
}

.news-icon-box{

    width:34px;
    height:34px;

    border-radius:10px;

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

    font-size:1rem;

    flex-shrink:0;
}

.modern-news-title{

    margin:0;

    font-size:1rem;

    font-weight:700;

    line-height:1.35;
}

.modern-news-title a{

    color:white;

    text-decoration:none;
}

.modern-news-title a:hover{

    color:#93c5fd;
}

.modern-news-meta{

    display:flex;

    flex-wrap:wrap;

    gap:8px;

    margin-top:4px;

    color:rgba(255,255,255,.52);

    font-size:1rem;
}

.modern-news-meta span{

    display:flex;

    align-items:center;

    gap:4px;
}

/* =========================================
   ACTIONS
========================================= */

.modern-news-actions{

    display:flex;

    gap:5px;
}

.news-action-btn{

    width:30px;
    height:30px;

    border-radius:8px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid rgba(255,255,255,.05);

    color:#cbd5e1;

    text-decoration:none;

    font-size:1rem;

    transition:.18s ease;
}

.news-action-btn:hover{

    background:
        rgba(59,130,246,.14);

    color:#93c5fd;

    transform:translateY(-1px);
}

.edit-btn:hover{

    background:
        rgba(250,204,21,.14);

    color:#fde047;
}

/* =========================================
   BODY
========================================= */

.modern-news-body{

    padding:12px 14px;
}

.news-content-preview{

    color:rgba(255,255,255,.82);

    line-height:1.7;

    font-size:1rem;
}

.news-content-preview p{

    margin-bottom:.8rem;
}

.news-content-preview img{

    max-width:100%;

    border-radius:10px;

    margin:8px 0;
}

/* =========================================
   FOOTER
========================================= */

.modern-news-footer{

    padding:0 14px 14px;
}

.read-more-btn{

    display:inline-flex;

    align-items:center;

    padding:8px 13px;

    border-radius:10px;

    text-decoration:none;

    background:
        rgba(59,130,246,.10);

    border:
        1px solid rgba(59,130,246,.15);

    color:#93c5fd;

    font-size:1rem;

    font-weight:700;

    transition:.18s ease;
}

.read-more-btn:hover{

    color:white;

    background:
        rgba(59,130,246,.18);

    transform:translateY(-1px);
}

/* =========================================
   EMPTY STATE
========================================= */

.modern-news-empty{

    position:relative;

    overflow:hidden;

    padding:24px 18px;

    border-radius:16px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.045),
            rgba(255,255,255,.018)
        );

    border:
        1px solid rgba(255,255,255,.05);

    backdrop-filter:blur(12px);

    box-shadow:
        0 8px 20px rgba(0,0,0,.18);
}

.empty-glow{

    position:absolute;

    top:-60px;
    right:-60px;

    width:140px;
    height:140px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(59,130,246,.12),
            transparent 70%
        );
}

.empty-icon{

    width:48px;
    height:48px;

    margin:0 auto 10px;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:1rem;

    color:white;
}

.modern-news-btn{

    display:inline-flex;

    align-items:center;

    padding:8px 14px;

    border-radius:10px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    text-decoration:none;

    font-size:1rem;

    font-weight:700;

    transition:.18s ease;
}

.modern-news-btn:hover{

    transform:translateY(-1px);

    color:white;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-news-image{

        height:110px;
    }

    .modern-news-header{

        padding:10px 12px 8px;
    }

    .modern-news-body{

        padding:10px 12px;
    }

    .modern-news-footer{

        padding:0 12px 12px;
    }

    .modern-news-title,
    .modern-news-meta,
    .news-content-preview,
    .read-more-btn{

        font-size:1rem;
    }
}

</style>