@extends('layouts.app')

@section('content')

<div class="container py-5">

    {{-- =========================================================
         BREADCRUMB
         ========================================================= --}}

    <div class="forum-breadcrumb mb-4">

        <a href="{{ route('forum.index') }}"
           class="forum-breadcrumb-link">

            <i class="bi bi-arrow-left"></i>

            <span>Back to Forum</span>

        </a>

    </div>


    {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}

    <div class="forum-category-header mb-4">

        <div>

            <div class="forum-category-icon">

                <i class="bi bi-folder-plus"></i>

            </div>

        </div>

        <div class="flex-grow-1">

            <h1 class="forum-category-title">

                Add Category

            </h1>

            <p class="forum-category-description mb-0">

                Create a new forum category.

            </p>

        </div>

    </div>


    {{-- =========================================================
         FORM
         ========================================================= --}}

    <div class="forum-category-form">

        <form method="POST"
              action="{{ route('forum.category.store') }}">

            @csrf


            {{-- CATEGORY NAME --}}

            <div class="mb-4">

                <label for="name"
                       class="forum-form-label">

                    Category Name

                </label>

                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="form-control forum-form-control @error('name') is-invalid @enderror"
                       placeholder="e.g. General Discussion"
                       required>

                @error('name')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- DESCRIPTION --}}

            <div class="mb-4">

                <label for="description"
                       class="forum-form-label">

                    Description

                </label>

                <textarea id="description"
                          name="description"
                          rows="4"
                          class="form-control forum-form-control @error('description') is-invalid @enderror"
                          placeholder="Describe what this category is for...">{{ old('description') }}</textarea>

                @error('description')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- ICON --}}

            <div class="mb-4">

                <label for="icon"
                       class="forum-form-label">

                    Icon

                </label>

                <input type="text"
                       id="icon"
                       name="icon"
                       value="{{ old('icon') }}"
                       class="form-control forum-form-control @error('icon') is-invalid @enderror"
                       placeholder="e.g. bi-chat-square-text-fill">

                <div class="forum-form-help">

                    Use a Bootstrap Icons class, for example:
                    <code>bi-chat-square-text-fill</code>

                </div>

                @error('icon')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- POSITION --}}

            <div class="mb-4">

                <label for="position"
                       class="forum-form-label">

                    Position

                </label>

                <input type="number"
                       id="position"
                       name="position"
                       value="{{ old('position', 0) }}"
                       min="0"
                       class="form-control forum-form-control @error('position') is-invalid @enderror">

                <div class="forum-form-help">

                    Lower numbers appear first.

                </div>

                @error('position')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>


<input type="hidden" name="is_private" value="0">
            {{-- PRIVATE CATEGORY --}}

            <div class="mb-4">

                <div class="forum-checkbox-row">

                    <input type="checkbox"
                           id="is_private"
                           name="is_private"
                           value="1"
                           {{ old('is_private') ? 'checked' : '' }}>

                    <label for="is_private">

                        Private Category

                    </label>

                </div>

                <div class="forum-form-help">

                    Private categories are not visible to normal forum users.

                </div>

            </div>


            {{-- ACTIONS --}}

            <div class="forum-form-actions">

                <a href="{{ route('forum.index') }}"
                   class="forum-cancel-btn">

                    Cancel

                </a>

                <button type="submit"
                        class="forum-new-topic-btn">

                    <i class="bi bi-plus-lg me-1"></i>

                    Create Category

                </button>

            </div>

        </form>

    </div>

</div>


<style>

    /* =========================================================
       CATEGORY FORM
       ========================================================= */

    .forum-category-form {

        max-width: 800px;

        margin: 0 auto;

        padding: 28px;

        background:
            var(--theme-surface, rgba(10,13,23,.96));

        border:
            1px solid var(--theme-border, rgba(255,255,255,.07));

        border-radius: 18px;

        box-shadow:
            0 12px 35px var(--theme-shadow, rgba(0,0,0,.25));

    }


    .forum-form-label {

        display: block;

        margin-bottom: 8px;

        color: var(--theme-text, rgba(255,255,255,.85));

        font-size: var(--site-font-body, 13px);

        font-weight: 700;

    }


    .forum-form-control {

        background:
            var(--theme-control, rgba(255,255,255,0.028));

        border:
            1px solid var(--theme-border, rgba(255,255,255,.10));

        border-radius: 10px;

        color: var(--theme-text, white);

        padding: 11px 13px;

    }


    .forum-form-control:focus {

        background:
            var(--theme-control, rgba(255,255,255,0.035));

        border-color:
            var(--theme-blue-border, rgba(59,130,246,.55));

        color: var(--theme-text, white);

        box-shadow:
            0 0 0 .2rem var(--theme-shadow, rgba(59,130,246,.10));

    }


    .forum-form-control::placeholder {

        color:
            var(--theme-muted, rgba(255,255,255,.28));

    }


    .forum-form-help {

        margin-top: 6px;

        color:
            var(--theme-muted, rgba(255,255,255,.38));

        font-size: var(--site-font-small, 13px);

    }


    .forum-form-help code {

        color:
            var(--theme-blue-text, #93c5fd);

    }


    .forum-checkbox-row {

        display: flex;

        align-items: center;

        gap: 9px;

    }


    .forum-checkbox-row input {

        width: 17px;

        height: 17px;

        accent-color: var(--theme-blue-text, #2563eb);

    }


    .forum-checkbox-row label {

        color:
            var(--theme-muted, rgba(255,255,255,.82));

        font-size: var(--site-font-body, 13px);

        font-weight: 700;

        cursor: pointer;

    }


    .forum-form-actions {

        display: flex;

        justify-content: flex-end;

        align-items: center;

        gap: 10px;

        padding-top: 8px;

    }


    .forum-cancel-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        padding: 10px 16px;

        border-radius: 10px;

        border: 1px solid var(--theme-border, rgba(255,255,255,.08));

        color: var(--theme-muted, rgba(255,255,255,.65));

        text-decoration: none;

        font-size: var(--site-font-body, 13px);

        font-weight: 700;

    }


    .forum-cancel-btn:hover {

        color: var(--theme-text, white);

        background:
            var(--theme-surface-alt, rgba(255,255,255,0.035));

    }


    @media (max-width: 767px) {

        .forum-category-form {

            padding: 20px;

        }


        .forum-form-actions {

            flex-direction: column-reverse;

        }


        .forum-form-actions > * {

            width: 100%;

        }

    }

</style>

@endsection
