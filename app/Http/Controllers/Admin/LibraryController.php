<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserClass;
use App\Services\LibraryCatalogueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{
    private function catalogue(string $kind): LibraryCatalogueService
    {
        abort_unless((auth()->user()?->user_class ?? 0) >= UserClass::ADMIN, 403);
        $service = app(LibraryCatalogueService::class);
        $service->models($kind);
        return $service;
    }

    public function index(Request $request, string $kind)
    {
        $service = $this->catalogue($kind);
        $request->validate(['q' => 'nullable|string|max:200']);
        $titles = $service->query($kind)->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->input('q').'%'))
            ->orderBy('title')->paginate(50)->withQueryString();
        [, $model] = $service->models($kind);
        $online = $model::whereIn('tmdb_id', $titles->pluck('tmdbid'))->get()->keyBy('tmdb_id');
        return view('admin.library.index', compact('titles', 'online', 'kind', 'service'));
    }

    public function create(string $kind)
    {
        $this->catalogue($kind);
        return view('admin.library.edit', ['kind' => $kind, 'title' => null, 'online' => null]);
    }

    public function edit(string $kind, int $tmdbid)
    {
        $service = $this->catalogue($kind);
        $title = $service->query($kind)->where('tmdbid', $tmdbid)->firstOrFail();
        [, $model] = $service->models($kind);
        $online = $model::where('tmdb_id', $tmdbid)->first();
        return view('admin.library.edit', compact('kind', 'title', 'online'));
    }

    public function store(Request $request, string $kind)
    {
        $this->catalogue($kind);
        $request->validate(['tmdbid' => 'required|integer|min:1']);
        abort_if(app(LibraryCatalogueService::class)->query($kind)->where('tmdbid', $request->integer('tmdbid'))->exists(), 409, 'This title already exists. Edit it instead.');
        return $this->save($request, $kind, $request->integer('tmdbid'));
    }

    public function update(Request $request, string $kind, int $tmdbid)
    {
        $this->catalogue($kind)->query($kind)->where('tmdbid', $tmdbid)->firstOrFail();
        return $this->save($request, $kind, $tmdbid);
    }

    private function save(Request $request, string $kind, int $tmdbid)
    {
        [$library, $model] = $this->catalogue($kind)->models($kind);
        $existing = $model::where('tmdb_id', $tmdbid)->first();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'nullable|integer|between:1800,2200',
            'rating' => 'nullable|numeric|between:0,10',
            'poster_path' => ['nullable', 'string', 'max:255', 'regex:~^/[a-zA-Z0-9_.-]+$~'],
            'backdrop_path' => ['nullable', 'string', 'max:255', 'regex:~^/[a-zA-Z0-9_.-]+$~'],
            'overview' => 'nullable|string|max:50000',
            'online_enabled' => 'required|boolean',
            'imdb_id' => ['nullable', 'required_if:online_enabled,1', 'regex:/^tt[0-9]+$/D', Rule::unique((new $model)->getTable(), 'imdb_id')->ignore($existing?->id)],
        ]);
        DB::transaction(function () use ($library, $model, $existing, $data, $tmdbid, $kind) {
            $library::updateOrCreate(['tmdbid' => $tmdbid], [
                'title' => $data['title'], 'slug' => Str::slug($data['title']),
                'year' => $data['year'] ?? null, 'rating' => $data['rating'] ?? null,
                'poster_path' => $data['poster_path'] ?? null, 'backdrop_path' => $data['backdrop_path'] ?? null,
            ]);
            $model::updateOrCreate(['tmdb_id' => $tmdbid], [
                'name' => $data['title'], 'imdb_id' => $data['imdb_id'] ?? $existing?->imdb_id,
                'online_enabled' => (bool) $data['online_enabled'],
                'poster_path' => $data['poster_path'] ?? '', 'backdrop_path' => $data['backdrop_path'] ?? null,
                'overview' => $data['overview'] ?? null, 'vote_average' => $data['rating'] ?? null,
                $kind === 'movies' ? 'release_date' : 'first_air_date' => !empty($data['year']) ? $data['year'].'-01-01' : null,
            ]);
        });
        return redirect()->route('admin.library.index', $kind)->with('status', 'Library title saved.');
    }
}
