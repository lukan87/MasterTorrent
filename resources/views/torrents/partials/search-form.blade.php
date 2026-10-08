@php
    $visibleCategories = $categories->filter(fn ($category) => in_array((int) $category->id, \App\Models\Category::ADULT_IDS, true) === $adultSearch)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    $requestedCategories = request('categories', $adultSearch && request('category') ? [request('category')] : []);
    $selectedCategories = is_array($requestedCategories) ? $visibleCategories->whereIn('id', array_filter($requestedCategories, 'is_scalar'))->pluck('id')->all() : [];
    $groupOrder = $adultSearch ? ['Videos', 'Packs', 'Image sets'] : ['Movies', 'TV shows', 'Music', 'Games', 'Software', 'Other'];
    $categoryGroups = $visibleCategories->groupBy(fn ($category) => $category->browseGroup());
    $groupIcons = ['Movies' => 'bi-film', 'TV shows' => 'bi-tv', 'Music' => 'bi-music-note-beamed', 'Games' => 'bi-controller', 'Software' => 'bi-window', 'Other' => 'bi-grid', 'Videos' => 'bi-play-circle', 'Packs' => 'bi-collection-play', 'Image sets' => 'bi-images'];
    $statuses = ['all' => 'All statuses', 'active' => 'Active', 'dead' => 'Dead', 'free' => 'Freeleech', 'double' => 'Double upload', 'seedbox' => 'Seedbox'];
    $status = is_string(request('torrent_status')) && isset($statuses[request('torrent_status')]) ? request('torrent_status') : 'active';
    $keyword = is_string(request('keyword')) ? request('keyword') : '';
    $genreId = is_scalar(request('genre')) ? request('genre') : '';
    $filters = [];
    if ($keyword !== '') $filters['keyword'] = ['Keyword', $keyword];
    if ($genreId !== '') $filters['genre'] = ['Genre', $allGenres->firstWhere('id', $genreId)?->name ?? $genreId];
    if (request('torrent_status')) $filters['torrent_status'] = ['Status', $statuses[$status]];
    if (is_scalar(request('tmdbid')) && request('tmdbid')) $filters['tmdbid'] = ['TMDB', request('tmdbid')];
@endphp

@once
    <link rel="stylesheet" href="{{ asset('css/torrent-search.css') }}?v={{ filemtime(public_path('css/torrent-search.css')) }}">
    <script src="{{ asset('js/torrent-search.js') }}?v={{ filemtime(public_path('js/torrent-search.js')) }}" defer></script>
@endonce

<div class="torrent-search card mb-4 mt-5">
    <div class="card-body">
        <form action="{{ route($searchRoute) }}" method="GET" data-torrent-search role="search" aria-label="{{ $adultSearch ? 'Search adult torrents' : 'Search torrents' }}">
            @foreach(['sort', 'direction', 'tmdbid'] as $parameter)
                @if(request()->filled($parameter) && is_scalar(request($parameter)))
                    <input type="hidden" name="{{ $parameter }}" value="{{ request($parameter) }}">
                @endif
            @endforeach
            <div class="row g-3 align-items-end">
                <div class="col-xl-6 col-lg-5">
                    <label class="form-label" for="torrent-search-keyword">Search</label>
                    <div class="torrent-search-input">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" name="keyword" id="torrent-search-keyword" class="form-control" placeholder="Title, IMDb link, keyword…" value="{{ $keyword }}" data-search-keyword aria-describedby="torrent-search-help">
                        <button type="button" class="torrent-category-trigger {{ $selectedCategories ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#torrent-search-categories" aria-controls="torrent-search-categories" aria-expanded="{{ $selectedCategories ? 'true' : 'false' }}">
                            <i class="bi bi-collection" aria-hidden="true"></i>
                            <span data-category-count>Categories{{ $selectedCategories ? ' ('.count($selectedCategories).')' : '' }}</span>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-sm-6">
                    <label class="form-label" for="torrent-search-genre">Genre</label>
                    <select class="form-select" name="genre" id="torrent-search-genre">
                        <option value="">All genres</option>
                        @foreach($allGenres as $genre)
                            <option value="{{ $genre->id }}" @selected((string) $genreId === (string) $genre->id)>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2 col-sm-6">
                    <label class="form-label" for="torrent-search-status">Status</label>
                    <select class="form-select" name="torrent_status" id="torrent-search-status">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2">
                    <button type="submit" class="btn btn-primary torrent-search-submit w-100"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                </div>
            </div>
            <p id="torrent-search-help" class="torrent-search-help">Press <kbd>/</kbd> to focus search. Results update after you pause typing.</p>

            <div class="collapse {{ $selectedCategories ? 'show' : '' }}" id="torrent-search-categories">
                <div class="torrent-category-panel">
                    <div class="torrent-category-tools">
                        <div class="torrent-category-find">
                            <label class="visually-hidden" for="torrent-category-find">Find a category</label>
                            <input id="torrent-category-find" type="search" class="form-control" placeholder="Find a category…" data-category-find>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-category-clear>Clear categories</button>
                    </div>
                    <div class="torrent-category-grid">
                        @foreach($groupOrder as $groupName)
                            @if($categoryGroups->has($groupName))
                                <fieldset class="torrent-category-group" data-category-group>
                                    <legend class="visually-hidden">{{ $groupName }}</legend>
                                    <div class="torrent-category-heading">
                                        <div class="torrent-category-title">
                                            <i class="bi {{ $groupIcons[$groupName] }}" aria-hidden="true"></i>
                                            <strong>{{ $groupName }}</strong>
                                            <span>{{ $categoryGroups[$groupName]->count() }}</span>
                                        </div>
                                        <button type="button" class="torrent-category-select" data-category-select>Select group</button>
                                    </div>
                                    <div class="torrent-category-options">
                                        @foreach($categoryGroups[$groupName] as $category)
                                            <div class="torrent-category-check" data-category-option data-category-name="{{ $category->name }}">
                                                <input class="form-check-input" type="checkbox" name="categories[]" id="torrent-category-{{ $category->id }}" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories))>
                                                <label for="torrent-category-{{ $category->id }}">{{ $category->browseLabel() }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endif
                        @endforeach
                    </div>
                    <p class="torrent-search-help mb-0" data-category-empty hidden>No matching categories.</p>
                </div>
            </div>

            @if($filters || $selectedCategories)
                <div class="torrent-active-filters" aria-label="Active filters">
                    <span class="torrent-filter-label"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Active filters</span>
                    @foreach($filters as $key => [$label, $value])
                        <a class="torrent-filter-chip" href="{{ route($searchRoute, request()->except([$key, 'page'])) }}" aria-label="Remove {{ $label }} filter">{{ $label }}: {{ $value }} <i class="bi bi-x" aria-hidden="true"></i></a>
                    @endforeach
                    @if($selectedCategories)
                        <a class="torrent-filter-chip" href="{{ route($searchRoute, request()->except(['categories', 'category', 'page'])) }}" aria-label="Remove category filters">Categories: {{ count($selectedCategories) }} <i class="bi bi-x" aria-hidden="true"></i></a>
                    @endif
                    <a class="torrent-filter-chip torrent-filter-clear" href="{{ route($searchRoute) }}">Clear all</a>
                </div>
            @endif
        </form>
    </div>
</div>
