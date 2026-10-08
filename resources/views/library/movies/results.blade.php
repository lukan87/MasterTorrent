    {{-- ============================================================
         HEADER + SEARCH
         ============================================================ --}}
    <div class="lib-header mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h1 class="lib-page-title mb-1">
                    <i class="bi bi-film me-2"></i>
                    Movies
                </h1>

                <p class="lib-page-sub mb-0">
                    All titles in your library
                    &middot;
                    {{ $movies->total() }} titles
                </p>

            </div>

        </div>


        @include('library.partials.search', ['libraryType' => 'movies'])

        {{-- Search results --}}
        @if(!empty($query))

            <p class="lib-search-result-text mt-3 mb-0">

                Results for
                <strong>{{ $query }}</strong>

                <span class="text-muted">
                    &middot;
                    {{ $movies->total() }} found
                </span>

            </p>

        @endif

    </div>


    {{-- ============================================================
         MOVIE GRID
         ============================================================ --}}
    <div class="row g-3">

        @forelse($movies as $i => $movie)

            <div
                class="col-6 col-sm-4 col-md-3 col-lg-2 lib-card-col"
                style="--i:{{ $i }}"
            >

                <a
                    href="{{ route('library.movies.show', [
                        'tmdbid' => $movie->tmdbid,
                        'slug' => $movie->slug
                    ]) }}"
                    class="text-decoration-none d-block"
                >

                    <div class="lib-card">

                        {{-- Poster --}}
                        <div class="lib-card-poster">

                            <img
                                src="{{ $movie->poster
                                    ? 'https://image.tmdb.org/t/p/w500' . $movie->poster
                                    : asset('images/no-poster.png') }}"
                                alt="{{ $movie->title }}"
                                loading="lazy"
                            >


                            {{-- Rating --}}
                            @if($movie->rating)

                                <span class="lib-badge lib-badge-rating">

                                    <i class="bi bi-star-fill"></i>

                                    {{ number_format($movie->rating, 1) }}

                                </span>

                            @endif


                            {{-- Year --}}
                            @if($movie->year)

                                <span class="lib-badge lib-badge-year">
                                    {{ $movie->year }}
                                </span>

                            @endif


                            {{-- Seeders --}}
                            @if(!empty($movie->max_seeders))

                                <span class="lib-badge lib-badge-seeders">

                                    <i class="bi bi-arrow-up-circle-fill"></i>

                                    {{ $movie->max_seeders }}

                                </span>

                            @endif


                            {{-- Hover overlay --}}
                            <div class="lib-card-overlay">

                                <div class="lib-card-overlay-inner">

                                    <i class="bi bi-play-circle-fill lib-play-icon"></i>

                                    <span class="lib-overlay-label">
                                        View Details
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- Card information --}}
                        <div class="lib-card-meta">

                            <h5 class="lib-card-title">
                                {{ Str::limit($movie->title, 35) }}
                            </h5>

                            <div class="lib-card-info">

                                @if($movie->year)

                                    <span>
                                        {{ $movie->year }}
                                    </span>

                                @endif

                                @if($movie->max_seeders)

                                    <span>

                                        <i class="bi bi-arrow-up-circle-fill"></i>

                                        {{ $movie->max_seeders }}
                                        seeders

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        @empty

            <div class="col-12 text-center py-5">

                <div class="lib-empty">

                    <i class="bi bi-film"></i>

                    <h4>
                        No movies found
                    </h4>

                    <p>
                        Try searching for a different title,
                        or check back later.
                    </p>

                </div>

            </div>

        @endforelse

    </div>


    {{-- ============================================================
         PAGINATION
         ============================================================ --}}
    @if($movies->hasPages())

        <div class="d-flex justify-content-center mt-4">

            {{ $movies->links('pagination::bootstrap-5') }}

        </div>

    @endif

