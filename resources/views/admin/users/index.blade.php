@extends('layouts.app')

@section('content')
<div class="mt-4">
    <div class="row">

        <!-- Left Column: Users Table -->
        <div class="col-md-8 col-lg-9 mb-4">

            <h1>Manage Users</h1>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search users by name or email" value="{{ request()->input('search') }}">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td><a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}">{{ $user->name }}</a></td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role_name }}</td> <!-- Assuming there's a role column or method -->
                                    <td>
                                        <!-- Show Button -->
                                        <a href="{{ route('admin.users.show', $user->name) }}" class="btn btn-sm btn-info">Show</a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                    <!-- Pagination Links -->
                    <div class="mt-3">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: User Statistics & Mass Messaging -->
        <div class="col-md-4 col-lg-3">

            <!-- User Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">User Statistics</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Users created in the last 24 hours:
                            <span class="badge bg-success">{{ $last24Hours }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Users created in the last week:
                            <span class="badge bg-info">{{ $lastWeek }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Users created in the last month:
                            <span class="badge bg-warning">{{ $lastMonth }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Mass Message Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mass Message</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.sendMassMessage') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="user_class">User Classes</label>
                            <select name="user_class[]" id="user_class" class="form-control" multiple>
                                @foreach(App\Models\UserClass::getClasses() as $class => $name)
                                    <option value="{{ $class }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-4">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" class="form-control" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Send Message</button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
