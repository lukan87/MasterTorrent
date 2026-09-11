@if(!empty($display['recommendations']))

<div class="tmdb-recs mt-3">

    <div class="tmdb-recs-header">

        <div>
            <h5 class="tmdb-recs-title mb-0">
                <i class="bi bi-stars me-2"></i>
                You Might Also Like
            </h5>

            <div class="tmdb-recs-subtitle">
                Recommendations from The Movie Database
            </div>
        </div>

        @if($torrent->tmdbid)

            <a href="https://www.themoviedb.org/{{ $display['type'] }}/{{ $torrent->tmdbid }}/recommendations"
               target="_blank"
               rel="noreferrer"
               class="tmdb-recs-link">

                <i class="bi bi-box-arrow-up-right me-1"></i>
                View All

            </a>

        @endif

    </div>

    <div class="tmdb-recs-body">

        <div class="tmdb-recs-row">

            @foreach($display['recommendations'] as $rec)

                <a href="https://www.themoviedb.org/{{ $display['type'] }}/{{ $rec['id'] }}"
                   target="_blank"
                   rel="noreferrer"
                   class="tmdb-recs-card text-decoration-none">

                    <div class="tmdb-recs-poster-wrap">

                        <img
                            src="{{ $rec['poster'] ?? '/images/not-found.jpg' }}"
                            loading="lazy"
                            class="tmdb-recs-poster"
                            alt="{{ $rec['title'] }}"
                        >

                        @if($rec['rating'])

                            <span class="tmdb-recs-rating">
                                <i class="bi bi-star-fill"></i>
                                {{ number_format($rec['rating'], 1) }}
                            </span>

                        @endif

                    </div>

                    <div class="tmdb-recs-info">

                        <div class="tmdb-recs-name">
                            {{ $rec['title'] }}
                        </div>

                        @if($rec['year'])

                            <div class="tmdb-recs-year">
                                {{ $rec['year'] }}
                            </div>

                        @endif

                    </div>

                </a>

            @endforeach

        </div>

    </div>

</div>

<style>
/* =========================================================
   FILEIPLAY — TMDB RECOMMENDATIONS
   Dark navy glass + teal accent
   ========================================================= */

.tmdb-recs {
    overflow: hidden;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );

    border: 1px solid var(--ui-border);
    border-radius: .85rem;

    box-shadow: 0 14px 36px rgba(0, 0, 0, .28);

    backdrop-filter: blur(14px);
}
/* Header */

.tmdb-recs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;

    padding: 14px 16px;

    background: rgba(45, 212, 191, .045);

    border-bottom: 1px solid var(--ui-border);
}

.tmdb-recs-title {
    color: #fff;

    font-size: 14px;
    font-weight: 700;
}

.tmdb-recs-title i {
    color: var(--ui-accent);
}

.tmdb-recs-subtitle {
    margin-top: 3px;

    color: rgba(255, 255, 255, .42);

    font-size: 12px;
}

.tmdb-recs-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    padding: 6px 10px;

    border-radius: .5rem;

    background: rgba(45, 212, 191, .07);
    border: 1px solid rgba(45, 212, 191, .18);

    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 600;

    transition: background .15s ease, border-color .15s ease;
}

.tmdb-recs-link:hover {
    background: rgba(45, 212, 191, .12);
    border-color: rgba(45, 212, 191, .32);
    color: var(--ui-accent);
}

/* Body */

.tmdb-recs-body {
    padding: 12px;
}

.tmdb-recs-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;

    overflow-x: auto;
    overflow-y: hidden;

    padding: 3px 2px 8px;

    scroll-behavior: smooth;
    scrollbar-width: none;
}

.tmdb-recs-row::-webkit-scrollbar {
    display: none;
}
/* Card */

.tmdb-recs-card {
    flex: 0 0 140px;

    width: 140px;
    min-width: 140px;
    max-width: 140px;

    padding: 8px;

    background: rgba(9, 16, 29, .48);

    border: 1px solid rgba(255, 255, 255, .055);
    border-radius: .6rem;

    transition:
        background .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.tmdb-recs-card:hover {
    background: rgba(45, 212, 191, .05);
    border-color: rgba(45, 212, 191, .24);

    transform: translateY(-2px);
}

/* Poster */

.tmdb-recs-poster-wrap {
    position: relative;

    aspect-ratio: 2 / 3;

    border-radius: .45rem;

    overflow: hidden;

    background: #0f172a;
}

.tmdb-recs-poster {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform .3s ease;
}

.tmdb-recs-card:hover .tmdb-recs-poster {
    transform: scale(1.04);
}

.tmdb-recs-rating {
    position: absolute;

    top: 6px;
    right: 6px;

    display: inline-flex;
    align-items: center;
    gap: 3px;

    padding: 3px 6px;

    border-radius: .4rem;

    background: rgba(5, 10, 18, .82);

    color: #facc15;
    font-size: 11px;
    font-weight: 700;
}

/* Info */

.tmdb-recs-info {
    padding: 7px 2px 2px;
}

.tmdb-recs-name {
    overflow: hidden;

    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.tmdb-recs-year {
    margin-top: 3px;

    color: rgba(255, 255, 255, .42);
    font-size: 11px;
}

/* Mobile */

@media (max-width: 768px) {

    .tmdb-recs-header {
        padding: 12px 13px;
    }

    .tmdb-recs-body {
        padding: 9px;
    }

    .tmdb-recs-card {
        flex: 0 0 118px;

        width: 118px;
        min-width: 118px;
        max-width: 118px;
    }
}

</style>

@endif