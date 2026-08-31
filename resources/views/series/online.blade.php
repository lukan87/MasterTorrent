

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
            <div class="col-xl-2 col-lg-6 col-md-6">

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
            <div class="col-xl-4 col-lg-6 col-md-6">

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
            <div class="col-xl-6 col-lg-9 col-md-12">

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

/* WRAPPER */

.episodes-wrapper {
    position: relative;
    z-index: 5;
}

/* GLASS */

.episodes-glass-panel {

    background:
        rgba(15,20,32,0.72);

    backdrop-filter: blur(24px);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 32px;

    padding: 28px;

    box-shadow:
        0 20px 60px rgba(0,0,0,0.55);
}

/* PANELS */

.panel-header h5 {
    color: white;
    font-weight: 800;
    margin-bottom: 16px;
}

/* SEASONS */

.season-scroll {
    display: flex;
    flex-direction: column;
    gap: 14px;

    max-height: 700px;
    overflow-y: auto;

    padding-right: 5px;
}

.season-card {

    width: 100%;

    border: none;

    border-radius: 22px;

    background:
        rgba(255,255,255,0.04);

    color: white;

    padding: 10px;

    transition: 0.3s ease;
}

.season-card:hover,
.season-card.active {

    background:
        linear-gradient(135deg,
            rgba(38, 38, 37, 0.744),
            rgba(124,58,237,0.95));

    transform: translateY(-2px);

    box-shadow:
        0 12px 30px rgba(124,58,237,0.35);
}

.season-label {

    display: block;

    font-size: 0.72rem;

    letter-spacing: 2px;

    text-transform: uppercase;

    opacity: 0.7;
}

.season-number {

    font-size: 1.3rem;

    font-weight: 800;
}

/* PROGRESS */

.season-progress-modern {

    height: 5px;

    background:
        rgba(255,255,255,0.08);

    border-radius: 30px;

    overflow: hidden;

    margin-top: 8px;
}

.season-progress-fill {

    height: 100%;
    width: 0%;

    border-radius: 30px;

    background:
        linear-gradient(90deg,
            #10b981,
            #34d399);

    transition: width 0.4s ease;
}

/* SEARCH */

.search-box-modern {

    display: flex;
    align-items: center;

    gap: 10px;

    padding: 12px 14px;

    border-radius: 16px;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.06);
}

.search-box-modern input {

    width: 100%;

    border: none;
    outline: none;

    background: transparent;

    color: white;
}

.search-box-modern input::placeholder {
    color: rgba(255,255,255,0.4);
}

/* EPISODES */

.episodes-modern-list {

    max-height: 700px;
    overflow-y: auto;

    padding-right: 5px;
}

.episode-card {

    display: flex;
    align-items: center;

    gap: 16px;

    padding: 5px;

    margin-bottom: 12px;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.05);

    cursor: pointer;

    transition: 0.3s ease;
}

.episode-card:hover,
.active-episode {

    background:
        rgba(124,58,237,0.2);

    border-color:
        rgba(124,58,237,0.4);

    transform: translateY(-2px);
}

/* NUMBER */

.episode-number-box {

    width: 52px;
    height: 52px;

    border-radius: 16px;

    display: flex;
    align-items: center;
    justify-content: center;

    background:
        linear-gradient(135deg,
            #4f46e5,
            #7c3aed);

    color: white;

    font-weight: 800;
}

/* INFO */

.episode-info {
    flex: 1;
}

.episode-info h6 {

    color: white;

    font-weight: 700;

    margin-bottom: 4px;
}

.episode-info small {

    color:
        rgba(255,255,255,0.5);
}

/* BADGE */

.watched-badge {

    color: #10b981;

    font-size: 1.3rem;
}

/* PREVIEW */

.preview-modern-card {

    overflow: hidden;

    border-radius: 28px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.06);
}

.preview-image-wrapper {

    position: relative;

    height: 380px;
}

.preview-modern-image {

    width: 100%;
    height: 100%;

    object-fit: cover;
}

.preview-image-overlay {

    position: absolute;
    inset: 0;

    background:
        linear-gradient(to top,
            rgba(7,11,20,1),
            rgba(7,11,20,0.2));
}

.preview-modern-content {
    padding: 28px;
}

.preview-badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 50px;

    background:
        rgba(124,58,237,0.18);

    color: #c4b5fd;

    font-size: 0.7rem;

    letter-spacing: 2px;

    margin-bottom: 16px;
}

#previewTitle {

    color: white;

    font-size: 2rem;

    font-weight: 900;

    margin-bottom: 16px;
}

.preview-description {

    color:
        rgba(255,255,255,0.72);

    line-height: 1.8;

    min-height: 90px;
}

/* ACTIONS */

.preview-actions {

    display: flex;

    gap: 14px;

    margin-top: 25px;
}

.play-btn-modern {

    flex: 1;

    border: none;

    border-radius: 18px;

    padding: 16px;

    font-weight: 700;

    background:
        linear-gradient(135deg,
            #4f46e5,
            #7c3aed);

    color: white;

    transition: 0.3s ease;
}

.play-btn-modern:hover {

    transform: translateY(-2px);

    color: white;

    box-shadow:
        0 12px 30px rgba(124,58,237,0.35);
}

.watched-btn-modern {

    width: 65px;

    border-radius: 18px;

    background:
        rgba(255,255,255,0.06);

    border:
        1px solid rgba(255,255,255,0.08);

    color: white;
}

.vip-lock {

    flex: 1;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 10px;

    padding: 16px;

    border-radius: 18px;

    background:
        rgba(239,68,68,0.15);

    color: #fca5a5;

    font-weight: 700;
}

/* MODAL */

.modern-modal {

    background: #070b14;

    border-radius: 24px;

    overflow: hidden;

    border:
        1px solid rgba(255,255,255,0.08);
}

.modern-modal-header {

    background:
        rgba(255,255,255,0.03);

    border-bottom:
        1px solid rgba(255,255,255,0.06);

    color: white;
}

.watch-frame {

    width: 100%;
    height: 75vh;

    border: none;
}

/* MOBILE */

@media(max-width:768px) {

    .episodes-glass-panel {
        padding: 18px;
    }

    .preview-actions {
        flex-direction: column;
    }

    .watched-btn-modern {
        width: 100%;
    }

    .preview-image-wrapper {
        height: 240px;
    }

    #previewTitle {
        font-size: 1.5rem;
    }
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

                    const modalEl =
                        document.getElementById('watchModal');

                    const frame =
                        document.getElementById('watchFrame');

                    frame.src =
                        this.dataset.embed;

                    const modal =
                        new bootstrap.Modal(modalEl);

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

    const modalEl =
        document.getElementById('watchModal');

    if (modalEl) {

        modalEl.addEventListener('hidden.bs.modal', function () {

            const frame =
                document.getElementById('watchFrame');

            frame.src = '';
        });
    }

});

</script>