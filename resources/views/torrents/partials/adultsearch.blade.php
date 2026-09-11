```blade
<!-- Premium Search Form -->

<div class="torrent-search card border-0 mb-4 mt-5">

    <div class="card-body">

        <form action="{{ route('torrents.adult') }}" method="GET">

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
                class="collapse mt-4 {{ request('categories') ? 'show' : '' }}"
                id="categoriesPanel"
            >

                <div class="card premium-category-panel p-3 p-md-4">

                    <div class="row g-4">


                        <!-- ADULT -->

                        <div class="col-md-12">

                            <h6 class="section-title text-primary">

                                <i class="bi bi-fire me-2"></i>

                                Adult

                            </h6>


                            @foreach ($categories->whereIn('id', [
                                27,
                                34,
                                60
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

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   FILEIPLAY — ADULT TORRENT SEARCH
   Dark premium search/filter UI
   ========================================================= */


/* =========================================================
   SEARCH CARD
   ========================================================= */

.torrent-search {

    position: relative;

    z-index: 1000;

    background: linear-gradient(
        145deg,
        #1b1b1b8b,
        #242424
    );

    border-radius: 16px;

    box-shadow:
        0 10px 30px rgba(0,0,0,.35);
}


.torrent-search .card-body {

    position: relative;

    z-index: 1001;
}


/* =========================================================
   SHARED SEARCH + FILTER BAR
   ========================================================= */

.premium-search-combined {

    display: flex;

    align-items: center;

    position: relative;

    z-index: 10;

    background: linear-gradient(
        145deg,
        #111,
        #1c1c1c
    );

    border: 1px solid rgba(255,255,255,.12);

    border-radius: 14px;

    padding: 6px 12px;

    transition: .25s ease;
}


.premium-search-combined:focus-within {

    border-color: #7c8cff;

    box-shadow:
        0 0 0 3px rgba(124,140,255,.2);
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

    color: #888;

    margin-right: 8px;
}


.premium-input-combined {

    width: 100%;

    background: transparent !important;

    border: none !important;

    box-shadow: none !important;

    color: #fff !important;
}


.premium-input-combined::placeholder {

    color: rgba(255,255,255,.40);
}


.premium-input-combined:focus {

    outline: none;

    box-shadow: none !important;
}


/* =========================================================
   DIVIDER
   ========================================================= */

.search-divider {

    width: 1px;

    height: 26px;

    background: rgba(255,255,255,.12);

    margin: 0 12px;

    flex: 0 0 1px;
}


/* =========================================================
   CATEGORY / SEARCH BUTTON
   ========================================================= */

.categories-trigger {

    background: transparent;

    border: none;

    color: #ccc;

    display: flex;

    align-items: center;

    font-weight: 600;

    cursor: pointer;

    transition: .2s ease;

    white-space: nowrap;

}


.categories-trigger:hover {

    color: #7c8cff;
}


/* =========================================================
   ARROW ANIMATION
   ========================================================= */

.toggle-arrow {

    transition:
        transform .25s ease;
}


.categories-trigger:not(.collapsed) .toggle-arrow {

    transform: rotate(180deg);
}


/* =========================================================
   INLINE DROPDOWN
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


/* =========================================================
   DROPDOWN TRIGGER
   ========================================================= */

.inline-dropdown-trigger {

    background: transparent;

    border: none;

    color: #ccc;

    display: flex;

    align-items: center;

    font-weight: 500;

    cursor: pointer;

    padding: 6px 6px;

    white-space: nowrap;

    transition:
        color .2s ease;
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

   The JavaScript moves this menu directly under <body>
   while open. This prevents the torrent list or another
   stacking context from covering the clickable options.
   ========================================================= */

.inline-dropdown-menu {

    position: fixed;

    min-width: 220px;

    max-width: 320px;

    max-height: 300px;

    padding: 6px;

    background: #1c1c1c;

    border-radius: 12px;

    border: 1px solid rgba(255,255,255,.10);

    box-shadow:
        0 20px 45px rgba(0,0,0,.70),
        0 0 0 1px rgba(124,140,255,.08);

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

    padding: 10px 14px;

    cursor: pointer;

    color: #ccc;

    border-radius: 8px;

    font-size: 14px;

    line-height: 1.35;

    user-select: none;

    transition:
        background .2s ease,
        color .2s ease;
}


.inline-option:hover {

    background: rgba(124,140,255,.15);

    color: #7c8cff;
}


.inline-option:active {

    background: rgba(124,140,255,.25);

    color: #fff;
}


/* =========================================================
   CATEGORY PANEL
   ========================================================= */

.premium-category-panel {

    background:
        linear-gradient(
            145deg,
            #151515,
            #1e1e1e
        );

    border-radius: 16px;

    border: 1px solid rgba(255,255,255,.08);

    box-shadow:
        0 20px 40px rgba(0,0,0,.55);
}


/* =========================================================
   CATEGORY TITLE
   ========================================================= */

.section-title {

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 12px;
}


.section-title.text-primary {

    color: #7c8cff !important;
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

    background-color: #7c8cff;

    border-color: #7c8cff;
}


.premium-check .form-check-input:focus {

    border-color: #7c8cff;

    box-shadow:
        0 0 0 .2rem rgba(124,140,255,.15);
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

    color: #7c8cff;
}


/* =========================================================
   CATEGORY BADGE PULSE
   ========================================================= */

@keyframes badgePulse {

    0% {

        transform: scale(1);

    }

    30% {

        transform: scale(1.15);

    }

    60% {

        transform: scale(.95);

    }

    100% {

        transform: scale(1);

    }

}


#categoriesLabel.pulse {

    animation:
        badgePulse .35s
        cubic-bezier(.4,0,.2,1);
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .torrent-search {

        border-radius: 12px;

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


    .search-divider {

        margin: 0 7px;

    }


    .categories-trigger,
    .inline-dropdown-trigger {

        font-size: 13px;

    }


    .inline-dropdown-menu {

        max-width:
            calc(100vw - 20px);

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
       FILEIPLAY — INLINE DROPDOWN SYSTEM
       ========================================================= */

    const dropdowns = document.querySelectorAll(
        '.premium-inline-dropdown'
    );


    dropdowns.forEach(drop => {

        const trigger =
            drop.querySelector(
                '.inline-dropdown-trigger'
            );


        const menu =
            drop.querySelector(
                '.inline-dropdown-menu'
            );


        const hiddenInput =
            drop.querySelector(
                'input[type="hidden"]'
            );


        const label =
            trigger?.querySelector('span');


        if (
            !trigger ||
            !menu ||
            !hiddenInput ||
            !label
        ) {

            return;

        }


        /*
         * Remember original location.
         */

        const originalParent =
            menu.parentNode;


        const originalNextSibling =
            menu.nextSibling;


        menu._originalParent =
            originalParent;


        menu._originalNextSibling =
            originalNextSibling;


        /* =====================================================
           CLOSE DROPDOWN
           ===================================================== */

        function closeDropdown() {

            drop.classList.remove(
                'active'
            );


            menu.classList.remove(
                'show-menu'
            );


            /*
             * Move menu back to its original
             * position in the DOM.
             */

            if (
                menu.parentNode ===
                document.body
            ) {

                if (
                    originalNextSibling &&
                    originalNextSibling.parentNode ===
                    originalParent
                ) {

                    originalParent.insertBefore(
                        menu,
                        originalNextSibling
                    );

                } else {

                    originalParent.appendChild(
                        menu
                    );

                }

            }


            menu.style.left = '';

            menu.style.top = '';

            menu.style.width = '';

        }


        /* =====================================================
           CLOSE ALL OTHER DROPDOWNS
           ===================================================== */

        function closeAllDropdowns(
            except = null
        ) {

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


                other.classList.remove(
                    'active'
                );


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
                        nextSibling.parentNode ===
                        parent
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
             * Dropdown width.
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
             * Open upward when there
             * isn't enough space below.
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
             * Close any other open dropdown.
             */

            closeAllDropdowns(drop);


            drop.classList.add(
                'active'
            );


            /*
             * CRITICAL FIX:
             *
             * Move the dropdown directly into BODY.
             *
             * This removes it from the search card's
             * stacking context and prevents torrent
             * content from blocking mouse clicks.
             */

            document.body.appendChild(
                menu
            );


            menu.classList.add(
                'show-menu'
            );


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
                     * Update visible text.
                     */

                    label.textContent =
                        this.textContent.trim();


                    /*
                     * Close dropdown.
                     */

                    closeDropdown();

                }
            );

        });


        /* =====================================================
           WINDOW RESIZE
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
           WINDOW SCROLL
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
       CLICK OUTSIDE
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
                            nextSibling.parentNode ===
                            parent
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


        /*
         * Initial pulse when categories
         * are already selected.
         */

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


            if (
                count !==
                previousCount
            ) {

                categoriesLabel.classList.remove(
                    'pulse'
                );


                void categoriesLabel.offsetWidth;


                categoriesLabel.classList.add(
                    'pulse'
                );

            }


            previousCount =
                count;

        }


        /*
         * Initial state.
         */

        updateCategoryCount();


        /*
         * Watch checkbox changes.
         */

        categoryCheckboxes.forEach(
            cb => {

                cb.addEventListener(
                    'change',
                    updateCategoryCount
                );

            }
        );

    }

});

</script>
```
