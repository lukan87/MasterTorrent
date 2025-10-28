<!-- Search Form -->
<div class="mt-5 card shadow-sm border-0 mb-4">
    <div class="card-header bg-grey">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Search Torrents</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('torrents.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <!-- Keyword -->
                <div class="col-md-3">
                    <label for="keyword" class="form-label">🔍 Keyword</label>
                    <input type="text" name="keyword" id="keyword" class="form-control" placeholder="e.g., The Matrix, IMDb URL" value="{{ request('keyword') }}">
                </div>

<!-- Categories Dropdown -->
<div class="col-md-3">
    <label class="form-label">📁 Categories</label>
    <div class="dropdown w-100">
        <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Select Categories
        </button>
        <div class="dropdown-menu p-3 shadow-lg" style="max-height: 450px; overflow-y: auto; width: 750px;">
            <!-- Movies Section -->
            <div class="mb-4">
                <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom border-primary">
                    <i class="bi bi-film me-2"></i>Movies
                </h6>
                <div class="row g-3">
                    @foreach ($categories->whereIn('id', [1, 2, 5, 6, 9, 10, 11, 12, 16, 17, 18, 19, 24, 25, 31, 32, 54, 55, 81, 82]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ str_replace('Movies:', '', $category->name) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- TV Section -->
            <div class="mb-4">
                <h6 class="fw-bold text-success mb-3 pb-2 border-bottom border-success">
                    <i class="bi bi-tv me-2"></i>TV Shows
                </h6>
                <div class="row g-2">
                    @foreach ($categories->whereIn('id', [13, 14, 20, 21]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $category->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Other Categories -->
            <div class="mb-2">
                <h6 class="fw-bold text-warning mb-3 pb-2 border-bottom border-warning">
                    <i class="bi bi-collection me-2"></i>Other Categories
                </h6>
                <div class="row g-2">
                    @foreach ($categories->whereIn('id', [22, 26, 28, 30, 33, 42, 43, 44, 49, 56, 57]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $category->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

                <!-- Genre -->
                <div class="col-md-2">
                    <label for="genre" class="form-label">🎬 Genre</label>
                    <select name="genre" id="genre" class="form-select">
                        <option value="">All Genres</option>
                        @foreach($allGenres as $genre)
                            <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                                {{ $genre->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Torrent Status -->
                <div class="col-md-2">
                    <label for="torrent_status" class="form-label">📊 Status</label>
                    <select name="torrent_status" id="torrent_status" class="form-select">
                        <option value="active" {{ request('torrent_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="dead" {{ request('torrent_status') == 'dead' ? 'selected' : '' }}>Dead</option>
                        <option value="free" {{ request('torrent_status') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="double" {{ request('torrent_status') == 'double' ? 'selected' : '' }}>Double</option>
                        <option value="seedbox" {{ request('torrent_status') == 'seedbox' ? 'selected' : '' }}>Seedbox</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>