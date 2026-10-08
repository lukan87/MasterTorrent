<div class="lib-search mt-3">
    <form method="GET" action="{{ route('library.' . $libraryType . '.index') }}"
          class="lib-search-form lib-search-card" id="library-search" data-library-search>
        <div class="lib-search-field">
            <label class="lib-filter-label" for="library-query">Search {{ $libraryType }}</label>
            <div class="lib-search-query">
                <i class="bi bi-search lib-search-icon" aria-hidden="true"></i>
                <input type="text" id="library-query" name="q" maxlength="200"
                       value="{{ $query ?? '' }}" placeholder="Search your {{ $libraryType === 'movies' ? 'movie' : 'series' }} library..."
                       class="lib-search-input">
                @if(!empty($query))
                    <a href="{{ route('library.' . $libraryType . '.index') }}"
                       class="lib-search-clear" title="Clear search" aria-label="Clear search">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>

        @include('library.partials.browse-options')

        <button type="submit" class="lib-search-btn">
            <i class="bi bi-search me-1" aria-hidden="true"></i>
            Search
        </button>
    </form>
</div>
