@extends('layouts.app')

@section('content')
<h2 class="mb-4">
    <i class="bi bi-plus-circle-fill text-info"></i> Add New Seedbox
</h2>

<div class="card shadow-sm bg-dark text-white border-secondary">
    <div class="card-body">
        <form action="{{ route('seedboxes.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label"><i class="bi bi-hdd-network"></i> Name:</label>
                <input class="form-control form-control-dark" type="text" name="name" placeholder="E.g: My Seedbox" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label for="server" class="form-label"><i class="bi bi-server"></i> Server:</label>
                <input class="form-control form-control-dark" type="text" name="address" placeholder="E.g: https://my.ultraseedbox.net/plugins/httprpc/action.php or https://my.ultraseedbox.net/rutorrent/plugins/httprpc/action.php" value="{{ old('address') }}" required>
                <small class="form-text text-muted">
                    This must be the URL to your rTorrent RPC2 endpoint or ruTorrent RPC plugin (e.g., https://seedbox/path/to/rutorrent/plugins/httprpc/action.php).
                </small>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label"><i class="bi bi-person-circle"></i> Username:</label>
                <input class="form-control form-control-dark" type="text" name="username" value="{{ old('username') }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><i class="bi bi-key-fill"></i> Password:</label>
                <input class="form-control form-control-dark" type="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="auth" class="form-label"><i class="bi bi-shield-lock-fill"></i> Authentication:</label>
                <select class="form-select form-select-dark" name="auth_type" required>
                    <option value="basic" {{ old('auth_type') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="digest" {{ old('auth_type') === 'digest' ? 'selected' : '' }}>Digest</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle-fill"></i> Submit
            </button>
            <a href="{{ route('seedboxes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle"></i> Cancel
            </a>
        </form>

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

    </div>
</div>
@endsection
