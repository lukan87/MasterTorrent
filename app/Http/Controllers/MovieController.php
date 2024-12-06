<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Add this line







class MovieController extends Controller
{
    // TMDB API key
    private $apiKey;

    public function __construct()
    {
        // Get API key from config file
        $this->apiKey = config('app.tmdb_api_key');
    }

    public function index()
    {
        // Fetch movies from the database
        $movies = Movie::latest()->paginate(12);

        return view('movies.index', compact('movies'))->with('links', 'vendor.pagination.bootstrap-5');
    }

    public function search(Request $request)
    {
        // Validate the incoming request
        $request->validate(['movie_name' => 'required|string|max:255']);

        // Get the search query
        $movieName = $request->movie_name;



        // Make an API call to TMDB to search for the movie
        $searchResponse = Http::get("https://api.themoviedb.org/3/search/movie", [
            'api_key' => $this->apiKey,
            'query' => $movieName
        ]);

        // Decode the JSON response and convert to a collection
        $movies = collect($searchResponse->json()['results'] ?? []);

        // Return a view with the list of movies
        return view('movies.search_results', compact('movies'));
    }

    public function selectMovie($tmdb_id)
    {
        // Check if the movie already exists in the database
        if (Movie::where('tmdb_id', $tmdb_id)->exists()) {
            return redirect()->route('movies.create')->with('error', 'Movie already exists in the database.');
        }

        // Fetch movie details by TMDB ID
        $response = Http::get("https://api.themoviedb.org/3/movie/{$tmdb_id}", [
            'api_key' => '325f0b42fccd356be82ede4d2be6312c'
        ]);

        $data = $response->json();

        // Create a new movie entry using the private method
        $this->createMovie($data);


        // Redirect to the newly created movie's show page
        return redirect()->route('movies.show', $data['id'])->with('status', 'Movie added successfully!');
    }

    public function show($id, $slug = null)
    {
        // Fetch the movie by ID
        $movie = Movie::findOrFail($id);

        // If the slug is not provided, redirect to the URL with the slug
        if ($slug === null) {
            return redirect()->route('movies.show', ['id' => $id, 'slug' => $movie->slug]);
        }

        // If slug is provided, make sure the slug matches the one in the database
        if ($slug !== $movie->slug) {
            return redirect()->route('movies.show', ['id' => $id, 'slug' => $movie->slug]);
        }

        // Cache key based on movie TMDB ID
        $cacheKey = 'movie_' . $movie->tmdb_id . '_details';

        $comments = $movie->comments()->with('user')->get(); // Eager load users

        // Check if movie details are cached
        $movieDetails = cache()->remember($cacheKey, now()->addWeek(), function () use ($movie) {
            return Http::get("https://api.themoviedb.org/3/movie/{$movie->tmdb_id}", [
                'api_key' => '325f0b42fccd356be82ede4d2be6312c',
                'language' => 'en-US',
                'append_to_response' => 'credits,videos,images,external_ids'
            ])->json();
        });

        // Cache OMDB data
        $omdbCacheKey = 'movie_' . $movie->imdb_id . '_omdb';
        $movieOm = cache()->remember($omdbCacheKey, now()->addWeek(), function () use ($movie) {
            return Http::get("http://www.omdbapi.com", [
                'apikey' => 'd3eb5201', // Store OMDB API key in .env
                'i' => $movie->imdb_id,
                'plot' => 'full'
            ])->json();
        });

        // Return the view with the movie data
        return view('movies.show', compact('movieDetails', 'movieOm', 'movie', 'comments'));
    }



    public function create()
    {
        // Check if the user is authorized (is Owner)
        if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN) {
            return view('movies.create'); // return the create view
        }

        // If not authorized, redirect with an error message
        return redirect()->route('movies.index')->with('error', 'Unauthorized access.');
    }

    public function store(Request $request)
{
    // Validate the request data
    $request->validate(['tmdb_id' => 'required|string|max:255']);

    // Check if the movie with the same TMDB ID already exists
    if (Movie::where('tmdb_id', $request->tmdb_id)->exists()) {
        return redirect()->route('movies.create')->with('error', 'Movie already exists in the database.');
    }

    // Create the movie from TMDB data
    $url = "https://api.themoviedb.org/3/movie/{$request->tmdb_id}?api_key=325f0b42fccd356be82ede4d2be6312c&language=en-US&append_to_response=credits,videos,images,keywords,external_ids";

    try {
        // Make the GET request to TMDB API
        $response = Http::get($url);
        $data = $response->json();

        // Check if the response contains an error
        if (isset($data['success']) && !$data['success']) {
            return redirect()->route('movies.create')->with('error', 'Movie not found on TMDB. Please check the TMDB ID.');
        }

        // Create a new movie entry using the private method
        $this->createMovie($data);

        // Redirect to the movies index with a success message
        return redirect()->route('movies.index')->with('status', 'Movie created successfully!');
    } catch (\Exception $e) {
        // Handle the error if the API request fails
        return redirect()->route('movies.create')->with('error', 'Error fetching data from TMDB API: ' . $e->getMessage());
    }
}

public function bulkSelect(Request $request)
{
    // Log the incoming request for debugging
    Log::info('Incoming request to bulkSelect:', $request->all());

    // Validate the incoming request
    $request->validate([
        'movies' => 'required|array',
        'movies.*' => 'integer|distinct'
    ]);

    // Check if movies were selected
    if (empty($request->movies)) {
        Log::info('No movies selected, redirecting with error message.');
        return redirect()->route('movies.index')->with('error', 'Please select at least one movie to add.');
    }

    // Loop through selected movie IDs and add them to the database
    foreach ($request->movies as $tmdb_id) {
        if (!Movie::where('tmdb_id', $tmdb_id)->exists()) {
            // Fetch movie details by TMDB ID
            $response = Http::get("https://api.themoviedb.org/3/movie/{$tmdb_id}", [
                'api_key' => '325f0b42fccd356be82ede4d2be6312c'
            ]);
            $data = $response->json();

            // Create a new movie entry using the private method
            $this->createMovie($data);
        }
    }
    // After performing some action



    return redirect()->route('movies.index')->with('success', 'Operation was successful!');
}


    public function searchMovie(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('name');

        // Check if the search term is empty
        if (empty($searchTerm)) {
            return redirect()->route('movies.index');
        }

        // Fetch movies matching the search term
        $movies = Movie::where('name', 'LIKE', "%{$searchTerm}%")->paginate(12);



        // Return the search results to the view
        return view('movies.index', compact('movies'));
    }

    // Private method to create a movie entry
    private function createMovie(array $data)
{
    $uniqueName = $this->generateUniqueName($data['title'], $data['release_date']);

    return Movie::create([
        'name' => $uniqueName,
        'tmdb_id' => $data['id'],
        'imdb_id' => $data['imdb_id'] ?? null,
        'poster_path' => $data['poster_path'] ?? null,
        'collection_id' => $data['belongs_to_collection']['id'] ?? null,
        'collection_name' => $data['belongs_to_collection']['name'] ?? null,
        'overview' => $data['overview'] ?? null,
        'backdrop_path' => $data['backdrop_path'] ?? null,
        'slug' => $this->generateUniqueSlug($uniqueName, $data['release_date']),
    ]);
}

private function generateUniqueName($title, $releaseDate)
{
    // Parse the release date and extract the year
    $year = \Carbon\Carbon::parse($releaseDate)->format('Y');

    // Check if a movie with the same name exists
    if (Movie::where('name', $title)->exists()) {
        // If it exists, append the year to the name
        return $title . ' (' . $year . ')';
    }

    // If no conflict, return the original name
    return $title;
}

private function generateUniqueSlug($title, $releaseDate)
{
    // Parse the release date and extract the year
    $year = \Carbon\Carbon::parse($releaseDate)->format('Y');

    // Generate the base slug
    $slug = Str::slug($title);
    $originalSlug = $slug;
    $counter = 1;

    // Check for slug uniqueness
    while (Movie::where('slug', $slug)->exists()) {
        if ($counter === 1) {
            // Append the year to the title on the first conflict
            $slug = Str::slug($title . ' ' . $year);
            $originalSlug = $slug; // Update the original slug base
        } else {
            // Add a counter for further conflicts
            $slug = $originalSlug . '-' . $counter;
        }
        $counter++;
    }

    return $slug;
}






}
