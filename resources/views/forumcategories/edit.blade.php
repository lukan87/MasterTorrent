@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 800px;">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-pencil-square me-2"></i>Edit Category
        </h1>
        <p class="text-muted mb-0">
            Update category details and visibility rules.
        </p>
    </div>

    {{-- Card --}}
    <div class="card border-0 shadow-sm bg-dark bg-opacity-50">
        <div class="card-body">

            {{-- UPDATE FORM --}}
            <form method="POST" action="{{ route('forumcategories.update', $category->id) }}">
                @csrf
                @method('PUT')

                {{-- Category name --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name) }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea
                        name="description"
                        rows="3"
                        class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Position --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Position (order)</label>
                    <input
                        type="number"
                        name="position"
                        class="form-control"
                        value="{{ old('position', $category->position) }}"
                    >
                </div>

                {{-- Minimum user class --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Minimum User Class Required</label>
                    <select name="min_class_required" class="form-select" required>
                        @foreach(\App\Models\UserClass::getClasses() as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('min_class_required', $category->min_class_required) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ACTION BAR --}}
                <div class="d-flex justify-content-between align-items-center">

                    <a href="{{ route('forums.category', $category->id) }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>

                </div>
            </form>

            {{-- DELETE FORM (SEPARATE) --}}
            @if(\App\Models\UserClass::userHasPermission(Auth::user()->user_class, 'delete_categories'))
                <hr class="my-4">

                <form method="POST"
                      action="{{ route('forumcategories.destroy', $category->id) }}"
                      onsubmit="return confirm('This action will archive it for later use if needed. Continue?')">
                    @csrf
                    @method('DELETE')

                    <button data-bs-toggle="tooltip" title="This action will not permanently delete the category. It will archive it for later use if needed" type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-archive me-1"></i> Archive Category For Future User
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>

<style>
.card {
    backdrop-filter: blur(6px);
}
</style>
@endsection
