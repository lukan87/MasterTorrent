<div class="lib-browse-options">
    <div class="lib-filter-field">
        <label class="lib-filter-label" for="library-year">Year</label>
        <input class="form-control form-control-sm" type="number" id="library-year" name="year"
               min="1800" max="2200" placeholder="Any year" value="{{ request('year') }}">
    </div>
    <div class="lib-filter-field">
        <label class="lib-filter-label" for="library-availability">Availability</label>
        <select class="form-select form-select-sm" id="library-availability" name="availability">
            <option value="all" @selected(request('availability', 'all') === 'all')>All titles</option>
            <option value="seeded" @selected(request('availability') === 'seeded')>With seeders</option>
        </select>
    </div>
    <div class="lib-filter-field">
        <label class="lib-filter-label" for="library-sort">Sort by</label>
        <select class="form-select form-select-sm" id="library-sort" name="sort">
            @foreach(['latest' => 'Recently added', 'title' => 'Title A–Z', 'rating' => 'Highest rating', 'year' => 'Newest year'] as $value => $label)
                <option value="{{ $value }}" @selected(request('sort', 'latest') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
