@extends('layouts.app')
@section('title', 'API & Upload Automation')
@include('profile.api.partials.styles')
@section('content')
<div class="api-settings-page">
    @include('profile.api.partials.navigation')
    <section class="card mb-4" aria-labelledby="api-settings-heading" style="color: var(--theme-text, #e5edf7)">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-3 border p-3 text-info d-none d-sm-inline-flex"><i class="bi bi-key fs-3" aria-hidden="true"></i></span>
                    <div>
                        <p class="api-header-label text-info small mb-2">ACCOUNT SETTINGS</p>
                        <h1 class="h3 mb-2" id="api-settings-heading">API &amp; upload automation</h1>
                        <p class="mb-0" style="color: var(--theme-muted, #9caec4)">Connect trusted scripts to your FileIPlay account.</p>
                    </div>
                </div>
                <a class="btn btn-outline-info" href="{{ route('profile.api.documentation') }}"><i class="bi bi-book me-1" aria-hidden="true"></i> How it works</a>
            </div>
            @if(in_array('torrents:upload', $scopes))
            <div class="border-top mt-4 pt-3 d-flex align-items-start gap-2">
                <i class="bi bi-shield-lock text-info mt-1" aria-hidden="true"></i>
                <p class="mb-0">Uploads default to <strong>{{ $user->anonymous ? 'Anonymous' : 'showing your uploader name' }}</strong>. Override this per upload with <code>anon</code>, or <a href="{{ route('profile.edit', [$user->id, $user->name]) }}#anonymous-default">change your profile preference</a>.</p>
            </div>
            @endif
        </div>
    </section>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($newToken)
    <div class="alert alert-success" role="status">
        <h2 class="h5">{{ $revealedToken ? 'Your personal token' : 'Your new token is ready' }}</h2><p>Copy this token into your script. You can view it again here by confirming your account password.</p>
        <label for="new-api-token" class="form-label">Personal API token</label>
        <input id="new-api-token" class="form-control font-monospace" type="password" readonly autocomplete="off" value="{{ $newToken }}">
        <button class="btn btn-outline-success btn-sm mt-2" type="button" data-copy-api-token>Copy token</button>
        <button class="btn btn-outline-secondary btn-sm mt-2" type="button" data-reveal-api-token aria-controls="new-api-token" aria-pressed="false">Show token</button>
        <span class="small ms-2" data-copy-status aria-live="polite"></span>
    </div>
    @endif
    <div class="row g-4">
        <div class="col-lg-4"><section class="card h-100"><div class="card-body p-4">
            <h2 class="h5">Create a personal token</h2><p class="text-muted small">Use a separate token for each script. Choose only the permissions it needs.</p>
            <form method="post" action="{{ route('profile.api.store') }}">@csrf
                <div class="mb-3"><label class="form-label" for="api-token-name">Token name</label><input class="form-control" id="api-token-name" name="name" required maxlength="80" value="{{ old('name') }}" placeholder="Home uploader"></div>
                <fieldset class="mb-3"><legend class="fs-6">Permissions</legend>
                @foreach($scopes as $scope)
                    <div class="form-check mb-2"><input class="form-check-input" id="scope-{{ $loop->index }}" type="checkbox" name="scopes[]" value="{{ $scope }}" @checked(in_array($scope, old('scopes', ['torrents:read'])))>
                    <label class="form-check-label" for="scope-{{ $loop->index }}">{{ $scope === 'torrents:upload' ? 'Upload torrents' : 'Read torrent metadata and categories' }}<span class="d-block text-muted small font-monospace">{{ $scope }}</span></label></div>
                @endforeach
                </fieldset>
                <div class="mb-3"><label for="api-expiry" class="form-label">Expires after</label><select class="form-select" id="api-expiry" name="expires_days">@foreach([30,90,365] as $days)<option value="{{ $days }}" @selected((int)old('expires_days',90) === $days)>{{ $days }} days</option>@endforeach</select></div>
                <button class="btn btn-info w-100" type="submit">Generate token</button>
            </form>
            @unless(in_array('torrents:upload',$scopes))<p class="small text-muted mt-3 mb-0">Upload tokens require uploader permission. You can continue to use the website as usual.</p>@endunless
        </div></section></div>
        <div class="col-lg-8"><section class="card"><div class="card-body p-4">
            <h2 class="h5">Your personal tokens <span class="text-muted">({{ $tokens->count() }})</span></h2>
            <p class="small text-muted">Use View token and confirm your password to reveal a saved token. Older tokens without an encrypted copy must be replaced.</p>
            @if($tokens->isEmpty())<div class="py-4 text-center"><i class="bi bi-key fs-1 text-info" aria-hidden="true"></i><p class="mt-2 mb-0">No personal API tokens yet.</p><p class="small text-muted">Create one when you are ready to connect a script.</p></div>
            @else
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Token</th><th>Created / expires</th><th>Last used</th><th><span class="visually-hidden">Actions</span></th></tr></thead><tbody>
            @foreach($tokens as $token)<tr><td><strong>{{ $token->name }}</strong><div class="small text-muted">{{ implode(', ', $token->abilities ?? []) }}</div></td>
            <td class="small">{{ $token->created_at->format('d M Y') }}<div class="text-muted">{{ $token->expires_at?->format('d M Y') ?? 'No expiry' }}</div></td><td class="small">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
            <td>
                @if($token->can_reveal && (! $token->expires_at || $token->expires_at->isFuture()))
                <details class="mb-2"><summary class="text-info">View token</summary>
                    <form class="mt-2" method="post" action="{{ route('profile.api.reveal', $token->id) }}">@csrf
                        <label class="form-label small" for="token-password-{{ $token->id }}">Confirm your account password</label>
                        <input class="form-control form-control-sm mb-2" type="password" name="password" id="token-password-{{ $token->id }}" autocomplete="current-password" maxlength="1024" required>
                        <button class="btn btn-outline-info btn-sm" type="submit">Reveal token</button>
                    </form>
                </details>
                @elseif(! $token->can_reveal)<span class="d-block small text-muted mb-2">One-time token; replace to enable viewing.</span>
                @else<span class="d-block small text-muted mb-2">Expired</span>@endif
                <form method="post" action="{{ route('profile.api.destroy',$token->id) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Revoke {{ $token->name }}">Revoke</button></form></td></tr>@endforeach
            </tbody></table></div>
            <details class="mt-3"><summary class="text-danger">Revoke all personal tokens</summary><p class="small mt-2">Every connected uploader script will lose access immediately.</p>
            <form method="post" action="{{ route('profile.api.destroy-all') }}">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Revoke all tokens</button></form></details>
            @endif
        </div></section>
        <section class="card mt-3"><div class="card-body p-4"><h2 class="h6">Get started in three steps</h2><ol class="small mb-2"><li>Create a token with upload permission.</li><li>Send a torrent file and its details to the upload endpoint.</li><li>Download the published torrent from its FileIPlay page and seed it.</li></ol>
        <code class="d-block text-break">POST {{ url('/api/v1/torrents') }}</code><a class="small d-inline-block mt-2" href="{{ route('profile.api.documentation') }}">View examples and field reference →</a></div></section>
        </div>
    </div>
    <a class="btn btn-outline-secondary mt-4" href="{{ route('profile.edit', [$user->id,$user->name]) }}">← Back to profile settings</a>
</div>
@endsection
@push('scripts')<script src="{{ asset('js/upload-api-settings.js') }}" defer></script>@endpush
