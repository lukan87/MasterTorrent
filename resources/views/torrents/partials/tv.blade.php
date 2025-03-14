<div style="display:block; height:50px"></div>

<div class="row">
    <div class="col-md-12">
        <!-- Removed card card-custom card-blur classes -->
        <div class="content-overlay mb-3">
            <div class="card-body"><div class="tagss">TV-Series</div>
                <div class="row">
                    <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-1 order-xxl-0 text-center">
                        <img src="{{$torrent->poster}}"
                             style="border-radius: 12px;
                                    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                                    width:100%;
                                    margin-top: -50px;">

<div style="margin-top: 10px;">
    <!-- TMDB Link -->
    @if($torrent->tmdbid)
        <a href="https://www.themoviedb.org/tv/{{ $torrent->tmdbid }}" target="_blank" class="btn btn-dark btn-sm">View on TMDB</a>
    @endif

    <!-- IMDb Link -->
    @if($torrent->imdbid)
        <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}" target="_blank" class="btn btn-dark btn-sm">View on IMDb</a>
    @endif
</div>
                    </div>

                    <div class="col-sm-9 col-xxl-10 order-1 order-sm-0 order-xxl-1">


    <!-- Rated (OMDB) -->

    @php
    $PG = ''; // Initialize the variable
    @endphp
    @if(isset($omdbData['Rated']) && $omdbData['Rated'] != 'N/A')
    @php
        $rating = $omdbData['Rated'];


        switch ($rating) {
             // TV Ratings
       case 'TV-Y':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Designed to be appropriate for children of all ages. The thematic elements portrayed in programs with this rating are specifically designed for a very young audience, including children from ages 2-6. '><b>$rating</b></i>";
        break;
    case 'TV-Y7':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Designed for children age 7 and older. The FCC states that it \"may be more appropriate for children who have acquired the developmental skills needed to distinguish between make-believe and reality\".The thematic elements portrayed in programs with this rating contain mild fantasy and comedic violence. '><b>$rating</b></i>";
        break;
    case 'TV-G':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Programs are generally suitable for all audiences, though they may not necessarily contain content of interest to children. The FCC states that \"this rating does not signify a program designed specifically for children, [and] most parents may let younger children watch this program unattended\". The thematic elements portrayed in programs with this rating contain little or no violence, mild language, and little or no sexual dialogue or situations'><b>$rating</b></i>";
        break;
    case 'TV-PG':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Programs may contain some material that parents or guardians may find inappropriate for younger children. Programs assigned a TV-PG rating may include infrequent coarse language, some sexual content, some suggestive dialogue, or moderate violence.'><b>$rating</b></i>";
        break;
    case 'TV-14':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Programs contain material that parents or adult guardians may find unsuitable for children under the age of 14. The FCC warns that \"parents are cautioned to exercise some care in monitoring this program and are cautioned against letting children under the age of 14 watch unattended\". Programs with this rating contain intensely suggestive dialogue, strong coarse language, intense sexual situations or intense violence.'><b>$rating</b></i>";
        break;
    case 'TV-MA':
        $PG = "<i class='bi bi-stars' aria-hidden='false' data-bs-toggle='tooltip' title='Contains content that may be unsuitable for children. This rating was originally TV-M prior to the announced revisions to the rating system in August 1997 but was changed due to a trademark dispute and in order to remove confusion with the Entertainment Software Rating Board's (ESRB) \"M for Mature\" rating for video games.This rating is rarely used by broadcast networks or local television stations due to FCC restrictions on program content, although it is commonly applied to television programs featured on certain cable channels (basic and premium networks) and streaming networks for both mainstream and softcore programs. Programs with this rating may include crude indecent language, explicit sexual activity and graphic violence.'><b>$rating</b></i>
        ";
        break;

    default:
        $PG = "<i class='fa fa-star-o' aria-hidden='false' data-bs-toggle='tooltip' title='Rated'><b>$rating</b></i>";
        break;
        }
    @endphp
@endif
                    <h3>{{ $tmdbData['name'] }}
                    @if(isset($tmdbData['first_air_date']))
                    ( {{ \Carbon\Carbon::parse($tmdbData['first_air_date'])->format('F j, Y') }} )
                    @endif
    {!! $PG !!}</h3>
                    @if(isset($tmdbData['tagline']))
                    <h5><i>{{ $tmdbData['tagline'] }}</i></h5>
                    @endif

<dd class="col-lg-12">
@foreach($torrent->genres as $genre)
                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary" title="Search for {{ $genre->name }} torrents">{{ $genre->name }}</a>
            @endforeach
</dd>

@if(isset($tmdbData['overview']))
    <dd class="col-sm-12">
        <b>
            <font size="3">
                <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="Overview"></i>&nbsp;&nbsp;{{ $tmdbData['overview'] }}
            </font>
        </b>
    </dd>
@endif



<!-- OMDB Data Section -->



    @if(isset($omdbData['imdbVotes']))
        <dd class="col-sm-12">
            <p><strong>IMDB Votes: <i>{{ $omdbData['imdbVotes'] }}</i> </strong></p>
        </dd>
    @endif

    <!-- OMDB Ratings (Multiple Sources) -->
    @if(isset($omdbData['Ratings']) && count($omdbData['Ratings']) > 0)
            <dd class="col-sm-12">
                <p><strong>Ratings:</strong></p>
                <ul>
                    @foreach($omdbData['Ratings'] as $rating)
                        <li><strong>{{ $rating['Source'] }}:</strong> {{ $rating['Value'] }}</li>
                    @endforeach
                </ul>
            </dd>
        @endif



@if(isset($tmdbData['number_of_seasons']))
        <dd class="col-sm-12">
            <p><strong>Seasons: <i>{{ $tmdbData['number_of_seasons'] }}</i> </strong> /
            <strong>Episodes: <i>{{ $tmdbData['number_of_episodes'] }}</i> </strong>
            </p>
        </dd>
    @endif

    <div class="col-12">
        <h5><strong>Trailer</strong></h5>
        
            @if(!empty($torrent->trailer))
                <!-- Display trailer from database -->
                @php
                    $dbTrailerUrl = htmlspecialchars($torrent->trailer);
                    $embedUrl = str_replace("watch?v=", "embed/", $dbTrailerUrl);
                @endphp
                <a href="{{ $embedUrl }}?version=3&amp;autohide=3&amp;hl=ro_RO&amp;showinfo=0&amp;autoplay=1&amp;disablekb=0&amp;hd=1&amp;theme=dark" 
                   data-lity>
                    <img src="{{ asset('images/youtube-hover.png') }}" alt="Play Trailer" style="width: 30px; border-radius: 50%;" data-bs-toggle="tooltip" title="Play {{$tmdbData['title']}} Trailer">
                </a>
            @elseif(isset($tmdbData['videos']['results']) && count($tmdbData['videos']['results']) > 0)
                <!-- Display trailer from TMDB -->
                @php
                    $tmdbTrailer = collect($tmdbData['videos']['results'])->firstWhere('type', 'Trailer');
                @endphp
                @if($tmdbTrailer)
                    <a href="https://www.youtube.com/embed/{{ $tmdbTrailer['key'] }}?version=3&amp;autohide=3&amp;hl=ro_RO&amp;showinfo=0&amp;autoplay=1&amp;disablekb=0&amp;hd=1&amp;theme=dark" 
                       data-lity>
                        <img src="{{ asset('images/youtube-hover.png') }}" alt="Play Trailer" style="width: 30px; border-radius: 50%;" data-bs-toggle="tooltip" title="Play {{$tmdbData['name']}} Trailer">
                    </a>
                @else
                    <p>No trailer available.</p>
                @endif
            @else
                <p>No trailer available.</p>
            @endif
       
    </div>
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

@foreach ($tmdbData['credits']['cast'] as $castMember)
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

        <div class="select col-12 col-sm-6 col-md-3 col-lg-2 text-center">
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

<style>
    /* Style adjustments for background display */
    .content-overlay {
        background: none; /* Remove card background */
        padding: 20px;
    }

    body::before {
        content: '';
        position: fixed;
        /* position: absolute; */
        top: 55px;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('{{$torrent->background}}');
        background-position-x: center;
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0.7;
    }

    .videoWrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
    }

    .videoWrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
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
        backdrop-filter: brightness(130%) blur(10px);
    }
</style>
