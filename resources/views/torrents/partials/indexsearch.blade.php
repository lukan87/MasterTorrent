<!-- Premium Search Form -->
<div class="torrent-search card border-0 mb-4 mt-5">
    <div class="card-body">
        <form action="{{ route('torrents.index') }}" method="GET">

            <div class="row g-4 align-items-end">

                <!-- SEARCH -->
                <div class="col-xxl-9 col-xl-8 col-md-6">
                    <label class="form-label">Search</label>

                    <div class="premium-search-combined">

                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                name="keyword"
                                class="form-control premium-input-combined"
                                placeholder="Title, IMDb link, keyword…"
                                value="{{ request('keyword') }}">
                        </div>

                        <div class="search-divider"></div>

                        <button 
    type="button"
    class="categories-trigger collapsed"
    data-bs-toggle="collapse"
    data-bs-target="#categoriesPanel"
>
    <i class="bi bi-collection me-1"></i>
    <span id="categoriesLabel" class="fw-semibold">
        Categories
        @if(request('categories'))
            ({{ count(request('categories')) }})
        @endif
    </span>
    <i class="bi bi-chevron-down ms-1 toggle-arrow"></i>
</button>

                    </div>
                </div>

                <!-- FILTERS -->
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <label class="form-label">Filters</label>

                    <div class="premium-search-combined">

                        <!-- GENRE -->
                        <div class="premium-inline-dropdown">
                            <input type="hidden" name="genre" value="{{ request('genre') }}">
                            <button type="button" class="inline-dropdown-trigger">
                                <i class="bi bi-film me-2 search-icon"></i>
                                <span>
                                    {{ $allGenres->firstWhere('id', request('genre'))->name ?? 'All Genres' }}
                                </span>
                                <i class="bi bi-chevron-down ms-2 toggle-arrow"></i>
                            </button>

                            <div class="inline-dropdown-menu">
                                <div class="inline-option" data-value="">All Genres</div>
                                @foreach($allGenres as $genre)
                                    <div class="inline-option" data-value="{{ $genre->id }}">
                                        {{ $genre->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="search-divider"></div>

                        <!-- STATUS -->
                        <div class="premium-inline-dropdown">
                            <input type="hidden" name="torrent_status" value="{{ request('torrent_status') }}">
                            <button type="button" class="inline-dropdown-trigger">
                                <i class="bi bi-activity me-2 search-icon"></i>
                                <span>
                                    {{ ucfirst(request('torrent_status') ?? 'All Status') }}
                                </span>
                                <i class="bi bi-chevron-down ms-2 toggle-arrow"></i>
                            </button>

                            <div class="inline-dropdown-menu">
                                <div class="inline-option" data-value="">All</div>
                                <div class="inline-option" data-value="active">Active</div>
                                <div class="inline-option" data-value="dead">Dead</div>
                                <div class="inline-option" data-value="free">Free</div>
                                <div class="inline-option" data-value="double">Double</div>
                                <div class="inline-option" data-value="seedbox">Seedbox</div>
                            </div>
                        </div>

                        <div class="search-divider"></div>

                        <button type="submit" class="categories-trigger">
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>

                    </div>
                </div>

            </div>

            <!-- CATEGORIES PANEL -->
            {{-- <div class="collapse mt-4 {{ request('categories') ? 'show' : '' }}" id="categoriesPanel"> --}}
                <div class="collapse mt-4" id="categoriesPanel">
                <div class="card premium-category-panel p-3 p-md-4">

                    <div class="row g-4">

                        <!-- MOVIES -->
                        <div class="col-md-4">
                            <h6 class="section-title text-primary">
                                <i class="bi bi-film me-2"></i>Movies
                            </h6>
                            @foreach ($categories->whereIn('id',[1,2,5,6,9,10,11,12,16,17,18,19,24,25,31,32,54,55,81,82]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        {{ str_replace('Movies:', '', $category->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- TV -->
                        <div class="col-md-4">
                            <h6 class="section-title text-success">
                                <i class="bi bi-tv me-2"></i>TV Shows
                            </h6>
                            @foreach ($categories->whereIn('id',[13,14,20,21]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>

                        <!-- OTHER -->
                        <div class="col-md-4">
                            <h6 class="section-title text-warning">
                                <i class="bi bi-collection me-2"></i>Other
                            </h6>
                            @foreach ($categories->whereIn('id',[22,26,28,30,33,42,43,44,49,56,57]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>

                    </div>

                </div>
            </div>

        </form>
    </div>
</div>


<style>
/* =========================================================
   FILEIPLAY — TORRENT SEARCH
   Dark navy glass + teal forum style
   ========================================================= */

.torrent-search {
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    box-shadow: 0 14px 35px rgba(0,0,0,.28);
    backdrop-filter: blur(14px);
}

.torrent-search .card-body {
    padding: 18px;
}

.torrent-search .form-label {
    color: rgba(255,255,255,.70);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 7px;
}

/* Combined search/filter bars */

.premium-search-combined {
    display: flex;
    align-items: center;
    min-height: 44px;

    background: rgba(10, 17, 30, .78);
    border: 1px solid var(--ui-border);
    border-radius: .65rem;

    padding: 5px 9px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.premium-search-combined:focus-within {
    border-color: rgba(45,212,191,.40);
    box-shadow: 0 0 0 3px rgba(45,212,191,.07);
    background: rgba(10, 17, 30, .92);
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.search-icon {
    color: var(--ui-accent);
    margin-right: 8px;
}

.premium-input-combined {
    width: 100%;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #fff !important;
    font-size: 14px;
    padding: 6px 2px;
}

.premium-input-combined::placeholder {
    color: rgba(255,255,255,.38);
}

.premium-input-combined:focus {
    outline: none;
}

.search-divider {
    width: 1px;
    height: 26px;
    flex: 0 0 1px;
    background: var(--ui-border);
    margin: 0 10px;
}

/* Buttons */

.categories-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 2px;

    background: transparent;
    border: none;

    color: rgba(255,255,255,.70);

    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
    cursor: pointer;

    padding: 6px 5px;

    transition:
        color .15s ease,
        background .15s ease;
}

.categories-trigger:hover {
    color: var(--ui-accent);
}

.toggle-arrow {
    transition: transform .2s ease;
}

.categories-trigger:not(.collapsed) .toggle-arrow {
    transform: rotate(180deg);
}

/* Inline dropdowns */

.premium-inline-dropdown {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 0;
}

.inline-dropdown-trigger {
    display: flex;
    align-items: center;

    background: transparent;
    border: none;

    color: rgba(255,255,255,.70);

    font-size: 13px;
    font-weight: 500;

    cursor: pointer;

    padding: 6px 5px;

    white-space: nowrap;

    transition: color .15s ease;
}

.inline-dropdown-trigger:hover {
    color: #fff;
}

.inline-dropdown-trigger .search-icon {
    margin-right: 7px;
}

.inline-dropdown-menu {
    position: absolute;
    top: calc(100% + 7px);
    left: 0;

    min-width: 200px;
    max-height: 280px;

    padding: 5px;

    background: #111b2d;
    border: 1px solid var(--ui-border);
    border-radius: .65rem;

    box-shadow: 0 18px 35px rgba(0,0,0,.48);

    display: none;
    overflow-y: auto;

    z-index: 1000;
}

.inline-option {
    padding: 8px 10px;

    border-radius: .4rem;

    color: rgba(255,255,255,.68);

    font-size: 13px;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease;
}

.inline-option:hover {
    background: rgba(45,212,191,.08);
    color: var(--ui-accent);
}

.premium-inline-dropdown.active .inline-dropdown-menu {
    display: block;
}

/* Categories panel */

.premium-category-panel {
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.96),
        rgba(15,23,42,.90)
    );

    border: 1px solid var(--ui-border);
    border-radius: .75rem;

    box-shadow: 0 18px 38px rgba(0,0,0,.32);
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 12px;
}

.section-title.text-primary {
    color: var(--ui-accent) !important;
}

.section-title.text-success {
    color: #6ee7b7 !important;
}

.section-title.text-warning {
    color: #fcd34d !important;
}

.premium-check {
    margin-bottom: 7px;
}

.premium-check .form-check-input {
    background-color: rgba(255,255,255,.04);
    border-color: rgba(255,255,255,.18);
    margin-top: .18em;
    cursor: pointer;
}

.premium-check .form-check-input:checked {
    background-color: var(--ui-accent);
    border-color: var(--ui-accent);
}

.premium-check .form-check-input:focus {
    border-color: var(--ui-accent);
    box-shadow: 0 0 0 .2rem rgba(45,212,191,.10);
}

.premium-check .form-check-label {
    color: rgba(255,255,255,.68);
    font-size: 13px;
    cursor: pointer;
}

.premium-check:hover .form-check-label {
    color: #fff;
}

/* Category count */

#categoriesLabel.active-count {
    color: var(--ui-accent);
}

/* Subtle count pulse */

@keyframes badgePulse {
    0% {
        transform: scale(1);
    }

    30% {
        transform: scale(1.08);
    }

    60% {
        transform: scale(.97);
    }

    100% {
        transform: scale(1);
    }
}

#categoriesLabel.pulse {
    animation: badgePulse .35s cubic-bezier(.4,0,.2,1);
}

/* Mobile */

@media (max-width: 768px) {

    .torrent-search {
        border-radius: .75rem;
    }

    .torrent-search .card-body {
        padding: 14px;
    }

    .premium-search-combined {
        flex-wrap: wrap;
        gap: 2px;
        padding: 6px 8px;
    }

    .search-input-wrapper {
        flex: 1 1 100%;
    }

    .search-input-wrapper .premium-input-combined {
        min-width: 0;
    }

    .search-divider {
        margin: 0 7px;
    }

    .categories-trigger,
    .inline-dropdown-trigger {
        font-size: 13px;
    }

    .inline-dropdown-menu {
        max-width: calc(100vw - 40px);
    }

    .premium-category-panel {
        padding: 14px !important;
    }

    .premium-check .form-check-label {
        font-size: 13px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ==============================
       INLINE DROPDOWNS
    ============================== */
    document.querySelectorAll('.premium-inline-dropdown').forEach(drop => {

        const trigger = drop.querySelector('.inline-dropdown-trigger');
        const hiddenInput = drop.querySelector('input');
        const label = trigger.querySelector('span');

        trigger.addEventListener('click', () => {
            document.querySelectorAll('.premium-inline-dropdown').forEach(d => {
                if (d !== drop) d.classList.remove('active');
            });
            drop.classList.toggle('active');
        });

        drop.querySelectorAll('.inline-option').forEach(option => {
            option.addEventListener('click', () => {
                hiddenInput.value = option.dataset.value;
                label.textContent = option.textContent;
                drop.classList.remove('active');
            });
        });

    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.premium-inline-dropdown')) {
            document.querySelectorAll('.premium-inline-dropdown')
                .forEach(d => d.classList.remove('active'));
        }
    });


/* ==============================
   CATEGORY COUNT + PULSE
============================== */
const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
const categoriesLabel = document.getElementById('categoriesLabel');

if (categoriesLabel && categoryCheckboxes.length) {

    let previousCount = document.querySelectorAll('input[name="categories[]"]:checked').length;

    if (previousCount > 0) {
        categoriesLabel.classList.add('pulse');
    }

    function updateCategoryCount() {
        const count = document.querySelectorAll('input[name="categories[]"]:checked').length;

        categoriesLabel.textContent = count > 0 ? `Categories (${count})` : 'Categories';

        if (count > 0) {
            categoriesLabel.classList.add('active-count');
        } else {
            categoriesLabel.classList.remove('active-count');
        }

        if (count !== previousCount) {
            categoriesLabel.classList.remove('pulse');
            void categoriesLabel.offsetWidth;
            categoriesLabel.classList.add('pulse');
        }

        previousCount = count;
    }

    updateCategoryCount(); // 👈 important

    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateCategoryCount);
    });
}

});
</script>