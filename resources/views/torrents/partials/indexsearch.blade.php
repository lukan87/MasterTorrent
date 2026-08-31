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

.torrent-search {
    background: linear-gradient(145deg,#1b1b1b8b,#242424);
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}

/* Shared Search + Filters Bar */
.premium-search-combined {
    display:flex;
    align-items:center;
    background:linear-gradient(145deg,#111,#1c1c1c);
    border:1px solid rgba(255,255,255,.12);
    border-radius:14px;
    padding:6px 12px;
    transition:.25s ease;
}

.premium-search-combined:focus-within {
    border-color:#7c8cff;
    box-shadow:0 0 0 3px rgba(124,140,255,.2);
}

.search-input-wrapper {
    display:flex;
    align-items:center;
    flex:1;
}

.search-icon {
    color:#888;
    margin-right:8px;
}

.premium-input-combined {
    background:transparent;
    border:none;
    color:#fff;
}

.search-divider {
    width:1px;
    height:26px;
    background:rgba(255,255,255,.12);
    margin:0 12px;
}

.categories-trigger {
    background:transparent;
    border:none;
    color:#ccc;
    display:flex;
    align-items:center;
    font-weight:600;
    transition:.2s ease;
}

.categories-trigger:hover {
    color:#7c8cff;
}

/* Arrow animation */
.toggle-arrow {
    transition:transform .25s ease;
}

.categories-trigger:not(.collapsed) .toggle-arrow {
    transform:rotate(180deg);
}

/* Dropdown */
.premium-inline-dropdown {
    position:relative;
    display:flex;
    align-items:center;
}

.inline-dropdown-trigger {
    background:transparent;
    border:none;
    color:#ccc;
    display:flex;
    align-items:center;
    font-weight:500;
    cursor:pointer;
    padding:4px 6px;
}

.inline-dropdown-trigger:hover {
    color:#fff;
}

.inline-dropdown-menu {
    position:absolute;
    top:calc(100% + 6px);
    left:0;
    min-width:200px;
    background:#1c1c1c;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 20px 40px rgba(0,0,0,.6);
    display:none;
    max-height:280px;
    overflow-y:auto;
    z-index:1000;
}

.inline-option {
    padding:10px 14px;
    cursor:pointer;
    color:#ccc;
    transition:.2s ease;
}

.inline-option:hover {
    background:rgba(124,140,255,.15);
    color:#7c8cff;
}

.premium-inline-dropdown.active .inline-dropdown-menu {
    display:block;
}

/* Categories panel */
.premium-category-panel {
    background:linear-gradient(145deg,#151515,#1e1e1e);
    border-radius:16px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 20px 40px rgba(0,0,0,.55);
}

#categoriesLabel.active-count {
    color: #7c8cff;
}
/* ==============================
   CATEGORY BADGE PULSE
============================== */

@keyframes badgePulse {
    0% {
        transform: scale(1);
    }
    30% {
        transform: scale(1.15);
    }
    60% {
        transform: scale(0.95);
    }
    100% {
        transform: scale(1);
    }
}

#categoriesLabel.pulse {
    animation: badgePulse 0.35s cubic-bezier(.4,0,.2,1);
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