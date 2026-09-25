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
        $featured = Movie::whereNotNull('backdrop_path')
    ->where('views', '>', 0)
    ->orderByRaw('RAND() * views DESC')
    ->first()
    ?? Movie::whereNotNull('backdrop_path')
        ->inRandomOrder()
        ->first();

        return view('movies.index', compact('movies', 'featured', 'sort'))
            ->with('links', 'vendor.pagination.bootstrap-5');
    }

    public function search(Request $request)
    {
        $request->validate(['movie_name' => 'required|string|max:255']);

        $query = trim($request->movie_name);

        $data = $this->tmdb('search/movie', ['query' => $query, 'include_adult' => false]);
        $movies = collect($data['results'] ?? []);

        // Partition search results into those already in our DB and those we can add.
        $dbIds = Movie::whereIn('tmdb_id', $movies->pluck('id'))->pluck('tmdb_id')->flip();
        $existingMovies = $movies->filter(fn($m) => isset($dbIds[$m['id']]))->values();
        $newMovies     = $movies->filter(fn($m) => !isset($dbIds[$m['id']]))->values();

        // The /search/movie endpoint does not include an imdb_id, so fetch it
        // per candidate. Titles that genuinely have no IMDb ID are hidden from
        // the "New Movies to Add" list so they can never be added.
        $noImdbCount = 0;
        $newMovies = $newMovies->filter(function ($m) use (&$noImdbCount) {
            if (empty($m['id'])) {
                $noImdbCount++;
                return false;
            }
            $ext = $this->tmdb("movie/{$m['id']}/external_ids");
            $imdb = $ext['imdb_id'] ?? null;
            if (empty($imdb)) {
                $noImdbCount++;
                return false;
            }
            return true;
        })->values();

        // Use the best search hit as the anchor for similar + recommended rows.
        $similarMovies     = collect();
        $recommendedMovies = collect();
        $reference = $movies
            ->filter(fn($m) => !empty($m['poster_path']))
            ->first() ?? $movies->first();

        if ($reference && !empty($reference['id'])) {
            $refId = $reference['id'];

            $similarData = $this->tmdb("movie/{$refId}/similar", ['include_adult' => false]);
            $recomData   = $this->tmdb("movie/{$refId}/recommendations", ['include_adult' => false]);

            $similarMovies     = collect($similarData['results'] ?? [])->take(10)->values();
            $recommendedMovies = collect($recomData['results'] ?? [])->take(10)->values();

            // Batch-mark DB status so we don't query per row.
            $relatedIds = $similarMovies->pluck('id')->merge($recommendedMovies->pluck('id'))->unique();
            $relatedDb  = Movie::whereIn('tmdb_id', $relatedIds)->pluck('tmdb_id')->flip();

            $similarMovies     = $similarMovies->map(fn($m) => ['movie' => $m, 'exists' => isset($relatedDb[$m['id']])]);
            $recommendedMovies = $recommendedMovies->map(fn($m) => ['movie' => $m, 'exists' => isset($relatedDb[$m['id']])]);
        }

        return view('movies.search_results', compact(
            'query', 'movies', 'existingMovies', 'newMovies', 'similarMovies', 'recommendedMovies', 'noImdbCount'
        ));
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

      $similarData = cache()->remember(
    'movie_similar_v3_' . $movie->tmdb_id,
    now()->addWeek(),
    function () use ($movie) {
        $data = $this->tmdb("movie/{$movie->tmdb_id}/similar");

        return collect($data['results'] ?? [])
            ->map(fn ($item) => [
                'tmdb_id' => $item['id'] ?? null,
                'name'    => $item['title'] ?? 'Unknown',
                'poster'  => !empty($item['poster_path'])
                    ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path']
                    : null,
                'year'    => !empty($item['release_date'])
                    ? substr($item['release_date'], 0, 4)
                    : null,
                'rating'  => number_format($item['vote_average'] ?? 0, 1),
            ])
            ->values();
    }
);

/*
|--------------------------------------------------------------------------
| Filter and randomise AFTER the cached TMDB data
|--------------------------------------------------------------------------
*/
$similar = $similarData
    ->filter(function ($item) {
        return !empty($item['poster'])
            && !empty($item['year'])
            && (int) $item['year'] >= 1990;
    })
    ->shuffle()
    ->take(10)
    ->values();

        // Resolve whether each similar movie already exists in the DB (fresh lookup,
        // not cached, so newly added movies show up as linkable immediately).
        $existingSim = \App\Models\Movie::whereIn('tmdb_id', $similar->pluck('tmdb_id')->filter())
            ->get()
            ->keyBy('tmdb_id');

        $similar = $similar->map(function ($item) use ($existingSim) {
            $dbMovie = ($item['tmdb_id'] ?? null) ? $existingSim->get($item['tmdb_id']) : null;
            $item['in_library'] = (bool) $dbMovie;
            $item['db_url'] = $dbMovie
                ? route('movies.show', [$dbMovie->id, $dbMovie->slug])
                : null;
            return $item;
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

        $added = 0;
        foreach ($request->movies as $tmdb_id) {
            if (Movie::where('tmdb_id', $tmdb_id)->exists()) {
                continue;
            }
            $data = $this->tmdb("movie/{$tmdb_id}", ['append_to_response' => 'external_ids']);
            if (!$data || empty($data['id'])) {
                continue;
            }
            // Never add titles without an IMDb ID.
            $imdb = $data['imdb_id'] ?? $data['external_ids']['imdb_id'] ?? null;
            if (empty($imdb)) {
                continue;
            }
            $this->createMovie($data);
            $added++;
        }

        return redirect()->route('movies.index')->with(
            'status',
            $added > 0 ? "{$added} movie(s) added successfully!" : 'No movies were added (titles missing an IMDb ID were skipped).'
        );
    }

    /**
     * Delete a movie from the online catalogue (admins only, from the show page).
     */
    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->user_class < \App\Models\UserClass::ADMIN) {
            return redirect()->route('movies.index')->with('error', 'Unauthorized.');
        }

        $movie = Movie::findOrFail($id);

        // Clear dependent records so nothing is orphaned.
        $movie->comments()->delete();
        $movie->torrents()->delete();
        \App\Models\TorrentMovie::where('tmdbid', $movie->tmdb_id)->delete();

        $movie->delete();

        return redirect()->route('movies.index')->with('status', "Movie \"{$movie->name}\" deleted successfully.");
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

        $movie = Movie::create([
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

        // Also sync to the torrent library so it shows in the library section
        $this->syncToTorrentLibrary($movie);

        return $movie;
    }

    /**
     * Create or update a TorrentMovie entry so the library stays in sync
     * with the online movie catalogue.
     */
    private function syncToTorrentLibrary(Movie $movie): void
    {
        \App\Models\TorrentMovie::updateOrCreate(
            ['tmdbid' => $movie->tmdb_id],
            [
                'title'         => $movie->name,
                'slug'          => $movie->slug,
                'poster_path'   => $movie->poster_path,
                'backdrop_path' => $movie->backdrop_path,
                'rating'        => $movie->vote_average,
                'year'          => $movie->release_date ? $movie->release_date->format('Y') : null,
            ]
        );
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
