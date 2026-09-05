@extends('layouts.app')

@section('content')

<div class="container py-5">


{{-- =========================================================
     FORUM HEADER
========================================================== --}}

<div class="forum-index-header mb-4">

    <div class="forum-header-copy">

        <div class="forum-eyebrow">
            <i class="bi bi-chat-square-dots-fill me-2"></i>
            COMMUNITY
        </div>

        <h1 class="forum-page-title">
            Forum
        </h1>

        <p class="forum-page-subtitle">
            Welcome to the FileIplay community.
            Join the conversation, share ideas and connect with others.
        </p>

    </div>

    @auth

        @if(auth()->user()->user_class > \App\Models\UserClass::MODERATOR)

            <a href="{{ route('forum.category.create') }}"
               class="forum-add-category-btn">

                <i class="bi bi-folder-plus me-2"></i>

                Add Category

            </a>

        @endif

    @endauth

</div>


{{-- =========================================================
     ACTIVE CATEGORIES
========================================================== --}}

<div class="forum-category-list">

    @forelse($categories as $category)

        <a href="{{ route('forum.category', $category->slug) }}"
           class="forum-category-link">

            <div class="forum-category-card">

                {{-- Icon --}}
                <div class="forum-category-icon">

                    <i class="bi {{ $category->icon ?? 'bi-chat' }}"></i>

                </div>


                {{-- Main content --}}
                <div class="forum-category-content">

                    <div class="forum-category-title-row">

                        <h3 class="forum-category-title">
                            {{ $category->name }}
                        </h3>

                        <span class="forum-category-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </span>

                    </div>

                    @if($category->description)

                        <p class="forum-category-description">
                            {{ $category->description }}
                        </p>

                    @endif

                </div>


                {{-- Topic count --}}
                <div class="forum-topic-count">

                    <strong>
                        {{ number_format($category->topics_count) }}
                    </strong>

                    <span>
                        {{ $category->topics_count === 1 ? 'Topic' : 'Topics' }}
                    </span>

                </div>

            </div>

        </a>

    @empty

        <div class="forum-empty-state">

            <div class="forum-empty-icon">
                <i class="bi bi-chat-square-text"></i>
            </div>

            <h3>
                No forum categories yet
            </h3>

            <p>
                There are no discussion categories available at the moment.
            </p>

        </div>

    @endforelse

</div>


{{-- =========================================================
     DELETED CATEGORIES
========================================================== --}}

@if(
    auth()->check() &&
    auth()->user()->user_class > \App\Models\UserClass::MODERATOR &&
    $deletedCategories->isNotEmpty()
)

    <section class="forum-deleted-categories mt-5">

        {{-- Header --}}
        <div class="forum-deleted-header">

            <div class="forum-deleted-heading">

                <div class="forum-deleted-icon">
                    <i class="bi bi-trash3-fill"></i>
                </div>

                <div>

                    <h3>
                        Deleted Categories
                    </h3>

                    <p>
                        Restore previously deleted categories or permanently remove them.
                    </p>

                </div>

            </div>

            <span class="forum-deleted-count">
                {{ $deletedCategories->count() }}
                {{ $deletedCategories->count() === 1 ? 'Category' : 'Categories' }}
            </span>

        </div>


        {{-- Deleted category list --}}
        <div class="forum-deleted-category-list">

            @foreach($deletedCategories as $category)

                <div class="forum-deleted-category-card">

                    <div class="forum-deleted-category-main">

                        <div class="forum-deleted-category-icon">
                            <i class="bi {{ $category->icon ?? 'bi-chat' }}"></i>
                        </div>

                        <div class="forum-deleted-category-info">

                            <h4>
                                {{ $category->name }}
                            </h4>

                            @if($category->description)

                                <p>
                                    {{ $category->description }}
                                </p>

                            @endif

                            <span class="forum-deleted-date">
                                <i class="bi bi-clock me-1"></i>
                                Deleted {{ $category->deleted_at->format('d/m/Y H:i') }}
                            </span>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="forum-deleted-actions">

                        <form method="POST"
                              action="{{ route('forum.category.restore', $category->id) }}"
                              onsubmit="return confirm('Restore this category?');">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="forum-restore-category-btn">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>

                                Restore

                            </button>

                        </form>


                        <form method="POST"
                              action="{{ route('forum.category.force-delete', $category->id) }}"
                              onsubmit="return confirm('{{ $category->topics_count > 0 ? 'WARNING: This category contains ' . $category->topics_count . ' ' . ($category->topics_count === 1 ? 'topic' : 'topics') . '. Permanently deleting it will also permanently delete the category and all of its topics, posts and reactions. This cannot be undone. Are you sure?' : 'WARNING: This will permanently delete this category. This cannot be undone. Are you sure?' }}');">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="forum-permanent-delete-category-btn">

                                <i class="bi bi-trash3-fill me-1"></i>

                                Permanently Delete

                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </section>

@endif

</div>


@include('forum.partials.index-css')

@endsection
