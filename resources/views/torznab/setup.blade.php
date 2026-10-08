@extends('layouts.app')

@section('title', 'Connect FileIplay to Media Apps')

@section('content')
<div class="card my-4 mx-auto" style="max-width: 900px;">
    <div class="card-body p-4 p-md-5">

        <h1 class="h3 mb-2">Connect your media apps</h1>

        <p class="text-muted mb-4">
            Connect FileIplay to Prowlarr, Radarr, or Sonarr using Torznab.
            Searches and torrent downloads will use your FileIplay account.
        </p>

        {{-- Connection details --}}
        <h2 class="h5 mb-3">Your FileIplay connection details</h2>

        <div class="mb-3">
            <label class="form-label" for="torznab-prowlarr">
                Prowlarr URL
            </label>

            <input
                id="torznab-prowlarr"
                class="form-control"
                readonly
                value="{{ url('/torznab') }}"
            >

            <div class="form-text">
                Use this URL when adding FileIplay as a Generic Torznab indexer in Prowlarr.
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="torznab-direct">
                Radarr / Sonarr URL
            </label>

            <input
                id="torznab-direct"
                class="form-control"
                readonly
                value="{{ url('/torznab') }}"
            >

            <div class="form-text">
                Use this URL when adding FileIplay directly as a custom Torznab indexer.
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label" for="torznab-key">
                Your integration API key
            </label>

            <input
                id="torznab-key"
                type="password"
                class="form-control"
                readonly
                value="{{ $apiKey }}"
                autocomplete="off"
            >
        </div>

        <button
            type="button"
            class="btn btn-outline-secondary btn-sm mb-2"
            onclick="
                const input = document.getElementById('torznab-key');
                input.type = input.type === 'password' ? 'text' : 'password';
                this.textContent = input.type === 'password'
                    ? 'Show API key'
                    : 'Hide API key';
            "
        >
            Show API key
        </button>

        <p class="small text-muted mb-4">
            Keep this key private. Resetting your FileIplay account passkey
            revokes this key and any previously generated download links.
        </p>

        <hr class="my-4">

        {{-- Prowlarr --}}
        <h2 class="h4 mb-3">Prowlarr</h2>

        <ol class="mb-4">
            <li class="mb-2">
                Open
                <strong>Settings → Indexers → Add Indexer → Generic Torznab</strong>.
            </li>

            <li class="mb-2">
                Set the name to <strong>FileIplay</strong>.
            </li>

            <li class="mb-2">
                For the URL use:
                <code>{{ url('/torznab') }}</code>
            </li>

            <li class="mb-2">
                Paste your FileIplay integration API key into the
                <strong>API Key</strong> field.
            </li>

            <li class="mb-2">
                Select the categories you want to use.
                FileIplay uses <strong>2000 for Movies</strong> and
                <strong>5000 for TV</strong>.
            </li>

            <li>
                Click <strong>Test</strong>. If the test succeeds,
                click <strong>Save</strong>.
            </li>
        </ol>

        <div class="alert alert-info">
            <strong>Important:</strong>
            Do not add <code>/api</code> to the Prowlarr URL.
            Prowlarr automatically uses the Torznab API endpoint.
        </div>

        <hr class="my-4">

        {{-- Radarr --}}
        <h2 class="h4 mb-3">Radarr</h2>

        <p>
            FileIplay can also be connected directly to Radarr without Prowlarr.
        </p>

        <ol class="mb-4">
            <li class="mb-2">
                Open
                <strong>Settings → Indexers → Add Indexer → Torznab → Custom</strong>.
            </li>

            <li class="mb-2">
                Name the indexer <strong>FileIplay</strong>.
            </li>

            <li class="mb-2">
                Set the URL to:
                <code>{{ url('/torznab') }}</code>
            </li>

            <li class="mb-2">
                Paste your FileIplay integration API key.
            </li>

            <li class="mb-2">
                Enable
                <strong>RSS</strong>,
                <strong>Automatic Search</strong>, and
                <strong>Interactive Search</strong>.
            </li>

            <li class="mb-2">
                Select the <strong>Movies</strong> category
                (Torznab category <strong>2000</strong>).
            </li>

            <li>
                Click <strong>Test</strong> and then <strong>Save</strong>.
            </li>
        </ol>

        <hr class="my-4">

        {{-- Sonarr --}}
        <h2 class="h4 mb-3">Sonarr</h2>

        <p>
            FileIplay can be connected directly to Sonarr in the same way.
        </p>

        <ol class="mb-4">
            <li class="mb-2">
                Open
                <strong>Settings → Indexers → Add Indexer → Torznab → Custom</strong>.
            </li>

            <li class="mb-2">
                Name the indexer <strong>FileIplay</strong>.
            </li>

            <li class="mb-2">
                Set the URL to:
                <code>{{ url('/torznab') }}</code>
            </li>

            <li class="mb-2">
                Paste your FileIplay integration API key.
            </li>

            <li class="mb-2">
                Enable
                <strong>RSS</strong>,
                <strong>Automatic Search</strong>, and
                <strong>Interactive Search</strong>.
            </li>

            <li class="mb-2">
                Select the <strong>TV</strong> category
                (Torznab category <strong>5000</strong>).
            </li>

            <li>
                Click <strong>Test</strong> and then <strong>Save</strong>.
            </li>
        </ol>

        <hr class="my-4">

        {{-- Download client --}}
        <h2 class="h4 mb-3">Download client</h2>

        <p>
            Radarr and Sonarr also require a torrent download client such as
            <strong>rTorrent</strong>, <strong>qBittorrent</strong>,
            <strong>Deluge</strong>, or another supported client.
        </p>

        <p>
            Configure your download client under
            <strong>Settings → Download Clients</strong>
            in Radarr or Sonarr.
        </p>

        <div class="alert alert-warning mb-0">
            <strong>Private tracker rules still apply.</strong>
            Using FileIplay through Prowlarr, Radarr, or Sonarr does not bypass
            your account permissions, download restrictions, ratio requirements,
            or seeding requirements. Keep completed torrents seeding according
            to FileIplay rules.
        </div>

    </div>
</div>
@endsection
