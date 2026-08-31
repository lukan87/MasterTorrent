@extends('layouts.app')

@section('content')

<div class="container-fluid py-4 movie-page">

    {{-- HEADER --}}
    <div class="movies-header mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h1 class="movies-title mb-1">
                    <i class="bi bi-film me-2"></i>Movies
                </h1>

                <p class="movies-subtitle mb-0">
                    Browse and discover your movie collection
                </p>
            </div>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                <a href="{{ route('movies.create') }}"
                   class="btn btn-add-movie">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Movie
                </a>
            @endif

        </div>

    </div>

    {{-- SEARCH --}}
    <div class="search-card mb-5">

        <form action="{{ route('movies.search-movie') }}" method="POST">
            @csrf

            <div class="search-wrapper">

                <i class="bi bi-search search-icon"></i>

                <input type="text"
                       name="name"
                       id="name"
                       class="movie-search-input"
                       required
                       placeholder="Search for a movie...">

                <button type="submit" class="search-btn">
                    Search
                </button>

            </div>

        </form>

    </div>

    {{-- TOP PAGINATION --}}
    <div class="d-flex justify-content-center mb-4">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>

    {{-- MOVIES GRID --}}
    <div class="row g-4">

        @forelse ($movies as $movie)

            <div class="col-6 col-sm-4 col-md-3 col-lg-2">

                <div class="movie-card">

                    <a href="{{ route('movies.show', ['id' => $movie->id, 'slug' => $movie->slug]) }}"
                       class="movie-poster-link">

                        <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}"
                             class="movie-poster"
                             alt="{{ $movie->name }}">

                        <div class="movie-overlay">

                            <div class="movie-overlay-content">

                                <i class="bi bi-play-circle-fill"></i>

                                <span>View Movie</span>

                            </div>

                        </div>

                    </a>

                    <div class="movie-info">

                        <h5 class="movie-title">
                            {{ $movie->name }}
                        </h5>

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

    {{-- BOTTOM PAGINATION --}}
    <div class="d-flex justify-content-center mt-5">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>

</div>

{{-- ALERTS --}}
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

<style>

/* =========================================
   PAGE
========================================= */

.movie-page{
    max-width:1600px;
}

/* =========================================
   HEADER
========================================= */

.movies-header{
    padding:12px 4px;
}

.movies-title{
    font-size:2.2rem;
    font-weight:800;
    letter-spacing:-1px;
    color:#fff;
}

.movies-title i{
    color:#ff4d6d;
}

.movies-subtitle{
    color:rgba(255,255,255,.55);
    font-size:.95rem;
}

/* =========================================
   ADD BUTTON
========================================= */

.btn-add-movie{

    background:
        linear-gradient(
            135deg,
            #ff4d6d,
            #ff758f
        );

    border:none;

    color:#fff;

    border-radius:14px;

    padding:.8rem 1.2rem;

    font-weight:700;

    transition:.18s ease;

    box-shadow:
        0 10px 30px rgba(255,77,109,.25);
}

.btn-add-movie:hover{

    transform:
        translateY(-2px);

    color:#fff;

    box-shadow:
        0 16px 40px rgba(255,77,109,.35);
}

/* =========================================
   SEARCH
========================================= */

.search-card{

    background:
        linear-gradient(
            180deg,
            rgba(20,20,22,.96),
            rgba(12,12,14,.98)
        );

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:
        24px;

    padding:
        1rem;

    box-shadow:
        0 20px 50px rgba(0,0,0,.35);
}

.search-wrapper{

    display:flex;
    align-items:center;

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:
        18px;

    padding:
        0 18px;

    min-height:
        62px;

    transition:
        .2s ease;
}

.search-wrapper:focus-within{

    border-color:#ff4d6d;

    box-shadow:
        0 0 0 4px rgba(255,77,109,.12);
}

.search-icon{
    color:#ff758f;
    font-size:1rem;
    margin-right:12px;
}

.movie-search-input{

    flex:1;

    background:transparent;

    border:none;

    color:#fff;

    font-size:1rem;
}

.movie-search-input:focus{
    outline:none;
}

.movie-search-input::placeholder{
    color:rgba(255,255,255,.35);
}

.search-btn{

    background:
        linear-gradient(
            135deg,
            #ff4d6d,
            #ff758f
        );

    border:none;

    color:#fff;

    border-radius:12px;

    padding:.75rem 1.2rem;

    font-weight:700;

    transition:.18s ease;
}

.search-btn:hover{

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 25px rgba(255,77,109,.25);
}

/* =========================================
   MOVIE CARD
========================================= */

.movie-card{
    position:relative;
    transition:.22s ease;
}

.movie-card:hover{
    transform:translateY(-6px) scale(1.02);
}

/* =========================================
   POSTER
========================================= */

.movie-poster-link{
    display:block;
    position:relative;
    overflow:hidden;
    border-radius:20px;
}

.movie-poster{

    width:100%;
    aspect-ratio:2/3;

    object-fit:cover;

    border-radius:20px;

    transition:
        transform .3s ease;

    box-shadow:
        0 18px 40px rgba(0,0,0,.35);
}

.movie-card:hover .movie-poster{
    transform:scale(1.05);
}

/* =========================================
   OVERLAY
========================================= */

.movie-overlay{

    position:absolute;
    inset:0;

    background:
        linear-gradient(
            to top,
            rgba(0,0,0,.85),
            rgba(0,0,0,.15)
        );

    display:flex;
    align-items:center;
    justify-content:center;

    opacity:0;

    transition:.25s ease;
}

.movie-card:hover .movie-overlay{
    opacity:1;
}

.movie-overlay-content{

    display:flex;
    flex-direction:column;
    align-items:center;
    gap:10px;

    color:#fff;

    transform:translateY(10px);

    transition:.25s ease;
}

.movie-card:hover .movie-overlay-content{
    transform:translateY(0);
}

.movie-overlay-content i{
    font-size:3rem;
}

.movie-overlay-content span{
    font-weight:700;
}

/* =========================================
   INFO
========================================= */

.movie-info{
    padding-top:14px;
}

.movie-title{

    font-size:.95rem;

    font-weight:700;

    line-height:1.35;

    color:#fff;

    margin:0;

    display:-webkit-box;

    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;

    overflow:hidden;
}

/* =========================================
   EMPTY
========================================= */

.empty-movies{

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:
        24px;

    padding:
        4rem 2rem;

    text-align:center;

    color:
        rgba(255,255,255,.6);
}

.empty-movies i{
    font-size:4rem;
    margin-bottom:1rem;
}

/* =========================================
   PAGINATION
========================================= */

.pagination .page-link{

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.06);

    color:#ddd;

    margin:0 4px;

    border-radius:12px;

    min-width:42px;

    text-align:center;
}

.pagination .page-link:hover{
    background:rgba(255,255,255,.08);
}

.pagination .page-item.active .page-link{

    background:#ff4d6d;
    border-color:#ff4d6d;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .movies-title{
        font-size:1.7rem;
    }

    .search-wrapper{
        padding:0 12px;
    }

    .search-btn{
        padding:.65rem 1rem;
    }

}

</style>

@endsection