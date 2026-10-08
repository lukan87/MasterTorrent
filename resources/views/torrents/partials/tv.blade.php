@include('torrents.partials.css.media-css')
@include('torrents.partials.backdrop-slideshow')
@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])

{{-- =========================
     CAST SECTION
========================= --}}



{{-- =========================
     NEXT EPISODE TO AIR
========================= --}}
@if(!empty($display['next_episode_to_air']))

    @php
        $nextEp = $display['next_episode_to_air'];
    @endphp

    <div class="tv-episode-card next-episode mt-5">

        <div class="episode-card-header">
            <div class="episode-icon-box next-icon">
                <i class="bi bi-calendar2-event"></i>
            </div>
            <div>
                <h3 class="episode-card-title mb-0">Next Episode</h3>
                <div class="episode-card-subtitle">Airing soon</div>
            </div>
        </div>

        <div class="episode-card-body">

            @if($nextEp['still_path'])
                <img src="{{ $nextEp['still_path'] }}"
                     alt="{{ $nextEp['name'] }}"
                     class="episode-still"
                     loading="lazy">
            @endif

            <div class="episode-info">

                <div class="episode-title">
                    {{ $nextEp['name'] ?? 'Episode ' . $nextEp['episode_number'] }}
                </div>

                <div class="episode-meta">

                    @if($nextEp['season_number'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-collection-play"></i>
                            S{{ $nextEp['season_number'] }}E{{ $nextEp['episode_number'] }}
                        </span>
                    @endif

                    @if($nextEp['air_date'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-calendar"></i>
                            {{ \Carbon\Carbon::parse($nextEp['air_date'])->format('M d, Y') }}
                        </span>
                    @endif

                    @if($nextEp['vote_average'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-star-fill"></i>
                            {{ number_format($nextEp['vote_average'], 1) }}
                        </span>
                    @endif

                </div>

                @if($nextEp['overview'])
                    <div class="episode-overview">
                        {{ $nextEp['overview'] }}
                    </div>
                @endif

            </div>

        </div>

    </div>

@endif


@includeWhen(
    $display['type'] === 'movie',
    'torrents.partials.collection'
)

@php
function getTVRatingBadge($rating) {

    $ratings = [

        'TV-Y' => [
            'class' => 'tv-y-rating',
            'icon' => 'bi-balloon-heart',
            'title' => 'TV-Y — All Children.'
        ],

        'TV-Y7' => [
            'class' => 'tv-y7-rating',
            'icon' => 'bi-emoji-sunglasses',
            'title' => 'TV-Y7 — Older Children.'
        ],

        'TV-G' => [
            'class' => 'tv-g-rating',
            'icon' => 'bi-people',
            'title' => 'TV-G — General Audience.'
        ],

        'TV-PG' => [
            'class' => 'tv-pg-rating',
            'icon' => 'bi-exclamation-circle',
            'title' => 'TV-PG — Parental Guidance Suggested.'
        ],

        'TV-14' => [
            'class' => 'tv-14-rating',
            'icon' => 'bi-shield-exclamation',
            'title' => 'TV-14 — Parents Strongly Cautioned.'
        ],

        'TV-MA' => [
            'class' => 'tv-ma-rating',
            'icon' => 'bi-explicit',
            'title' => 'TV-MA — Mature Audience Only.'
        ],

    ];

    $rating = strtoupper($rating);

    if (isset($ratings[$rating])) {

        $r = $ratings[$rating];

        return "<span class='rating-badge {$r['class']}' data-bs-toggle='tooltip' title='{$r['title']}'>
                    <i class='bi {$r['icon']}'></i> {$rating}
                </span>";
    }

    return "<span class='rating-badge'>
                <i class='bi bi-question-circle'></i> {$rating}
            </span>";
}

function getLanguageName($code) {

    $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'ru' => 'Russian',
        'hi' => 'Hindi'
    ];

    return $languages[strtolower($code)] ?? strtoupper($code);
}
@endphp

<style>

/* =========================================================
   FILEIPLAY — RATING BADGES
   ========================================================= */

.rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    padding: 5px 8px;

    border-radius: .45rem;

    background: var(--theme-surface-alt, rgba(255,255,255,0.028));

    border: 1px solid var(--ui-border);

    color: var(--theme-muted, rgba(255, 255, 255, .82));

    font-size: var(--site-font-small, 13px);
    font-weight: 700;

    line-height: 1;
}

.rating-badge i {
    font-size: 12px;
}

.tv-y-rating,
.tv-y7-rating,
.tv-g-rating {
    color: var(--theme-green-text, #86efac);
    border-color: var(--theme-green-border, rgba(134, 239, 172, .20));
    background: var(--theme-green-soft, rgba(134, 239, 172, .06));
}

.tv-pg-rating {
    color: var(--theme-amber-text, #fde68a);
    border-color: var(--theme-amber-border, rgba(253, 230, 138, .20));
    background: var(--theme-amber-soft, rgba(253, 230, 138, .06));
}

.tv-14-rating {
    color: var(--theme-amber-text, #fdba74);
    border-color: var(--theme-amber-border, rgba(253, 186, 116, .20));
    background: var(--theme-amber-soft, rgba(253, 186, 116, .06));
}

.tv-ma-rating {
    color: var(--theme-red-text, #fca5a5);
    border-color: var(--theme-red-border, rgba(252, 165, 165, .20));
    background: var(--theme-red-soft, rgba(252, 165, 165, .06));
}

/* =========================================================
   TV EPISODE CARDS (Next / Last to Air)
   ========================================================= */

.tv-episode-card {
    overflow: hidden;
    border-radius: .85rem;
    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.95)),
        var(--theme-surface, rgba(10,15,27,.84))
    );
    border: 1px solid var(--ui-border);
    box-shadow: 0 14px 36px var(--theme-shadow, rgba(0, 0, 0, .28));
    backdrop-filter: blur(14px);
}

.episode-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--ui-border);
    background: var(--theme-teal-soft, rgba(45, 212, 191, .045));
}

.episode-icon-box {
    width: 38px;
    height: 38px;
    border-radius: .55rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-text, #fff);
    font-size: 16px;
}

.next-icon {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .16));
    color: var(--ui-accent);
}

.last-icon {
    background: var(--theme-surface-alt, rgba(148,163,184,0.112));
    color: var(--theme-muted, #94a3b8);
}

.episode-card-title {
    color: var(--theme-text, #fff);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.episode-card-subtitle {
    color: var(--theme-muted, rgba(255, 255, 255, .42));
    font-size: var(--site-font-small, 13px);
}

.episode-card-body {
    display: flex;
    gap: 14px;
    padding: 16px;
}

.episode-still {
    width: 160px;
    height: 90px;
    object-fit: cover;
    border-radius: .55rem;
    flex: 0 0 auto;
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .07));
}

.episode-info {
    min-width: 0;
    flex: 1;
}

.episode-title {
    color: var(--theme-text, rgba(255, 255, 255, .92));
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.episode-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 7px;
}

.episode-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: .45rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.028));
    border: 1px solid var(--ui-border);
    color: var(--theme-muted, rgba(255, 255, 255, .68));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
}

.episode-meta-pill i {
    color: var(--ui-accent);
    font-size: 10px;
}

.episode-overview {
    margin-top: 9px;
    color: var(--theme-muted, rgba(255, 255, 255, .55));
    font-size: var(--site-font-small, 13px);
    line-height: 1.55;

    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* =========================================================
   TV SEASONS
   ========================================================= */

.tv-seasons-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 12px;
}

/* More than 10 seasons */
.tv-seasons-row.seasons-scrollable {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 12px;
    padding-bottom: 12px;
    scroll-behavior: smooth;
}

/* Fixed card width when scrolling */
.tv-seasons-row.seasons-scrollable .season-card {
    flex: 0 0 170px;
    width: 170px;
}

/* Scrollbar */
.tv-seasons-row.seasons-scrollable::-webkit-scrollbar {
    height: 7px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-track {
    background: var(--theme-surface-alt, rgba(255,255,255,0.028));
    border-radius: 10px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-thumb {
    background: var(--theme-teal-soft, rgba(45, 212, 191, 0.45));
    border-radius: 10px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-thumb:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, 0.7));
}

.tv-seasons-row.seasons-scrollable {
    scrollbar-width: thin;
    scrollbar-color: var(--theme-teal-border, rgba(45, 212, 191, 0.45))
                     var(--theme-border, rgba(255, 255, 255, 0.04));
}

.season-card {
    overflow: hidden;
    border-radius: .7rem;
    background: var(--theme-surface, rgba(6,10,19,.48));
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .055));
    transition:
        transform .18s ease,
        border-color .18s ease;
}

.season-card:hover {
    transform: translateY(-3px);
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .28));
}

.season-poster-wrap {
    position: relative;
    aspect-ratio: 2 / 3;
    overflow: hidden;
    background: #0a0f1b;
}

.season-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .25s ease;
}

.season-card:hover .season-poster {
    transform: scale(1.04);
}

.season-rating {
    position: absolute;
    top: 6px;
    right: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 6px;
    border-radius: .4rem;
    background: var(--theme-surface-alt, rgba(3,6,12,.82));
    color: var(--theme-amber-text, #facc15);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.season-info {
    padding: 10px;
}

.season-name {
    color: var(--theme-text, rgba(255, 255, 255, .85));
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    line-height: 1.35;
}

.season-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
    color: var(--theme-muted, rgba(255, 255, 255, .48));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
}

.season-meta i {
    color: var(--ui-accent);
    font-size: 10px;
}

.season-overview {
    margin-top: 7px;
    color: var(--theme-muted, rgba(255, 255, 255, .52));
    font-size: var(--site-font-small, 13px);
    line-height: 1.5;

    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .episode-card-body {
        flex-direction: column;
    }

    .episode-still {
        width: 100%;
        height: auto;
    }

   .tv-seasons-row:not(.seasons-scrollable) {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 9px;
    }

    .tv-seasons-row.seasons-scrollable {
        gap: 9px;
    }

    .tv-seasons-row.seasons-scrollable .season-card {
        flex: 0 0 130px;
        width: 130px;
    }
}

</style>
