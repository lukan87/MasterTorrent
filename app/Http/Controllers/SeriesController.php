<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Torrent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
        $featured = Series::whereNotNull('backdrop_path')
        ->inRandomOrder()
        ->first();
        return view('series.index', compact('series', 'featured'))->with('links', 'vendor.pagination.bootstrap-5');
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



public function show($id, $slug = null)
{
    // Redirect if slug missing
    if (!$slug) {
        $series = Series::findOrFail($id);
        return redirect()->route('series.show', [
            'id' => $series->id,
            'slug' => $series->slug
        ]);
    }

    $apiKey = '325f0b42fccd356be82ede4d2be6312c';

    /*
    |--------------------------------------------------------------------------
    | CACHE MAIN SERIES (DB)
    |--------------------------------------------------------------------------
    */

    $series = Cache::remember(
        "series_model_{$slug}",
        now()->addMinutes(30),
        fn() => Series::where('slug', $slug)->firstOrFail()
    );

    /*
    |--------------------------------------------------------------------------
    | CACHE COMMENTS
    |--------------------------------------------------------------------------
    */

    $comments = Cache::remember(
        "series_comments_{$series->id}",
        now()->addMinutes(5),
        fn() => $series->comments()->with('user')->get()
    );

    /*
    |--------------------------------------------------------------------------
    | EXTERNAL API CACHE (1 MONTH)
    |--------------------------------------------------------------------------
    */

    $seriesDetails = Cache::remember(
        "series_{$series->tmdb_id}_details",
        now()->addMonth(),
        fn() => Http::get(
            "https://api.themoviedb.org/3/tv/{$series->tmdb_id}",
            [
                'api_key' => $apiKey,
                'language' => 'en-US',
                'append_to_response' => 'credits,videos,images,keywords,external_ids'
            ]
        )->json()
    );

    $seriesOm = Cache::remember(
        "series_{$series->imdb_id}_omdb",
        now()->addMonth(),
        fn() => Http::get(
            "http://www.omdbapi.com",
            [
                'apikey' => 'd3eb5201',
                'i' => $series->imdb_id,
                'plot' => 'full'
            ]
        )->json()
    );

    $TvMaze = Cache::remember(
        "series_{$series->imdb_id}_tvmaze",
        now()->addMonth(),
        fn() => Http::get(
            "http://api.tvmaze.com/lookup/shows",
            ['imdb' => $series->imdb_id]
        )->json()
    );

    if (!empty($TvMaze['id'])) {

        $tvMazeSeasons = Cache::remember(
            "series_{$series->imdb_id}_tvmaze_seasons",
            now()->addMonth(),
            fn() => Http::get(
                "https://api.tvmaze.com/shows/{$TvMaze['id']}/seasons"
            )->json()
        );

        $tvMazeEpisodes = Cache::remember(
            "series_{$series->imdb_id}_tvmaze_episodes",
            now()->addMonth(),
            fn() => Http::get(
                "https://api.tvmaze.com/shows/{$TvMaze['id']}/episodes"
            )->json()
        );

    } else {
        $tvMazeSeasons = null;
        $tvMazeEpisodes = null;
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE TORRENTS + GROUPING
    |--------------------------------------------------------------------------
    */

    $groupedTorrents = Cache::remember(
        "series_torrents_{$series->tmdb_id}",
        now()->addMinutes(10),
        function () use ($series) {

            $torrents = Torrent::where('tmdbid', $series->tmdb_id)
                ->latest()
                ->get();

            return $torrents->groupBy(function ($torrent) {

                $season = 'Unknown';
                $name = $torrent->name;

                if (preg_match('/S(\d{1,2})E\d{1,2}/i', $name, $matches)) {
                    $season = (int) $matches[1];
                } elseif (preg_match('/(\d{1,2})x\d{1,2}/i', $name, $matches)) {
                    $season = (int) $matches[1];
                } elseif (preg_match('/S(\d{1,2})(?!E)/i', $name, $matches)) {
                    $season = (int) $matches[1];
                }

                return "Season {$season}";
            });
        }
    );

    return view('series.show', compact(
        'seriesDetails',
        'seriesOm',
        'series',
        'TvMaze',
        'tvMazeSeasons',
        'tvMazeEpisodes',
        'comments',
        'groupedTorrents'
    ));
}




 public function create()
{
    $featured = Series::whereNotNull('backdrop_path')
        ->inRandomOrder()
        ->first();

    return view('series.create', compact('featured'));
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

    $series = Series::where('name', 'LIKE', "%{$searchTerm}%")
        ->paginate(12);

    $featured = Series::whereNotNull('backdrop_path')
        ->inRandomOrder()
        ->first();

    return view('series.index', compact('series', 'featured'));
}

    public function destroy($id)
{
    try {
        $series = Series::findOrFail($id);

        $series->delete();

        return redirect()->route('series.index')->with('status', 'Series deleted successfully!');
    } catch (\Exception $e) {
        Log::error("Error deleting series: " . $e->getMessage());
        return redirect()->route('series.index')->with('error', 'Error deleting series!');
    }
}

public function syncTrailer($id)
{
    $serie = Series::findOrFail($id);

    $response = Http::get("https://api.themoviedb.org/3/tv/{$serie->tmdb_id}/videos", [
        'api_key' => config('services.tmdb.key')
    ]);

    $trailer = collect($response['results'])
        ->firstWhere('type', 'Trailer');

    if($trailer){
        $serie->update(['trailer_key' => $trailer['key']]);
    }

    return response()->json(['success'=>true]);
}


public function liveSearch(Request $request)
{
    $q = $request->get('q');

    if (!$q) {
        return response()->json([]);
    }

    $results = Series::where('name', 'LIKE', "%{$q}%")
        ->limit(8)
        ->get(['id', 'name', 'slug']);

    return response()->json($results);
}

}
