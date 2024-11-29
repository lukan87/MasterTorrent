@extends('layouts.app')

@section('title',  'Browse Torrents' )

@section('content')

    <h1 class="page-title">Torrent List</h1>


    <!-- Search Form -->
    <form action="{{ route('torrents.index') }}" method="GET" class="search-form mb-4">
        <div class="row g-3">
            <!-- Search Keyword with Autocomplete -->
            <div class="col-md-4 col-12">
                <label for="keyword" class="form-label">Keyword</label>
                <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Search Torrent by name, imdb url, tmdbid..." value="{{ request('keyword') }}">
            </div>

            <!-- Category Filter -->
            <div class="col-md-3 col-12">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Genre Filter -->
            <div class="col-md-2 col-12">
                <label for="genre" class="form-label">Genre</label>
                <select name="genre" id="genre" class="form-select">
                    <option value="">All Genres</option>
                    @foreach($allGenres as $genre)
                        <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-12">
    <label for="torrent_status" class="form-label">Torrent Status</label>
    <select name="torrent_status" id="torrent_status" class="form-control">

        <option value="active" {{ request('torrent_status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="dead" {{ request('torrent_status') == 'dead' ? 'selected' : '' }}>Dead (Seeders = 0)</option>
        <option value="free" {{ request('torrent_status') == 'free' ? 'selected' : '' }}>Free</option>
        <option value="double" {{ request('torrent_status') == 'double' ? 'selected' : '' }}>Double</option>
        <option value="seedbox" {{ request('torrent_status') == 'seedbox' ? 'selected' : '' }}>Seedbox</option>
    </select>
</div>


            <!-- Submit Button -->
            <div class="col-md-1 col-12 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    <!-- Torrent Table -->
    <div class="torrent-table-container">
        <div class="torrent-table-wrapper">
            <table class="table table-striped table-responsive">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Name</th>
                        <th></th>
                        <th><i class="bi bi-stopwatch"></i></th>
                        <th>
                            <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'size', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-pie-chart-fill"></i>
                                @if ($sortColumn == 'size')
                                    <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'seeders', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i>
                                @if ($sortColumn == 'seeders')
                                    <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'leechers', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i>
                                @if ($sortColumn == 'leechers')
                                    <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'times_completed', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i>
                                @if ($sortColumn == 'times_completed')
                                    <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                            <th>Uploader</th>
                        @endif
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse ($torrents as $torrent)
                        <tr>
                        <td><img src="{{ $torrent->category->image }}" style="width: 87px; height: 47px; border-radius: 0;"></td>
                            <td>
                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="torrent-name" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="<div class='card' style='width: 200px;'>
                                    <img src='{{ $torrent->poster }}' class='img-fluid rounded' alt='Poster Image' style='width: 150px; height: auto;' />
                                  </div>">
                                  {{ \Illuminate\Support\Str::limit($torrent->name, 75, ' ...') }}

                                </a>
                                @include('torrents.partials.tags')
                                <!-- Display Genres allocated to this torrent with search link -->
        <div class="torrent-genres">
            @foreach($torrent->genres as $genre)
                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary" title="Search for {{ $genre->name }} torrents">{{ $genre->name }}</a>
            @endforeach
        </div>
                            </td>
                            <td>
                                <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}">
                                  <i class="bi bi-file-earmark-arrow-down-fill" data-bs-toggle="tooltip" title="Download torrent"></i>
                                </a>
                            </td>
                            <!-- <td>{{ \Carbon\Carbon::parse($torrent->created_at)->diffForHumans() }}</td> -->
                            <td>{{ \Carbon\Carbon::parse($torrent->created_at)->format('d-M-Y') }}</td>

                            <td>{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</td>
                            <td>{{ $torrent->seeders }}</td>
                            <td>{{ $torrent->leechers }}</td>
                            <td>{{ $torrent->times_completed }}</td>

                            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                            <th>{{$torrent->uploader->name ?? 'Unknown'}}</th>
                        @endif

                            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                                <td>
                                    <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-warning btn-sm action-btn"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('torrents.destroy', $torrent->slug) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm action-btn" onclick="return confirm('Are you sure you want to delete this torrent?');"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No torrents found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>

@endsection
