@extends('layouts.app')

@section('content')

<div style='display:block;height:50px'></div>




<div class="row mt-5">
    <div class="col-md-12 col-lg-12">
        <!-- Removed card card-custom card-blur classes -->
        <div class="content-overlay mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-1 order-xxl-0 text-center">
                        <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $series->poster_path }}"
                             style="border-radius: 12px;
                                    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                                    width:100%;">

<div style="margin-top: 10px;">
    <!-- TMDB Link -->
    @if($series->tmdb_id)
        <a href="https://www.themoviedb.org/movie/{{ $series->tmdb_id }}" target="_blank" class="btn btn-dark btn-sm">View on TMDB</a>
    @endif

    <!-- IMDb Link -->
    @if($series->imdb_id)
        <a href="https://www.imdb.com/title/{{ $series->imdb_id }}" target="_blank" class="btn btn-dark btn-sm">View on IMDb</a>
    @endif
</div>
                    </div>

                    <div class="col-sm-9 col-xxl-10 order-1 order-sm-0 order-xxl-1">

                    <dd class="col-lg-12">

                    @if(isset($seriesOm['Rated']) && $seriesOm['Rated'] != 'N/A')
    @php
        $rating = $seriesOm['Rated'];
        $PG = '';

        switch ($rating) {
            case 'G':
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='General Audiences: G - All ages admitted. Nothing that would offend parents for viewing by children'><b>$rating</b></i>";
                break;
            case 'PG':
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='Parental Guidance Suggested: PG - Some material may not be suitable for children. Parents urged to give parental guidance. May contain some material parents might not like for their young children.'><b>$rating</b></i>";
                break;
            case 'PG-13':
                $PG = "<i class='bi bi-stars select' aria-hidden='true' data-bs-toggle='tooltip' title='Parental Strongly Cautioned: PG-13 - Some material may be inappropriate for children under 13. Parents are urged to be cautious. Some material may not be appropriate for pre-teenagers.'><b>$rating</b></i>";
                break;
            case 'R':
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='Restricted: R - Under 17 requires accompanying parent or adult guardian. Contains some adult material. Parents are urged to learn more about the film before taking their young children with them.'><b>$rating</b></i>";
                break;
            case 'TV-PG':
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='Parental Guidance Suggested: This program contains material that parents may find unsuitable for younger children. Many parents may want to watch it with their younger children.'><b>$rating</b></i>";
                break;
            case 'NC-17':
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='Adults Only: NC-17 - No one 17 and under admitted.'><b>$rating</b></i>";
                break;
            default:
                $PG = "<i class='bi bi-stars' aria-hidden='true' data-bs-toggle='tooltip' title='Rated: $rating'><b>$rating</b></i>";
                break;
        }
        @endphp
    @else
    @php
    $PG = "";
    @endphp
@endif


                    <p>
                        <h3>{{ $seriesDetails['name'] }}
                                 @if(isset($seriesDetails['first_air_date']))
                                      ({{ \Carbon\Carbon::parse($seriesDetails['first_air_date'])->format('F j, Y') }})
                                 @endif
                                 {!! $PG !!}
                        </h3>
                    </p>
                    </dd>

<dd class="col-lg-12">
@foreach (array_slice($seriesDetails['genres'], 0, 8) as $genre)
                                    <button class="btn btn-dark"><i>{{ $genre['name'] }}</i></button>
                                @endforeach
</dd>



@if(isset($seriesDetails['overview']))
    <dd class="col-sm-12">
        <b>
            <font size="3">
                <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="Overview"></i>&nbsp;&nbsp;{{ $seriesDetails['overview'] }}
            </font>
        </b>
    </dd>
@endif

@if(isset($seriesDetails['runtime']))
    @php
        $hours = intdiv($seriesDetails['runtime'], 60);  // Get the number of hours
        $minutes = $seriesDetails['runtime'] % 60;      // Get the remaining minutes
    @endphp
    <dd class="col-sm-12">
        <p><strong>Runtime:</strong> {{ $hours }} hour{{ $hours != 1 ? 's' : '' }}
        @if($minutes > 0)
            and {{ $minutes }} minute{{ $minutes != 1 ? 's' : '' }}
        @endif
        </p>
    </dd>
@endif

@if(isset($seriesDetails['number_of_seasons']))

    <dd class="col-sm-12">
        <p><strong>Seasons:</strong> {{ $seriesDetails['number_of_seasons'] }} /
           <strong>Episodes:</strong> {{ $seriesDetails['number_of_episodes'] }}

        </p>
    </dd>
@endif



<!-- OMDB Data Section -->

@if(isset($seriesOm))
    <!-- Rated (OMDB) -->

    @if(isset($seriesOm['Rated']))
        <dd class="col-sm-12">
            <p><strong>IMDB Votes:</strong> {{ $seriesOm['imdbVotes'] }}</p>
        </dd>
    @endif

    <!-- OMDB Ratings (Multiple Sources) -->
    @if(isset($seriesOm['Ratings']) && count($seriesOm['Ratings']) > 0)
            <dd class="col-sm-12">
                <p><strong>Ratings:</strong></p>
                <ul>
                    @foreach($seriesOm['Ratings'] as $rating)
                        <li><strong>{{ $rating['Source'] }}:</strong> {{ $rating['Value'] }}</li>
                    @endforeach
                </ul>
            </dd>
        @endif

@endif

<dd>
                    <ul>
                        <h3>Trailers</h3>
                        @foreach (array_slice($seriesDetails['videos']['results'], 0, 3) as $video)

                                <a
                                    href="#"
                                    onmouseover="this.href='https://www.youtube.com/watch?v={{ $video['key'] }}'"
                                    onmouseout="this.href='#'"
                                    data-lity
                                >
                                    <h6>{{ $video['name'] }}</h6>
                                </a>

                        @endforeach
                    </ul>
                </dd>

                @if(isset($seriesDetails['created_by']))
       <dd class="col-lg-12">
        <strong>Created by:</strong>
    @foreach (array_slice($seriesDetails['created_by'], 0, 8) as $index => $creator)
    <strong><i>{{ $creator['name'] }}</i></strong>
        @if ($index < count($seriesDetails['created_by']) - 1 && $index < 7)
            <span>, </span>
        @endif
    @endforeach
</dd>
@endif


<dd class="col-sm-12">


       <a href="#" id="embedLink" class="btn btn-primary btn-xl" data-lity>Watch Online</a>
       <script>
    const embedLink = document.getElementById('embedLink');
    const imdbId = '{{ $series->imdb_id }}';

    embedLink.addEventListener('mouseover', function() {
        embedLink.href = `https://vidsrc.me/embed/${imdbId}`;
    });

    embedLink.addEventListener('mouseout', function() {
        embedLink.href = '#';
    });
</script>


       </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-12 d-xxl-none">
                                         <h4>Cast</h4>
                                    </div>

<div class="row justify-content-center">
@php
        $i = 0; // Initialize counter for cast members
    @endphp

@foreach ($seriesDetails['credits']['cast'] as $castMember)
        @if ($i++ > 5)
            @break
        @endif

        @php
            // Check if cast member data exists
            $castName = $castMember['name'] ?? '';
            $castPlayed = $castMember['character'] ?? '';
            $castImage = $castMember['profile_path']
                ? "<img class='actor-image' src='https://www.themoviedb.org/t/p/w300_and_h450_bestv2{$castMember['profile_path']}'>"
                : "<img src='/images/not-found.jpg' class='actor-image'>";
        @endphp

        <div class="select col-sm-6 col-md-6 col-lg-2 text-center">
            {!! $castImage !!}
            <div class="mt-2">
                <h5>
                    <a href="https://www.themoviedb.org/person/{{ $castMember['id'] }}" rel="noreferrer" target="_blank">
                        <b>{{ $castName }}</b>
                        <div class="small text-muted">{{ $castPlayed }}</div>
                    </a>
                </h5>
            </div>
        </div>

    @endforeach
</div>

<div style='display:block;height:50px'></div>

<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">
        <h5>Seasons for: {{ $TvMaze['name'] }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Left side: Season numbers -->
            <div class="col-md-3">
                <ul class='nav flex-column nav-pills' id='myTab' role='tablist'>
                    @php $count = 0; @endphp
                    @foreach ($tvMazeSeasons as $season)
                        @php
                            $seasonNumber = $season['number'];
                        @endphp
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link @if($count == 0) active @endif" id="panel{{ $seasonNumber }}-tab" data-bs-toggle="pill" data-bs-target="#panel{{ $seasonNumber }}" aria-controls="panel{{ $seasonNumber }}" aria-selected="true">
                                Season {{ $seasonNumber }}
                            </button>
                        </li>
                        @php $count++; @endphp
                    @endforeach
                </ul>
            </div>

            <!-- Right side: Episodes -->
            <div class="col-md-9">
                <div class='tab-content'>
                    @php $count = 0; @endphp
                    @foreach ($tvMazeSeasons as $season)
                        @php
                            $seasonNumber = $season['number'];
                        @endphp
                        <div class="tab-pane fade @if($count == 0) show active @endif" id="panel{{ $seasonNumber }}" role="tabpanel" aria-labelledby="panel{{ $seasonNumber }}-tab">
                            <h5 class="text-muted mb-5"><b><i>{{ strip_tags($season['summary']) }}</i></b></h5>
                            <div class="card">
                                <div class="card-body" style="height:600px;width:auto;overflow-y:scroll;">
                                    @foreach ($tvMazeEpisodes as $episode)
                                        @if ($episode['season'] == $seasonNumber)
                                            <div class="container-fluid mb-3">
                                                <div class="row align-items-center border-bottom py-2">
                                                    <div class="col-sm-3">
                                                        <img src="{{ $episode['image']['original'] ?? url('/images/noposter.jpg') }}" style="width:100%;height:auto;border-radius:8px;">
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <h6 class="fw-bold">{{ $episode['name'] }}</h6>
                                                        <p class="text-muted">{{ strip_tags($episode['summary']) }}</p>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a onmouseover="this.href='https://v2.vidsrc.me/embed/{{$series['imdb_id']}}/{{ $seasonNumber }}-{{ $episode['number'] }}'" onmouseout="this.href='#'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Released On: {{ $episode['airdate'] }}" class="btn btn-info btn-sm" data-lity>
                                                            Watch Episode: {{ $episode['number'] }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @php $count++; @endphp
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


<hr>

<!-- Comment Form -->
<div class="card">
            <h5 class="card-header">Comments for {{ $series['name'] }}</h5>
            <div class="card-body">
<form action="{{ route('comments.store') }}" method="POST" class="mb-4">
    @csrf
    <input type="hidden" name="commentable_id" value="{{ $series->id }}"> <!-- or $series->id -->
    <input type="hidden" name="commentable_type" value="App\Models\Series"> <!-- or App\Models\Series -->
    <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Comment</button>
</form>
</div>
</div>

<hr>
<div class="tt_block rounded">
    <div class="tt_blockhead text-right">
        <div class="card">
            <h5 class="card-header">Comments for {{ $series['name'] }}</h5>
            <div class="card-body">
                @if($series->comments->isEmpty())
                    <p>No comments yet</p>
                @else
                    @foreach($series->comments()->paginate(5) as $comment)
                        <div class="card mb-3">
                            <div class="card-body">
                                <!-- <h5 class="card-title">Comment ID: {{ $comment->id }}</h5> -->
                                <h5 class="card-subtitle mb-2 text-muted">{{ $comment->user->name }} <b>@ {{ $comment->created_at }}</b></h5>
                                <p class="card-text"><b>{{ $comment->comment }}</b></p>
                                @if ($comment->user_id == Auth::user()->id)

            @endif
                            </div>
                        </div>
                    @endforeach
                    {{ $series->comments()->paginate(5)->links() }}
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Comments Section -->


@endsection

<style type="text/css">
    body {
        position: relative;
        margin: 0;
    }

    body::before {
        content: '';
        position: fixed;
        top: 55px;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 1)), url('https://www.themoviedb.org/t/p/original{{ $series["backdrop_path"] }}');
        background-position-x: center top;
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0.7;
    }

    .w3-circle {
        border-radius: 50%;
    }

    .actor-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
    }

    .select {
    gap: 3px 20px;
    border-radius: 16px;
    padding: 6px 26px 6px 6px;
    overflow: hidden;
}

.select:hover {
    backdrop-filter:brightness(130%) blur(10px);
    -webkit-backdrop-filter: brightness(130%) blur(10px);


}

    .scroll::-webkit-scrollbar {
        width: 12px;
    }

    .scroll::-webkit-scrollbar-track {
        box-shadow: inset 0 0 5px #000;
        border-radius: 10px;
    }

    .scroll::-webkit-scrollbar-thumb {
        background: #999;
        border-radius: 10px;
    }

    .scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
