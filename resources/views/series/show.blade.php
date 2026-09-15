@extends('layouts.app')

@section('content')

<div class="series-page">

    {{-- BACKDROP --}}
    <div class="backdrop-wrapper">
        <div class="backdrop-overlay"></div>

        <img src="{{ $series->backdrop_path
            ? 'https://image.tmdb.org/t/p/original'.$series->backdrop_path
            : '/images/nobackdrop.jpg' }}"
             class="backdrop-image">
    </div>

    <div class="container-fluid px-lg-5 px-3 position-relative">

        {{-- HERO --}}
        <div class="hero-card">

            <div class="row g-5 align-items-center">

                {{-- POSTER --}}
                <div class="col-12 col-lg-3">

                    <div class="poster-container">

                        <img src="https://image.tmdb.org/t/p/w500{{ $series->poster_path }}"
                             class="main-poster"
                             alt="{{ $seriesDetails['name'] }}">

                        <div class="poster-glow"></div>

                        {{-- ACTIONS --}}
                        <div class="poster-buttons">

                            @if($series->tmdb_id)
                                <a href="https://www.themoviedb.org/tv/{{ $series->tmdb_id }}"
                                   target="_blank"
                                   class="circle-btn tmdb-btn">

                                    <i class="bi bi-film"></i>
                                </a>
                            @endif

                            @if($series->imdb_id)
                                <a href="https://www.imdb.com/title/{{ $series->imdb_id }}"
                                   target="_blank"
                                   class="circle-btn imdb-btn">

                                    <i class="bi bi-star-fill"></i>
                                </a>
                            @endif

                        </div>

                    </div>

                </div>

                {{-- INFO --}}
                <div class="col-12 col-lg-9">

                    <div class="hero-content">

                        {{-- TITLE --}}
                        <div class="title-row">

                            <h1 class="series-title">
                                {{ $seriesDetails['name'] }}
                            </h1>

                            @if(isset($seriesDetails['first_air_date']))
                                <span class="year-pill">
                                    {{ \Carbon\Carbon::parse($seriesDetails['first_air_date'])->format('Y') }}
                                </span>
                            @endif

                        </div>

                        {{-- GENRES --}}
                        <div class="genres-row">

                            @foreach(array_slice($seriesDetails['genres'],0,6) as $genre)

                                <span class="genre-pill">
                                    {{ $genre['name'] }}
                                </span>

                            @endforeach

                        </div>

                        {{-- OVERVIEW --}}
                        @if(isset($seriesDetails['overview']))
                            <div class="overview-box">
                                <p>
                                    {{ $seriesDetails['overview'] }}
                                </p>
                            </div>
                        @endif

                        {{-- META --}}
                        <div class="meta-grid">

                            @if(isset($seriesDetails['number_of_seasons']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Seasons
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesDetails['number_of_seasons'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesDetails['number_of_episodes']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Episodes
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesDetails['number_of_episodes'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesOm['imdbRating']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        IMDb Rating
                                    </span>

                                    <span class="meta-value">
                                        ⭐ {{ $seriesOm['imdbRating'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesOm['imdbVotes']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Votes
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesOm['imdbVotes'] }}
                                    </span>

                                </div>
                            @endif

                            @if(($series->views ?? 0) > 0)
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Views
                                    </span>

                                    <span class="meta-value">
                                        <i class="bi bi-eye"></i> {{ number_format($series->views) }}
                                    </span>

                                </div>
                            @endif

                        </div>

                        {{-- TRAILERS --}}
                        <div class="trailers-section">

                            <h5 class="section-title">
                                Trailers
                            </h5>

                            <div class="trailer-buttons">

                                @foreach(array_slice($seriesDetails['videos']['results'],0,3) as $video)

                                    <a href="https://www.youtube.com/watch?v={{ $video['key'] }}"
                                       data-lity
                                       class="trailer-btn">

                                        <i class="bi bi-play-circle-fill"></i>

                                        {{ $video['name'] }}

                                    </a>

                                @endforeach

                            </div>

                        </div>

                        {{-- ACTIONS (ADMIN DELETE) --}}
                        <div class="watch-actions">

                            @php
                                $userClass = optional(auth()->user())->user_class ?? 0;
                            @endphp

                            @if($userClass >= \App\Models\UserClass::ADMIN)
                                <form action="{{ route('series.delete', $series->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete &quot;{{ addslashes($series->name) }}&quot; permanently? This also removes its comments, torrents and torrent-library entry.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="watch-delete-btn">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- CAST --}}
        <div class="cast-section">

            <div class="section-header">
                <h2>Top Cast</h2>
            </div>

            <div class="cast-slider">

                @foreach(array_slice($seriesDetails['credits']['cast'], 0, 12) as $castMember)

                    <div class="cast-card">

                        <div class="cast-image-wrapper">

                            <img src="{{ $castMember['profile_path']
                                ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                                : '/images/not-found.jpg' }}"
                                 class="cast-image">

                        </div>

                        <div class="cast-info">

                            <h6>
                                {{ $castMember['name'] }}
                            </h6>

                            <p>
                                {{ $castMember['character'] }}
                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        {{-- SIMILAR SERIES --}}
        @if(isset($similar) && $similar->isNotEmpty())
            <div class="cast-section similar-section">

                <div class="section-header">
                    <h2>You May Also Like</h2>
                </div>

                <div class="cast-slider">
                    @foreach($similar as $sim)
                        @php
                            $simHref = $sim['in_library']
                                ? $sim['db_url']
                                : 'https://www.themoviedb.org/search/tv?query=' . urlencode($sim['name']) . ($sim['year'] ? '&first_air_date_year='.$sim['year'] : '');
                        @endphp
                        <a href="{{ $simHref }}"
                           {{ $sim['in_library'] ? '' : 'target="_blank"' }}
                           class="cast-card text-decoration-none">
                            <div class="cast-image-wrapper">
                                <img src="{{ $sim['poster'] }}"
                                     class="cast-image"
                                     loading="lazy"
                                     alt="{{ $sim['name'] }}">
                                @if($sim['in_library'])
                                    <span class="in-library-badge"><i class="bi bi-check2-circle"></i> Online</span>
                                @endif
                            </div>
                            <div class="cast-info">
                                <h6>{{ $sim['name'] }}</h6>
                                <p>
                                    <i class="bi bi-star-fill text-warning"></i> {{ $sim['rating'] }}
                                    @if($sim['year']) · {{ $sim['year'] }} @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

            </div>
        @endif

        {{-- SEASONS --}}
        @include('series.seasons')

        {{-- ONLINE --}}
        @include('series.online')


         {{-- COMMENTS --}}
        <div class="comments-modern-wrapper">

            <div class="comments-modern-card">

                <div class="comments-header">

                    <h3>
                        Comments
                    </h3>

                </div>

                {{-- FORM --}}
                <form action="{{ route('comments.store') }}"
                      method="POST"
                      class="comment-form-modern">

                    @csrf

                    <input type="hidden"
                           name="commentable_id"
                           value="{{ $series->id }}">

                    <input type="hidden"
                           name="commentable_type"
                           value="App\Models\Series">

                    <textarea name="comment"
          rows="4"
          required
          placeholder="Write your thoughts about this movie..."></textarea>

                    <button type="submit"
                            class="submit-comment-btn">

                        Post Comment

                    </button>

                </form>

                {{-- COMMENTS LIST --}}
                @if($series->comments->isEmpty())

                    <div class="no-comments-box">

                        No comments yet

                    </div>

                @else

                    @foreach($series->comments()->paginate(5) as $comment)

                        <div class="single-comment-card">

                            <div class="comment-user-row">

                                <div class="comment-avatar">

                                    {{ strtoupper(substr($comment->user->name,0,1)) }}

                                </div>

                                <div>

                                    <h6>
                                        {{ $comment->user->name }}
                                    </h6>

                                    <small>
                                        {{ $comment->created_at }}
                                    </small>

                                </div>

                            </div>

                            <p class="comment-text">

                                {{ $comment->comment }}

                            </p>

                        </div>

                    @endforeach

                    <div class="mt-4">

                        {{ $series->comments()->paginate(5)->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection

<style>
.series-page{position:relative;min-height:100vh;background:#070b14;color:#e2e8f0;overflow-x:hidden}.backdrop-wrapper{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}.backdrop-image{width:100%;height:100%;object-fit:cover;filter:blur(5px) brightness(.28) saturate(.7);transform:scale(1.04)}.backdrop-overlay{position:absolute;inset:0;z-index:2;background:linear-gradient(to bottom,rgba(7,11,20,.38),rgba(7,11,20,.8) 48%,#070b14 88%,#070b14)}.series-page>.container-fluid{position:relative;z-index:3}.hero-card{position:relative;margin-top:100px;margin-bottom:42px;padding:30px;background:linear-gradient(135deg,rgba(22,32,51,.94),rgba(15,23,42,.88));border:1px solid rgba(255,255,255,.08);border-radius:.8rem;box-shadow:0 18px 50px rgba(0,0,0,.42);backdrop-filter:blur(16px)}.poster-container{position:relative;max-width:280px;margin:auto}.main-poster{position:relative;z-index:2;display:block;width:100%;border-radius:.65rem;border:1px solid rgba(255,255,255,.09);box-shadow:0 18px 38px rgba(0,0,0,.45);transition:.25s}.poster-container:hover .main-poster{transform:translateY(-4px);box-shadow:0 22px 44px rgba(0,0,0,.52)}.poster-glow{position:absolute;inset:12% 8%;z-index:1;background:rgba(45,212,191,.12);filter:blur(35px);border-radius:50%}.poster-buttons{position:absolute;z-index:4;bottom:14px;left:50%;transform:translateX(-50%);display:flex;gap:8px}.circle-btn{width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:.55rem;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.14);box-shadow:0 8px 18px rgba(0,0,0,.3);backdrop-filter:blur(8px);transition:.2s}.tmdb-btn{background:rgba(1,180,228,.76)}.imdb-btn{background:rgba(245,197,24,.86);color:#111}.circle-btn:hover{color:#fff;transform:translateY(-3px);border-color:rgba(255,255,255,.28)}.imdb-btn:hover{color:#111}.series-title{margin:0;color:#f1f5f9;font-size:2.45rem;font-weight:700;line-height:1.15;letter-spacing:-.02em}.title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.year-pill{display:inline-flex;padding:.28rem .55rem;border-radius:.4rem;background:rgba(45,212,191,.08);border:1px solid rgba(45,212,191,.2);color:#8ff5e6;font-size:.84rem;font-weight:600}.genres-row{display:flex;flex-wrap:wrap;gap:6px;margin-top:15px}.genre-pill{padding:.28rem .55rem;border-radius:.4rem;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.08);color:rgba(226,232,240,.72);font-size:.82rem}.overview-box{max-width:900px;margin-top:20px;padding:.8rem .95rem;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.065);border-left:3px solid rgba(45,212,191,.42);border-radius:.5rem}.overview-box p{margin:0;color:rgba(226,232,240,.78);font-size:.96rem;line-height:1.65}.meta-grid{display:flex;flex-wrap:wrap;gap:8px;margin-top:20px}.meta-card{min-width:115px;padding:.65rem .75rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.065);border-radius:.5rem}.meta-label{display:block;margin-bottom:3px;color:rgba(203,213,225,.48);font-size:.66rem;font-weight:600;text-transform:uppercase}.meta-value{color:#e8f0f7;font-size:.82rem;font-weight:600}.trailers-section{margin-top:24px}.section-title{margin-bottom:10px;color:#e8f0f7;font-size:.92rem;font-weight:600}.trailer-buttons{display:flex;flex-wrap:wrap;gap:7px}.trailer-btn{display:inline-flex;align-items:center;gap:6px;max-width:100%;padding:.42rem .65rem;border-radius:.45rem;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.08);color:rgba(226,232,240,.78);font-size:.73rem;text-decoration:none;transition:.2s}.trailer-btn i{color:var(--ui-accent,#2dd4bf)}.trailer-btn:hover{color:#fff;background:rgba(45,212,191,.07);border-color:rgba(45,212,191,.25);transform:translateY(-1px)}.cast-section{position:relative;z-index:5;margin:0 auto 42px;width:100%;text-align:center}.section-header{display:flex;justify-content:center;align-items:center;width:100%}.section-header h2{margin:0 0 14px;color:#e8f0f7;font-size:1.15rem;font-weight:650;text-align:center}.cast-slider{display:flex;justify-content:center;align-items:flex-start;gap:12px;overflow-x:auto;padding:2px 2px 10px;width:100%;scrollbar-width:thin;scrollbar-color:rgba(45,212,191,.25) transparent}.cast-slider::-webkit-scrollbar{height:5px}.cast-slider::-webkit-scrollbar-thumb{background:rgba(45,212,191,.22);border-radius:10px}.cast-card{flex:0 0 145px;min-width:145px;max-width:145px;overflow:hidden;background:linear-gradient(135deg,rgba(22,32,51,.94),rgba(15,23,42,.9));border:1px solid rgba(255,255,255,.07);border-radius:.65rem;box-shadow:0 8px 20px rgba(0,0,0,.2);transition:.2s}.cast-card:hover{transform:translateY(-3px);border-color:rgba(45,212,191,.22);box-shadow:0 12px 26px rgba(0,0,0,.3)}.cast-image-wrapper{height:205px;overflow:hidden;background:rgba(7,14,27,.7);display:flex;align-items:center;justify-content:center}.cast-image{display:block;width:100%;height:100%;object-fit:cover;object-position:50% 50%;margin:0 auto;transition:.25s}.cast-card:hover .cast-image{transform:scale(1.035)}.cast-info{padding:.65rem .7rem}.cast-info h6{margin-bottom:.2rem;overflow:hidden;color:#e8f0f7;font-size:.78rem;font-weight:600;text-overflow:ellipsis;white-space:nowrap}.cast-info p{margin:0;overflow:hidden;color:rgba(203,213,225,.52);font-size:.68rem;line-height:1.35;text-overflow:ellipsis;white-space:nowrap}.similar-section .cast-card{flex:0 0 150px;min-width:150px;max-width:150px}.similar-section .cast-image-wrapper{height:215px;display:flex;align-items:center;justify-content:center}.series-page .text-warning{color:#e4b85d!important}@media(max-width:992px){.hero-card{margin-top:85px;padding:22px}.series-title{font-size:2rem}.poster-container{max-width:245px}.meta-card{min-width:105px}}@media(max-width:768px){.hero-card{margin-top:70px;margin-bottom:28px;padding:16px;border-radius:.65rem}.series-title{font-size:1.65rem}.poster-container{max-width:220px}.overview-box{margin-top:15px}.overview-box p{font-size:.82rem;line-height:1.55}.meta-grid{gap:6px;margin-top:15px}.meta-card{min-width:calc(50% - 3px);padding:.55rem .6rem}.meta-value{font-size:.77rem}.trailer-btn{width:100%}.cast-section{margin-bottom:30px}.section-header h2{font-size:1rem}.cast-card{flex:0 0 125px;min-width:125px;max-width:125px}.cast-image-wrapper{height:175px}.similar-section .cast-card{flex:0 0 130px;min-width:130px;max-width:130px}.similar-section .cast-image-wrapper{height:185px;display:flex;align-items:center;justify-content:center}}
/* COMMENTS */
.comments-modern-wrapper{
    position:relative;
    z-index:5;
    margin-bottom:34px;
}

.comments-modern-card{
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.94),
        rgba(15,23,42,.88)
    );
    border:1px solid rgba(255,255,255,.08);
    border-radius:.8rem;
    padding:18px;
    box-shadow:0 14px 36px rgba(0,0,0,.30);
    backdrop-filter:blur(14px);
}

.comments-header{
    border-bottom:1px solid rgba(255,255,255,.06);
    margin-bottom:14px;
    padding-bottom:10px;
}

.comments-header h3{
    margin:0;
    color:#e8f0f7;
    font-size:1rem;
    font-weight:650;
}

.comment-form-modern textarea{
    width:100%;
    min-height:105px;
    padding:.7rem .8rem;
    resize:vertical;
    outline:none;
    border:1px solid rgba(255,255,255,.08);
    border-radius:.5rem;
    background:rgba(255,255,255,.025);
    color:#e8f0f7;
    font-size:.9rem;
}

.comment-form-modern textarea:focus{
    border-color:rgba(45,212,191,.30);
    box-shadow:0 0 0 2px rgba(45,212,191,.06);
}

.comment-form-modern textarea::placeholder{
    color:rgba(203,213,225,.38);
}

.submit-comment-btn{
    margin-top:8px;
    padding:.45rem .75rem;
    border:1px solid rgba(45,212,191,.22);
    border-radius:.45rem;
    background:rgba(45,212,191,.09);
    color:#8ff5e6;
    font-size:.74rem;
    font-weight:600;
    transition:.2s ease;
}

.submit-comment-btn:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
}

.single-comment-card{
    margin-top:10px;
    padding:.75rem;
    border-radius:.55rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.06);
}

.comment-user-row{
    display:flex;
    align-items:center;
    gap:9px;
    margin-bottom:8px;
}

.comment-avatar{
    width:34px;
    height:34px;
    flex:0 0 34px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.20);
    color:#8ff5e6;
    font-size:.72rem;
    font-weight:700;
}

.comment-user-row h6{
    margin:0 0 2px;
    color:#e8f0f7;
    font-size:.76rem;
    font-weight:600;
}

.comment-user-row small{
    color:rgba(203,213,225,.45);
    font-size:.65rem;
}

.comment-text{
    margin:0;
    color:rgba(226,232,240,.76);
    font: size 0.8em;7rem;
    line-height:1.55;
}

.no-comments-box{
    padding:22px;
    border:1px dashed rgba(255,255,255,.08);
    border-radius:.55rem;
    background:rgba(255,255,255,.02);
    color:rgba(203,213,225,.48);
    font-size:.75rem;
    text-align:center;
}
.watch-actions{
        margin-top:18px;
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .watch-delete-btn{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:.55rem .85rem;
        border-radius:.5rem;
        background:rgba(239,68,68,.10);
        border:1px solid rgba(239,68,68,.30);
        color:#fca5a5;
        font-size:.78rem;
        font-weight:600;
        cursor:pointer;
        text-decoration:none;
        transition:.2s ease;
        font-family:inherit;
    }

    .watch-delete-btn:hover{
        color:#fff;
        background:#ef4444;
        border-color:#ef4444;
        transform:translateY(-1px);
    }

    .cast-image-wrapper{position:relative}

    .in-library-badge{
        position:absolute;
        top:8px;
        left:8px;
        z-index:3;
        display:inline-flex;
        align-items:center;
        gap:4px;
        padding:.22rem .5rem;
        border-radius:999px;
        background:rgba(45,212,191,.92);
        color:#06291f;
        font-size:.62rem;
        font-weight:700;
        line-height:1;
        box-shadow:0 3px 8px rgba(0,0,0,.35);
        pointer-events:none;
    }
</style>