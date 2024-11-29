<div class="row">
    <!-- Movies Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h2>Movies</h2>
            </div>
            <div class="card-body text-center">
                @foreach ($movies->shuffle()->take(8) as $movie)
                    <div style="display: inline-block; margin: 5px;">
                        <a href="{{ route('movies.show', $movie->slug) }}">
                            <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}" class="img-fluid rounded" style="width: 150px; height: auto;">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Series Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h2>Series</h2>
            </div>
            <div class="card-body text-center">
                @foreach ($series->shuffle()->take(8) as $serie)
                    <div style="display: inline-block; margin: 5px;">
                        <a href="{{ route('series.show', $serie->slug) }}">
                            <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}" class="img-fluid rounded" style="width: 150px; height: auto;">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
