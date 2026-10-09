<?php

namespace App\Http\Controllers;

use App\Jobs\PublishSeedboxTorrent;
use App\Models\Category;
use App\Models\Seedbox;
use App\Models\SeedboxPublication;
use App\Models\User;
use App\Services\SeedboxPublishingClients;
use App\Services\SeedboxPublishingReadiness;
use App\Services\Torrent\TorrentAnonymity;
use App\Services\Torrent\UploadPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeedboxPublicationController extends Controller
{
    public function index(Request $request, SeedboxPublishingClients $clients)
    {
        app(UploadPermission::class)->authorize($request->user());
        $boxes = Seedbox::where('user_id', $request->user()->id)->get(['id', 'name']);
        $selected = null;
        $candidates = [];
        $providerError = null;
        if ($request->filled('seedbox_id')) {
            $request->validate(['seedbox_id' => ['integer']]);
            $selected = Seedbox::where('user_id', $request->user()->id)->findOrFail($request->integer('seedbox_id'));
            try {
                $candidates = array_slice($clients->forSeedbox($selected)->candidates(), 0, 200, true);
            } catch (\Throwable $e) {
                $providerError = 'This provider cannot currently offer eligible content for safe publishing.';
            }
        }

        return response()->view('profile.api.publishing', [
            'boxes' => $boxes, 'selected' => $selected, 'candidates' => $candidates, 'providerError' => $providerError,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'operations' => SeedboxPublication::where('user_id', $request->user()->id)->with(['seedbox:id,name', 'torrent:id,slug,name'])->latest()->paginate(20),
            'queueReady' => app(SeedboxPublishingReadiness::class)->ready(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        app(UploadPermission::class)->authorize($request->user());
        abort_unless(app(SeedboxPublishingReadiness::class)->ready(), 422, 'Seedbox publishing is unavailable. Contact staff to enable it after background processing checks.');
        $data = $request->validate([
            'seedbox_id' => ['required', 'integer'], 'source_hash' => ['required', 'regex:/^[a-fA-F0-9]{40}$/D'],
            'name' => ['nullable', 'string', 'max:255'], 'description' => ['required', 'string', 'max:100000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tmdbid' => ['nullable', 'integer', 'min:1', 'max:2147483647'], 'tmdb_type' => ['required_with:tmdbid', 'nullable', 'in:movie,tv'],
            'tvdbid' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'anon' => ['sometimes', 'boolean'],
            'register' => ['sometimes', 'boolean'], 'authorize_publish' => ['accepted'],
        ]);
        // Freeze the user's explicit/default choice when publication is authorized.
        $data['anon'] = app(TorrentAnonymity::class)->resolve($request, $request->user());
        $box = Seedbox::where('user_id', $request->user()->id)->findOrFail($data['seedbox_id']);
        $operation = DB::transaction(function () use ($data, $box, $request) {
            // Serialize publication requests for this owner, including first creation.
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $operation = SeedboxPublication::firstOrCreate([
                'user_id' => $request->user()->id, 'seedbox_id' => $box->id, 'source_hash' => strtolower($data['source_hash']),
            ], ['metadata' => array_intersect_key($data, array_flip(['name', 'description', 'category_id', 'tmdbid', 'tmdb_type', 'tvdbid', 'anon'])),
                'register' => $request->boolean('register'), 'status' => 'queued']);
            if ($operation->wasRecentlyCreated) {
                PublishSeedboxTorrent::dispatch($operation->id);
            }

            return $operation;
        });

        return redirect()->route('profile.api.publishing', ['seedbox_id' => $box->id])->with('success', 'Publication request #'.$operation->id.' is '.$operation->status.'.');
    }

    public function retry(Request $request, int $publication)
    {
        app(UploadPermission::class)->authorize($request->user());
        abort_unless(app(SeedboxPublishingReadiness::class)->ready(), 422, 'Seedbox publishing is unavailable. Contact staff to enable it after background processing checks.');
        DB::transaction(function () use ($request, $publication) {
            $operation = SeedboxPublication::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($publication);
            abort_unless(in_array($operation->status, ['failed', 'registration_failed', 'manual_seeding'], true), 409, 'This request cannot be retried now.');
            abort_unless(Seedbox::where('user_id', $request->user()->id)->whereKey($operation->seedbox_id)->exists(), 403);
            $operation->update(['status' => 'queued', 'error_code' => null]);
            PublishSeedboxTorrent::dispatch($operation->id);
        });

        return redirect()->route('profile.api.publishing')->with('success', 'Publication retry queued.');
    }
}
