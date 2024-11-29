@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Edit User</h1>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


        <!-- Edit User Form -->
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-3">
    <label for="user_class" class="form-label">User Role</label>
    <select name="user_class" id="user_class" class="form-control">
        @foreach (App\Models\UserClass::getClasses() as $classValue => $className)
            <option value="{{ $classValue }}" {{ $user->user_class == $classValue ? 'selected' : '' }}>
                {{ $className }}
            </option>
        @endforeach
    </select>
</div>


            <div class="mb-3">
                <label for="profile_image" class="form-label">Profile Image URL</label>
                <input type="url" name="profile_image" id="profile_image" class="form-control" value="{{ old('profile_image', $user->profile_image) }}">
            </div>

            <div class="mb-3">
                <label for="info" class="form-label">User Info</label>
                <textarea name="info" id="info" class="form-control" rows="4">{{ old('info', $user->info) }}</textarea>
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to Users List</a>
        </form>
    </div>
@endsection
