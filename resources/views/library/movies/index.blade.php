@extends('layouts.app')

@section('content')

<div class="lib-page container-fluid py-3 px-lg-4 px-3">

    {{-- HERO --}}
    @php $hero = $featured->first(); @endphp
    @if($hero && $hero->backdrop)
        <div class="lib-hero mb-4" style="background-image:url('https://image.tmdb.org/t/p/w1280/{{ $hero->backdrop }}')">
            <div class="lib-hero-overlay"></div>
            <div class="lib-hero-content position-relative z-2">
                <span class="lib-hero-badge"><i class="bi bi-film me-1"></i> MOVIE LIBRARY</span>
                <h1 class="lib-hero-title mt-3">{{ $hero->title }}</h1>
                <p class="lib-hero-sub">
                    <i class="bi bi-star-fill text-warning"></i> {{ number_format($hero->rating ?? 0, 1) }}
                    @if($hero->year)
                        &bull; {{ $hero->year }}
                    @endif
                    &bull; {{ $movies->total() }} titles available
                </p>
                <a href="{{ route('library.movies.show', ['tmdbid' => $hero->tmdbid, 'slug' => $hero->slug]) }}" class="lib-btn-hero">
                    <i class="bi bi-play-circle me-1"></i> View Details
                </a>
            </div>
        </div>
    @endif

    {{-- HEADER + SEARCH --}}
    <div class="lib-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="lib-page-title mb-1">
                    <i class="bi bi-film me-2"></i>Movies
                </h1>
                <p class="lib-page-sub mb-0">
                    Torrents with active seeders &middot; {{ $movies->total() }} titles
                </p>
            </div>
        </div>

        <div class="lib-search mt-3">
            <form method="GET" action="{{ route('library.movies.index') }}" class="lib-search-form">
                <i class="bi bi-search lib-search-icon"></i>
                <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search your movie library..." class="lib-search-input">
                @if(!empty($query))
                    <a href="{{ route('library.movies.index') }}" class="lib-search-clear" title="Clear search"><i class="bi bi-x-lg"></i></a>
                @endif
                <button type="submit" class="lib-search-btn"><i class="bi bi-search me-1"></i> Search</button>
            </form>
        </div>

        @if(!empty($query))
            <p class="lib-search-result-text mt-3 mb-0">
                Results for <strong>{{ $query }}</strong>
                <span class="text-muted">&middot; {{ $movies->total() }} found</span>
            </p>
        @endif
    </div>

    {{-- GRID --}}
    <div class="row g-3">
        @forelse($movies as $i => $movie)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 lib-card-col" style="--i:{{ $i }}">
                <a href="{{ route('library.movies.show', ['tmdbid' => $movie->tmdbid, 'slug' => $movie->slug]) }}" class="text-decoration-none d-block">
                    <div class="lib-card">
                        <div class="lib-card-poster">
                            <img src="{{ $movie->poster ? 'https://image.tmdb.org/t/p/w500' . $movie->poster : asset('images/no-poster.png') }}" alt="{{ $movie->title }}" loading="lazy">
                            @if($movie->rating)
                                <span class="lib-badge lib-badge-rating"><i class="bi bi-star-fill"></i> {{ number_format($movie->rating, 1) }}</span>
                            @endif
                            @if($movie->year)
                                <span class="lib-badge lib-badge-year">{{ $movie->year }}</span>
                            @endif
                            @if(!empty($movie->max_seeders))
                                <span class="lib-badge lib-badge-seeders"><i class="bi bi-arrow-up-circle-fill"></i> {{ $movie->max_seeders }}</span>
                            @endif
                            <div class="lib-card-overlay">
                                <div class="lib-card-overlay-inner">
                                    <i class="bi bi-play-circle-fill lib-play-icon"></i>
                                    <span class="lib-overlay-label">View Details</span>
                                </div>
                            </div>
                        </div>
                        <div class="lib-card-meta">
                            <h5 class="lib-card-title">{{ Str::limit($movie->title, 35) }}</h5>
                            <div class="lib-card-info">
                                @if($movie->year)
                                    <span>{{ $movie->year }}</span>
                                @endif
                                @if($movie->max_seeders)
                                    <span><i class="bi bi-arrow-up-circle-fill"></i> {{ $movie->max_seeders }} seeders</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="lib-empty">
                    <i class="bi bi-film"></i>
                    <h4>No movies found</h4>
                    <p>Try searching for a different title, or check back later.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($movies->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $movies->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

@endsection

<style>
/* ══════════════════════════════════════════════════════════════
   LIBRARY MOVIES — Premium Dark Theme
   ══════════════════════════════════════════════════════════════ */

.lib-page { padding-bottom: 2rem; }

/* ── HERO ─────────────────────────────────────────────────── */
.lib-hero {
    position: relative; min-height: 340px; overflow: hidden;
    display: flex; align-items: flex-end;
    background-size: cover; background-position: center top;
    background-color: var(--ui-surface);
    border: 1px solid var(--ui-border); border-radius: .9rem;
    box-shadow: var(--ui-shadow);
}
.lib-hero::after {
    content: ""; position: absolute; inset: 0;
    border-left: 3px solid var(--ui-accent); pointer-events: none;
}
.lib-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(5,12,22,.97) 0%, rgba(5,12,22,.86) 40%, rgba(5,12,22,.45) 72%, rgba(5,12,22,.2) 100%);
}
.lib-hero-content { padding: 1.6rem; max-width: 720px; }
.lib-hero-badge { display: inline-block; padding: .28rem .7rem; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: 2px; color: #061311; background: var(--ui-accent); }
.lib-hero-title { color: #f1f5f9; font-size: 2rem; font-weight: 800; line-height: 1.15; margin-bottom: .35rem; }
.lib-hero-sub  { color: #cbd5e1; font-size: 14px; margin-bottom: 1rem; }
.lib-btn-hero { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.1rem; border-radius: .6rem; font-weight: 700; color: #061311; background: var(--ui-accent); border: 0; text-decoration: none; transition: filter .18s; }
.lib-btn-hero:hover { filter: brightness(1.08); color: #061311; }

/* ── HEADER ────────────────────────────────────────────────── */
.lib-page-title { color: #f1f5f9; font-size: 1.5rem; font-weight: 800; }
.lib-page-title i { color: var(--ui-accent); }
.lib-page-sub   { color: var(--ui-text-muted); font-size: 13px; }

/* ── SEARCH ────────────────────────────────────────────────── */
.lib-search-form { display: flex; align-items: center; gap: .5rem; padding: .35rem .65rem; background: var(--ui-surface); border: 1px solid var(--ui-border); border-radius: .6rem; transition: border-color .18s, box-shadow .18s; }
.lib-search-form:focus-within { border-color: var(--ui-accent); box-shadow: 0 0 0 2px rgba(99,210,198,.1); }
.lib-search-icon { color: var(--ui-accent); font-size: 14px; }
.lib-search-input { flex: 1; border: 0; outline: 0; background: transparent; color: #e2e8f0; font-size: 13px; min-width: 180px; }
.lib-search-input::placeholder { color: #64748b; }
.lib-search-clear { color: #64748b; font-size: 12px; padding: 4px 6px; transition: color .15s; }
.lib-search-clear:hover { color: #f1f5f9; }
.lib-search-btn { display: inline-flex; align-items: center; gap: .3rem; padding: .4rem .75rem; border: 0; border-radius: .45rem; font-weight: 700; font-size: 13px; color: #061311; background: var(--ui-accent); cursor: pointer; white-space: nowrap; transition: filter .15s; }
.lib-search-btn:hover { filter: brightness(1.08); }
.lib-search-result-text { color: var(--ui-text-muted); font-size: 13px; }
.lib-search-result-text strong { color: #f1f5f9; }

/* ── CARD ──────────────────────────────────────────────────── */
.lib-card {
    position: relative; border-radius: .65rem; overflow: hidden;
    background: var(--ui-surface); border: 1px solid var(--ui-border);
    transition: transform .28s cubic-bezier(.22,1,.36,1), box-shadow .28s, border-color .28s;
}
.lib-card:hover {
    transform: translateY(-6px) scale(1.02); z-index: 5;
    box-shadow: 0 20px 40px rgba(0,0,0,.55), 0 0 20px rgba(99,210,198,.06);
    border-color: rgba(99,210,198,.25);
}
.lib-card-poster { position: relative; overflow: hidden; aspect-ratio: 2/3; }
.lib-card-poster img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .4s cubic-bezier(.22,1,.36,1);
}
.lib-card:hover .lib-card-poster img { transform: scale(1.08); }

/* ── BADGES ────────────────────────────────────────────────── */
.lib-badge {
    position: absolute; z-index: 3; padding: .2rem .45rem;
    border-radius: .35rem; font-size: 11px; font-weight: 800;
    line-height: 1; box-shadow: 0 4px 12px rgba(0,0,0,.35); pointer-events: none;
}
.lib-badge-rating { top: 8px; left: 8px; color: #061311; background: var(--ui-accent); }
.lib-badge-rating i { color: #061311; font-size: 9px; }
.lib-badge-year { top: 8px; right: 8px; color: #f8fafc; background: rgba(5,12,22,.72); backdrop-filter: blur(4px); }
.lib-badge-seeders { bottom: 8px; left: 8px; color: #22c55e; background: rgba(5,12,22,.82); backdrop-filter: blur(4px); }
.lib-badge-seeders i { font-size: 9px; }

/* ── OVERLAY ───────────────────────────────────────────────── */
.lib-card-overlay {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(to top, rgba(5,12,22,.92) 0%, rgba(5,12,22,.45) 50%, transparent 100%);
    opacity: 0; transition: opacity .25s ease;
}
.lib-card:hover .lib-card-overlay { opacity: 1; }
.lib-card-overlay-inner {
    display: flex; flex-direction: column; align-items: center; gap: .35rem;
    transform: translateY(10px); transition: transform .28s cubic-bezier(.22,1,.36,1);
}
.lib-card:hover .lib-card-overlay-inner { transform: translateY(0); }
.lib-play-icon { font-size: 36px; color: var(--ui-accent); filter: drop-shadow(0 4px 12px rgba(99,210,198,.35)); }
.lib-overlay-label { font-size: 12px; font-weight: 700; color: #f1f5f9; letter-spacing: .5px; text-transform: uppercase; }

/* ── CARD META ─────────────────────────────────────────────── */
.lib-card-meta { padding: .6rem .65rem .7rem; }
.lib-card-title {
    color: #e2e8f0; font-size: .82rem; font-weight: 700; margin: 0; line-height: 1.25;
    transition: color .18s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.lib-card:hover .lib-card-title { color: var(--ui-accent); }
.lib-card-info { display: flex; align-items: center; gap: .5rem; margin-top: .25rem; font-size: 11px; color: var(--ui-text-muted); }
.lib-card-info i { color: #22c55e; font-size: 9px; }

/* ── EMPTY STATE ───────────────────────────────────────────── */
.lib-empty { padding: 3rem 0; }
.lib-empty i { display: block; margin-bottom: .65rem; font-size: 38px; color: var(--ui-accent); opacity: .6; }
.lib-empty h4 { margin: 0 0 .25rem; color: #f1f5f9; font-size: 14px; font-weight: 700; }
.lib-empty p  { margin: 0; color: #64748b; font-size: 13px; }

/* ── PAGINATION ────────────────────────────────────────────── */
.lib-page .pagination { margin-bottom: 0; }
.lib-page .pagination .page-link {
    min-width: 34px; margin: 0 2px; padding: .4rem .6rem;
    color: #cbd5e1; background: var(--ui-surface);
    border: 1px solid var(--ui-border); border-radius: .5rem;
    font-size: 13px; text-align: center; transition: all .18s;
}
.lib-page .pagination .page-link:hover { color: var(--ui-accent); background: rgba(99,210,198,.07); border-color: rgba(99,210,198,.3); }
.lib-page .pagination .page-item.active .page-link { color: #061311; background: var(--ui-accent); border-color: var(--ui-accent); }
.lib-page .pagination .page-item.disabled .page-link { color: #475569; background: rgba(15,23,42,.55); border-color: rgba(148,163,184,.1); }

/* ── STAGGER ANIMATION ─────────────────────────────────────── */
.lib-card-col { animation: libFadeUp .45s cubic-bezier(.22,1,.36,1) both; animation-delay: calc(var(--i, 0) * 40ms); }
@keyframes libFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media (max-width: 768px) {
    .lib-page { padding-left: .5rem !important; padding-right: .5rem !important; }
    .lib-hero { min-height: 280px; border-radius: .75rem; }
    .lib-hero-content { padding: 1rem; }
    .lib-hero-title { font-size: 1.4rem; }
}
@media (max-width: 576px) {
    .lib-card-poster { aspect-ratio: auto; }
    .lib-card-poster img { height: 220px; }
    .lib-search-form { flex-wrap: wrap; }
    .lib-search-btn { width: 100%; justify-content: center; }
}
@media (max-width: 420px) {
    .lib-page-title { font-size: 1.2rem; }
}
</style>
