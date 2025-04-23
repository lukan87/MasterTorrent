@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Admin Dashboard</h1>

        <div class="row">
        <!-- Total Torrents -->
        <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Total Torrents</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $totalTorrents }} torrents on site</p>
                        <a href="{{ route('admin.torrents.index') }}" class="btn btn-primary">Manage Torrents</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Total Users</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $totalUsers }} registered users</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
            </div>

            </div>

        <div class="row">
            <!-- Total Movies -->
            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Total Movies</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $totalMovies }} movies on site</p>
                        <a href="{{ route('admin.movies.index') }}" class="btn btn-primary">Manage Movies</a>
                    </div>
                </div>
            </div>

            <!-- Total Series -->
            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Total Series</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $totalSeries }} series on site</p>
                        <a href="{{ route('admin.series.index') }}" class="btn btn-primary">Manage Series</a>
                    </div>
                </div>
            </div>
        </div>
        @if (Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)
        <div class="row">

        <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5>System Info</h5>
                    </div>
                    <div class="card-body">
                    <a href="{{ route('admin.systemInfo.index') }}" class="btn btn-primary">View System Info</a>

                    </div>
                </div>
            </div>
        </div>

        @endif



    </div>
@endsection
