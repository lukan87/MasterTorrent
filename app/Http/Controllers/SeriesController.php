<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('app.tmdb_api_key'); // Get API key from config file
    }

    public function index()
    {
        $series = Series::latest()->paginate(12);
        return view('series.index', compact('series'))->with('links', 'vendor.pagination.bootstrap-5');
    }

    public function search(Request $request)
    {
        $request->validate(['series_name' => 'required|string|max:255']);

        $seriesName = $request->series_name;

        try {
            $searchResponse = Http::get("https://api.themoviedb.org/3/search/tv", [
                'api_key' => $this->apiKey,
                'query' => $seriesName
            ]);

            $series = collect($searchResponse->json()['results'] ?? []);

            return view('series.search_results', compact('series'));
        } catch (\Exception $e) {
            Log::error("TMDB API search error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error fetching data from TMDB API!');
        }
    }

    public function selectSeries($tmdb_id)
    {
        if (Series::where('tmdb_id', $tmdb_id)->exists()) {
            return redirect()->route('series.create')->with('error', 'Series already exists in the database.');
        }

        try {
            $response = Http::get("https://api.themoviedb.org/3/tv/{$tmdb_id}", [
                'api_key' => $this->apiKey
            ]);

            $data = $response->json();
            Series::create([
                'name' => $data['name'],
                'tmdb_id' => $data['id'],
                'imdb_id' => $data['imdb_id'] ?? null,
                'poster_path' => $data['poster_path'] ?? null,
                'collection_id' => $data['belongs_to_collection']['id'] ?? null,
            ]);

            return redirect()->route('series.show', $data['id'])->with('status', 'Series added successfully!');
        } catch (\Exception $e) {
            Log::error("Error selecting series: " . $e->getMessage());
            return redirect()->route('series.create')->with('error', 'Error fetching series details from TMDB API!');
        }
    }


    public function show($slug)
    {
        $apiKey = '325f0b42fccd356be82ede4d2be6312c';
        $series = Series::where('slug', $slug)->firstOrFail();
        $comments = $series->comments()->with('user')->get(); // Eager load users

        if (!$series) {
            // Series not found, return error message
            return redirect()->back()->with('error', 'Series Not Found');
        }

        // Cache key based on series TMDB ID and IMDb ID
        $seriesCacheKey = 'series_' . $series->tmdb_id . '_details';
        $omdbCacheKey = 'series_' . $series->imdb_id . '_omdb';
        $tvMazeCacheKey = 'series_' . $series->imdb_id . '_tvmaze';
        $tvMazeSeasonsCacheKey = 'series_' . $series->imdb_id . '_tvmaze_seasons';
        $tvMazeEpisodesCacheKey = 'series_' . $series->imdb_id . '_tvmaze_episodes';

        // Cache series details from TMDB for 1 month
        $seriesDetails = cache()->remember($seriesCacheKey, now()->addMonth(), function () use ($series, $apiKey) {
            $seriesDetailsResponse = Http::get("https://api.themoviedb.org/3/tv/{$series->tmdb_id}?api_key=$apiKey&language=en-US&append_to_response=credits,videos,images,keywords,external_ids");
            return $seriesDetailsResponse->json();
        });

        // Cache OMDB data for 1 month
        $seriesOm = cache()->remember($omdbCacheKey, now()->addMonth(), function () use ($series) {
            $seriesOmdbResponse = Http::get("http://www.omdbapi.com?apikey=d3eb5201&i={$series->imdb_id}&plot=full");
            return $seriesOmdbResponse->json();
        });

        // Cache TVMaze data for 1 month
        $TvMaze = cache()->remember($tvMazeCacheKey, now()->addMonth(), function () use ($series) {
            $tvMazeResponse = Http::get("http://api.tvmaze.com/lookup/shows?imdb=$series->imdb_id");
            return $tvMazeResponse->json();
        });

        // Cache TVMaze seasons data for 1 month
        $tvMazeSeasons = cache()->remember($tvMazeSeasonsCacheKey, now()->addMonth(), function () use ($TvMaze) {
            $tvMazeSeasonsResponse = Http::get("https://api.tvmaze.com/shows/{$TvMaze['id']}/seasons");
            return $tvMazeSeasonsResponse->json();
        });

        // Cache TVMaze episodes data for 1 month
        $tvMazeEpisodes = cache()->remember($tvMazeEpisodesCacheKey, now()->addMonth(), function () use ($TvMaze) {
            $tvMazeEpisodesResponse = Http::get("https://api.tvmaze.com/shows/{$TvMaze['id']}/episodes");
            return $tvMazeEpisodesResponse->json();
        });

        // Return the view with the cached data
        return view('series.show', compact('seriesDetails', 'seriesOm', 'series', 'TvMaze', 'tvMazeSeasons', 'tvMazeEpisodes', 'comments'));
    }

    public function create()
    {
        return view('series.create');
    }

    public function store(Request $request)
    {
        $request->validate(['tmdb_id' => 'required|string|max:255']);

        if (Series::where('tmdb_id', $request->tmdb_id)->exists()) {
            return redirect()->route('series.create')->with('error', 'Series already exists in the database.');
        }

        try {
            $response = Http::get("https://api.themoviedb.org/3/tv/{$request->tmdb_id}", [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
                'append_to_response' => 'credits,videos,images,keywords,external_ids'
            ]);

            $data = $response->json();
            Series::create([
                'name' => $data['name'],
                'tmdb_id' => $data['id'],
                'imdb_id' => $data['imdb_id'] ?? null,
                'poster_path' => $data['poster_path'] ?? null,
            ]);

            return redirect()->route('series.index')->with('status', 'Series created successfully!');
        } catch (\Exception $e) {
            Log::error("Error storing series: " . $e->getMessage());
            return redirect()->route('series.create')->with('error', 'Error fetching data from TMDB API!');
        }
    }

    public function bulkSelect(Request $request)
    {
        $request->validate([
            'series' => 'required|array',
            'series.*' => 'integer|distinct'
        ]);

        foreach ($request->series as $tmdb_id) {
            if (!Series::where('tmdb_id', $tmdb_id)->exists()) {
                try {
                    $response = Http::get("https://api.themoviedb.org/3/tv/{$tmdb_id}", [
                        'api_key' => $this->apiKey,
                        'append_to_response' => 'external_ids'
                    ]);

                    $data = $response->json();
                    Series::create([
                        'name' => $data['name'],
                        'tmdb_id' => $data['id'],
                        'imdb_id' => $data['external_ids']['imdb_id'] ?? null,
                        'poster_path' => $data['poster_path'] ?? null,
                        'overview' => $data['overview'] ?? null,
                        'backdrop_path' => $data['backdrop_path'] ?? null,
                        'slug' => $this->generateUniqueSlug($data['name']),
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error fetching series data for bulk select: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('series.index')->with('status', 'Series added successfully!');
    }

    private function generateUniqueSlug($title)
    {
        // Generate an initial slug
        $slug = Str::slug($title);

        // Check if slug already exists
        $originalSlug = $slug;
        $counter = 1;

        // Append counter until a unique slug is found
        while (Series::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function searchSeries(Request $request)
    {
        $searchTerm = $request->input('name');
        if (empty($searchTerm)) {
            return redirect()->route('series.index');
        }

        $series = Series::where('name', 'LIKE', "%{$searchTerm}%")->paginate(12);
        return view('series.index', compact('series'));
    }
}
