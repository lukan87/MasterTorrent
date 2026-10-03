@php $values = isset($request) ? $request->getAttributes() : ($defaults ?? []); @endphp
<div class="row g-4">
    <div class="col-12">
        <label for="name">Request title <span class="rq-muted">(required)</span></label>
        <input class="form-control" id="name" name="name" value="{{ old('name', $values['name'] ?? '') }}" maxlength="255" required placeholder="Title, release year, season or edition">
    </div>
    <div class="col-md-6">
        <label for="category_id">Category <span class="rq-muted">(required)</span></label>
        <select class="form-select" id="category_id" name="category_id" required>
            <option value="">Choose a category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $values['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="image">Poster URL <span class="rq-muted">(optional)</span></label>
        <input class="form-control" type="url" id="image" name="image" maxlength="255" value="{{ old('image', $values['image'] ?? '') }}" placeholder="https://…">
    </div>
    @foreach(['imdb_url' => 'IMDb', 'tmdb_url' => 'TMDB', 'steam_url' => 'Steam'] as $field => $label)
        <div class="col-md-4">
            <label for="{{ $field }}">{{ $label }} URL</label>
            <input class="form-control" type="url" id="{{ $field }}" name="{{ $field }}" maxlength="255" value="{{ old($field, $values[$field] ?? '') }}" placeholder="https://…">
        </div>
    @endforeach
    <div class="col-12">
        <label for="description">What are you looking for?</label>
        <textarea class="form-control" id="description" name="description" rows="6" maxlength="500" aria-describedby="description-help" placeholder="Include quality, language, subtitles, platform or edition details.">{{ old('description', $values['description'] ?? '') }}</textarea>
        <div id="description-help" class="rq-muted small mt-2">Maximum 500 characters. <span id="rq-count" aria-live="polite"></span></div>
    </div>
</div>
<div class="rq-actions mt-4">
    <button class="btn rq-btn" type="submit">{{ isset($request) ? 'Save changes' : 'Create request' }}</button>
    <a class="btn rq-secondary" href="{{ isset($request) ? route('requests.show', $request->id) : route('requests.index') }}">Cancel</a>
</div>
@push('scripts')
<script>
(() => {
    const input = document.getElementById('description');
    const counter = document.getElementById('rq-count');
    const update = () => counter.textContent = `${Array.from(input.value).length}/500 used`;
    input.addEventListener('input', update);
    update();
})();
</script>
@endpush
