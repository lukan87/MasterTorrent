@extends('layouts.app')

@section('content')


          <!-- <div class="app-content-header">
                <div class="container-fluid btn btn-secondary btn-sm">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">{{ $movie->name }}</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Index
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div> -->
            <div style='display:block;height:50px'></div>

      <div class="card">
      <h3 class="card-header">
      {{ $movie->name }} - <i>{{ isset($movieDetails['tagline']) ? $movieDetails['tagline'] : 'No tagline available' }}</i>

      </h3>





           <div class="card-body"><div class="card mb-3" style="max-width: auto;">
 <div class="row">


   <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-0 order-xxl-0 text-center">
                                                                    <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}" width="100%">
                                    </div>







   <div class="col-12 col-sm-6 order-1 order-sm-3 order-xxl-0">
     <div class="card-body">

     <dd class="col-sm-12">
       <span class="text-bright text-bold" style="font-size: 15px;">




        <p>
        @foreach (array_slice($movieDetails['genres'], 0, 8) as $genre)
                                    <button class="btn btn-dark"><i>{{ $genre['name'] }}</i></button>
                                @endforeach
        </p>


       </span>
       </dd>
       <dd class="col-sm-12">
       <div class="embed-responsive embed-responsive-16by9">

                                <iframe src="https://vidsrc.me/embed/{{ $movie->imdb_id }}" width="100%" height="330" webkitallowfullscreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>

                    </div>
       </dd>



     </div>
   </div>
   <div class="col-12 col-sm-3 order-2 order-sm-0 order-xxl-0">

       <p class="card-text">
       <dd class="col-sm-12">
       <span class="text-bright text-bold" style="font-size: 20px;">
       <b>
       Plot
       </b>
       </span><br>
       <span class="text-bright text-bold" style="font-size: 15px;">
       <b>
       <i class="fa fa-info-circle"></i>&nbsp;&nbsp;{{ $movie->overview }}
       </b>
       </span>
       </dd>
       <dd class="col-sm-12">
       <span class="text-bright text-bold" style="font-size: 20px;">
       <b>
       Release Date
       </b>
       </span><br>
       <span class="text-bright text-bold" style="font-size: 15px;">
       <b>
       {{ $movieDetails['release_date'] }}
       </b>
       </span>
       </dd>
       <dd class="col-sm-12">
       <span class="text-bright text-bold" style="font-size: 20px;">
       <b>
       Runtime
       </b>
       </span><br>
       <span class="text-bright text-bold" style="font-size: 15px;">
       <b>
       {{ $movieDetails['runtime'] }} minutes
       </b>
       </span>
       </dd>
       <dd class="col-sm-12">
       <span class="text-bright text-bold" style="font-size: 20px;">
       <b>
       Director
       </b>
       </span><br>
       <span class="text-bright text-bold" style="font-size: 15px;">
       <b>
       {{$movieOm['Director']}}
       </b>
       </span>
       </dd>
       @if (empty($movie->collection_id))
       @else
      <dd class="col-sm-12">
        <span class="text-bright text-bold" style="font-size: 20px;">
            <b>Belongs to collection</b>
        </span><br>
        <span class="text-bright text-bold" style="font-size: 15px;">
            <b>
                <a href="{{ route('collections.show', $movie->collection_id) }}">{{ $movie->collection_name }}</a>
            </b>
        </span>
       </dd>
       @endif




       </p>


   </div>

   <div class="hidden-mobile"><center><small class="text-muted">
   @foreach (array_slice($movieDetails['credits']['cast'], 0, 8) as $castMember)
    <div style='display: inline-block; text-align:center; margin:10px;'>
        <img class='select' src="https://image.tmdb.org/t/p/w138_and_h175_face{{ $castMember['profile_path'] }}" style='border-radius: 50%;padding:3px;width:150px;height:150px;box-shadow: -4px 4px 5px 0 #000; cursor: pointer;' data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Playing as {{ $castMember['character'] }}">
        <div style='text-align:center; margin-top:5px;'>
            <span>{{ $castMember['name'] }}</span>
        </div>
    </div>
@endforeach


</small></center></div><br>

<dd>
    <ul>
<h3>Trailers</h3>
@foreach (array_slice($movieDetails['videos']['results'], 0, 3) as $video)

<li>
    <a
        href="#"
        onmouseover="this.href='https://www.youtube.com/watch?v={{ $video['key'] }}'"
        onmouseout="this.href='#'"
        data-lity
    >
        <h6>{{ $video['name'] }}</h6>
    </a>
</li>

@endforeach
    </ul>
</dd>

   </div>
 </div>
 </div>
 </div>



 <hr>


<!-- Comment Form -->
<div class="card">

            <div class="card-body">
<form action="{{ route('comments.store') }}" method="POST" class="mb-4">
    @csrf
    <input type="hidden" name="commentable_id" value="{{ $movie->id }}"> <!-- or $series->id -->
    <input type="hidden" name="commentable_type" value="App\Models\Movie"> <!-- or App\Models\Series -->
    <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Comment</button>
</form>
</div>
</div>
<hr>

<!-- Comments Section -->
<div class="tt_block rounded">
    <div class="tt_blockhead text-right">
        <div class="card">
            <h5 class="card-header">Comments for {{ $movie['name'] }}</h5>
            <div class="card-body">
                @if($movie->comments->isEmpty())
                    <p>No comments yet</p>
                @else
                    @foreach($movie->comments()->paginate(5) as $comment)
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
                    {{ $movie->comments()->paginate(5)->links() }}
                @endif
            </div>
        </div>
    </div>
</div>












@endsection



<style type="text/css">


    body {
    position: relative;
    margin: 0; /* Remove default margin to cover the entire viewport */
}

body::before {
    /*display:block;
    height:660px;*/
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-image:  url('https://www.themoviedb.org/t/p/original{{ $movie->backdrop_path }}');
    background-size: cover;
    background-attachment: fixed;
    background-repeat: no-repeat;
    background-position: center;
    -webkit-transition: background 5s;
    -moz-transition: background 5s;
    -o-transition: background 5s;
    transition: background 5s;
    opacity: 0.8;
    z-index: -1;/* Adjust the initial opacity as needed */

}

 .w3-circle{border-radius:50%}

.select {
    gap: 3px 20px;
    border-radius: 16px;
    padding: 6px 26px 6px 6px;
    overflow: hidden;
}

.select:hover {
    backdrop-filter:brightness(130%) blur(10px);
    -webkit-backdrop-filter: brightness(130%) blur(10px);

    @supports (not (backdrop-filter: brightness(2) blur(10px))) and (not (-webkit-backdrop-filter: brightness(2) blur(10px))) {
        background: rgba(255,255,255,0.4);
    }
}

    iframe {
        width: 100%;
        border-radius: 20px;
    }
</style>







