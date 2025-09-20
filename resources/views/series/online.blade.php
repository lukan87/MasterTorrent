<div style="display:block;height:50px"></div>

@php
    $firstSeason = $tvMazeSeasons[0]['number'] ?? null;
@endphp

<div class="card shadow-lg border-0 rounded-4 overflow-hidden glass-card" style="background: rgba(30,30,40,0.6);
                backdrop-filter: blur(2px);
                border-radius: 20px;
                padding: 25px;
                box-shadow: 0 20px 50px rgba(0,0,0,0.7);
                border: 1px solid rgba(255,255,255,0.08);
                color: #fff;">
    <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center">
        <h4 class="mb-2 mb-sm-0"><i class="bi bi-collection-play"></i> Episodes of {{ $TvMaze['name'] }}</h4>
        <input type="text" id="episodeSearch" class="form-control form-control-sm w-50" placeholder="Search all episodes...">
    </div>

    <div class="card-body">
        <!-- Season Switcher -->
        <div class="d-flex flex-row flex-nowrap overflow-auto mb-4 pb-2 border-bottom" id="seasonSwitcher">
            @foreach ($tvMazeSeasons as $season)
                <button class="btn btn-outline-secondary me-2 season-btn @if($loop->first) active @endif"
                        data-season="{{ $season['number'] }}">
                    Season {{ $season['number'] }}
                </button>
            @endforeach
        </div>

        <!-- Episode Grid with vertical arrows -->
        <div class="episode-scroll-container position-relative">
            <!-- Up Arrow -->
            <button class="scroll-btn scroll-up btn btn-dark rounded-circle shadow"
                    style="top:10px; left:50%; transform:translateX(-50%);">
                <i class="bi bi-arrow-up"></i>
            </button>

            <!-- Scrollable Episodes -->
            <div class="episode-grid-wrapper" style="height:500px;">
                <div class="row g-4" id="episodeGrid">
                    @foreach ($tvMazeEpisodes as $episode)
                        @php $summaryText = strip_tags($episode['summary'] ?? ''); @endphp
                        <div class="col-12 col-md-6 col-lg-4 episode-card"
                             data-season="{{ $episode['season'] }}"
                             data-title="{{ strtolower($episode['name'] ?? '') }}"
                             data-summary="{{ strtolower($summaryText) }}"
                             @if($episode['season'] != $firstSeason) style="display:none;" @endif>
                            <div class="card h-100 border-0 shadow-sm episode-item">
                                <img src="{{ $episode['image']['original'] ?? url('/images/noposter.jpg') }}"
                                     class="card-img-top episode-poster"
                                     alt="{{ $episode['name'] ?? 'Episode' }}">

                                <div class="card-body d-flex flex-column">
                                    <h6 class="fw-bold">{{ $episode['season'] }}x{{ $episode['number'] }} — {{ $episode['name'] }}</h6>

                                    @if($summaryText)
                                        <p class="text-muted small episode-summary collapsed">{!! e($summaryText) !!}</p>
                                        @if(mb_strlen($summaryText) > 350)
                                            <button class="btn btn-link p-0 small read-more">Read more</button>
                                        @endif
                                    @endif

                                    <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                                        <small class="text-secondary">
                                            <i class="bi bi-calendar-event"></i> {{ $episode['airdate'] }}
                                        </small>
                                        <div class="d-flex align-items-center gap-2">
                                            @php $userClass = auth()->user()->user_class; @endphp
                                            <button class="btn btn-sm watched-btn btn-outline-success" data-episode="{{ $episode['id'] }}">
                                                <i class="bi bi-check-circle"></i>
                                            </button>

                                            @if ($userClass >= \App\Models\UserClass::VIP)
                                                <a href="#" class="btn btn-sm btn-outline-info watch-btn"
                                                   data-embed="https://v2.vidsrc.me/embed/{{ $series['imdb_id'] }}/{{ $episode['season'] }}-{{ $episode['number'] }}"
                                                   data-bs-toggle="tooltip"
                                                   title="Watch Episode">
                                                    <i class="bi bi-play-circle-fill"></i>
                                                </a>
                                            @else
                                                <span class="badge bg-danger">VIP Only</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Down Arrow -->
            <button class="scroll-btn scroll-down btn btn-dark rounded-circle shadow"
                    style="bottom:10px; left:50%; transform:translateX(-50%);">
                <i class="bi bi-arrow-down"></i>
            </button>
        </div>
    </div>
</div>

<!-- Watch Modal -->
<div class="modal fade" id="watchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title text-white"><i class="bi bi-play-btn"></i> Now Watching</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="watchFrame" src="" class="w-100" style="height:70vh;" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const seasonBtns = Array.from(document.querySelectorAll('.season-btn'));
    const episodeCards = Array.from(document.querySelectorAll('.episode-card'));
    const searchInput = document.getElementById('episodeSearch');
    const watchedKey = 'watchedEpisodes';
    const watchModalEl = document.getElementById('watchModal');
    const watchFrame = document.getElementById('watchFrame');

    const scrollWrapper = document.querySelector('.episode-grid-wrapper');
    const scrollUp = document.querySelector('.scroll-up');
    const scrollDown = document.querySelector('.scroll-down');

    let currentSeason = document.querySelector('.season-btn.active')?.dataset.season || seasonBtns[0]?.dataset.season || null;

    // Show only the selected season (if no search)
    function showSeason(season) {
        episodeCards.forEach(card => {
            card.style.display = (card.dataset.season === season) ? '' : 'none';
        });
        updateScrollArrows();
    }

    // Search (title + summary across all seasons)
    function applySearch(q) {
        const query = q.trim().toLowerCase();
        if (!query) {
            showSeason(currentSeason);
            return;
        }
        episodeCards.forEach(card => {
            const title = (card.dataset.title || '');
            const summary = (card.dataset.summary || '');
            card.style.display = (title.includes(query) || summary.includes(query)) ? '' : 'none';
        });
        updateScrollArrows();
    }

    // Initial
    showSeason(currentSeason);

    // Season buttons
    seasonBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            seasonBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSeason = this.dataset.season;
            if (searchInput.value.trim()) {
                applySearch(searchInput.value);
            } else {
                showSeason(currentSeason);
                scrollWrapper.scrollTop = 0;
            }
        });
    });

    // Search input
    searchInput.addEventListener('input', function () {
        applySearch(this.value);
    });

    // Watch button modal
    document.querySelectorAll('.watch-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            watchFrame.src = this.dataset.embed;
            new bootstrap.Modal(watchModalEl).show();
        });
    });
    watchModalEl.addEventListener('hidden.bs.modal', () => { watchFrame.src = ""; });

    // Watched toggles
    let watchedEpisodes = JSON.parse(localStorage.getItem(watchedKey) || '[]');
    function updateWatchedUI() {
        document.querySelectorAll('.watched-btn').forEach(btn => {
            const id = String(btn.dataset.episode);
            if (watchedEpisodes.includes(id)) {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="bi bi-check2-circle"></i>';
            } else {
                btn.classList.add('btn-outline-success');
                btn.classList.remove('btn-success');
                btn.innerHTML = '<i class="bi bi-check-circle"></i>';
            }
        });
    }
    updateWatchedUI();

    document.querySelectorAll('.watched-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = String(this.dataset.episode);
            if (watchedEpisodes.includes(id)) {
                watchedEpisodes = watchedEpisodes.filter(x => x !== id);
            } else {
                watchedEpisodes.push(id);
            }
            localStorage.setItem(watchedKey, JSON.stringify(watchedEpisodes));
            updateWatchedUI();
        });
    });

    // Read more
    document.querySelectorAll('.read-more').forEach(btn => {
        btn.addEventListener('click', function () {
            const summary = this.previousElementSibling;
            const expanded = summary.classList.toggle('expanded');
            summary.classList.toggle('collapsed', !expanded);
            this.textContent = expanded ? 'Read less' : 'Read more';
        });
    });

    // Scroll by page
    function scrollByPage(dir) {
        const amount = scrollWrapper.clientHeight;
        scrollWrapper.scrollBy({ top: dir * amount, left: 0, behavior: "smooth" });
    }
    scrollUp.addEventListener('click', () => scrollByPage(-1));
    scrollDown.addEventListener('click', () => scrollByPage(1));

    // Auto-hide arrows
    function updateScrollArrows() {
        if (!scrollWrapper) return;
        const atTop = scrollWrapper.scrollTop <= 0;
        const atBottom = scrollWrapper.scrollTop + scrollWrapper.clientHeight >= scrollWrapper.scrollHeight - 1;
        scrollUp.style.display = atTop ? "none" : "block";
        scrollDown.style.display = atBottom ? "none" : "block";
    }
    scrollWrapper.addEventListener('scroll', updateScrollArrows);
    updateScrollArrows();
});
</script>

<style>
.episode-grid-wrapper {
    overflow-y: auto;
    padding-right: 12px;
}
.episode-item {
    transition: transform .18s ease, box-shadow .18s ease;
}
.episode-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0,0,0,.10);
}
.episode-poster {
    height: 330px;
    object-fit: cover;
    border-top-left-radius: .5rem;
    border-top-right-radius: .5rem;
}
.episode-summary {
    overflow: hidden;
}
.episode-summary.collapsed {
    max-height: 110px;
}
.episode-summary.expanded {
    max-height: none;
}
.season-btn {
    border-radius: 20px;
    font-weight: 600;
    transition: all .15s ease;
}
.season-btn.active, .season-btn:hover {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
.read-more {
    color: #0d6efd;
    cursor: pointer;
}
.scroll-btn {
    position: absolute;
    width: 40px;
    height: 40px;
    opacity: 0.8;
    transition: opacity .2s;
}
.scroll-btn:hover {
    opacity: 1;
}
.episode-scroll-container {
    position: relative;
}
@media (max-width: 576px) {
    .episode-poster { height: 160px; }
    #episodeSearch { width: 100% !important; margin-top: .5rem; }
}
.glass-card {
    background: rgba(42, 41, 41, 0.2); /* semi-transparent white */
    backdrop-filter: blur(2px); /* adds the blur effect */
    -webkit-backdrop-filter: blur(2px); /* for Safari */
    border: 1px solid rgba(255, 255, 255, 0.3); /* subtle border */
}

</style>
    