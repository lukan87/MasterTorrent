@extends('layouts.app')

@section('content')

<div style='display:block;height:50px'></div>
<div style='display:block;height:50px'></div>

<div class="card">
    <h3 class="card-header">
        {{ $series['name'] }} - <i>{{ $seriesDetails['tagline'] }}</i>
    </h3>

    <div class="card-body">
        <div class="card mb-3" style="max-width: auto;">
            <div class="row">
                <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-1 order-xxl-0 text-center">
                    @if ($series['poster_path'])
                        <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $series['poster_path'] }}" width="100%">
                    @endif
                </div>

                <div class="col-sm-6">
                    <div class="card-body">
                        <dd class="col-sm-12">
                            <span class="text-bright text-bold" style="font-size: 15px;">
                                @foreach (array_slice($seriesDetails['genres'], 0, 8) as $genre)
                                    <button class="btn btn-dark"><i>{{ $genre['name'] }}</i></button>
                                @endforeach
                            </span>
                        </dd>
                        <dd class="col-sm-12">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe src="https://vidsrc.me/embed/{{ $series['imdb_id'] }}" width="100%" height="330" webkitallowfullscreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>
                            </div>
                        </dd>
                    </div>
                </div>

                <div class="col-sm-3">
                    <p class="card-text">
                        <dd class="col-sm-12">
                            <span class="text-bright text-bold" style="font-size: 20px;">
                                <b>Plot</b>
                            </span><br>
                            <span class="text-bright text-bold" style="font-size: 15px;">
                                <b><i class="fa fa-info-circle"></i>&nbsp;&nbsp;{{ $series['overview'] }}</b>
                            </span>
                        </dd>
                        <dd class="col-sm-12">
                            <span class="text-bright text-bold" style="font-size: 20px;">
                                <b>Release Date</b>
                            </span><br>
                            <span class="text-bright text-bold" style="font-size: 15px;">
                                <b>{{ $seriesDetails['first_air_date'] }}</b>
                            </span>
                        </dd>
                        <dd class="col-sm-12">
                            <span class="text-bright text-bold" style="font-size: 20px;">
                                <b>Director</b>
                            </span><br>
                            <span class="text-bright text-bold" style="font-size: 15px;">
                                <b>{{ $seriesOm['Director'] }}</b>
                            </span>
                        </dd>
                    </p>
                </div>

                <div class="hidden-mobile">
                    <center>
                        <small class="text-muted">
                            @foreach (array_slice($seriesDetails['credits']['cast'], 0, 8) as $castMember)
                                @if($castMember['profile_path'])
                                    <div style='display: inline-block; text-align:center; margin:10px;'>
                                        <img class='w3-circle' src="https://image.tmdb.org/t/p/w138_and_h175_face{{ $castMember['profile_path'] }}" style='padding:3px;width:120px;height:120px;box-shadow: -4px 4px 5px 0 #000; cursor: pointer;' data-bs-toggle="tooltip" data-bs-placement="bottom" title="Playing as {{ $castMember['character'] }}">
                                        <div style='text-align:center; margin-top:5px;'>
                                            <span>{{ $castMember['name'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </small>
                    </center>
                </div><br>

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

            </div>
        </div>
    </div>
</div>

<div style='display:block;height:50px'></div>

<div class="card">
    <div class="card-header">
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
                        <li class="nav-item" role="presentation">
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
                            <h6><i>{{ strip_tags($season['summary']) }}</i></h6><br>
                            <div class="card">
                                <div class="card-body" style='height:500px;width:auto;overflow-y:scroll;'>
                                    @foreach ($tvMazeEpisodes as $episode)
                                        @if ($episode['season'] == $seasonNumber)
                                            <div class="container-fluid mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-sm-3">
                                                        <img src="{{ $episode['image']['original'] ?? url('/images/noimg.png') }}" style="width:100%;height:auto;">
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <h6>{{ $episode['name'] }}</h6>
                                                        <p>{{ strip_tags($episode['summary']) }}</p>
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
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: url('https://www.themoviedb.org/t/p/original{{ $series["backdrop_path"] }}');
        background-size: cover;
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-position: center;
        transition: background 5s;
        opacity: 0.8;
        z-index: -1;
    }

    .w3-circle {
        border-radius: 50%;
    }

    .select {
        gap: 3px 20px;
        border-radius: 16px;
        padding: 6px 26px 6px 6px;
        overflow: hidden;
    }

    .select:hover {
        backdrop-filter: brightness(130%) blur(10px);
        background-color: #000;
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
