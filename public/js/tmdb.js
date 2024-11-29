// Get the TMDB information for a movie
function getTmdbInfo(movieId) {
    $.ajax({
      url: "https://api.themoviedb.org/3/movie/" + movieId,
      type: "GET",
      data: {
        api_key: "YOUR_API_KEY",
        language: "en-US",
      },
      success: function (response) {
        // Parse the response and display the information
        var title = response.title;
        var releaseDate = response.release_date;
        var overview = response.overview;
        var posterPath = "https://image.tmdb.org/t/p/w500" + response.poster_path;
        
        var content = "<h5>" + title + "</h5>" +
                      "<p><strong>Release Date:</strong> " + releaseDate + "</p>" +
                      "<p><strong>Overview:</strong> " + overview + "</p>" +
                      "<img src='" + posterPath + "' class='img-fluid rounded'>";
                      
        // Show the information in a tooltip
        $('[data-toggle="tooltip"][data-tmdbid="' + movieId + '"]').attr("title", content).tooltip("show");
      },
      error: function (xhr, status, error) {
        console.log(error);
      },
    });
  }
  