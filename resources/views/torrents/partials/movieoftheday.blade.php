{{-- MOVIE OF THE DAY --}}

@if($movieOfTheDay)

<div class="motd-sticky">

    <div class="modern-motd-card mt-3">

        {{-- BACKDROP --}}
        <div class="modern-motd-backdrop"
             style="background-image:url('{{ $movieOfTheDay->background ?? $movieOfTheDay->poster ?? '/images/default-bg.jpg' }}')">
        </div>

        {{-- OVERLAY --}}
        <div class="modern-motd-overlay"></div>

        <div class="modern-motd-content">

            {{-- POSTER --}}
            <div class="modern-motd-poster">

                <img src="{{ $movieOfTheDay->poster ?? '/images/default-poster.jpg' }}"
                     alt="{{ $movieOfTheDay->name }}">

            </div>

            {{-- INFO --}}
            <div class="modern-motd-info">

                <div class="motd-badge">

                    <i class="bi bi-stars"></i>

                    Movie of the Day

                </div>

                <h1 class="modern-motd-title">

                    <a href="{{ route('torrents.show', [$movieOfTheDay->id, urlencode($movieOfTheDay->slug)]) }}">

                        {{ $movieOfTheDay->clean_name ?? str_replace('.', ' ', $movieOfTheDay->name) }}

                    </a>

                </h1>

                <div class="modern-motd-meta">

                    <span class="motd-category-pill">

                        <i class="bi bi-collection-play-fill"></i>

                        {{ $movieOfTheDay->category->name ?? 'Unknown' }}

                    </span>

                    @if($movieOfTheDay->created_at)

                        <span class="motd-category-pill">

                            <i class="bi bi-calendar3"></i>

                            {{ $movieOfTheDay->created_at->format('M d, Y') }}

                        </span>

                    @endif

                </div>

                {{-- STATS --}}
                <div class="motd-stats">

                    <span class="modern-stat-pill seeders">

                        <i class="bi bi-arrow-up-circle-fill"></i>

                        {{ $movieOfTheDay->seeders }}

                        Seeders

                    </span>

                    <span class="modern-stat-pill leechers">

                        <i class="bi bi-arrow-down-circle-fill"></i>

                        {{ $movieOfTheDay->leechers }}

                        Leechers

                    </span>

                    <span class="modern-stat-pill completed">

                        <i class="bi bi-check-circle-fill"></i>

                        {{ $movieOfTheDay->times_completed }}

                        Completed

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

<style>

/* =========================================================
   FILEIPLAY — MOVIE OF THE DAY
   Dark navy glass + teal forum style
   ========================================================= */

.motd-sticky {
    position: relative;
    width: 100%;
}

.modern-motd-card {
    position: relative;
    overflow: hidden;

    min-height: 220px;

    border-radius: .85rem;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .96),
        rgba(15, 23, 42, .88)
    );

    border: 1px solid var(--ui-border);

    box-shadow: 0 16px 40px rgba(0, 0, 0, .30);

    backdrop-filter: blur(14px);
}

.modern-motd-backdrop {
    position: absolute;
    inset: 0;

    background-size: cover;
    background-position: top center;

    transform: scale(1.03);

    filter:
        blur(2px)
        brightness(.75);
}

.modern-motd-overlay {
    position: absolute;
    inset: 0;

    background: linear-gradient(
        90deg,
        rgba(7, 14, 25, .96) 0%,
        rgba(7, 14, 25, .88) 42%,
        rgba(7, 14, 25, .66) 72%,
        rgba(7, 14, 25, .78) 100%
    );
}

.modern-motd-content {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;

    gap: 22px;

    padding: 22px;
}

.modern-motd-poster {
    flex: 0 0 145px;
}

.modern-motd-poster img {
    display: block;

    width: 145px;
    height: auto;

    border-radius: .7rem;

    border: 1px solid rgba(255, 255, 255, .10);

    box-shadow:
        0 12px 30px rgba(0, 0, 0, .45);

    transition:
        transform .25s ease,
        border-color .25s ease;
}

.modern-motd-card:hover .modern-motd-poster img {
    transform: scale(1.025);

    border-color: rgba(45, 212, 191, .25);
}

.modern-motd-info {
    flex: 1;
    min-width: 0;
}

/* Badge */

.motd-badge {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 6px 10px;

    margin-bottom: 10px;

    border-radius: .5rem;

    background: rgba(45, 212, 191, .08);

    border: 1px solid rgba(45, 212, 191, .20);

    color: var(--ui-accent);

    font-size: 12px;
    font-weight: 700;

    letter-spacing: .35px;

    text-transform: uppercase;
}

.motd-badge i {
    font-size: 12px;
}

/* Title */

.modern-motd-title {
    margin: 0 0 11px;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.45;

    overflow-wrap: anywhere;
}

.modern-motd-title a {
    color: #fff;

    text-decoration: none;

    transition: color .15s ease;
}

.modern-motd-title a:hover {
    color: var(--ui-accent);
}

/* Meta */

.modern-motd-meta {
    display: flex;
    flex-wrap: wrap;

    gap: 7px;

    margin-bottom: 13px;
}

.motd-category-pill {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    padding: 6px 9px;

    border-radius: .5rem;

    background: rgba(255, 255, 255, .035);

    border: 1px solid var(--ui-border);

    color: rgba(255, 255, 255, .70);

    font-size: 12px;
    font-weight: 600;
}

.motd-category-pill i {
    color: var(--ui-accent);
}

/* Stats */

.motd-stats {
    display: flex;
    flex-wrap: wrap;

    gap: 7px;
}

.modern-stat-pill {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    padding: 7px 10px;

    border-radius: .5rem;

    font-size: 12px;
    font-weight: 700;

    backdrop-filter: blur(8px);
}

.modern-stat-pill i {
    font-size: 12px;
}

.modern-stat-pill.seeders {
    background: rgba(34, 197, 94, .08);

    border: 1px solid rgba(34, 197, 94, .18);

    color: #86efac;
}

.modern-stat-pill.leechers {
    background: rgba(239, 68, 68, .08);

    border: 1px solid rgba(239, 68, 68, .18);

    color: #fca5a5;
}

.modern-stat-pill.completed {
    background: rgba(45, 212, 191, .08);

    border: 1px solid rgba(45, 212, 191, .18);

    color: var(--ui-accent);
}

/* Mobile */

@media (max-width: 768px) {

    .modern-motd-card {
        min-height: auto;
        border-radius: .75rem;
    }

    .modern-motd-content {
        gap: 13px;
        padding: 13px;
    }

    .modern-motd-poster {
        flex: 0 0 72px;
    }

    .modern-motd-poster img {
        width: 72px;
        border-radius: .55rem;
    }

    .motd-badge {
        font-size: 11px;
        padding: 5px 8px;
        margin-bottom: 7px;
    }

    .modern-motd-title {
        font-size: 14px;
        line-height: 1.35;
        margin-bottom: 7px;
    }

    .modern-motd-meta {
        gap: 5px;
        margin-bottom: 8px;
    }

    .motd-category-pill {
        font-size: 11px;
        padding: 5px 7px;
    }

    .motd-stats {
        gap: 5px;
    }

    .modern-stat-pill {
        font-size: 11px;
        padding: 6px 8px;
    }
}

</style>

@endif
