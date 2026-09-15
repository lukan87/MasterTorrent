<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Torrent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    private $apiKey;
    private $omdbKey;

    public function __construct()
    {
        $this->apiKey  = config('services.tmdb.key') ?: config('app.tmdb_api_key');
        $this->omdbKey = config('services.omdb.key') ?: env('OMDB_API_KEY');
    }

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

        $query = Series::query();
        switch ($sort) {
            case 'rating': $query->orderByDesc('vote_average'); break;
            case 'views':  $query->orderByDesc('views'); break;
            default:       $query->latest();
        }

        $series = $query->paginate(12)->withQueryString();
        $featured = Series::whereNotNull('backdrop_path')->where('views', '>', 0)
            ->orderByDesc('views')->orderByDesc('vote_average')->first()
            ?? Series::whereNotNull('backdrop_path')->inRandomOrder()->first();

        return view('series.index', compact('series', 'featured', 'sort'))->with('links', 'vendor.pagination.bootstrap-5');
    }

    public function search(Request $request)
    {
        $request->validate(['series_name' => 'required|string|max:255']);

        $query = trim($request->series_name);

        $data = $this->tmdb('search/tv', ['query' => $query, 'include_adult' => false]);
        $series = collect($data['results'] ?? []);

        // Partition search results into those already in our DB and those we can add.
        $dbIds = Series::whereIn('tmdb_id', $series->pluck('id'))->pluck('tmdb_id')->flip();
        $existingSeries = $series->filter(fn($s) => isset($dbIds[$s['id']]))->values();
        $newSeries      = $series->filter(fn($s) => !isset($dbIds[$s['id']]))->values();

        // The /search/tv endpoint does not include an imdb_id, so fetch it
        // per candidate. Titles that genuinely have no IMDb ID are hidden from
        // the "New Series to Add" list so they can never be added.
        $noImdbCount = 0;
        $newSeries = $newSeries->filter(function ($s) use (&$noImdbCount) {
            if (empty($s['id'])) {
                $noImdbCount++;
                return false;
            }
            $ext = $this->tmdb("tv/{$s['id']}/external_ids");
            $imdb = $ext['imdb_id'] ?? null;
            if (empty($imdb)) {
                $noImdbCount++;
                return false;
            }
            return true;
        })->values();

        // Use the best search hit as the anchor for similar + recommended rows.
        $similarSeries     = collect();
        $recommendedSeries = collect();
        $reference = $series
            ->filter(fn($s) => !empty($s['poster_path']))
            ->first() ?? $series->first();

        if ($reference && !empty($reference['id'])) {
            $refId = $reference['id'];

            $similarData = $this->tmdb("tv/{$refId}/similar", ['include_adult' => false]);
            $recomData   = $this->tmdb("tv/{$refId}/recommendations", ['include_adult' => false]);

            $similarSeries     = collect($similarData['results'] ?? [])->take(10)->values();
            $recommendedSeries = collect($recomData['results'] ?? [])->take(10)->values();

            // Batch-mark DB status so we don't query per row.
            $relatedIds = $similarSeries->pluck('id')->merge($recommendedSeries->pluck('id'))->unique();
            $relatedDb  = Series::whereIn('tmdb_id', $relatedIds)->pluck('tmdb_id')->flip();

            $similarSeries     = $similarSeries->map(fn($s) => ['series' => $s, 'exists' => isset($relatedDb[$s['id']])]);
            $recommendedSeries = $recommendedSeries->map(fn($s) => ['series' => $s, 'exists' => isset($relatedDb[$s['id']])]);
        }

        return view('series.search_results', compact(
            'query', 'series', 'existingSeries', 'newSeries', 'similarSeries', 'recommendedSeries', 'noImdbCount'
        ));
    }

    public function selectSeries($tmdb_id)
    {
        if (Series::where('tmdb_id', $tmdb_id)->exists()) {
            return redirect()->route('series.create')->with('error', 'Series already exists in the database.');
        }

        $data = $this->tmdb("tv/{$tmdb_id}", ['append_to_response' => 'external_ids']);
        if (!$data || empty($data['id'])) {
            return redirect()->route('series.create')->with('error', 'Error fetching series details from TMDB API!');
        }

        $this->createSeries($data);

        return redirect()->route('series.show', $data['id'])->with('status', 'Series added successfully!');
    }

    public function show($id, $slug = null)
    {
        if (!$slug) {
            $routeSeries = Series::findOrFail($id);
            return redirect()->route('series.show', ['id' => $id, 'slug' => $routeSeries->slug]);
        }

        $series = Cache::remember("series_model_{$slug}", now()->addMinutes(30), fn() => Series::where('slug', $slug)->firstOrFail());

        $comments = Cache::remember("series_comments_{$series->id}", now()->addMinutes(5), fn() => $series->comments()->with('user')->get());

        $seriesDetails = Cache::remember("series_{$series->tmdb_id}_details", now()->addMonth(), function () use ($series) {
            return $this->tmdb("tv/{$series->tmdb_id}", [
                'language'            => 'en-US',
                'append_to_response' => 'credits,videos,images,keywords,external_ids',
            ]) ?: [];
        });

        $seriesOm = Cache::remember("series_{$series->imdb_id}_omdb", now()->addMonth(), fn() => $this->omdb($series->imdb_id));

        // TvMaze data (kept for episode availability).
        $TvMaze = Cache::remember("series_{$series->imdb_id}_tvmaze", now()->addMonth(), function () use ($series) {
            try {
                return Http::timeout(10)->get('http://api.tvmaze.com/lookup/shows', ['imdb' => $series->imdb_id])->json() ?: [];
            } catch (\Exception $e) {
                Log::error("TvMaze lookup failed: " . $e->getMessage());
                return [];
            }
        });

        $tvMazeSeasons = $tvMazeEpisodes = null;
        if (!empty($TvMaze['id'])) {
            $tvMazeSeasons  = Cache::remember("series_{$series->imdb_id}_tvmaze_seasons", now()->addMonth(), fn() => Http::timeout(10)->get("https://api.tvmaze.com/shows/{$TvMaze['id']}/seasons")->json());
            $tvMazeEpisodes = Cache::remember("series_{$series->imdb_id}_tvmaze_episodes", now()->addMonth(), fn() => Http::timeout(10)->get("https://api.tvmaze.com/shows/{$TvMaze['id']}/episodes")->json());
        }

        $groupedTorrents = Cache::remember("series_torrents_{$series->tmdb_id}", now()->addMinutes(10), function () use ($series) {
            $torrents = Torrent::where('tmdbid', $series->tmdb_id)->latest()->get();
            return $torrents->groupBy(function ($torrent) {
                $name = $torrent->name;
                $season = 'Unknown';
                if (preg_match('/S(\d{1,2})E\d{1,2}/i', $name, $m)) {
                    $season = (int) $m[1];
                } elseif (preg_match('/(\d{1,2})x\d{1,2}/i', $name, $m)) {
                    $season = (int) $m[1];
                } elseif (preg_match('/S(\d{1,2})(?!E)/i', $name, $m)) {
                    $season = (int) $m[1];
                }
                return "Season {$season}";
            });
        });

        // Similar series (TMDB).
        $similar = Cache::remember("series_similar_v2_{$series->tmdb_id}", now()->addMonth(), function () use ($series) {
            $data = $this->tmdb("tv/{$series->tmdb_id}/similar");
            return collect($data['results'] ?? [])->take(10)->map(fn($item) => [
                'tmdb_id' => $item['id'] ?? null,
                'name'   => $item['name'] ?? 'Unknown',
                'poster' => ($item['poster_path'] ?? null)
                    ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path']
                    : '/images/noposter.jpg',
                'year'   => !empty($item['first_air_date'])
                    ? \Carbon\Carbon::parse($item['first_air_date'])->format('Y')
                    : null,
                'rating' => number_format($item['vote_average'] ?? 0, 1),
            ]);
        });

        // Resolve whether each similar series already exists in the DB (fresh lookup,
        // not cached, so newly added titles become linkable immediately).
        $existingSim = \App\Models\Series::whereIn('tmdb_id', $similar->pluck('tmdb_id')->filter())
            ->get()
            ->keyBy('tmdb_id');

        $similar = $similar->map(function ($item) use ($existingSim) {
            $dbSeries = ($item['tmdb_id'] ?? null) ? $existingSim->get($item['tmdb_id']) : null;
            $item['in_library'] = (bool) $dbSeries;
            $item['db_url'] = $dbSeries
                ? route('series.show', [$dbSeries->id, $dbSeries->slug])
                : null;
            return $item;
        });

        // Track a view once per session.
        $viewKey = "series_viewed_{$series->id}";
        if (!session()->has($viewKey)) {
            session([$viewKey => true]);
            $series->recordView();
        }

        return view('series.show', compact(
            'seriesDetails', 'seriesOm', 'series', 'TvMaze', 'tvMazeSeasons',
            'tvMazeEpisodes', 'comments', 'groupedTorrents', 'similar'
        ));
    }

    public function create()
    {
        $featured = Series::whereNotNull('backdrop_path')->inRandomOrder()->first();
        return view('series.create', compact('featured'));
    }

    public function store(Request $request)
    {
        $request->validate(['tmdb_id' => 'required|string|max:255']);

        if (Series::where('tmdb_id', $request->tmdb_id)->exists()) {
            return redirect()->route('series.create')->with('error', 'Series already exists in the database.');
        }

        $data = $this->tmdb("tv/{$request->tmdb_id}", [
            'language'            => 'en-US',
            'append_to_response' => 'credits,videos,images,keywords,external_ids',
        ]);

        if (!$data || empty($data['id'])) {
            return redirect()->route('series.create')->with('error', 'Error fetching data from TMDB API!');
        }

        $this->createSeries($data);

        return redirect()->route('series.index')->with('status', 'Series created successfully!');
    }

    public function bulkSelect(Request $request)
    {
        $request->validate(['series' => 'required|array', 'series.*' => 'integer|distinct']);

        $added = 0;
        foreach ($request->series as $tmdb_id) {
            if (Series::where('tmdb_id', $tmdb_id)->exists()) {
                continue;
            }
            $data = $this->tmdb("tv/{$tmdb_id}", ['append_to_response' => 'external_ids']);
            if (!$data || empty($data['id'])) {
                continue;
            }
            // Never add titles without an IMDb ID.
            $imdb = $data['imdb_id'] ?? $data['external_ids']['imdb_id'] ?? null;
            if (empty($imdb)) {
                continue;
            }
            $this->createSeries($data);
            $added++;
        }

        return redirect()->route('series.index')->with(
            'status',
            $added > 0 ? "{$added} series added successfully!" : 'No series were added (titles missing an IMDb ID were skipped).'
        );
    }

    public function searchSeries(Request $request)
    {
        $searchTerm = $request->input('name');
        if (empty($searchTerm)) {
            return redirect()->route('series.index');
        }

        $series = Series::where('name', 'LIKE', "%{$searchTerm}%")->paginate(12)->withQueryString();
        $featured = $series->first();
        $sort = 'latest';

        return view('series.index', compact('series', 'featured', 'sort'));
    }

    /**
     * Delete a series from the online catalogue (admins only, from the show page).
     */
    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->user_class < \App\Models\UserClass::ADMIN) {
            return redirect()->route('series.index')->with('error', 'Unauthorized.');
        }

        try {
            $series = Series::findOrFail($id);

            // Clear dependent records so nothing is orphaned.
            $series->comments()->delete();
            $series->torrents()->delete();
            \App\Models\TorrentSeries::where('tmdbid', $series->tmdb_id)->delete();

            $series->delete();

            return redirect()->route('series.index')->with('status', "Series \"{$series->name}\" deleted successfully.");
        } catch (\Exception $e) {
            Log::error("Error deleting series: " . $e->getMessage());
            return redirect()->route('series.index')->with('error', 'Error deleting series!');
        }
    }

    public function syncTrailer($id)
    {
        $serie = Series::findOrFail($id);
        $data = $this->tmdb("tv/{$serie->tmdb_id}/videos");
        $trailer = collect($data['results'] ?? [])->firstWhere('type', 'Trailer');
        if ($trailer) {
            $serie->update(['trailer_key' => $trailer['key']]);
        }
        return response()->json(['success' => true]);
    }

    public function liveSearch(Request $request)
    {
        $q = $request->get('q');
        if (!$q) {
            return response()->json([]);
        }
        return response()->json(Series::where('name', 'LIKE', "%{$q}%")->limit(8)->get(['id', 'name', 'slug']));
    }

    private function createSeries(array $data)
    {
        $genres = array_map(fn($g) => $g['name'] ?? '', $data['genres'] ?? []);

        $series = Series::create([
            'name'               => $data['name'],
            'tmdb_id'            => $data['id'],
            'imdb_id'            => $data['imdb_id'] ?? $data['external_ids']['imdb_id'] ?? null,
            'poster_path'        => $data['poster_path'] ?? null,
            'overview'           => $data['overview'] ?? null,
            'backdrop_path'      => $data['backdrop_path'] ?? null,
            'first_air_date'     => !empty($data['first_air_date']) ? \Carbon\Carbon::parse($data['first_air_date'])->format('Y-m-d') : null,
            'number_of_seasons'  => $data['number_of_seasons'] ?? null,
            'number_of_episodes' => $data['number_of_episodes'] ?? null,
            'vote_average'       => $data['vote_average'] ?? null,
            'vote_count'         => $data['vote_count'] ?? 0,
            'tagline'            => $data['tagline'] ?? null,
            'status'             => $data['status'] ?? null,
            'genres'             => array_values(array_filter($genres)),
            'slug'               => $this->generateUniqueSlug($data['name']),
        ]);

        // Also sync to the torrent library so it shows in the library section
        $this->syncToTorrentLibrary($series);

        return $series;
    }

    /**
     * Create or update a TorrentSeries entry so the library stays in sync
     * with the online series catalogue.
     */
    private function syncToTorrentLibrary(Series $series): void
    {
        \App\Models\TorrentSeries::updateOrCreate(
            ['tmdbid' => $series->tmdb_id],
            [
                'title'         => $series->name,
                'slug'          => $series->slug,
                'poster_path'   => $series->poster_path,
                'backdrop_path' => $series->backdrop_path,
                'rating'        => $series->vote_average,
                'year'          => $series->first_air_date ? $series->first_air_date->format('Y') : null,
            ]
        );
    }

    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;
        while (Series::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        return $slug;
    }
}
