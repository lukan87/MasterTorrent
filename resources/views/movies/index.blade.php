@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 movie-page">

    {{-- HERO --}}
    @if(isset($featured) && $featured)
        <div class="movies-hero mb-4" style="background-image:url('{{ $featured->backdrop_url }}')">
            <div class="hero-overlay"></div>
            <div class="hero-content position-relative z-2">
                <span class="hero-badge">FEATURED MOVIE</span>
                <h1 class="hero-title mt-3">{{ $featured->name }}</h1>
                <p class="hero-subtitle">
                    <i class="bi bi-star-fill text-warning"></i> {{ number_format($featured->vote_average ?? 0, 1) }}
                    @if($featured->genres)
                        &nbsp;&bull;&nbsp; {{ collect($featured->genres)->take(3)->implode(' / ') }}
                    @endif
                    @if($featured->year)
                        &nbsp;&bull;&nbsp; {{ $featured->year }}
                    @endif
                </p>
                <a href="{{ route('movies.show', ['id'=>$featured->id,'slug'=>$featured->slug]) }}" class="btn btn-hero">
                    <i class="bi bi-play-circle me-1"></i> View Details
                </a>
            </div>
        </div>
    @endif

    <div class="movies-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="movies-title mb-1">
                    <i class="bi bi-film me-2"></i>Movies
                </h1>
                <p class="movies-subtitle mb-0">Browse and discover your movie collection</p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form action="{{ route('movies.index') }}" method="GET" class="sort-form">
                    <div class="sort-wrapper">
                        <i class="bi bi-sort-down"></i>
                        <select name="sort" id="movieSort" class="sort-select">
                            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Newest</option>
                            <option value="rating" @selected(($sort ?? '') === 'rating')>Top Rated</option>
                            <option value="views"  @selected(($sort ?? '') === 'views')>Most Viewed</option>
                        </select>
                    </div>
                </form>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <a href="{{ route('movies.create') }}" class="btn btn-add-movie">
                        <i class="bi bi-plus-lg me-1"></i> Add Movie
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="search-card mb-4">
        <form action="{{ route('movies.search-movie') }}" method="POST">
            @csrf
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="name" id="name" class="movie-search-input" required placeholder="Search for a movie...">
                <button type="submit" class="search-btn">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-center mb-4">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>

    <div class="row g-3">
        @forelse ($movies as $movie)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="movie-card">
                    <a href="{{ route('movies.show', ['id' => $movie->id, 'slug' => $movie->slug]) }}" class="movie-poster-link">
                        <img src="{{ $movie->poster_path ? 'https://www.themoviedb.org/t/p/w600_and_h900_bestv2'.$movie->poster_path : '/images/noposter.jpg' }}"
                             loading="lazy"
                             class="movie-poster"
                             alt="{{ $movie->name }}">

                        @if($movie->vote_average)
                            <span class="movie-badge rating-badge">
                                <i class="bi bi-star-fill"></i> {{ number_format($movie->vote_average, 1) }}
                            </span>
                        @endif

                        @if($movie->year)
                            <span class="movie-badge year-badge">{{ $movie->year }}</span>
                        @endif

                        <div class="movie-overlay">
                            <div class="movie-overlay-content">
                                <i class="bi bi-play-circle-fill"></i>
                                <span>View Movie</span>
                            </div>
                        </div>
                    </a>

                    <div class="movie-info">
                        <h5 class="movie-title">{{ $movie->name }}</h5>
                        @if($movie->genres)
                            <div class="movie-genres">
                                @foreach(array_slice($movie->genres,0,3) as $genre)
                                    <span>{{ $genre }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="movie-views">
                            <i class="bi bi-eye"></i> {{ number_format($movie->views ?? 0) }} views
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-movies">
                    <i class="bi bi-film"></i>
                    <h4>No movies found</h4>
                    <p>Try searching for another title.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>
</div>

@if(session('status'))
<script>
swal({
    title: "Success!",
    text: "{{ session('status') }}",
    icon: "success",
    button: "OK",
});
</script>
@endif

@if(session('error'))
<script>
swal({
    title: "Error!",
    text: "{{ session('error') }}",
    icon: "error",
    button: "OK",
});
</script>
@endif

<script>
    document.getElementById('movieSort').addEventListener('change', function () {
        this.closest('form').submit();
    });
</script>

<style>
.movie-page{max-width:1600px}
.movies-header{padding:.35rem .15rem}
.movies-title{color:#f1f5f9;font-size:14px;font-weight:700;line-height:1.3}
.movies-title i{color:var(--ui-accent,#22d3c5)}
.movies-subtitle{color:#94a3b8;font-size:13px;line-height:1.4}

.btn-add-movie,.search-btn{
    display:inline-flex;align-items:center;justify-content:center;gap:.25rem;
    color:#061311;background:var(--ui-accent,#22d3c5);
    border:1px solid var(--ui-accent,#22d3c5);border-radius:.55rem;
    font-size:13px;font-weight:600;text-decoration:none;transition:all .18s ease
}
.btn-add-movie{min-height:38px;padding:.45rem .8rem}
.btn-add-movie:hover,.search-btn:hover{color:#061311;filter:brightness(1.06);transform:translateY(-1px)}

.search-card,.empty-movies{
    position:relative;overflow:hidden;
    background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));
    border:1px solid var(--ui-border,rgba(148,163,184,.16));
    border-radius:.85rem;box-shadow:0 10px 28px rgba(0,0,0,.22)
}
.search-card{padding:.8rem}
.search-card:before,.empty-movies:before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:3px;
    background:var(--ui-accent,#22d3c5)
}
.search-wrapper{
    display:flex;align-items:center;gap:.6rem;min-height:42px;padding:0 .7rem;
    background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.17);
    border-radius:.6rem;transition:border-color .18s ease,box-shadow .18s ease
}
.search-wrapper:focus-within{border-color:var(--ui-accent,#22d3c5);box-shadow:0 0 0 2px rgba(34,211,197,.08)}
.search-icon{color:var(--ui-accent,#22d3c5);font-size:14px}
.movie-search-input{flex:1;min-width:0;height:40px;padding:0;color:#e2e8f0;background:transparent;border:0;outline:0;font-size:14px}
.movie-search-input::placeholder{color:#64748b}
.search-btn{flex:0 0 auto;min-height:34px;padding:.4rem .75rem}

.movie-card{position:relative;height:100%;transition:transform .2s ease}
.movie-card:hover{transform:translateY(-3px)}
.movie-poster-link{
    position:relative;display:block;overflow:hidden;background:#0f172a;
    border:1px solid rgba(148,163,184,.13);border-radius:.7rem;
    box-shadow:0 10px 24px rgba(0,0,0,.28)
}
.movie-poster{display:block;width:100%;aspect-ratio:2/3;object-fit:cover;border-radius:.65rem;transition:transform .25s ease,filter .25s ease}
.movie-card:hover .movie-poster{transform:scale(1.035);filter:brightness(.82)}
.movie-overlay{
    position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(to top,rgba(5,12,22,.82),rgba(5,12,22,.12));
    opacity:0;transition:opacity .2s ease
}
.movie-card:hover .movie-overlay{opacity:1}
.movie-overlay-content{
    display:flex;flex-direction:column;align-items:center;gap:.35rem;
    color:#f8fafc;font-size:13px;font-weight:600;transform:translateY(5px);transition:transform .2s ease
}
.movie-card:hover .movie-overlay-content{transform:translateY(0)}
.movie-overlay-content i{color:var(--ui-accent,#22d3c5);font-size:28px}
.movie-info{padding:.45rem .15rem 0}
.movie-title{
    display:-webkit-box;overflow:hidden;margin:0;color:#e2e8f0;font-size:13px;font-weight:600;
    line-height:1.35;text-overflow:ellipsis;-webkit-line-clamp:2;-webkit-box-orient:vertical
}
.movie-card:hover .movie-title{color:var(--ui-accent,#22d3c5)}

.empty-movies{padding:3rem 1.5rem;text-align:center;color:#94a3b8}
.empty-movies i{display:block;margin-bottom:.65rem;color:var(--ui-accent,#22d3c5);font-size:38px}
.empty-movies h4{margin:0 0 .25rem;color:#f1f5f9;font-size:14px;font-weight:700}
.empty-movies p{margin:0;color:#64748b;font-size:13px}

.movie-page .pagination{margin-bottom:0}
.movie-page .pagination .page-link{
    min-width:34px;margin:0 2px;padding:.4rem .6rem;color:#cbd5e1;
    background:rgba(22,32,51,.82);border:1px solid var(--ui-border,rgba(148,163,184,.16));
    border-radius:.5rem;font-size:13px;text-align:center;transition:all .18s ease
}
.movie-page .pagination .page-link:hover{color:var(--ui-accent,#22d3c5);background:rgba(34,211,197,.07);border-color:rgba(34,211,197,.3)}
.movie-page .pagination .page-item.active .page-link{color:#061311;background:var(--ui-accent,#22d3c5);border-color:var(--ui-accent,#22d3c5)}
.movie-page .pagination .page-item.disabled .page-link{color:#475569;background:rgba(15,23,42,.55);border-color:rgba(148,163,184,.1)}

@media(max-width:768px){
    .movie-page{padding-left:.5rem!important;padding-right:.5rem!important}
    .search-wrapper{gap:.45rem;padding:0 .55rem}
    .search-btn{padding:.4rem .6rem}
}
@media(max-width:420px){
    .movies-header .d-flex{align-items:flex-start!important}
    .btn-add-movie{width:100%}
    .search-btn{font-size:12px}
}

/* HERO */
.movies-hero{
    position:relative;min-height:360px;margin-top:.35rem;overflow:hidden;display:flex;align-items:flex-end;
    background-size:cover;background-position:center top;background-color:#0f172a;
    border:1px solid rgba(148,163,184,.16);border-radius:.9rem;box-shadow:0 16px 38px rgba(0,0,0,.32)
}
.movies-hero::after{content:"";position:absolute;inset:0;border-left:3px solid var(--ui-accent,#22d3c5);pointer-events:none}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(5,12,22,.97) 0%,rgba(5,12,22,.86) 40%,rgba(5,12,22,.45) 72%,rgba(5,12,22,.2) 100%)}
.hero-content{padding:1.6rem;max-width:720px}
.hero-badge{display:inline-block;padding:.28rem .7rem;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:2px;color:#061311;background:var(--ui-accent,#22d3c5)}
.hero-title{color:#f1f5f9;font-size:2rem;font-weight:800;line-height:1.15;margin-bottom:.35rem}
.hero-subtitle{color:#cbd5e1;font-size:14px;margin-bottom:1rem}
.btn-hero{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:.6rem;font-weight:700;color:#061311;background:var(--ui-accent,#22d3c5);border:0}
.btn-hero:hover{filter:brightness(1.08);color:#061311}

/* SORT */
.sort-form{margin:0}
.sort-wrapper{display:flex;align-items:center;gap:.5rem;height:42px;padding:0 .6rem;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.17);border-radius:.6rem;color:var(--ui-accent,#22d3c5)}
.sort-select{background:transparent;border:0;outline:0;color:#e2e8f0;font-size:13px;cursor:pointer}
.sort-select option{background:#0f172a;color:#e2e8f0}

/* CARD BADGES */
.movie-badge{position:absolute;z-index:3;padding:.22rem .5rem;border-radius:.4rem;font-size:11px;font-weight:800;line-height:1;box-shadow:0 4px 12px rgba(0,0,0,.35)}
.rating-badge{top:8px;left:8px;color:#061311;background:var(--ui-accent,#22d3c5)}
.rating-badge i{color:#061311}
.year-badge{top:8px;right:8px;color:#f8fafc;background:rgba(5,12,22,.72);backdrop-filter:blur(4px)}
.movie-genres{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.25rem}
.movie-genres span{padding:.15rem .45rem;font-size:10px;font-weight:600;color:#cbd5e1;background:rgba(34,211,197,.08);border:1px solid rgba(34,211,197,.18);border-radius:999px}
.movie-views{margin-top:.3rem;color:#64748b;font-size:11px}
.movie-views i{color:var(--ui-accent,#22d3c5)}
</style>

@endsection
