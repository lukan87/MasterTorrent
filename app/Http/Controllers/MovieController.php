<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    private $apiKey;
    private $omdbKey;

    public function __construct()
    {
        // Config-driven keys (never hardcoded credentials in source).
        $this->apiKey  = config('services.tmdb.key') ?: config('app.tmdb_api_key');
        $this->omdbKey = config('services.omdb.key') ?: env('OMDB_API_KEY');
    }

    /**
     * Safe TMDB GET helper: returns decoded JSON or null (never throws).
     */
    private function tmdb(string $endpoint, array $params = [])
    {
        try {
            $params['api_key'] = $this->apiKey;
            $response = Http::timeout(10)->get("https://api.themoviedb.org/3/{$endpoint}", $params);
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error("TMDB request failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Safe OMDB GET helper for extra scores.
     */
    private function omdb(?string $imdb)
    {
        if (!$imdb) return [];
        try {
            $response = Http::timeout(10)->get("http://www.omdbapi.com", [
                'apikey' => $this->omdbKey,
                'i'      => $imdb,
                'plot'   => 'full',
            ]);
            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (\Exception $e) {
            Log::error("OMDB request failed: " . $e->getMessage());
            return [];
        }
    }

    public function index(Request $request)
    {
        $sort = $request->get('sort', 'latest');

        $query = Movie::query();
        switch ($sort) {
            case 'rating': $query->orderByDesc('vote_average'); break;
            case 'views':  $query->orderByDesc('views'); break;
            default:       $query->latest();
        }

        $movies  = $query->paginate(12)->withQueryString();
        $featured = Movie::whereNotNull('backdrop_path')->where('views', '>', 0)
            ->orderByDesc('views')->orderByDesc('vote_average')->first()
            ?? Movie::whereNotNull('backdrop_path')->inRandomOrder()->first();

        return view('movies.index', compact('movies', 'featured', 'sort'))
            ->with('links', 'vendor.pagination.bootstrap-5');
    }

    public function search(Request $request)
    {
        $request->validate(['movie_name' => 'required|string|max:255']);

        $data = $this->tmdb('search/movie', ['query' => $request->movie_name]);
        $movies = collect($data['results'] ?? []);

        return view('movies.search_results', compact('movies'));
    }

    public function selectMovie($tmdb_id)
    {
        if (Movie::where('tmdb_id', $tmdb_id)->exists()) {
            return redirect()->route('movies.create')->with('error', 'Movie already exists in the database.');
        }

        $data = $this->tmdb("movie/{$tmdb_id}", ['append_to_response' => 'external_ids']);
        if (!$data || empty($data['id'])) {
            return redirect()->route('movies.create')->with('error', 'Could not fetch this movie from TMDB.');
        }

        $this->createMovie($data);

        return redirect()->route('movies.show', $data['id'])->with('status', 'Movie added successfully!');
    }

    public function show($id, $slug = null)
    {
        $movie = Movie::findOrFail($id);

        if ($slug === null || $slug !== $movie->slug) {
            return redirect()->route('movies.show', ['id' => $id, 'slug' => $movie->slug]);
        }

        // Track a view once per session so refreshes don't inflate the counter.
        $viewKey = "movie_viewed_{$movie->id}";
        if (!session()->has($viewKey)) {
            session([$viewKey => true]);
            $movie->recordView();
        }

        $detailsCacheKey = 'movie_' . $movie->tmdb_id . '_details';
        $movieDetails = cache()->remember($detailsCacheKey, now()->addWeek(), function () use ($movie) {
            $data = $this->tmdb("movie/{$movie->tmdb_id}", [
                'language'            => 'en-US',
                'append_to_response' => 'credits,videos,images,external_ids',
            ]) ?: [];

            $data['title']              = $data['title'] ?? $movie->name ?? 'Unknown Movie';
            $data['genres']             = $data['genres'] ?? [];
            $data['videos']['results']  = $data['videos']['results'] ?? [];
            $data['credits']['cast']    = $data['credits']['cast'] ?? [];
            return $data;
        });

        $movieOm = cache()->remember('movie_' . $movie->imdb_id . '_omdb', now()->addWeek(), fn() => $this->omdb($movie->imdb_id));

        $similar = cache()->remember('movie_similar_' . $movie->tmdb_id, now()->addWeek(), function () use ($movie) {
            $data = $this->tmdb("movie/{$movie->tmdb_id}/similar");
            return collect($data['results'] ?? [])->take(10)->map(fn($item) => [
                'name'   => $item['title'] ?? 'Unknown',
                'poster' => ($item['poster_path'] ?? null)
                    ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path']
                    : '/images/noposter.jpg',
                'year'   => !empty($item['release_date'])
                    ? \Carbon\Carbon::parse($item['release_date'])->format('Y')
                    : null,
                'rating' => number_format($item['vote_average'] ?? 0, 1),
            ]);
        });

        $torrents = $movie->torrents()->latest()->get();
        $comments = $movie->comments()->with('user')->get();

        return view('movies.show', compact('movieDetails', 'movieOm', 'movie', 'comments', 'similar', 'torrents'));
    }

    public function create()
    {
        if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN) {
            return view('movies.create');
        }
        return redirect()->route('movies.index')->with('error', 'Unauthorized access.');
    }

    public function store(Request $request)
    {
        $request->validate(['tmdb_id' => 'required|string|max:255']);

        if (Movie::where('tmdb_id', $request->tmdb_id)->exists()) {
            return redirect()->route('movies.create')->with('error', 'Movie already exists in the database.');
        }

        $data = $this->tmdb("movie/{$request->tmdb_id}", [
            'language'            => 'en-US',
            'append_to_response' => 'credits,videos,images,keywords,external_ids',
        ]);

        if (!$data || empty($data['id'])) {
            return redirect()->route('movies.create')->with('error', 'Movie not found on TMDB. Please check the TMDB ID.');
        }

        $this->createMovie($data);

        return redirect()->route('movies.index')->with('status', 'Movie created successfully!');
    }

    public function bulkSelect(Request $request)
    {
        $request->validate(['movies' => 'required|array', 'movies.*' => 'integer|distinct']);

        foreach ($request->movies as $tmdb_id) {
            if (Movie::where('tmdb_id', $tmdb_id)->exists()) {
                continue;
            }
            $data = $this->tmdb("movie/{$tmdb_id}", ['append_to_response' => 'external_ids']);
            if ($data && !empty($data['id'])) {
                $this->createMovie($data);
            }
        }

        return redirect()->route('movies.index')->with('status', 'Operation was successful!');
    }

    public function searchMovie(Request $request)
    {
        $searchTerm = $request->input('name');
        if (empty($searchTerm)) {
            return redirect()->route('movies.index');
        }

        $movies = Movie::where('name', 'LIKE', "%{$searchTerm}%")->paginate(12)->withQueryString();
        $featured = $movies->first();
        $sort = 'latest';

        return view('movies.index', compact('movies', 'featured', 'sort'));
    }

    private function createMovie(array $data)
    {
        $uniqueName = $this->generateUniqueName($data['title'], $data['release_date'] ?? null);
        $genres = array_map(fn($g) => $g['name'] ?? '', $data['genres'] ?? []);

        return Movie::create([
            'name'            => $uniqueName,
            'tmdb_id'         => $data['id'],
            'imdb_id'         => $data['imdb_id'] ?? $data['external_ids']['imdb_id'] ?? null,
            'poster_path'     => $data['poster_path'] ?? null,
            'collection_id'   => $data['belongs_to_collection']['id'] ?? null,
            'collection_name' => $data['belongs_to_collection']['name'] ?? null,
            'overview'        => $data['overview'] ?? null,
            'backdrop_path'   => $data['backdrop_path'] ?? null,
            'release_date'    => !empty($data['release_date']) ? \Carbon\Carbon::parse($data['release_date'])->format('Y-m-d') : null,
            'runtime'         => $data['runtime'] ?? null,
            'vote_average'    => $data['vote_average'] ?? null,
            'vote_count'      => $data['vote_count'] ?? 0,
            'tagline'         => $data['tagline'] ?? null,
            'status'          => $data['status'] ?? null,
            'genres'          => array_values(array_filter($genres)),
            'slug'            => $this->generateUniqueSlug($uniqueName, $data['release_date'] ?? null),
        ]);
    }

    private function generateUniqueName($title, $releaseDate)
    {
        $year = $releaseDate ? \Carbon\Carbon::parse($releaseDate)->format('Y') : null;
        if ($year && Movie::where('name', $title)->exists()) {
            return $title . ' (' . $year . ')';
        }
        return $title;
    }

    private function generateUniqueSlug($title, $releaseDate)
    {
        $year = $releaseDate ? \Carbon\Carbon::parse($releaseDate)->format('Y') : null;
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Movie::where('slug', $slug)->exists()) {
            if ($counter === 1 && $year) {
                $slug = Str::slug($title . ' ' . $year);
                $originalSlug = $slug;
            } else {
                $slug = $originalSlug . '-' . $counter;
            }
            $counter++;
        }

        return $slug;
    }
}
