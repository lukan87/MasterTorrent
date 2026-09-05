@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="forum-breadcrumb mb-4">
        <a href="{{ route('forum.index') }}"
           class="forum-breadcrumb-link">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Forum</span>
        </a>
    </div>

    <div class="forum-category-form-card">

        <div class="forum-category-form-header">
            <div>
                <div class="forum-category-form-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
            </div>

            <div>
                <h1 class="forum-category-form-title">
                    Edit Category
                </h1>

                <p class="forum-category-form-description mb-0">
                    Update the forum category details.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('forum.category.update', $category->id) }}">

            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="form-label">
                    Category Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $category->name) }}"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="4"
                >{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="mb-4">
                <label for="icon" class="form-label">
                    Icon
                </label>

                <input
                    type="text"
                    id="icon"
                    name="icon"
                    class="form-control"
                    value="{{ old('icon', $category->icon) }}"
                    placeholder="bi-chat-square-text-fill"
                >

                <div class="form-text">
                    Use a Bootstrap Icons class, for example:
                    <code>bi-chat-square-text-fill</code>
                </div>
            </div>

            <div class="mb-4">
                <label for="position" class="form-label">
                    Position
                </label>

                <input
                    type="number"
                    id="position"
                    name="position"
                    class="form-control"
                    value="{{ old('position', $category->position) }}"
                    min="0"
                >

                <div class="form-text">
                    Lower numbers appear first.
                </div>
            </div>

            <div class="form-check mb-4">
                <input
                    type="checkbox"
                    id="is_private"
                    name="is_private"
                    value="1"
                    class="form-check-input"
                    {{ old('is_private', $category->is_private) ? 'checked' : '' }}
                >

                <label for="is_private" class="form-check-label">
                    Private Category
                </label>

                <div class="form-text">
                    Private categories are not visible to normal forum users.
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">

                <a href="{{ route('forum.category', $category->slug) }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit"
                        class="btn forum-save-category-btn">
                    <i class="bi bi-check-lg me-1"></i>
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>

<style>

.forum-category-form-card {
    max-width: 850px;
    margin: 0 auto;
    padding: 30px;
    background: rgba(15, 20, 35, .78);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 18px;
    box-shadow: 0 12px 35px rgba(0,0,0,.18);
}

.forum-category-form-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 30px;
}

.forum-category-form-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: white;
    font-size: 1.3rem;
}

.forum-category-form-title {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 800;
    color: white;
}

.forum-category-form-description {
    margin-top: 4px;
    color: rgba(255,255,255,.55);
}

.forum-category-form-card .form-label {
    color: rgba(255,255,255,.85);
    font-weight: 700;
}

.forum-category-form-card .form-control {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.10);
    color: white;
    border-radius: 10px;
    padding: 11px 13px;
}

.forum-category-form-card .form-control:focus {
    background: rgba(255,255,255,.06);
    color: white;
    border-color: #4f8cff;
    box-shadow: 0 0 0 .2rem rgba(37,99,235,.15);
}

.forum-category-form-card .form-text {
    color: rgba(255,255,255,.45);
    margin-top: 6px;
}

.forum-category-form-card code {
    color: #93c5fd;
}

.forum-category-form-card .form-check-label {
    color: rgba(255,255,255,.85);
    font-weight: 700;
}

.forum-category-form-card .form-check-input {
    background-color: rgba(255,255,255,.05);
    border-color: rgba(255,255,255,.20);
}

.forum-save-category-btn {
    padding: 10px 17px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: white;
    font-weight: 700;
}

.forum-save-category-btn:hover {
    color: white;
    transform: translateY(-1px);
}

@media (max-width: 767px) {

    .forum-category-form-card {
        padding: 20px;
    }

    .forum-category-form-header {
        align-items: flex-start;
    }

    .forum-category-form-title {
        font-size: 1.5rem;
    }

}

</style>

@endsection