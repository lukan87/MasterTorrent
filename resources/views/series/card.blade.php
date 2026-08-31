<div class="col-6 col-md-4 col-lg-2">
    <div class="card bg-dark border-0 series-card position-relative">

        <img loading="lazy"
             src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}"
             class="card-img-top"
             alt="{{ $serie->name }}">

        {{-- STATUS --}}
        <div class="status-badge 
            {{ $serie->status === 'Ended' ? 'bg-danger' : 'bg-success' }}">
            {{ $serie->status === 'Ended' ? 'Completed' : 'Ongoing' }}
        </div>

        {{-- RATING --}}
        <div class="rating-badge text-warning">
            ⭐ {{ number_format($serie->vote_average ?? 0, 1) }}
        </div>

        {{-- OVERLAY --}}
        <div class="overlay p-3 d-flex flex-column justify-content-end text-white">

            <h6 class="fw-bold text-truncate">{{ $serie->name }}</h6>

            {{-- GENRES --}}
            <div class="mb-2">
                @foreach($serie->genres ?? [] as $genre)
                    <span class="badge bg-secondary genre-chip">{{ $genre }}</span>
                @endforeach
            </div>

            <a href="{{ route('series.show', ['id'=>$serie->id,'slug'=>$serie->slug]) }}"
               class="btn btn-sm btn-primary">
                View
            </a>

            {{-- TRAILER --}}
            @if(!empty($serie->trailer_key))
                <iframe class="position-absolute top-0 start-0 w-100 h-100"
                        data-src="https://www.youtube.com/embed/{{ $serie->trailer_key }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $serie->trailer_key }}"
                        allow="autoplay"
                        frameborder="0">
                </iframe>
            @endif

        </div>

    </div>
</div>
