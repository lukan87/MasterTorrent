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

    public function __construct()
    {
        // Config-driven keys (never hardcoded credentials in source).
        $this->apiKey  = config('services.tmdb.key') ?: config('app.tmdb_api_key');
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

    public function index(Request $request)
    {
        return redirect()->route('library.movies.index', $request->only(['q', 'year', 'availability', 'sort']));
    }

    public function search(Request $request)
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
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

        return view('admin.library.import.movies.search_results', compact(
            'query', 'movies', 'existingMovies', 'newMovies', 'similarMovies', 'recommendedMovies', 'noImdbCount'
        ));
    }

    public function selectMovie($tmdb_id)
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
        if (Movie::where('tmdb_id', $tmdb_id)->exists()) {
            return redirect()->route('movies.create')->with('error', 'Movie already exists in the database.');
        }

        $data = $this->tmdb("movie/{$tmdb_id}", ['append_to_response' => 'external_ids']);
        if (!$data || empty($data['id'])) {
            return redirect()->route('movies.create')->with('error', 'Could not fetch this movie from TMDB.');
        }

        $this->createMovie($data);

        return redirect()->route('library.movies.show', $data['id'])->with('status', 'Movie added successfully!');
    }

    public function show($id, $slug = null)
    {
        $media = Movie::findOrFail($id);
        return redirect()->route('library.movies.show', [$media->tmdb_id, $media->slug]);
    }

    public function create()
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
        if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN) {
            return view('admin.library.import.movies.create');
        }
        return redirect()->route('library.movies.index')->with('error', 'Unauthorized access.');
    }

    public function store(Request $request)
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
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

        return redirect()->route('library.movies.index')->with('status', 'Movie created successfully!');
    }

    public function bulkSelect(Request $request)
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
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

        return redirect()->route('library.movies.index')->with(
            'status',
            $added > 0 ? "{$added} movie(s) added successfully!" : 'No movies were added (titles missing an IMDb ID were skipped).'
        );
    }

    /**
     * Delete a movie from the online catalogue (admins only, from the show page).
     */
    public function destroy($id)
    {
        abort_unless((Auth::user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
        Movie::findOrFail($id)->update(['online_enabled' => false]);
        return redirect()->route('admin.library.index', 'movies')->with('status', 'Online playback disabled.');
    }

    public function searchMovie(Request $request)
    {
        return redirect()->route('library.movies.index', ['q' => $request->input('name')]);
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
