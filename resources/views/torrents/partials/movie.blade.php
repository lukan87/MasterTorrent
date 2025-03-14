
<div class="row">
    <div class="col-md-12 col-lg-12">
        <!-- Removed card card-custom card-blur classes -->
        <div class="content-overlay mb-3">
            <div class="card-body"><div class="tagss">Movies</div>
                <div class="row">
                    <div class="col-12 col-sm-3 col-xxl-2 order-0 order-sm-1 order-xxl-0 text-center">
                        <img src="{{$torrent->poster}}"
                             style="border-radius: 12px;
                                    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
                                    width:100%;">

<div style="margin-top: 10px;">
    <!-- TMDB Link -->
    @if($torrent->tmdbid)
        <a href="https://www.themoviedb.org/movie/{{ $torrent->tmdbid }}" target="_blank" class="btn btn-dark btn-sm">View on TMDB</a>
    @endif

    <!-- IMDb Link -->
    @if($torrent->imdbid)
        <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}" target="_blank" class="btn btn-dark btn-sm">View on IMDb</a>
    @endif
</div>
                    </div>

                    <div class="col-sm-9 col-xxl-10 order-1 order-sm-0 order-xxl-1">

                    <dd class="col-lg-12">

                    @if(isset($omdbData['Rated']) && $omdbData['Rated'] != 'N/A')
    @php
        $rating = $omdbData['Rated'];
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
                        <h3>{{ $tmdbData['title'] }}
                                 @if(isset($tmdbData['release_date']))
                                      ({{ \Carbon\Carbon::parse($tmdbData['release_date'])->format('F j, Y') }})
                                 @endif
                                 {!! $PG !!}
                        </h3>
                        @if(isset($tmdbData['tagline']))
                        <i>{{ $tmdbData['tagline'] }}</i>
                        @endif

                    </p>
                    </dd>

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

@if(isset($tmdbData['runtime']))
    @php
        $hours = intdiv($tmdbData['runtime'], 60);  // Get the number of hours
        $minutes = $tmdbData['runtime'] % 60;      // Get the remaining minutes
    @endphp
    <dd class="col-sm-12">
        <p><strong>Runtime:</strong> {{ $hours }} hour{{ $hours != 1 ? 's' : '' }}
        @if($minutes > 0)
            and {{ $minutes }} minute{{ $minutes != 1 ? 's' : '' }}
        @endif
        </p>
    </dd>
@endif



<!-- OMDB Data Section -->

@if(isset($omdbData))
    <!-- Rated (OMDB) -->

    @if(isset($omdbData['Rated']))
        <dd class="col-sm-12">
            <p><strong>IMDB Votes:</strong> {{ $omdbData['imdbVotes'] }}</p>
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
                        <img src="{{ asset('images/youtube-hover.png') }}" alt="Play Trailer" style="width: 30px; border-radius: 50%;" data-bs-toggle="tooltip" title="Play {{$tmdbData['title']}} Trailer">
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
background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 1)), url('{{$torrent->background}}');
background-position-x: center top;
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
