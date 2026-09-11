
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
                                value="{{ request('keyword') }}"
                            >

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

                            <input
                                type="hidden"
                                name="genre"
                                value="{{ request('genre') }}"
                            >

                            <button
                                type="button"
                                class="inline-dropdown-trigger"
                            >

                                <i class="bi bi-film me-2 search-icon"></i>

                                <span>
                                    {{ $allGenres->firstWhere('id', request('genre'))->name ?? 'All Genres' }}
                                </span>

                                <i class="bi bi-chevron-down ms-2 toggle-arrow"></i>

                            </button>

                            <div class="inline-dropdown-menu">

                                <div
                                    class="inline-option"
                                    data-value=""
                                >
                                    All Genres
                                </div>

                                @foreach($allGenres as $genre)

                                    <div
                                        class="inline-option"
                                        data-value="{{ $genre->id }}"
                                    >
                                        {{ $genre->name }}
                                    </div>

                                @endforeach

                            </div>

                        </div>


                        <div class="search-divider"></div>


                        <!-- STATUS -->

                        <div class="premium-inline-dropdown">

                            <input
                                type="hidden"
                                name="torrent_status"
                                value="{{ request('torrent_status') }}"
                            >

                            <button
                                type="button"
                                class="inline-dropdown-trigger"
                            >

                                <i class="bi bi-activity me-2 search-icon"></i>

                                <span>
                                    {{ ucfirst(request('torrent_status') ?? 'All Status') }}
                                </span>

                                <i class="bi bi-chevron-down ms-2 toggle-arrow"></i>

                            </button>

                            <div class="inline-dropdown-menu">

                                <div
                                    class="inline-option"
                                    data-value=""
                                >
                                    All
                                </div>

                                <div
                                    class="inline-option"
                                    data-value="active"
                                >
                                    Active
                                </div>

                                <div
                                    class="inline-option"
                                    data-value="dead"
                                >
                                    Dead
                                </div>

                                <div
                                    class="inline-option"
                                    data-value="free"
                                >
                                    Free
                                </div>

                                <div
                                    class="inline-option"
                                    data-value="double"
                                >
                                    Double
                                </div>

                                <div
                                    class="inline-option"
                                    data-value="seedbox"
                                >
                                    Seedbox
                                </div>

                            </div>

                        </div>


                        <div class="search-divider"></div>


                        <!-- SEARCH BUTTON -->

                        <button
                            type="submit"
                            class="categories-trigger"
                        >

                            <i class="bi bi-search me-1"></i>

                            Search

                        </button>

                    </div>

                </div>

            </div>


            <!-- CATEGORIES PANEL -->

            <div
                class="collapse mt-4"
                id="categoriesPanel"
            >

                <div class="card premium-category-panel p-3 p-md-4">

                    <div class="row g-4">


                        <!-- MOVIES -->

                        <div class="col-md-4">

                            <h6 class="section-title text-primary">

                                <i class="bi bi-film me-2"></i>

                                Movies

                            </h6>

                            @foreach ($categories->whereIn('id', [
                                1,2,5,6,9,10,11,12,16,17,
                                18,19,24,25,31,32,54,55,81,82
                            ]) as $category)

                                <div class="form-check premium-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="categories[]"
                                        value="{{ $category->id }}"
                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label">

                                        {{ str_replace('Movies:', '', $category->name) }}

                                    </label>

                                </div>

                            @endforeach

                        </div>


                        <!-- TV -->

                        <div class="col-md-4">

                            <h6 class="section-title text-success">

                                <i class="bi bi-tv me-2"></i>

                                TV Shows

                            </h6>

                            @foreach ($categories->whereIn('id', [
                                13,14,20,21
                            ]) as $category)

                                <div class="form-check premium-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="categories[]"
                                        value="{{ $category->id }}"
                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label">

                                        {{ $category->name }}

                                    </label>

                                </div>

                            @endforeach

                        </div>


                        <!-- OTHER -->

                        <div class="col-md-4">

                            <h6 class="section-title text-warning">

                                <i class="bi bi-collection me-2"></i>

                                Other

                            </h6>

                            @foreach ($categories->whereIn('id', [
                                22,26,28,30,33,42,43,44,49,56,57
                            ]) as $category)

                                <div class="form-check premium-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="categories[]"
                                        value="{{ $category->id }}"
                                        {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label">

                                        {{ $category->name }}

                                    </label>

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


/* =========================================================
   SEARCH CARD
   ========================================================= */

.torrent-search {

    position: relative;
    z-index: 1000;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );

    border: 1px solid var(--ui-border);

    border-radius: .85rem;

    box-shadow:
        0 14px 35px rgba(0,0,0,.28);

    backdrop-filter: blur(14px);
}


.torrent-search .card-body {

    position: relative;
    z-index: 1001;

    padding: 18px;
}


.torrent-search .form-label {

    color: rgba(255,255,255,.70);

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 7px;
}


/* =========================================================
   COMBINED SEARCH / FILTER BAR
   ========================================================= */

.premium-search-combined {

    display: flex;

    align-items: center;

    min-height: 44px;

    background: rgba(10, 17, 30, .78);

    border: 1px solid var(--ui-border);

    border-radius: .65rem;

    padding: 5px 9px;

    position: relative;

    z-index: 10;

    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}


.premium-search-combined:focus-within {

    border-color: rgba(45,212,191,.40);

    box-shadow:
        0 0 0 3px rgba(45,212,191,.07);

    background: rgba(10, 17, 30, .92);
}


/* =========================================================
   SEARCH INPUT
   ========================================================= */

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


/* =========================================================
   DIVIDER
   ========================================================= */

.search-divider {

    width: 1px;

    height: 26px;

    flex: 0 0 1px;

    background: var(--ui-border);

    margin: 0 10px;
}


/* =========================================================
   BUTTONS
   ========================================================= */

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


/* =========================================================
   ARROW
   ========================================================= */

.toggle-arrow {

    transition:
        transform .2s ease;
}


.categories-trigger:not(.collapsed) .toggle-arrow {

    transform: rotate(180deg);
}


/* =========================================================
   INLINE DROPDOWNS
   ========================================================= */

.premium-inline-dropdown {

    position: relative;

    display: flex;

    align-items: center;

    min-width: 0;
}


.premium-inline-dropdown.active {

    z-index: 100;
}


/* Trigger */

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

    transition:
        color .15s ease;
}


.inline-dropdown-trigger:hover {

    color: #fff;
}


.inline-dropdown-trigger .search-icon {

    margin-right: 7px;
}


/* =========================================================
   DROPDOWN MENU
   =========================================================

   IMPORTANT:
   The JavaScript moves this menu directly into <body>
   while it is open.

   This means torrent cards and other page containers
   cannot cover the menu or steal the mouse click.
   ========================================================= */

.inline-dropdown-menu {

    position: fixed;

    min-width: 220px;

    max-width: 320px;

    max-height: 300px;

    padding: 6px;

    background: #111b2d;

    border: 1px solid var(--ui-border);

    border-radius: .65rem;

    box-shadow:
        0 20px 45px rgba(0,0,0,.65),
        0 0 0 1px rgba(45,212,191,.08);

    display: none;

    overflow-y: auto;

    z-index: 2147483647;

    pointer-events: auto;
}


.inline-dropdown-menu.show-menu {

    display: block;
}


/* =========================================================
   DROPDOWN OPTIONS
   ========================================================= */

.inline-option {

    display: block;

    width: 100%;

    padding: 10px 12px;

    color: rgba(255,255,255,.75);

    background: transparent;

    border-radius: .45rem;

    font-size: 14px;

    line-height: 1.35;

    cursor: pointer;

    user-select: none;

    transition:
        background .15s ease,
        color .15s ease;
}


.inline-option:hover {

    background: rgba(45,212,191,.12);

    color: var(--ui-accent);
}


.inline-option:active {

    background: rgba(45,212,191,.20);

    color: #fff;
}


/* =========================================================
   CATEGORY PANEL
   ========================================================= */

.premium-category-panel {

    background: linear-gradient(
        135deg,
        rgba(22,32,51,.96),
        rgba(15,23,42,.90)
    );

    border: 1px solid var(--ui-border);

    border-radius: .75rem;

    box-shadow:
        0 18px 38px rgba(0,0,0,.32);
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


/* =========================================================
   CATEGORY CHECKBOXES
   ========================================================= */

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

    box-shadow:
        0 0 0 .2rem rgba(45,212,191,.10);
}


.premium-check .form-check-label {

    color: rgba(255,255,255,.68);

    font-size: 13px;

    cursor: pointer;
}


.premium-check:hover .form-check-label {

    color: #fff;
}


/* =========================================================
   CATEGORY COUNT
   ========================================================= */

#categoriesLabel.active-count {

    color: var(--ui-accent);
}


/* =========================================================
   CATEGORY COUNT PULSE
   ========================================================= */

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

    animation:
        badgePulse .35s cubic-bezier(.4,0,.2,1);
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .torrent-search {

        border-radius: .75rem;
    }


    .torrent-search .card-body {

        padding: 14px;
    }


    .premium-search-combined {

        display: flex;

        flex-wrap: wrap;

        gap: 2px;

        padding: 6px 8px;
    }


    .search-input-wrapper {

        flex: 1 1 100%;
    }


    .search-input-wrapper
    .premium-input-combined {

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

        max-width: calc(100vw - 20px);

        max-height: 60vh;
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


    /* =========================================================
       FILEIPLAY — INLINE DROPDOWNS
       ========================================================= */

    const dropdowns = document.querySelectorAll(
        '.premium-inline-dropdown'
    );


    dropdowns.forEach(drop => {

        const trigger = drop.querySelector(
            '.inline-dropdown-trigger'
        );

        const menu = drop.querySelector(
            '.inline-dropdown-menu'
        );

        const hiddenInput = drop.querySelector(
            'input[type="hidden"]'
        );

        const label = trigger?.querySelector('span');


        if (!trigger || !menu || !hiddenInput || !label) {

            return;
        }


        /*
         * Remember where the menu originally lives.
         */

        const originalParent = menu.parentNode;

        const originalNextSibling = menu.nextSibling;


        menu._originalParent = originalParent;

        menu._originalNextSibling = originalNextSibling;


        /* =====================================================
           CLOSE THIS DROPDOWN
           ===================================================== */

        function closeDropdown() {

            drop.classList.remove('active');

            menu.classList.remove('show-menu');


            /*
             * Return the menu to its original position.
             */

            if (menu.parentNode === document.body) {

                if (
                    originalNextSibling &&
                    originalNextSibling.parentNode === originalParent
                ) {

                    originalParent.insertBefore(
                        menu,
                        originalNextSibling
                    );

                } else {

                    originalParent.appendChild(menu);

                }

            }


            menu.style.left = '';

            menu.style.top = '';

            menu.style.width = '';

        }


        /* =====================================================
           CLOSE ALL OTHER DROPDOWNS
           ===================================================== */

        function closeAllDropdowns(except = null) {

            dropdowns.forEach(other => {

                if (other === except) {

                    return;
                }


                const otherMenu =
                    other.querySelector(
                        '.inline-dropdown-menu'
                    );


                if (!otherMenu) {

                    return;
                }


                other.classList.remove('active');

                otherMenu.classList.remove(
                    'show-menu'
                );


                const parent =
                    otherMenu._originalParent ||
                    otherMenu.parentNode;


                const nextSibling =
                    otherMenu._originalNextSibling ||
                    null;


                if (
                    otherMenu.parentNode ===
                    document.body
                ) {

                    if (
                        nextSibling &&
                        nextSibling.parentNode === parent
                    ) {

                        parent.insertBefore(
                            otherMenu,
                            nextSibling
                        );

                    } else {

                        parent.appendChild(
                            otherMenu
                        );

                    }

                }


                otherMenu.style.left = '';

                otherMenu.style.top = '';

                otherMenu.style.width = '';

            });

        }


        /* =====================================================
           POSITION DROPDOWN
           ===================================================== */

        function positionDropdown() {

            const rect =
                trigger.getBoundingClientRect();


            /*
             * Minimum dropdown width.
             */

            const width =
                Math.max(
                    220,
                    rect.width
                );


            menu.style.width =
                width + 'px';


            let left =
                rect.left;


            let top =
                rect.bottom + 7;


            /*
             * Keep inside right edge.
             */

            if (
                left + width >
                window.innerWidth - 10
            ) {

                left =
                    window.innerWidth -
                    width -
                    10;

            }


            /*
             * Keep inside left edge.
             */

            if (left < 10) {

                left = 10;

            }


            /*
             * Open upwards when there
             * isn't enough room below.
             */

            const menuHeight =
                Math.min(
                    menu.scrollHeight,
                    300
                );


            if (
                top + menuHeight >
                window.innerHeight - 10
            ) {

                const above =
                    rect.top -
                    menuHeight -
                    7;


                if (above >= 10) {

                    top = above;

                }

            }


            menu.style.left =
                left + 'px';


            menu.style.top =
                top + 'px';

        }


        /* =====================================================
           OPEN DROPDOWN
           ===================================================== */

        function openDropdown() {

            /*
             * Close all other menus.
             */

            closeAllDropdowns(drop);


            drop.classList.add('active');


            /*
             * CRITICAL FIX:
             *
             * Move the menu directly into BODY.
             *
             * This removes it from the stacking context
             * created by the torrent search card and prevents
             * torrent cards from covering the options.
             */

            document.body.appendChild(menu);


            menu.classList.add('show-menu');


            /*
             * Position after it is visible.
             */

            positionDropdown();

        }


        /* =====================================================
           TRIGGER CLICK
           ===================================================== */

        trigger.addEventListener(
            'click',
            function (e) {

                e.preventDefault();

                e.stopPropagation();


                if (
                    drop.classList.contains(
                        'active'
                    )
                ) {

                    closeDropdown();

                } else {

                    openDropdown();

                }

            }
        );


        /* =====================================================
           OPTION CLICK
           ===================================================== */

        menu.querySelectorAll(
            '.inline-option'
        ).forEach(option => {

            option.addEventListener(
                'click',
                function (e) {

                    e.preventDefault();

                    e.stopPropagation();


                    /*
                     * Set hidden input.
                     */

                    hiddenInput.value =
                        this.dataset.value;


                    /*
                     * Update visible label.
                     */

                    label.textContent =
                        this.textContent.trim();


                    /*
                     * Close menu.
                     */

                    closeDropdown();

                }
            );

        });


        /* =====================================================
           RESIZE
           ===================================================== */

        window.addEventListener(
            'resize',
            function () {

                if (
                    drop.classList.contains(
                        'active'
                    )
                ) {

                    positionDropdown();

                }

            }
        );


        /* =====================================================
           SCROLL
           ===================================================== */

        window.addEventListener(
            'scroll',
            function () {

                if (
                    drop.classList.contains(
                        'active'
                    )
                ) {

                    positionDropdown();

                }

            },
            true
        );

    });


    /* =========================================================
       CLICK OUTSIDE DROPDOWN
       ========================================================= */

    document.addEventListener(
        'click',
        function (e) {

            dropdowns.forEach(drop => {

                const menu =
                    drop.querySelector(
                        '.inline-dropdown-menu'
                    );


                if (!menu) {

                    return;
                }


                /*
                 * Don't close when clicking the
                 * trigger or the dropdown itself.
                 */

                if (
                    !drop.contains(e.target) &&
                    !menu.contains(e.target)
                ) {

                    drop.classList.remove(
                        'active'
                    );

                    menu.classList.remove(
                        'show-menu'
                    );


                    const parent =
                        menu._originalParent;


                    const nextSibling =
                        menu._originalNextSibling;


                    /*
                     * Restore original position.
                     */

                    if (
                        menu.parentNode ===
                        document.body
                    ) {

                        if (
                            nextSibling &&
                            nextSibling.parentNode === parent
                        ) {

                            parent.insertBefore(
                                menu,
                                nextSibling
                            );

                        } else {

                            parent.appendChild(
                                menu
                            );

                        }

                    }


                    menu.style.left = '';

                    menu.style.top = '';

                    menu.style.width = '';

                }

            });

        }
    );


    /* =========================================================
       CATEGORY COUNT + PULSE
       ========================================================= */

    const categoryCheckboxes =
        document.querySelectorAll(
            'input[name="categories[]"]'
        );


    const categoriesLabel =
        document.getElementById(
            'categoriesLabel'
        );


    if (
        categoriesLabel &&
        categoryCheckboxes.length
    ) {


        let previousCount =
            document.querySelectorAll(
                'input[name="categories[]"]:checked'
            ).length;


        if (previousCount > 0) {

            categoriesLabel.classList.add(
                'pulse'
            );

        }


        function updateCategoryCount() {

            const count =
                document.querySelectorAll(
                    'input[name="categories[]"]:checked'
                ).length;


            categoriesLabel.textContent =
                count > 0
                    ? `Categories (${count})`
                    : 'Categories';


            if (count > 0) {

                categoriesLabel.classList.add(
                    'active-count'
                );

            } else {

                categoriesLabel.classList.remove(
                    'active-count'
                );

            }


            if (count !== previousCount) {

                categoriesLabel.classList.remove(
                    'pulse'
                );


                void categoriesLabel.offsetWidth;


                categoriesLabel.classList.add(
                    'pulse'
                );

            }


            previousCount = count;

        }


        updateCategoryCount();


        categoryCheckboxes.forEach(cb => {

            cb.addEventListener(
                'change',
                updateCategoryCount
            );

        });

    }

});

</script>

