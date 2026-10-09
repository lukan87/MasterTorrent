@extends('layouts.app')
@section('title','Seedbox Publishing')
@section('content')
<div class="mx-auto my-4" style="max-width:1080px">
    <a href="{{ route('profile.api.index') }}" class="btn btn-outline-info btn-sm mb-3">← API &amp; automation</a>
    <h1 class="h3">Publish from your seedbox</h1><p class="text-muted">Select completed content, review its details and explicitly request publication.</p>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<p class="mb-1">{{ $error }}</p>@endforeach</div>@endif
    @unless($queueReady)<div class="alert alert-warning">Background publishing is currently unavailable. Contact staff to enable it after seedbox and background processing checks. Manual uploads remain available.</div>@endunless
    <div class="card mb-4"><div class="card-body p-4">
        <form method="get" action="{{ route('profile.api.publishing') }}" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="flex-grow-1"><label class="form-label" for="publish-seedbox">Your configured seedbox</label><select class="form-select" id="publish-seedbox" name="seedbox_id" required><option value="">Select a seedbox</option>@foreach($boxes as $box)<option value="{{ $box->id }}" @selected($selected?->id === $box->id)>{{ $box->name }}</option>@endforeach</select></div><button type="submit" class="btn btn-outline-info">Load eligible content</button>
        </form>
        @if($providerError)<div class="alert alert-warning mt-3">{{ $providerError }}</div>@endif
        @if($selected && !$providerError)
            @if(!$candidates)<p class="text-muted mt-3">No completed, verified torrents are eligible on this connection.</p>
            @else
            <form method="post" action="{{ route('profile.api.publish') }}" class="mt-4">@csrf
                <input type="hidden" name="seedbox_id" value="{{ $selected->id }}">
                <div class="mb-3"><label class="form-label" for="publish-source">Completed torrent (first 200 eligible items)</label><select class="form-select" id="publish-source" name="source_hash" required>@foreach($candidates as $item)<option value="{{ $item['hash'] }}">{{ $item['name'] }}</option>@endforeach</select></div>
                <div class="row g-3"><div class="col-md-7"><label class="form-label" for="publish-name">Release title</label><input class="form-control" id="publish-name" name="name" maxlength="255" value="{{ old('name') }}" required></div>
                <div class="col-md-5"><label class="form-label" for="publish-category">Category</label><select class="form-select" id="publish-category" name="category_id" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int)old('category_id') === $category->id)>{{ $category->name }}</option>@endforeach</select></div></div>
                <div class="my-3"><label class="form-label" for="publish-description">Description</label><textarea class="form-control" id="publish-description" name="description" rows="4" required maxlength="100000">{{ old('description') }}</textarea></div>
                <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label" for="publish-tmdb">TMDB ID (optional)</label><input type="number" class="form-control" id="publish-tmdb" name="tmdbid" min="1" max="2147483647" value="{{ old('tmdbid') }}"></div><div class="col-md-6"><label class="form-label" for="publish-type">Metadata type</label><select class="form-select" id="publish-type" name="tmdb_type"><option value="movie">Movie</option><option value="tv">TV series</option></select></div></div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="anon" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="publish-anonymous" name="anon" value="1" @checked(old('anon', auth()->user()->anonymous))>
                    <label class="form-check-label" for="publish-anonymous">Publish anonymously</label>
                    <p class="form-text mb-0">Other members see Anonymous. You and moderators retain ownership access. This choice is saved with your publication request.</p>
                </div>
                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="publish-register" name="register" value="1"><label class="form-check-label" for="publish-register">Also register the published torrent and start seeding after client verification, where supported.</label></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="publish-consent" name="authorize_publish" value="1" required><label class="form-check-label" for="publish-consent">I authorize publishing this selected content to FileIPlay.</label></div>
                <button class="btn btn-info" type="submit" @disabled(!$queueReady)>Queue publication</button>
            </form>
            @endif
        @endif
        <p class="small text-muted mt-3 mb-0">Requires a supported public HTTPS ruTorrent connection and source plugin. Arbitrary remote file creation is unavailable. No payload files pass through FileIPlay, and existing client torrents are never stopped or removed.</p>
    </div></div>
    <div class="card"><div class="card-body p-4"><div class="d-flex justify-content-between gap-2"><h2 class="h5">Publication status</h2><a href="{{ request()->fullUrl() }}" class="btn btn-outline-secondary btn-sm">Refresh status</a></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Torrent / seedbox</th><th>Status</th><th>Requested (UTC)</th><th>Action</th></tr></thead><tbody>
        @forelse($operations as $operation)<tr><td>{{ $operation->name ?? 'Publication #'.$operation->id }}<div class="text-muted small">{{ $operation->seedbox?->name }}</div></td><td><span class="badge {{ match($operation->status) { 'seeding', 'published' => 'text-bg-success', 'failed', 'registration_failed' => 'text-bg-danger', 'manual_seeding', 'duplicate' => 'text-bg-warning', default => 'text-bg-info' } }}">{{ $operation->statusLabel() }}</span>@if($operation->errorMessage())<div class="small text-muted mt-1">{{ $operation->errorMessage() }}</div>@endif</td><td class="small">{{ $operation->created_at->utc()->format('d M Y H:i') }}</td><td>
        @if($operation->torrent)<a class="btn btn-outline-info btn-sm mb-1" href="{{ route('torrents.show',['id'=>$operation->torrent->id,'slug'=>$operation->torrent->slug]) }}">View torrent</a>@endif
        @if(in_array($operation->status,['failed','registration_failed','manual_seeding']))<form method="post" action="{{ route('profile.api.publish-retry',$operation->id) }}">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">Retry safely</button></form>@endif
        </td></tr>@empty<tr><td colspan="4" class="text-muted py-4 text-center">No publishing requests yet.</td></tr>@endforelse
        </tbody></table></div>{{ $operations->links() }}
    </div></div>
</div>
@endsection
