@extends('layouts.app')

@section('title',  'Browse Torrents' )

@section('content')

    <h1 class="page-title">Torrent List</h1>


   <!-- Search Form -->
<form action="{{ route('torrents.index') }}" method="GET" class="search-form mb-4">
    <div class="row g-3 align-items-end">
        <!-- Search Keyword with Autocomplete -->
        <div class="col-md-3 col-sm-6">
            <label for="keyword" class="form-label">Keyword</label>
            <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Search Torrent by name, IMDb URL, TMDb ID..." value="{{ request('keyword') }}">
        </div>



<!-- Category Filter -->
<div class="col-md-3 col-sm-6">
    <label for="categoriesDropdown" class="form-label">Categories</label>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="categoriesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Select Categories
        </button>
        <div class="dropdown-menu custom-dropdown-width" aria-labelledby="categoriesDropdown">
            <div class="d-flex flex-wrap gap-3">
                @foreach ($categories as $category)
                    @if (!in_array($category->id, [27, 34, 60]))
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input me-1" name="categories[]" value="{{ $category->id }}"
                            {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                            {{ $category->name }}
                        </label>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>


<style>.custom-dropdown-width {
    min-width: 600px; /* Adjust width as needed */
}</style>

        <!-- Genre Filter -->
        <div class="col-md-2 col-sm-4">
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

        <!-- Torrent Status Filter -->
        <div class="col-md-2 col-sm-4">
            <label for="torrent_status" class="form-label">Status</label>
            <select name="torrent_status" id="torrent_status" class="form-select">
                <option value="active" {{ request('torrent_status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="dead" {{ request('torrent_status') == 'dead' ? 'selected' : '' }}>Dead</option>
                <option value="free" {{ request('torrent_status') == 'free' ? 'selected' : '' }}>Free</option>
                <option value="double" {{ request('torrent_status') == 'double' ? 'selected' : '' }}>Double</option>
                <option value="seedbox" {{ request('torrent_status') == 'seedbox' ? 'selected' : '' }}>Seedbox</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="col-md-2 col-sm-4">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </div>
</form>


    <!-- Torrent Table -->
<div class="container-fluid p-0">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
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
                        <td><img src="{{ $torrent->category->image }}" alt="Category Image" class="img-fluid" style="width: 87px; height: 47px;"></td>
                        <td>
                        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
   class="torrent-name" 
   data-bs-toggle="tooltip" 
   data-bs-html="true" 
   data-bs-title="<div class='card' style='width: 200px;'>
                    @if($torrent->poster)
                        <img src='{{ $torrent->poster }}' class='img-fluid rounded' alt='Poster Image' style='width: 150px; height: auto;'>
                    @else
                        <div style='padding: 10px; text-align: center;'>{{ $torrent->name }}</div>
                    @endif
                  </div>">
    {{ \Illuminate\Support\Str::limit($torrent->name, 75, ' ...') }}
</a>

                            @include('torrents.partials.tags')
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
                        <td><div data-bs-toggle="tooltip" title="{{ \Carbon\Carbon::parse($torrent->created_at)->diffForHumans() }}">{{ \Carbon\Carbon::parse($torrent->created_at)->format('d-M-Y') }}</div></td>
                        <td>{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</td>
                        <td>{{ $torrent->seeders }}</td>
                        <td>{{ $torrent->leechers }}</td>
                        <td>{{ $torrent->times_completed }}</td>
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                            <td>
                                @if(isset($torrent->uploader->id))
                                    <a href="{{ route('profile.show', ['id' => $torrent->uploader->id, 'name' => $torrent->uploader->name ?? 'Unknown']) }}"
                                       style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class ?? '') }}">
                                        {{ $torrent->uploader->name ?? 'Unknown' }}
                                    </a>
                                @else
                                    <span>Unknown</span>
                                @endif
                            </td>
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

        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>

@endsection
