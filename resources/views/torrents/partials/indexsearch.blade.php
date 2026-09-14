<div class="torrent-search card border-0 mb-4 mt-5">
    <div class="card-body">

        <form action="{{ route('torrents.index') }}" method="GET" id="torrentSearchForm">

            <div class="row g-4 align-items-end">

                <!-- SEARCH -->
                <div class="col-xxl-9 col-xl-8 col-md-6">
                    <label class="form-label">Search</label>
                    <div class="premium-search-combined">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="keyword" id="searchKeyword"
                                   class="form-control premium-input-combined"
                                   placeholder="Title, IMDb link, keyword… (press / to focus)"
                                   value="{{ request('keyword') }}">
                        </div>
                        <div class="search-divider"></div>
                        <button type="button" class="categories-trigger collapsed"
                                data-bs-toggle="collapse" data-bs-target="#categoriesPanel">
                            <i class="bi bi-collection me-1"></i>
                            <span id="categoriesLabel" class="fw-semibold">
                                Categories
                                @if(request('categories')) ({{ count(request('categories')) }}) @endif
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
                                <span>{{ $allGenres->firstWhere('id', request('genre'))->name ?? 'All Genres' }}</span>
                                <i class="bi bi-chevron-down ms-2 toggle-arrow"></i>
                            </button>
                            <div class="inline-dropdown-menu">
                                <div class="inline-option" data-value="">All Genres</div>
                                @foreach($allGenres as $genre)
                                    <div class="inline-option" data-value="{{ $genre->id }}">{{ $genre->name }}</div>
                                @endforeach
                            </div>
                        </div>

                        <div class="search-divider"></div>

                        <!-- STATUS -->
                        <div class="premium-inline-dropdown">
                            <input type="hidden" name="torrent_status" value="{{ request('torrent_status') }}">
                            <button type="button" class="inline-dropdown-trigger">
                                <i class="bi bi-activity me-2 search-icon"></i>
                                <span>{{ ucfirst(request('torrent_status') ?? 'All Status') }}</span>
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

                        <!-- SEARCH BUTTON -->
                        <button type="submit" class="categories-trigger">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- CATEGORIES PANEL -->
            <div class="collapse mt-4" id="categoriesPanel">
                <div class="card premium-category-panel p-3 p-md-4">
                    <div class="row g-4">

                        <!-- MOVIES -->
                        <div class="col-md-4">
                            <h6 class="section-title text-primary"><i class="bi bi-film me-2"></i>Movies</h6>
                            @foreach ($categories->whereIn('id', [1,2,5,6,9,10,11,12,16,17,18,19,24,25,31,32,54,55,81,82]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                           value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ str_replace('Movies:', '', $category->name) }}</label>
                                </div>
                            @endforeach
                        </div>

                        <!-- TV -->
                        <div class="col-md-4">
                            <h6 class="section-title text-success"><i class="bi bi-tv me-2"></i>TV Shows</h6>
                            @foreach ($categories->whereIn('id', [13,14,20,21]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                           value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>

                        <!-- OTHER -->
                        <div class="col-md-4">
                            <h6 class="section-title text-warning"><i class="bi bi-collection me-2"></i>Other</h6>
                            @foreach ($categories->whereIn('id', [22,26,28,30,33,42,43,44,49,56,57]) as $category)
                                <div class="form-check premium-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                           value="{{ $category->id }}"
                                           {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>


            <!-- ACTIVE FILTERS -->
            @php
                $af = [];
                if (request('keyword'))               { $af['keyword'] = ['Keyword', request('keyword')]; }
                if (request('genre'))                 { $af['genre']   = ['Genre',   $allGenres->firstWhere('id', request('genre'))->name ?? request('genre')]; }
                if (request('torrent_status'))        { $af['status']  = ['Status',  ucfirst(request('torrent_status'))]; }
                $catCount = count(request('categories', []));
            @endphp

            @if($af || $catCount)
                <div class="active-filters">
                    <span class="af-label"><i class="bi bi-funnel me-1"></i>Active filters</span>
                    @foreach($af as $key => [$facet, $val])
                        <a class="badge af-badge" href="{{ route('torrents.index', request()->except([$key, 'page'])) }}">
                            {{ $facet }}: {{ $val }} <i class="bi bi-x-lg ms-1"></i>
                        </a>
                    @endforeach
                    @if($catCount)
                        <a class="badge af-badge" href="{{ route('torrents.index', request()->except(['categories', 'page'])) }}">
                            Categories: {{ $catCount }} <i class="bi bi-x-lg ms-1"></i>
                        </a>
                    @endif
                    <a class="badge af-badge af-clear" href="{{ route('torrents.index') }}">
                        <i class="bi bi-x-circle me-1"></i>Clear all
                    </a>
                </div>
            @endif

        </form>
    </div>
</div>


<style>
/* ============================================================
   FILEIPLAY - TORRENT SEARCH
   Dark navy glass + teal forum style (page-scoped to index)
   ============================================================ */

/* ------- SEARCH CARD ------- */
.torrent-search { position: relative; z-index: 1000;
  background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
  border: 1px solid var(--ui-border); border-radius: .85rem;
  box-shadow: 0 14px 35px rgba(0,0,0,.28); backdrop-filter: blur(14px); }
.torrent-search .card-body { position: relative; z-index: 1001; padding: 18px; }
.torrent-search .form-label { color: rgba(255,255,255,.70); font-size: 13px; font-weight: 600; margin-bottom: 7px; }

/* ------- COMBINED SEARCH / FILTER BAR ------- */
.premium-search-combined { display: flex; align-items: center; min-height: 44px;
  background: rgba(10,17,30,.78); border: 1px solid var(--ui-border); border-radius: .65rem;
  padding: 5px 9px; position: relative; z-index: 10;
  transition: border-color .18s ease, box-shadow .18s ease, background .18s ease; }
.premium-search-combined:focus-within { border-color: rgba(45,212,191,.40);
  box-shadow: 0 0 0 3px rgba(45,212,191,.07); background: rgba(10,17,30,.92); }

/* ------- SEARCH INPUT ------- */
.search-input-wrapper { display: flex; align-items: center; flex: 1; min-width: 0; }
.search-icon { color: var(--ui-accent); margin-right: 8px; }
.premium-input-combined { width: 100%; background: transparent !important; border: none !important;
  box-shadow: none !important; color: #fff !important; font-size: 14px; padding: 6px 2px; }
.premium-input-combined::placeholder { color: rgba(255,255,255,.38); }
.premium-input-combined:focus { outline: none; }

/* ------- DIVIDER ------- */
.search-divider { width: 1px; height: 26px; flex: 0 0 1px; background: var(--ui-border); margin: 0 10px; }

/* ------- BUTTONS ------- */
.categories-trigger { display: inline-flex; align-items: center; justify-content: center; gap: 2px;
  background: transparent; border: none; color: rgba(255,255,255,.70); font-size: 13px; font-weight: 600;
  white-space: nowrap; cursor: pointer; padding: 6px 5px; transition: color .15s ease, background .15s ease; }
.categories-trigger:hover { color: var(--ui-accent); }

/* ------- ARROW ------- */
.toggle-arrow { transition: transform .2s ease; }
.categories-trigger:not(.collapsed) .toggle-arrow { transform: rotate(180deg); }

/* ------- INLINE DROPDOWNS ------- */
.premium-inline-dropdown { position: relative; display: flex; align-items: center; min-width: 0; }
.premium-inline-dropdown.active { z-index: 100; }
.inline-dropdown-trigger { display: flex; align-items: center; background: transparent; border: none;
  color: rgba(255,255,255,.70); font-size: 13px; font-weight: 500; cursor: pointer; padding: 6px 5px;
  white-space: nowrap; transition: color .15s ease; }
.inline-dropdown-trigger:hover { color: #fff; }
.inline-dropdown-trigger .search-icon { margin-right: 7px; }

/* ------- DROPDOWN MENU -------
   NOTE: JS relocates the open menu into <body> so cards can't cover it. */
.inline-dropdown-menu { position: fixed; min-width: 220px; max-width: 320px; max-height: 300px; padding: 6px;
  background: #111b2d; border: 1px solid var(--ui-border); border-radius: .65rem;
  box-shadow: 0 20px 45px rgba(0,0,0,.65), 0 0 0 1px rgba(45,212,191,.08);
  display: none; overflow-y: auto; z-index: 2147483647; pointer-events: auto; }
.inline-dropdown-menu.show-menu { display: block; }

/* ------- DROPDOWN OPTIONS ------- */
.inline-option { display: block; width: 100%; padding: 10px 12px; color: rgba(255,255,255,.75);
  background: transparent; border-radius: .45rem; font-size: 14px; line-height: 1.35; cursor: pointer;
  user-select: none; transition: background .15s ease, color .15s ease; }
.inline-option:hover { background: rgba(45,212,191,.12); color: var(--ui-accent); }
.inline-option:active { background: rgba(45,212,191,.20); color: #fff; }

/* ------- CATEGORY PANEL ------- */
.premium-category-panel { background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.90));
  border: 1px solid var(--ui-border); border-radius: .75rem; box-shadow: 0 18px 38px rgba(0,0,0,.32); }
.section-title { font-size: 14px; font-weight: 700; margin-bottom: 12px; }
.section-title.text-primary { color: var(--ui-accent) !important; }
.section-title.text-success { color: #6ee7b7 !important; }
.section-title.text-warning { color: #fcd34d !important; }

/* ------- CATEGORY CHECKBOXES ------- */
.premium-check { margin-bottom: 7px; }
.premium-check .form-check-input { background-color: rgba(255,255,255,.04); border-color: rgba(255,255,255,.18); margin-top: .18em; cursor: pointer; }
.premium-check .form-check-input:checked { background-color: var(--ui-accent); border-color: var(--ui-accent); }
.premium-check .form-check-input:focus { border-color: var(--ui-accent); box-shadow: 0 0 0 .2rem rgba(45,212,191,.10); }
.premium-check .form-check-label { color: rgba(255,255,255,.68); font-size: 13px; cursor: pointer; }
.premium-check:hover .form-check-label { color: #fff; }

/* ------- CATEGORY COUNT + PULSE ------- */
#categoriesLabel.active-count { color: var(--ui-accent); }
@keyframes badgePulse { 0% { transform: scale(1); } 30% { transform: scale(1.08); } 60% { transform: scale(.97); } 100% { transform: scale(1); } }
#categoriesLabel.pulse { animation: badgePulse .35s cubic-bezier(.4,0,.2,1); }

/* ------- ACTIVE FILTERS BAR ------- */
.active-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 16px; }
.af-label { color: rgba(255,255,255,.55); font-size: 12.5px; font-weight: 600; margin-right: 2px; }
.af-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(45,212,191,.14);
  border: 1px solid rgba(45,212,191,.35); color: #6ee7b7; border-radius: 999px; padding: 5px 12px;
  font-size: 12.5px; font-weight: 600; text-decoration: none;
  transition: background .15s ease, transform .15s ease, color .15s ease; }
.af-badge:hover { background: rgba(45,212,191,.26); color: #fff; transform: translateY(-1px); }
.af-badge .bi-x-lg { opacity: .7; transition: opacity .15s ease; }
.af-badge:hover .bi-x-lg { opacity: 1; }
.af-clear { background: rgba(248,113,113,.14); border-color: rgba(248,113,113,.35); color: #fca5a5; }
.af-clear:hover { background: rgba(248,113,113,.26); color: #fff; }

/* ------- MOBILE ------- */
@media (max-width: 768px) {
  .torrent-search { border-radius: .75rem; }
  .torrent-search .card-body { padding: 14px; }
  .premium-search-combined { display: flex; flex-wrap: wrap; gap: 2px; padding: 6px 8px; }
  .search-input-wrapper { flex: 1 1 100%; }
  .search-input-wrapper .premium-input-combined { min-width: 0; }
  .search-divider { margin: 0 7px; }
  .categories-trigger, .inline-dropdown-trigger { font-size: 13px; }
  .inline-dropdown-menu { max-width: calc(100vw - 20px); max-height: 60vh; }
  .premium-category-panel { padding: 14px !important; }
  .premium-check .form-check-label { font-size: 13px; }
  .af-badge { font-size: 12px; padding: 4px 10px; }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       FILEIPLAY - TORRENT SEARCH BEHAVIOUR
       ============================================================ */

    const dropdowns = document.querySelectorAll('.premium-inline-dropdown');

    /* Put a relocated menu back where it originally lived. */
    function restoreMenu(menu) {
        const parent = menu._parent || menu.parentNode;
        const sib = menu._sib;
        if (menu.parentNode === document.body) {
            if (sib && sib.parentNode === parent) parent.insertBefore(menu, sib);
            else parent.appendChild(menu);
        }
        menu.style.left = ''; menu.style.top = ''; menu.style.width = '';
    }

    function closeMenu(menu) {
        if (menu._drop) menu._drop.classList.remove('active');
        menu.classList.remove('show-menu');
        restoreMenu(menu);
    }

    function closeAll(exceptMenu) {
        dropdowns.forEach(function (drop) {
            var m = drop.querySelector('.inline-dropdown-menu');
            if (m && m !== exceptMenu) closeMenu(m);
        });
    }

    /* Viewport-aware placement: stays on-screen, flips upward when needed. */
    function positionMenu(menu, trigger) {
        var rect = trigger.getBoundingClientRect();
        var width = Math.max(220, rect.width);
        var mh = Math.min(menu.scrollHeight, 300);
        var left = rect.left;
        var top = rect.bottom + 7;
        if (left + width > window.innerWidth - 10) left = window.innerWidth - width - 10;
        if (left < 10) left = 10;
        if (top + mh > window.innerHeight - 10) {
            var above = rect.top - mh - 7;
            if (above >= 10) top = above;
        }
        menu.style.width = width + 'px';
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
    }

    dropdowns.forEach(function (drop) {
        var trigger = drop.querySelector('.inline-dropdown-trigger');
        var menu = drop.querySelector('.inline-dropdown-menu');
        var hiddenInput = drop.querySelector('input[type="hidden"]');
        var label = trigger && trigger.querySelector('span');
        if (!trigger || !menu || !hiddenInput || !label) return;

        menu._parent = menu.parentNode;
        menu._sib = menu.nextSibling;
        menu._drop = drop;

        function open() {
            closeAll(menu);
            drop.classList.add('active');
            document.body.appendChild(menu);   /* keep it above cards */
            menu.classList.add('show-menu');
            positionMenu(menu, trigger);
        }

        function toggle(e) {
            e.preventDefault(); e.stopPropagation();
            drop.classList.contains('active') ? closeMenu(menu) : open();
        }

        trigger.addEventListener('click', toggle);

        menu.querySelectorAll('.inline-option').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.preventDefault(); e.stopPropagation();
                hiddenInput.value = opt.dataset.value;
                label.textContent = opt.textContent.trim();
                closeMenu(menu);
            });
        });

        window.addEventListener('resize', function () {
            if (drop.classList.contains('active')) positionMenu(menu, trigger);
        });
        window.addEventListener('scroll', function () {
            if (drop.classList.contains('active')) positionMenu(menu, trigger);
        }, true);
    });

    /* ---- Click outside closes ---- */
    document.addEventListener('click', function (e) {
        dropdowns.forEach(function (drop) {
            var menu = drop.querySelector('.inline-dropdown-menu');
            if (menu && !drop.contains(e.target) && !menu.contains(e.target)) closeMenu(menu);
        });
    });

    /* ---- Escape closes ---- */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdowns.forEach(function (drop) {
                var menu = drop.querySelector('.inline-dropdown-menu');
                if (menu) closeMenu(menu);
            });
        }
    });

    /* ============================================================
       CATEGORY COUNT + PULSE
       ============================================================ */
    var categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
    var categoriesLabel = document.getElementById('categoriesLabel');

    if (categoriesLabel && categoryCheckboxes.length) {
        function countChecked() {
            return document.querySelectorAll('input[name="categories[]"]:checked').length;
        }
        var prev = countChecked();
        if (prev > 0) categoriesLabel.classList.add('pulse');

        function update() {
            var c = countChecked();
            categoriesLabel.textContent = c > 0 ? 'Categories (' + c + ')' : 'Categories';
            categoriesLabel.classList.toggle('active-count', c > 0);
            if (c !== prev) {
                categoriesLabel.classList.remove('pulse');
                void categoriesLabel.offsetWidth;   /* restart animation */
                categoriesLabel.classList.add('pulse');
            }
            prev = c;
        }
        update();
        categoryCheckboxes.forEach(function (cb) { cb.addEventListener('change', update); });
    }

    /* ============================================================
       SLASH SHORTCUT - focus the search box
       ============================================================ */
    var kw = document.getElementById('searchKeyword');
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !/^(input|textarea|select)$/.test((document.activeElement.tagName || '').toLowerCase())) {
            e.preventDefault();
            if (kw) {
                kw.focus();
                if (kw.value) kw.setSelectionRange(kw.value.length, kw.value.length);
            }
        }
    });

    /* ============================================================
       DEBOUNCED AUTO-SUBMIT AS YOU TYPE
       ============================================================ */
    var form = document.getElementById('torrentSearchForm');
    if (kw && form) {
        var debounce = null;
        kw.addEventListener('input', function () {
            clearTimeout(debounce);
            var v = kw.value.trim();
            if (v.length >= 2 || v.length === 0) {
                debounce = setTimeout(function () { form.submit(); }, 900);
            }
        });
    }

    /* ---- Restore focus/caret after a keyword-only reload ---- */
    if (kw) {
        var params = new URLSearchParams(location.search);
        var keys = Array.prototype.slice.call(params.keys());
        if (params.get('keyword') && keys.every(function (k) { return ['keyword','page','sort','direction'].indexOf(k) !== -1; })) {
            kw.focus();
            if (kw.value) kw.setSelectionRange(kw.value.length, kw.value.length);
        }
    }
});
</script>
