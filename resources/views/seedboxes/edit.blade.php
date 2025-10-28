@extends('layouts.app')

@section('content')
<h2>Edit Seedbox: {{ $seedbox->name }}</h2>

<form action="{{ route('seedboxes.update', $seedbox) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $seedbox->name) }}" required>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="url" name="address" id="address" class="form-control" value="{{ old('address', $seedbox->address) }}" required>
    </div>

    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $seedbox->username) }}" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="text" name="password" id="password" class="form-control" value="{{ old('password', $seedbox->password) }}" required>
    </div>

    <div class="mb-3">
        <label for="auth_type" class="form-label">Auth Type</label>
        <select name="auth_type" id="auth_type" class="form-select" required>
            <option value="basic" {{ $seedbox->auth_type === 'basic' ? 'selected' : '' }}>Basic</option>
            <option value="digest" {{ $seedbox->auth_type === 'digest' ? 'selected' : '' }}>Digest</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update Seedbox</button>
    <a href="{{ route('seedboxes.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
