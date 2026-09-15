@extends('layouts.app')

@section('content')

<style>
    :root{
        --ms-accent:#22d3ee;
        --ms-accent-2:#34d399;
        --ms-bg:#0f172a;
        --ms-card:#111c33;
        --ms-line:rgba(255,255,255,.09);
        --ms-text:#e2e8f0;
        --ms-muted:rgba(148,163,184,.72);
    }

    .ms-page{color:var(--ms-text);font-family:'Inter',system-ui,sans-serif}
    .ms-header{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:26px}
    .ms-title{font-size:1.7rem;font-weight:800;letter-spacing:-.015em;margin:0;color:#f8fafc}
    .ms-title .ms-query{background:linear-gradient(120deg,var(--ms-accent),var(--ms-accent-2));
        -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
    .ms-subtitle{margin:6px 0 0;color:var(--ms-muted);font-size:.9rem;font-weight:500}
    .ms-header-badges{display:flex;gap:9px;flex-wrap:wrap}
    .ms-badge{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:999px;font-size:.78rem;font-weight:700;border:1px solid var(--ms-line);background:rgba(255,255,255,.04);color:var(--ms-text)}
    .ms-badge b{font-size:.95rem}
    .ms-badge--accent{background:rgba(34,211,238,.1);border-color:rgba(34,211,238,.28);color:#67e8f9}
    .ms-badge--green{background:rgba(52,211,153,.1);border-color:rgba(52,211,153,.28);color:#6ee7b7}
    .ms-badge--gold{background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.28);color:#fcd34d}

    .ms-section{margin-bottom:30px}
    .ms-section-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px}
    .ms-section-title{display:flex;align-items:center;gap:10px;font-size:1.02rem;font-weight:800;color:#f1f5f9;margin:0;letter-spacing:.01em}
    .ms-section-title .ms-ic{display:grid;place-items:center;width:28px;height:28px;border-radius:.5rem;font-size:.95rem}
    .ms-section-title .ms-ic--green{background:rgba(52,211,153,.14);color:#34d399;border:1px solid rgba(52,211,153,.25)}
    .ms-section-title .ms-ic--accent{background:rgba(34,211,238,.14);color:#22d3ee;border:1px solid rgba(34,211,238,.25)}
    .ms-section-title .ms-ic--gold{background:rgba(251,191,36,.14);color:#fbbf24;border:1px solid rgba(251,191,36,.25)}
    .ms-section-count{font-size:.72rem;font-weight:700;color:var(--ms-muted);background:rgba(255,255,255,.05);border:1px solid var(--ms-line);padding:3px 10px;border-radius:999px}
    .ms-select-all-btn{display:inline-flex;align-items:center;gap:6px;font-size:.75rem;font-weight:700;color:var(--ms-accent);background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.3);padding:6px 13px;border-radius:.5rem;cursor:pointer;transition:.18s}
    .ms-select-all-btn:hover{background:rgba(34,211,238,.16)}
    .ms-section-note{display:flex;align-items:center;gap:7px;font-size:.76rem;font-weight:600;color:var(--ms-muted);background:rgba(251,191,36,.06);border:1px dashed rgba(251,191,36,.3);border-radius:.55rem;padding:8px 12px;margin:-6px 0 14px}
    .ms-section-note i{color:#fbbf24}
    .ms-hide{display:none!important}
.ms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(158px,1fr));gap:14px}
    .ms-grid--locked{max-width:100%;overflow-x:auto;overflow-y:hidden;grid-template-columns:none;grid-auto-flow:column;grid-auto-columns:minmax(158px,1fr);gap:14px;padding:4px 4px 16px;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:rgba(148,163,184,.35) transparent}
    .ms-grid--locked::-webkit-scrollbar{height:8px}
    .ms-grid--locked::-webkit-scrollbar-thumb{background:rgba(148,163,184,.35);border-radius:999px}
    .ms-grid--locked::-webkit-scrollbar-track{background:transparent}
    .ms-grid--locked .ms-card{animation:none}
    .ms-card{position:relative;display:flex;flex-direction:column;border-radius:.85rem;overflow:hidden;background:var(--ms-card);border:1px solid var(--ms-line);box-shadow:0 10px 24px rgba(0,0,0,.28);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;animation:msIn .45s ease both}
    .ms-card:hover{transform:translateY(-4px);box-shadow:0 18px 38px rgba(0,0,0,.42);border-color:rgba(34,211,238,.4)}
    .ms-card--addable:hover{border-color:var(--ms-accent)}
    .ms-card--checked{border-color:var(--ms-accent);box-shadow:0 0 0 2px rgba(34,211,238,.35),0 18px 38px rgba(0,0,0,.42)}
    .ms-card.is-existing{opacity:.92;cursor:default}
    @keyframes msIn{from{opacity:0;transform:translateY(14px) scale(.97)}to{opacity:1;transform:none}}

    .ms-poster{position:relative;aspect-ratio:2/3;background:#0b1120;overflow:hidden}
    .ms-poster img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease;display:block}
    .ms-card:hover .ms-poster img{transform:scale(1.06)}
    .ms-poster-fade{position:absolute;inset:auto 0 0 0;height:38%;background:linear-gradient(to bottom,transparent,rgba(17,28,51,.9))}

    .ms-pill{position:absolute;z-index:2;display:inline-flex;align-items:center;gap:4px;font-size:.68rem;font-weight:800;padding:3px 8px;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:rgba(2,6,23,.6);backdrop-filter:blur(4px)}
    .ms-pill--rating{top:8px;left:8px;color:#fbbf24}
    .ms-pill--year{top:8px;right:8px;color:#e2e8f0}
    .ms-pill--year i{color:var(--ms-accent)}

    .ms-owned{position:absolute;z-index:3;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(2,6,23,.34);opacity:0;transition:.25s}
    .ms-card.is-existing:hover .ms-owned{opacity:1}
    .ms-owned-chip{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:.75rem;font-weight:800;color:#052e16;background:rgba(52,211,153,.92);box-shadow:0 6px 18px rgba(0,0,0,.35)}
    .ms-owned-chip.dim{background:rgba(52,211,153,.22);color:#a7f3d0;border:1px solid rgba(52,211,153,.4)}

    .ms-card-body{display:flex;flex-direction:column;gap:8px;padding:12px;flex:1}
    .ms-card-title{font-size:.86rem;font-weight:700;color:#f1f5f9;line-height:1.32;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.4em}
    .ms-card-overview{font-size:.74rem;color:var(--ms-muted);line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1}

    .ms-add{display:flex;align-items:center;gap:8px;margin-top:auto;padding-top:4px}
    .ms-check{width:16px;height:16px;margin:0;accent-color:#22d3ee;cursor:pointer;transition:.15s}
    .ms-add-label{font-size:.76rem;font-weight:700;color:var(--ms-accent);cursor:pointer;user-select:none}
    .ms-link{display:inline-flex;align-items:center;gap:5px;font-size:.74rem;font-weight:700;color:#67e8f9;text-decoration:none}

    .ms-inline{display:flex;gap:14px;flex-wrap:wrap;justify-content:center}
    .ms-inline .ms-card{width:132px;flex:0 0 132px}
    .ms-inline .ms-card-title{font-size:.76rem;min-height:2.2em}

    .ms-scroll{display:flex;gap:14px;overflow-x:auto;padding:6px 4px 16px;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch}
    .ms-scroll .ms-card{flex:0 0 150px;scroll-snap-align:start}
    .ms-scroll .ms-card-title{font-size:.78rem;min-height:2.2em}
    .ms-scroll::-webkit-scrollbar{height:8px}
    .ms-scroll::-webkit-scrollbar-thumb{background:rgba(148,163,184,.3);border-radius:999px}
    .ms-scroll::-webkit-scrollbar-track{background:transparent}

    .ms-section-divider{border:0;border-top:1px solid var(--ms-line);margin:6px 0 26px}

    .ms-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;text-align:center;padding:40px 20px;border:1px dashed rgba(251,191,36,.3);border-radius:1rem;background:rgba(251,191,36,.04);color:#fcd34d;font-size:.9rem;font-weight:600}

    .ms-footer{position:sticky;bottom:16px;z-index:40;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:13px 16px;border-radius:.95rem;border:1px solid var(--ms-line);background:rgba(15,23,42,.88);backdrop-filter:blur(14px);box-shadow:0 12px 34px rgba(0,0,0,.4)}
    .ms-footer-left{display:flex;align-items:center;gap:10px}
    .ms-count-chip{font-size:.8rem;font-weight:800;color:#f8fafc;background:rgba(34,211,238,.14);border:1px solid rgba(34,211,238,.32);padding:6px 13px;border-radius:999px}
    .ms-count-chip .ms-num{color:#22d3ee;font-size:.95rem}
    .ms-add-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border:0;border-radius:.6rem;font-size:.86rem;font-weight:800;color:#061311;background:linear-gradient(120deg,var(--ms-accent),var(--ms-accent-2));cursor:pointer;transition:.2s;box-shadow:0 8px 22px rgba(34,211,238,.28)}
    .ms-add-btn:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(34,211,238,.38)}
    .ms-add-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}

    .ms-back{display:inline-flex;align-items:center;gap:7px;font-size:.8rem;font-weight:700;color:var(--ms-muted);text-decoration:none;margin-bottom:14px;transition:.15s}
    .ms-back:hover{color:var(--ms-accent)}

    @media(max-width:576px){
        .ms-header{flex-direction:column;align-items:flex-start}
        .ms-grid{grid-template-columns:repeat(auto-fill,minmax(128px,1fr));gap:10px}
        .ms-scroll .ms-card{flex-basis:132px}
        .ms-footer{flex-direction:column;align-items:stretch}
        .ms-footer-left{justify-content:center}
    }
</style>
<div class="ms-page">
    <a href="{{ url()->previous() }}" class="ms-back">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="ms-header">
        <div>
            <h1 class="ms-title">
                Results for <span class="ms-query">"{{ $query }}"</span>
            </h1>
            <p class="ms-subtitle">
                Pick series to add. Titles you already own show at the top — everything else is ready to add, plus suggested picks below.
            </p>
        </div>
        <div class="ms-header-badges">
            @if($existingSeries->isNotEmpty())
                <span class="ms-badge ms-badge--green"><i class="bi bi-check2-circle"></i> <b>{{ $existingSeries->count() }}</b> owned</span>
            @endif
            <span class="ms-badge ms-badge--gold"><i class="bi bi-shuffle"></i> <b>{{ $similarSeries->count() }}</b> similar</span>
            <span class="ms-badge ms-badge--accent"><i class="bi bi-magic"></i> <b>{{ $recommendedSeries->count() }}</b> recommended</span>
            <span class="ms-badge"><i class="bi bi-collection"></i> <b>{{ $newSeries->count() }}</b> to add</span>
        </div>
    </div>

    @if (Session::has('message'))
        <script>
            swal("Message", @json(Session::get('message')), 'success', { button: true, button: "OK" });
        </script>
    @endif

    <form action="{{ route('series.bulkSelect') }}" method="POST" id="msForm">
        @csrf

        @if($series->isNotEmpty())

            {{-- ===== ROW A: Already in database (top, inline, centered) ===== --}}
<section class="ms-section" id="existingSection">
                <div class="ms-section-head">
                    <h2 class="ms-section-title">
                        <span class="ms-ic ms-ic--green"><i class="bi bi-bookmark-check"></i></span>
                        Already in Database
                        <span class="ms-section-count">{{ $existingSeries->count() }}</span>
                    </h2>
                </div>
                <div class="ms-inline">
                    @forelse($existingSeries as $s)
                        @if(!empty($s['poster_path']))
                            <div class="ms-card is-existing">
                                <div class="ms-poster">
                                    <img src="https://image.tmdb.org/t/p/w300_and_h450_bestv2{{ $s['poster_path'] }}" alt="{{ $s['name'] }}" loading="lazy">
                                    <div class="ms-poster-fade"></div>
                                    <span class="ms-owned-chip dim"><i class="bi bi-check2-circle"></i> Owned</span>
                                </div>
                                <div class="ms-card-body">
                                    <p class="ms-card-title">{{ $s['name'] }}</p>
                                    <p class="ms-card-overview">{{ $s['overview'] ?: '' }}</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="ms-empty">
                            <i class="bi bi-bookmark-check" style="font-size:2rem"></i>
                            None of these results are in your database yet — they're all ready to add below.
                        </div>
                    @endforelse
                </div>
            </section>

            <hr class="ms-section-divider">

            {{-- ===== ROW B: New series to add (horizontal scroll when more than 8) ===== --}}
<section class="ms-section" id="newSection">
                <div class="ms-section-head">
                    <h2 class="ms-section-title">
                        <span class="ms-ic ms-ic--accent"><i class="bi bi-plus-circle"></i></span>
                        New Series to Add
                        <span class="ms-section-count">{{ $newSeries->count() }}</span>
                    </h2>
                    @if($newSeries->isNotEmpty())
                        <span class="ms-select-all-btn" data-target="#newSection"><i class="bi bi-check2-square"></i> Select all</span>
                    @endif
                </div>
                @if(!empty($noImdbCount))
                    <p class="ms-section-note"><i class="bi bi-info-circle"></i> {{ $noImdbCount }} result(s) with no IMDb ID were hidden and won't be added.</p>
                @endif
                <div class="ms-grid {{ $newSeries->count() > 8 ? 'ms-grid--locked' : '' }}">
                    @forelse($newSeries as $s)
                        @if(!empty($s['poster_path']))
                            <div class="ms-card ms-card--addable" data-id="{{ $s['id'] }}" data-title="{{ $s['name'] }}">
                                <div class="ms-poster">
                                    <img src="https://image.tmdb.org/t/p/w300_and_h450_bestv2{{ $s['poster_path'] }}" alt="{{ $s['name'] }}" loading="lazy">
                                    <div class="ms-poster-fade"></div>
                                    @if(isset($s['vote_average']) && $s['vote_average'])
                                        <span class="ms-pill ms-pill--rating"><i class="bi bi-star-fill"></i>{{ number_format($s['vote_average'], 1) }}</span>
                                    @endif
                                    @if(!empty($s['first_air_date']))
                                        <span class="ms-pill ms-pill--year"><i class="bi bi-calendar3"></i>{{ substr($s['first_air_date'], 0, 4) }}</span>
                                    @endif
                                </div>
                                <div class="ms-card-body">
                                    <p class="ms-card-title">{{ $s['name'] }}</p>
                                    <p class="ms-card-overview">{{ $s['overview'] ?: 'No overview available.' }}</p>
                                    <label class="ms-add">
                                        <input type="checkbox" name="series[]" value="{{ $s['id'] }}" class="ms-check">
                                        <span class="ms-add-label">Add</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="ms-empty" style="grid-column:1/-1">
                            <i class="bi bi-journal-check" style="font-size:2rem"></i>
                            No new series to add — everything from this search is already in your database.
                        </div>
                    @endforelse
                </div>
            </section>

            <hr class="ms-section-divider">
{{-- ===== ROW C: Similar series (based on the searched title) ===== --}}
            <section class="ms-section" id="similarSection">
                <div class="ms-section-head">
                    <h2 class="ms-section-title">
                        <span class="ms-ic ms-ic--gold"><i class="bi bi-shuffle"></i></span>
                        Similar Series
                        <span class="ms-section-count">{{ $similarSeries->count() }}</span>
                    </h2>
                    @if($similarSeries->isNotEmpty())
                        <span class="ms-select-all-btn" data-target="#similarSection"><i class="bi bi-check2-square"></i> Select all</span>
                    @endif
                </div>
                @if($similarSeries->isNotEmpty())
                    <div class="ms-scroll">
                        @foreach($similarSeries as $entry)
                            @php $s = $entry['series']; @endphp
                            @if(!empty($s['poster_path']))
                                <div class="ms-card ms-card--addable" data-id="{{ $s['id'] }}">
                                    <div class="ms-poster">
                                        <img src="https://image.tmdb.org/t/p/w300_and_h450_bestv2{{ $s['poster_path'] }}" alt="{{ $s['name'] }}" loading="lazy">
                                        <div class="ms-poster-fade"></div>
                                        @if(isset($s['vote_average']) && $s['vote_average'])
                                            <span class="ms-pill ms-pill--rating"><i class="bi bi-star-fill"></i>{{ number_format($s['vote_average'], 1) }}</span>
                                        @endif
                                        @if(!empty($s['first_air_date']))
                                            <span class="ms-pill ms-pill--year"><i class="bi bi-calendar3"></i>{{ substr($s['first_air_date'], 0, 4) }}</span>
                                        @endif
                                        @if($entry['exists'])
                                            <div class="ms-owned"><span class="ms-owned-chip"><i class="bi bi-check2-circle"></i> Already in DB</span></div>
                                        @endif
                                    </div>
                                    <div class="ms-card-body">
                                        <p class="ms-card-title">{{ $s['name'] }}</p>
                                        <p class="ms-card-overview">{{ $s['overview'] ?: 'No overview available.' }}</p>
                                        @if($entry['exists'])
                                            <span class="ms-link"><i class="bi bi-check2-circle"></i> Already owned</span>
                                        @else
                                            <label class="ms-add">
                                                <input type="checkbox" name="series[]" value="{{ $s['id'] }}" class="ms-check">
                                                <span class="ms-add-label">Add</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="ms-empty">
                        <i class="bi bi-shuffle" style="font-size:2rem"></i>
                        No similar series found for this title.
                    </div>
                @endif
            </section>
            {{-- ===== ROW D: Recommended series ===== --}}
            <section class="ms-section" id="recommendedSection">
                <div class="ms-section-head">
                    <h2 class="ms-section-title">
                        <span class="ms-ic ms-ic--accent"><i class="bi bi-magic"></i></span>
                        Recommended Series
                        <span class="ms-section-count">{{ $recommendedSeries->count() }}</span>
                    </h2>
                    @if($recommendedSeries->isNotEmpty())
                        <span class="ms-select-all-btn" data-target="#recommendedSection"><i class="bi bi-check2-square"></i> Select all</span>
                    @endif
                </div>
                @if($recommendedSeries->isNotEmpty())
                    <div class="ms-scroll">
                        @foreach($recommendedSeries as $entry)
                            @php $s = $entry['series']; @endphp
                            @if(!empty($s['poster_path']))
                                <div class="ms-card ms-card--addable" data-id="{{ $s['id'] }}">
                                    <div class="ms-poster">
                                        <img src="https://image.tmdb.org/t/p/w300_and_h450_bestv2{{ $s['poster_path'] }}" alt="{{ $s['name'] }}" loading="lazy">
                                        <div class="ms-poster-fade"></div>
                                        @if(isset($s['vote_average']) && $s['vote_average'])
                                            <span class="ms-pill ms-pill--rating"><i class="bi bi-star-fill"></i>{{ number_format($s['vote_average'], 1) }}</span>
                                        @endif
                                        @if(!empty($s['first_air_date']))
                                            <span class="ms-pill ms-pill--year"><i class="bi bi-calendar3"></i>{{ substr($s['first_air_date'], 0, 4) }}</span>
                                        @endif
                                        @if($entry['exists'])
                                            <div class="ms-owned"><span class="ms-owned-chip"><i class="bi bi-check2-circle"></i> Already in DB</span></div>
                                        @endif
                                    </div>
                                    <div class="ms-card-body">
                                        <p class="ms-card-title">{{ $s['name'] }}</p>
                                        <p class="ms-card-overview">{{ $s['overview'] ?: 'No overview available.' }}</p>
                                        @if($entry['exists'])
                                            <span class="ms-link"><i class="bi bi-check2-circle"></i> Already owned</span>
                                        @else
                                            <label class="ms-add">
                                                <input type="checkbox" name="series[]" value="{{ $s['id'] }}" class="ms-check">
                                                <span class="ms-add-label">Add</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="ms-empty">
                        <i class="bi bi-magic" style="font-size:2rem"></i>
                        No recommendations available for this title.
                    </div>
                @endif
            </section>

        @else
            <div class="ms-empty">
                <i class="bi bi-exclamation-triangle" style="font-size:2rem"></i>
                No series found for "{{ $query }}". Please try a different search.
            </div>
        @endif
{{-- ===== Sticky footer with live counter ===== --}}
        @if($series->isNotEmpty())
            <div class="ms-footer">
                <div class="ms-footer-left">
                    <span class="ms-count-chip"><i class="bi bi-check2-square me-1"></i><span class="ms-num" id="selectedCount">0</span> selected</span>
                </div>
                <button type="submit" class="ms-add-btn" id="submitBtn" data-bs-toggle="tooltip" title="Add all checked series to the database">
                    <i class="bi bi-plus-circle"></i> Add Selected Series to Database
                </button>
            </div>
        @endif
    </form>
</div>

<script>
    (function () {
        "use strict";
        const form = document.getElementById('msForm');
        if (!form) return;

        const counter = document.getElementById('selectedCount');
        const submitBtn = document.getElementById('submitBtn');
        const checkboxes = form.querySelectorAll('input.ms-check');

        function refresh() {
            let n = 0;
            checkboxes.forEach(cb => {
                const card = cb.closest('.ms-card');
                if (card) {
                    if (cb.checked) { n++; card.classList.add('ms-card--checked'); }
                    else { card.classList.remove('ms-card--checked'); }
                }
            });
            if (counter) counter.textContent = n;
            if (submitBtn) submitBtn.disabled = n === 0;
        }

        document.querySelectorAll('.ms-select-all-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const section = document.querySelector(this.dataset.target);
                if (!section) return;
                const boxes = section.querySelectorAll('input.ms-check:not(:disabled)');
                const anyUnchecked = Array.from(boxes).some(b => !b.checked);
                boxes.forEach(b => b.checked = anyUnchecked);
                refresh();
            });
        });

        checkboxes.forEach(cb => cb.addEventListener('change', refresh));
        refresh();
    })();
</script>

@endsection