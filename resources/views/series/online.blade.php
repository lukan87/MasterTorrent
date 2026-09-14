



@php

    $firstSeason = $tvMazeSeasons[0]['number'] ?? null;

    $firstEpisode = collect($tvMazeEpisodes)

        ->where('season', $firstSeason)

        ->first();

@endphp

<div class="episodes-wrapper container-fluid">

    <div class="episodes-glass-panel">

        <div class="row g-4">

            {{-- SEASONS --}}

            <div class="col-xl-2 col-lg-3 col-md-4">

                <div class="panel-box">

                    <div class="panel-header">

                        <h5>Seasons</h5>

                    </div>

                    <div class="season-scroll">

                        @foreach ($tvMazeSeasons as $season)

                            <div class="season-card-wrapper">

                                <button class="season-card @if($loop->first) active @endif"

                                        data-season="{{ $season['number'] }}">

                                    <div class="season-card-content">

                                        <span class="season-label">

                                            Season

                                        </span>

                                        <span class="season-number">

                                            {{ $season['number'] }}

                                        </span>

                                    </div>

                                </button>

                                <div class="season-progress-modern">

                                    <div class="season-progress-fill"

                                         data-season-progress="{{ $season['number'] }}">

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

            {{-- EPISODES --}}

            <div class="col-xl-4 col-lg-5 col-md-8">

                <div class="panel-box">

                    <div class="panel-header">

                        <h5>Episodes</h5>

                        <div class="search-box-modern">

                            <i class="bi bi-search"></i>

                            <input type="text"

                                   id="episodeSearch"

                                   placeholder="Search episodes...">

                        </div>

                    </div>

                    <div class="episodes-modern-list"

                         id="episodeList">

                        @foreach ($tvMazeEpisodes as $episode)

                            <div class="episode-card"

                                 data-season="{{ $episode['season'] }}"

                                 data-title="{{ strtolower($episode['name']) }}"

                                 data-summary="{{ strtolower(strip_tags($episode['summary'] ?? '')) }}"

                                 data-image="{{ $episode['image']['original'] ?? url('/images/noposter.jpg') }}"

                                 data-embed="https://v2.vidsrc.me/embed/{{ $series['imdb_id'] }}/{{ $episode['season'] }}-{{ $episode['number'] }}"

                                 data-id="{{ $episode['id'] }}"

                                 data-fullsummary="{{ strip_tags($episode['summary'] ?? '') }}"

                                 @if($episode['season'] != $firstSeason) style="display:none;" @endif>

                                <div class="episode-number-box">

                                    <span>

                                        {{ $episode['number'] }}

                                    </span>

                                </div>

                                <div class="episode-info">

                                    <h6>

                                        {{ $episode['name'] }}

                                    </h6>

                                    <small>

                                        Season {{ $episode['season'] }}

                                        •

                                        Episode {{ $episode['number'] }}

                                    </small>

                                </div>

                                <div class="episode-status-modern">

                                    <span class="watched-badge d-none">

                                        <i class="bi bi-check-circle-fill"></i>

                                    </span>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

            {{-- PREVIEW --}}

            <div class="col-xl-6 col-lg-4 col-md-12">

                <div class="preview-modern-card">

                    <div class="preview-image-wrapper">

                        <img id="previewImage"

                             src="{{ $firstEpisode['image']['original'] ?? url('/images/noposter.jpg') }}"

                             class="preview-modern-image">

                        <div class="preview-image-overlay"></div>

                    </div>

                    <div class="preview-modern-content">

                        <div class="preview-top">

                            <div>

                                <span class="preview-badge">

                                    NOW SELECTED

                                </span>

                                <h2 id="previewTitle">

                                    {{ $firstEpisode['name'] ?? '' }}

                                </h2>

                            </div>

                        </div>

                        <p id="previewSummary"

                           class="preview-description">

                            {{ strip_tags($firstEpisode['summary'] ?? '') }}

                        </p>

                        <div class="preview-actions">

                            <button id="watchedBtn"

                                    class="btn watched-btn-modern"

                                    data-id="">

                                <i class="bi bi-check-circle"></i>

                            </button>

                            @if(auth()->user()->user_class >= \App\Models\UserClass::VIP)

                                <button id="watchBtn"

                                        class="btn play-btn-modern">

                                    <i class="bi bi-play-fill"></i>

                                    Watch Episode

                                </button>

                            @else

                                <div class="vip-lock">

                                    <i class="bi bi-gem"></i>

                                    VIP ONLY

                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- WATCH MODAL --}}

<div class="modal fade"

     id="watchModal"

     tabindex="-1"

     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content modern-modal">

            <div class="modal-header modern-modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-play-circle-fill"></i>

                    Now Watching

                </h5>

                <button type="button"

                        class="btn-close btn-close-white"

                        data-bs-dismiss="modal">

                </button>

            </div>

            <div class="modal-body p-0">

                <iframe id="watchFrame"

                        src=""

                        class="watch-frame"

                        allowfullscreen>

                </iframe>

            </div>

        </div>

    </div>

</div>

<style>
/* FileIplay scoped layout - prevents collisions with parent page CSS */
.episodes-wrapper{position:relative;z-index:1;width:100%;clear:both;padding:0 0 1rem;color:#dbe4ef;}
.episodes-wrapper *{box-sizing:border-box;}
.episodes-wrapper .episodes-glass-panel{width:100%;background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.9));border:1px solid var(--ui-border,rgba(255,255,255,.08));border-radius:.75rem;padding:1rem;box-shadow:0 10px 28px rgba(0,0,0,.28);}
.episodes-wrapper .row{align-items:stretch;}
.episodes-wrapper .panel-box,.episodes-wrapper .preview-modern-card{width:100%;background:rgba(10,16,28,.58);border:1px solid rgba(255,255,255,.07);border-radius:.65rem;overflow:hidden;position:relative;}
.episodes-wrapper .panel-box{height:100%;}
.episodes-wrapper .panel-header{display:flex;align-items:center;justify-content:space-between;gap:.65rem;padding:.75rem .85rem;min-height:48px;border-bottom:1px solid rgba(255,255,255,.07);}
.episodes-wrapper .panel-header h5{margin:0;color:#fff;font-size:.9rem;font-weight:700;line-height:1.2;}
.episodes-wrapper .season-scroll{max-height:700px;overflow-y:auto;overflow-x:hidden;padding:.65rem;}
.episodes-wrapper .season-card-wrapper{display:block;width:100%;margin:0 0 .45rem;}
.episodes-wrapper .season-card{display:block;width:100%;height:auto;min-height:50px;margin:0;border:1px solid rgba(255,255,255,.07);border-radius:.5rem;background:rgba(255,255,255,.035);color:#dbe4ef;padding:.6rem .7rem;text-align:left;cursor:pointer;transition:background .18s ease,border-color .18s ease;}
.episodes-wrapper .season-card:hover,.episodes-wrapper .season-card.active{background:rgba(20,184,166,.12);border-color:rgba(45,212,191,.42);color:#fff;}
.episodes-wrapper .season-card-content{display:flex;align-items:center;justify-content:space-between;gap:.5rem;width:100%;}
.episodes-wrapper .season-label{display:block;font-size:.9rem;text-transform:uppercase;letter-spacing:.07em;color:#8fa0b5;line-height:1.2;}
.episodes-wrapper .season-number{font-size:1.25rem;font-weight:700;color:#fff;line-height:1.2;}
.episodes-wrapper .season-progress-modern{display:block;width:100%;height:3px;margin-top:.35rem;background:rgba(255,255,255,.07);overflow:hidden;border-radius:3px;}
.episodes-wrapper .season-progress-fill{display:block;width:0;height:100%;background:var(--ui-accent,#2dd4bf);transition:width .3s ease;}
.episodes-wrapper .search-box-modern{display:flex;align-items:center;gap:.4rem;flex:0 1 180px;min-width:120px;height:32px;padding:.3rem .5rem;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.07);border-radius:.45rem;}
.episodes-wrapper .search-box-modern i{flex:0 0 auto;color:var(--ui-accent,#2dd4bf);font-size:.88rem;}
.episodes-wrapper .search-box-modern input{display:block;width:100%;min-width:0;height:100%;border:0!important;outline:0!important;background:transparent!important;color:#e8eef6!important;font-size:.76rem;line-height:1.2;box-shadow:none!important;}
.episodes-wrapper .search-box-modern input::placeholder{color:#718096;opacity:1;}
.episodes-wrapper .episodes-modern-list{max-height:700px;overflow-y:auto;overflow-x:hidden;padding:.65rem;}
.episodes-wrapper .episode-card{display:flex;align-items:center;gap:.6rem;width:100%;min-height:52px;margin:0 0 .4rem;padding:.5rem;border:1px solid rgba(255,255,255,.06);border-radius:.5rem;background:rgba(255,255,255,.025);cursor:pointer;transition:background .18s ease,border-color .18s ease;}
.episodes-wrapper .episode-card:hover,.episodes-wrapper .episode-card.active-episode{background:rgba(20,184,166,.09);border-color:rgba(45,212,191,.3);}
.episodes-wrapper .episode-number-box{flex:0 0 38px;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:.45rem;background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.22);color:#7ee7da;font-weight:700;font-size:.95rem;}
.episodes-wrapper .episode-info{flex:1 1 auto;min-width:0;overflow:hidden;}
.episodes-wrapper .episode-info h6{display:block;margin:0 0 .15rem!important;padding:0!important;color:#f2f6fb;font-size:.98rem;font-weight:600;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.episodes-wrapper .episode-info small{display:block;margin:0;padding:0;color:#8190a3;font-size:.98rem;line-height:1.25;}
.episodes-wrapper .episode-status-modern{flex:0 0 auto;display:flex;align-items:center;}
.episodes-wrapper .watched-badge{display:inline-flex;align-items:center;color:#34d399;font-size:1rem;line-height:1;}
.episodes-wrapper .preview-modern-card{height:100%;display:flex;flex-direction:column;}
.episodes-wrapper .preview-image-wrapper{position:relative;width:100%;height:300px;flex:0 0 300px;overflow:hidden;}
.episodes-wrapper .preview-modern-image{display:block;width:100%;height:100%;object-fit:cover;}
.episodes-wrapper .preview-image-overlay{position:absolute;inset:0;pointer-events:none;background:linear-gradient(to top,rgba(7,11,20,.95),rgba(7,11,20,.05) 65%);}
.episodes-wrapper .preview-modern-content{display:flex;flex:1 1 auto;flex-direction:column;padding:1rem;min-width:0;}
.episodes-wrapper .preview-top{display:block;width:100%;}
.episodes-wrapper .preview-badge{display:inline-block;padding:.25rem .5rem;border-radius:.35rem;background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.18);color:#6ee7d8;font-size:.6rem;letter-spacing:.08em;line-height:1.2;margin-bottom:.5rem;}
.episodes-wrapper #previewTitle{display:block;width:100%;margin:0 0 .55rem!important;padding:0!important;color:#fff;font-size:1.3rem;font-weight:700;line-height:1.25;overflow-wrap:anywhere;}
.episodes-wrapper .preview-description{display:block;width:100%;margin:0;color:#aeb9c8;font-size:.95rem;line-height:1.55;min-height:70px;overflow-wrap:anywhere;}
.episodes-wrapper .preview-actions{display:flex;align-items:stretch;gap:.5rem;width:100%;margin-top:auto;padding-top:.85rem;}
.episodes-wrapper .play-btn-modern{flex:1 1 auto;min-width:0;border:1px solid rgba(45,212,191,.3);border-radius:.5rem;padding:.55rem .75rem;font-size:.78rem;font-weight:600;line-height:1.2;background:rgba(20,184,166,.12);color:#7ee7da;transition:background .18s ease,border-color .18s ease;}
.episodes-wrapper .play-btn-modern:hover{background:rgba(20,184,166,.2);border-color:rgba(45,212,191,.45);color:#fff;}
.episodes-wrapper .watched-btn-modern{flex:0 0 44px;width:44px;height:auto;min-height:38px;padding:.45rem;border-radius:.5rem;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.08);color:#cbd5e1;}
.episodes-wrapper .vip-lock{flex:1 1 auto;min-width:0;display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem;border-radius:.5rem;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.18);color:#fbbf24;font-size:.76rem;font-weight:700;line-height:1.2;}
.modern-modal{position:relative;z-index:1060;background:#0b1220;border:1px solid rgba(255,255,255,.09);border-radius:.7rem;overflow:hidden;}
.modern-modal-header{position:relative;z-index:1062;background:rgba(255,255,255,.035);border-bottom:1px solid rgba(255,255,255,.07);color:#fff;}
.modern-modal-header .btn-close{position:relative;z-index:1063;pointer-events:auto;}
.watch-frame{display:block;position:relative;z-index:1;width:100%;height:75vh;border:0;background:#000;pointer-events:auto;}
.modal-backdrop{z-index:1050;}
#watchModal{z-index:1060;}
@media(max-width:1199.98px){
    .episodes-wrapper .season-scroll,.episodes-wrapper .episodes-modern-list{max-height:560px;}
    .episodes-wrapper .preview-image-wrapper{height:280px;flex-basis:280px;}
}
@media(max-width:991.98px){
    .episodes-wrapper .preview-modern-card{height:auto;}
    .episodes-wrapper .preview-image-wrapper{height:300px;flex-basis:300px;}
}
@media(max-width:767.98px){
    .episodes-wrapper .episodes-glass-panel{padding:.7rem;}
    .episodes-wrapper .panel-header{padding:.65rem .75rem;}
    .episodes-wrapper .search-box-modern{flex:0 1 145px;min-width:110px;}
    .episodes-wrapper .season-scroll,.episodes-wrapper .episodes-modern-list{max-height:360px;}
    .episodes-wrapper .preview-image-wrapper{height:230px;flex-basis:230px;}
    .episodes-wrapper #previewTitle{font-size:1.1rem;}
    .episodes-wrapper .preview-actions{gap:.4rem;}
    .episodes-wrapper .watched-btn-modern{flex-basis:44px;width:44px;}
}
</style>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const seasonBtns = document.querySelectorAll('.season-card');

    const episodes = document.querySelectorAll('.episode-card');

    const search = document.getElementById('episodeSearch');

    const previewImage = document.getElementById('previewImage');

    const previewTitle = document.getElementById('previewTitle');

    const previewSummary = document.getElementById('previewSummary');

    const watchBtn = document.getElementById('watchBtn');

    const watchedBtn = document.getElementById('watchedBtn');

    const watchedKey = 'watchedEpisodes';

    let watchedEpisodes =

        JSON.parse(localStorage.getItem(watchedKey) || '[]');

    let currentSeason =

        document.querySelector('.season-card.active')?.dataset.season;

    let currentEpisodeId = null;

    /* WATCHED STORAGE */

    function saveWatched() {

        localStorage.setItem(

            watchedKey,

            JSON.stringify(watchedEpisodes)

        );

    }

    function updateWatchedButtonUI() {

        if (!currentEpisodeId) return;

        if (watchedEpisodes.includes(currentEpisodeId)) {

            watchedBtn.classList.remove('btn-outline-success');

            watchedBtn.innerHTML =

                '<i class="bi bi-check2-circle-fill"></i>';

        } else {

            watchedBtn.classList.add('btn-outline-success');

            watchedBtn.innerHTML =

                '<i class="bi bi-check-circle"></i>';

        }

    }

    function updateEpisodeBadges() {

        episodes.forEach(row => {

            const id = row.dataset.id;

            const badge =

                row.querySelector('.watched-badge');

            if (watchedEpisodes.includes(id)) {

                badge.classList.remove('d-none');

            } else {

                badge.classList.add('d-none');

            }

        });

    }

    function updateSeasonProgress() {

        const seasons = {};

        episodes.forEach(row => {

            const season = row.dataset.season;

            const id = row.dataset.id;

            if (!seasons[season]) {

                seasons[season] = {

                    total: 0,

                    watched: 0

                };

            }

            seasons[season].total++;

            if (watchedEpisodes.includes(id)) {

                seasons[season].watched++;

            }

        });

        Object.keys(seasons).forEach(season => {

            const bar = document.querySelector(

                `.season-progress-fill[data-season-progress="${season}"]`

            );

            if (!bar) return;

            const percent = seasons[season].total

                ? (seasons[season].watched / seasons[season].total) * 100

                : 0;

            bar.style.width = percent + '%';

        });

    }

    function refreshWatchedUI() {

        updateEpisodeBadges();

        updateSeasonProgress();

        updateWatchedButtonUI();

    }

    /* FILTER */

    function filterSeason(season) {

        episodes.forEach(ep => {

            ep.style.display =

                ep.dataset.season === season

                    ? ''

                    : 'none';

        });

    }

    seasonBtns.forEach(btn => {

        btn.addEventListener('click', function () {

            seasonBtns.forEach(b =>

                b.classList.remove('active')

            );

            this.classList.add('active');

            currentSeason = this.dataset.season;

            filterSeason(currentSeason);

        });

    });

    /* ACTIVE */

    function highlightActiveEpisode(activeEl) {

        episodes.forEach(ep =>

            ep.classList.remove('active-episode')

        );

        activeEl.classList.add('active-episode');

    }

    /* CLICK */

    episodes.forEach(ep => {

        ep.addEventListener('click', function () {

            previewImage.src =

                this.dataset.image;

            previewTitle.innerText =

                this.querySelector('h6').innerText;

            previewSummary.innerText =

                this.dataset.fullsummary;

            currentEpisodeId =

                this.dataset.id;

            highlightActiveEpisode(this);

            updateWatchedButtonUI();

            if (watchBtn) {

                watchBtn.onclick = () => {

                    const modalEl = document.getElementById('watchModal');
                    const frame = document.getElementById('watchFrame');

                    if (!modalEl || !frame || typeof bootstrap === 'undefined') {
                        return;
                    }

                    // Keep the Bootstrap modal outside any parent stacking/transform context.
                    // This prevents the iframe from sitting above the modal header/backdrop.
                    if (modalEl.parentElement !== document.body) {
                        document.body.appendChild(modalEl);
                    }

                    frame.src = this.dataset.embed;

                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });

                    modal.show();
                };

            }

        });

    });

    /* WATCHED */

    watchedBtn.addEventListener('click', function () {

        if (!currentEpisodeId) return;

        if (watchedEpisodes.includes(currentEpisodeId)) {

            watchedEpisodes =

                watchedEpisodes.filter(

                    id => id !== currentEpisodeId

                );

        } else {

            watchedEpisodes.push(currentEpisodeId);

        }

        saveWatched();

        refreshWatchedUI();

    });

    /* SEARCH */

    search.addEventListener('input', function () {

        const q = this.value.toLowerCase();

        episodes.forEach(ep => {

            const match =

                ep.dataset.title.includes(q)

                ||

                ep.dataset.summary.includes(q);

            ep.style.display =

                match ? '' : 'none';

        });

    });

    /* INIT */

    const firstVisible =

        document.querySelector('.episode-card:not([style*="display:none"])');

    if (firstVisible) {

        firstVisible.click();

    }

    refreshWatchedUI();

    /* MODAL CLOSE */

    const modalEl = document.getElementById('watchModal');

    if (modalEl) {

        // Move the modal to <body> once so Bootstrap's fixed positioning and z-index
        // work correctly even when this partial is included inside another container.
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            const frame = document.getElementById('watchFrame');
            if (frame) {
                frame.src = '';
            }
        });
    }

});

</script>