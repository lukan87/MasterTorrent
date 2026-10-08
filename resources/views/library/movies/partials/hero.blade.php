<div class="movie-hero-card">

    <div class="row g-5 align-items-center">

        {{-- POSTER --}}
        <div class="col-12 col-lg-3">

            <div class="movie-poster-container">

                <img src="{{ !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$movie['poster_path'] : '/images/noposter.jpg' }}"
                     class="movie-main-poster"
                     alt="{{ $movie['title'] ?? 'Movie' }}">

                <div class="movie-poster-glow"></div>

                {{-- ACTION BUTTONS --}}
                <div class="movie-poster-actions">

                    @if(!empty($tmdbid))

                        <a href="https://www.themoviedb.org/movie/{{ $tmdbid }}"
                           target="_blank"
                           class="movie-circle-btn tmdb-btn">

                            <i class="bi bi-film"></i>

                        </a>

                    @endif

                    @if(!empty($movie['imdb_id']))

                        <a href="https://www.imdb.com/title/{{ $movie['imdb_id'] }}"
                           target="_blank"
                           class="movie-circle-btn imdb-btn">

                            <i class="bi bi-star-fill"></i>

                        </a>

                    @endif

                </div>

            </div>

        </div>

        {{-- INFO --}}
        <div class="col-12 col-lg-9">

            <div class="movie-hero-content">

                {{-- TITLE --}}
                <div class="movie-title-row">

                    <h1 class="movie-title">
                        {{ $movie['title'] ?? 'Movie' }}
                    </h1>

                    @if(!empty($movie['release_date']))

                        <span class="movie-year-pill">

                            {{ \Carbon\Carbon::parse($movie['release_date'])->format('Y') }}

                        </span>

                    @endif

                    @if(!empty($movie['vote_average']))

                        <span class="movie-rating-pill">
                            ⭐ {{ number_format($movie['vote_average'], 1) }}
                        </span>

                    @endif

                </div>

                {{-- GENRES --}}
                <div class="movie-genres-row">

                    @foreach(array_slice($movie['genres'] ?? [], 0, 8) as $genre)

                        <span class="movie-genre-pill">

                            {{ $genre['name'] }}

                        </span>

                    @endforeach

                </div>

                {{-- OVERVIEW --}}
                @if(!empty($movie['overview']))

                    <div class="movie-overview-box">

                        <p>
                            {{ $movie['overview'] }}
                        </p>

                    </div>

                @endif

                {{-- META --}}
                <div class="movie-meta-grid">

                    @if(!empty($movie['runtime']))

                        @php
                            $hours = intdiv($movie['runtime'], 60);
                            $minutes = $movie['runtime'] % 60;
                        @endphp

                        <div class="movie-meta-card">

                            <span class="movie-meta-label">
                                Runtime
                            </span>

                            <span class="movie-meta-value">
                                {{ $hours }}h {{ $minutes }}m
                            </span>

                        </div>

                    @endif

                    {{-- DIRECTOR --}}
                    @php
                        $director = collect($movie['credits']['crew'] ?? [])->firstWhere('job', 'Director');
                    @endphp
                    @if($director)
                        <div class="movie-meta-card">
                            <span class="movie-meta-label">Director</span>
                            <span class="movie-meta-value">{{ $director['name'] }}</span>
                        </div>
                    @endif

                    {{-- CAST --}}
                    @php
                        $cast = collect($movie['credits']['cast'] ?? [])->take(5)->pluck('name')->implode(', ');
                    @endphp
                    @if($cast)
                        <div class="movie-meta-card" style="min-width: 250px;">
                            <span class="movie-meta-label">Cast</span>
                            <span class="movie-meta-value">{{ $cast }}</span>
                        </div>
                    @endif

                    {{-- TRAILER --}}
                    @php
                        $trailer = collect($movie['videos']['results'] ?? [])->where('type', 'Trailer')->where('site', 'YouTube')->first();
                    @endphp
                    @if($trailer)
                        <div class="movie-meta-card">
                            <span class="movie-meta-label">Trailer</span>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#trailerModal" data-video-key="{{ $trailer['key'] }}" class="movie-meta-value text-decoration-none" style="color:var(--theme-teal-text, #2dd4bf);">
                                <i class="bi bi-play-circle"></i> Watch
                            </a>
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@if($trailer)
    @include('library.partials.trailer-modal')
@endif
