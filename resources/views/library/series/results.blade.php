    {{-- ============================================================
         HEADER + SEARCH
         ============================================================ --}}
    <div class="lib-header mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h1 class="lib-page-title mb-1">

                    <i class="bi bi-tv me-2"></i>

                    Series

                </h1>


                <p class="lib-page-sub mb-0">

                    All titles in your library

                    &middot;

                    {{ $series->total() }} titles

                </p>

            </div>

        </div>


        @include('library.partials.search', ['libraryType' => 'series'])

        {{-- Search results --}}
        @if(!empty($query))

            <p class="lib-search-result-text mt-3 mb-0">

                Results for

                <strong>
                    {{ $query }}
                </strong>

                <span class="text-muted">

                    &middot;

                    {{ $series->total() }} found

                </span>

            </p>

        @endif

    </div>


    {{-- ============================================================
         SERIES GRID
         ============================================================ --}}
    <div class="row g-3">

        @forelse($series as $i => $item)

            <div
                class="col-6 col-sm-4 col-md-3 col-lg-2 lib-card-col"
                style="--i:{{ $i }}"
            >

                <a
                    href="{{ route('library.series.show', [
                        'tmdbid' => $item->tmdbid,
                        'slug' => $item->slug
                    ]) }}"
                    class="text-decoration-none d-block"
                >

                    <div class="lib-card">

                        {{-- Poster --}}
                        <div class="lib-card-poster">

                            <img
                                src="{{ $item->poster
                                    ? 'https://image.tmdb.org/t/p/w500' . $item->poster
                                    : asset('images/no-poster.png') }}"
                                alt="{{ $item->title }}"
                                loading="lazy"
                            >


                            {{-- Rating --}}
                            @if($item->rating)

                                <span class="lib-badge lib-badge-rating">

                                    <i class="bi bi-star-fill"></i>

                                    {{ number_format($item->rating, 1) }}

                                </span>

                            @endif


                            {{-- Year --}}
                            @if($item->year)

                                <span class="lib-badge lib-badge-year">

                                    {{ $item->year }}

                                </span>

                            @endif


                            {{-- Seeders --}}
                            @if(!empty($item->max_seeders))

                                <span class="lib-badge lib-badge-seeders">

                                    <i class="bi bi-arrow-up-circle-fill"></i>

                                    {{ $item->max_seeders }}

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

                                {{ Str::limit($item->title, 35) }}

                            </h5>


                            <div class="lib-card-info">

                                @if($item->year)

                                    <span>
                                        {{ $item->year }}
                                    </span>

                                @endif


                                @if($item->max_seeders)

                                    <span>

                                        <i class="bi bi-arrow-up-circle-fill"></i>

                                        {{ $item->max_seeders }}

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

                    <i class="bi bi-tv"></i>

                    <h4>
                        No series found
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
    @if($series->hasPages())

        <div class="d-flex justify-content-center mt-4">

            {{ $series->links('pagination::bootstrap-5') }}

        </div>

    @endif

