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

/* =========================================================
   FILEIPLAY NEWS
   Matches the established forum UI
   ========================================================= */

.modern-news-card,
.modern-news-empty {
    position: relative;
    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 10px 26px rgba(0, 0, 0, .16),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        transform 160ms ease,
        border-color 160ms ease,
        box-shadow 160ms ease;
}


/* subtle top highlight */
.modern-news-card::before,
.modern-news-empty::before {
    content: "";

    position: absolute;

    top: 0;
    left: 8%;
    right: 8%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, .07),
            transparent
        );

    pointer-events: none;
}


/* teal side accent */
.modern-news-card::after,
.modern-news-empty::after {
    content: "";

    position: absolute;

    top: 18px;
    bottom: 18px;
    left: 0;

    width: 3px;

    background:
        linear-gradient(
            180deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border-radius: 0 4px 4px 0;

    opacity: .75;

    pointer-events: none;
}


.modern-news-card:hover {
    transform: translateY(-2px);

    border-color:
        rgba(99, 210, 198, .20);

    box-shadow:
        0 14px 32px rgba(0, 0, 0, .22),
        0 0 0 1px rgba(99, 210, 198, .025);
}


/* =========================================================
   IMAGE
   ========================================================= */

.modern-news-image-wrap {
    position: relative;

    overflow: hidden;

    background:
        rgba(8, 15, 29, .65);

    border-bottom:
        1px solid rgba(148, 163, 184, .09);
}


.modern-news-image {
    display: block;

    width: 100%;
    height: 145px;

    object-fit: cover;

    opacity: .88;

    transition:
        transform 300ms ease,
        opacity 300ms ease;
}


.modern-news-card:hover .modern-news-image {
    transform: scale(1.015);
    opacity: .96;
}


/* dark image overlay */
.modern-news-image-wrap::after {
    content: "";

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            180deg,
            rgba(8, 15, 29, .02),
            rgba(8, 15, 29, .30)
        );

    pointer-events: none;
}


/* =========================================================
   HEADER
   ========================================================= */

.modern-news-header {
    position: relative;
    z-index: 2;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    padding: 1rem 1.15rem .9rem;

    border-bottom:
        1px solid rgba(148, 163, 184, .08);
}


/* =========================================================
   NEWS ICON
   ========================================================= */

.news-icon-box {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .075);

    border:
        1px solid rgba(99, 210, 198, .14);

    border-radius: .65rem;

    font-size: .95rem;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        background 160ms ease,
        border-color 160ms ease;
}


.modern-news-card:hover .news-icon-box {
    background:
        rgba(99, 210, 198, .11);

    border-color:
        rgba(99, 210, 198, .22);
}


/* =========================================================
   TITLE
   ========================================================= */

.modern-news-title {
    margin: 0;

    color: #f1f5f9;

    font-size: 1rem;
    font-weight: 800;

    line-height: 1.35;

    overflow-wrap: anywhere;
}


.modern-news-title a {
    color: #f1f5f9;

    text-decoration: none;

    transition: color 160ms ease;
}


.modern-news-title a:hover {
    color: var(--ui-accent);
}


/* =========================================================
   META
   ========================================================= */

.modern-news-meta {
    display: flex;

    align-items: center;
    flex-wrap: wrap;

    gap: .45rem .9rem;

    margin-top: .35rem;

    color: #71859b;

    font-size: .72rem;
}


.modern-news-meta span {
    display: inline-flex;

    align-items: center;

    gap: .3rem;
}


.modern-news-meta i {
    color: #60758b;

    font-size: .7rem;
}


/* =========================================================
   ADMIN ACTIONS
   ========================================================= */

.modern-news-actions {
    display: flex;

    align-items: center;

    gap: .35rem;

    flex-shrink: 0;
}


.news-action-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 30px;
    height: 30px;

    color: #8295aa;

    background:
        rgba(148, 163, 184, .045);

    border:
        1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .72rem;

    text-decoration: none;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}


.news-action-btn:hover {
    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .07);

    border-color:
        rgba(99, 210, 198, .17);

    transform: translateY(-1px);
}


.news-action-btn.edit-btn:hover {
    color: #c9f3ee;

    background:
        rgba(99, 210, 198, .07);

    border-color:
        rgba(99, 210, 198, .17);
}


/* =========================================================
   BODY
   ========================================================= */

.modern-news-body {
    position: relative;
    z-index: 1;

    padding: 1rem 1.15rem;
}


.news-content-preview {
    color: #c2cedb;

    font-size: .95rem;

    line-height: 1.7;
}


.news-content-preview p {
    margin-bottom: .65rem;
}


.news-content-preview p:last-child {
    margin-bottom: 0;
}


.news-content-preview strong {
    color: #e5edf7;
}


.news-content-preview a {
    color: var(--ui-accent);

    text-decoration: none;
}


.news-content-preview a:hover {
    text-decoration: underline;
}


.news-content-preview img {
    display: block;

    max-width: 100%;
    height: auto;

    margin: .7rem 0;

    border:
        1px solid rgba(148, 163, 184, .12);

    border-radius: .65rem;
}


/* =========================================================
   FOOTER
   ========================================================= */

.modern-news-footer {
    position: relative;
    z-index: 2;

    display: flex;

    align-items: center;

    padding: .8rem 1.15rem 1rem;

    border-top:
        1px solid rgba(148, 163, 184, .07);
}


.read-more-btn {
    display: inline-flex;

    align-items: center;

    min-height: 30px;

    padding: .35rem .65rem;

    color: #8295aa;

    background:
        rgba(148, 163, 184, .045);

    border:
        1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .72rem;
    font-weight: 700;

    text-decoration: none;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}


.read-more-btn:hover {
    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .07);

    border-color:
        rgba(99, 210, 198, .17);

    transform: translateY(-1px);
}


.read-more-btn i {
    color: var(--ui-accent);

    font-size: .8rem;
}


/* =========================================================
   EMPTY NEWS STATE
   ========================================================= */

.modern-news-empty {
    padding: 2rem 1.25rem;
}


.modern-news-empty .position-relative {
    position: relative !important;
    z-index: 2;
}


.empty-icon {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 48px;
    height: 48px;

    margin: 0 auto .85rem;

    color: var(--ui-accent);

    background:
        rgba(99, 210, 198, .075);

    border:
        1px solid rgba(99, 210, 198, .14);

    border-radius: .75rem;

    font-size: 1.05rem;
}


.modern-news-empty h6 {
    color: #f1f5f9;

    font-size: .95rem;
    font-weight: 800;
}


.modern-news-empty p {
    color: #71859b !important;

    font-size: .78rem;
}


/* =========================================================
   CREATE NEWS BUTTON
   ========================================================= */

.modern-news-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 34px;

    padding: .42rem .75rem;

    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border:
        1px solid rgba(99, 210, 198, .25);

    border-radius: .5rem;

    font-size: .8rem;
    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .15);

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        filter 160ms ease;
}


.modern-news-btn:hover {
    color: #062523;

    transform: translateY(-1px);

    filter: brightness(1.05);

    box-shadow:
        0 6px 16px rgba(0, 0, 0, .2);
}


/* =========================================================
   GLOW
   Very subtle — matches forum rather than flashy UI
   ========================================================= */

.news-card-glow,
.empty-glow {
    position: absolute;

    top: -80px;
    right: -80px;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(99, 210, 198, .055),
            transparent 70%
        );

    pointer-events: none;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    .modern-news-header {
        align-items: flex-start;

        padding: .85rem .9rem .75rem;
    }

    .news-icon-box {
        width: 35px;
        height: 35px;

        flex-basis: 35px;
    }

    .modern-news-title {
        font-size: .92rem;
    }

    .modern-news-meta {
        font-size: 1rem;

        gap: .35rem .7rem;
    }

    .modern-news-body {
        padding: .85rem .9rem;
    }

    .news-content-preview {
        font-size: .84rem;
    }

    .modern-news-footer {
        padding: .7rem .9rem .85rem;
    }

    .modern-news-image {
        height: 115px;
    }

    .modern-news-actions {
        gap: .25rem;
    }

    .news-action-btn {
        width: 28px;
        height: 28px;
    }

    .modern-news-empty {
        padding: 1.5rem 1rem;
    }
}


/* =========================================================
   SMALL MOBILE
   ========================================================= */

@media (max-width: 480px) {

    .modern-news-header {
        gap: .65rem;
    }

    .modern-news-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: .2rem;
    }

    .modern-news-actions {
        margin-left: auto;
    }
}

</style>