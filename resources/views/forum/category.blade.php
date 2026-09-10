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
         CATEGORY HEADER
         ========================================================= --}}

    <div class="forum-category-header mb-4">

        <div>

            <div class="forum-category-icon">

                <i class="bi bi-chat-square-text-fill"></i>

            </div>

        </div>


        <div class="flex-grow-1">

            <h1 class="forum-category-title">

                {{ $category->name }}

            </h1>

            @if($category->description)

                <p class="forum-category-description mb-0">

                    {{ $category->description }}

                </p>

            @endif

        </div>


        {{-- Actions--}}
@auth
    <div class="d-flex flex-wrap gap-2">

        @if(auth()->user()->user_class > \App\Models\UserClass::MODERATOR)

            <a href="{{ route('forum.category.edit', $category->id) }}"
               class="btn forum-edit-category-btn">
                <i class="bi bi-pencil-square me-1"></i>
                Edit Category
            </a>

            <form method="POST"
                  action="{{ route('forum.category.destroy', $category->id) }}"
                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="btn forum-delete-category-btn">
                    <i class="bi bi-trash me-1"></i>
                    Delete Category
                </button>
            </form>

        @endif

        @if(!auth()->user()->forumblock)
            <a href="{{ route('forum.topic.create', $category->slug) }}"
               class="btn forum-new-topic-btn">
                <i class="bi bi-plus-lg me-1"></i>
                New Topic
            </a>
        @endif

    </div>
@endauth


    </div>


    {{-- =========================================================
         SORT BAR
         ========================================================= --}}

    <div class="forum-sort-bar mb-4">
        <span class="sort-label">
            <i class="bi bi-funnel me-1"></i>
            Sort:
        </span>

        <a href="{{ route('forum.category', ['category' => $category->slug, 'sort' => 'latest']) }}"
           class="forum-sort-btn {{ $sort === 'latest' ? 'active' : '' }}">
            Latest activity
        </a>

        <a href="{{ route('forum.category', ['category' => $category->slug, 'sort' => 'created']) }}"
           class="forum-sort-btn {{ $sort === 'created' ? 'active' : '' }}">
            Newest
        </a>

        <a href="{{ route('forum.category', ['category' => $category->slug, 'sort' => 'views']) }}"
           class="forum-sort-btn {{ $sort === 'views' ? 'active' : '' }}">
            Most viewed
        </a>

        <a href="{{ route('forum.category', ['category' => $category->slug, 'sort' => 'replies']) }}"
           class="forum-sort-btn {{ $sort === 'replies' ? 'active' : '' }}">
            Most replied
        </a>
    </div>


    {{-- =========================================================
         TOPIC LIST
         ========================================================= --}}

    <div class="forum-topic-list">

        @forelse($topics as $topic)

            <article class="forum-topic-row
                {{ $topic->is_pinned ? 'topic-pinned' : '' }}
                {{ $topic->is_locked ? 'topic-locked' : '' }}">


                {{-- TOPIC ICON --}}

                <div class="forum-topic-icon">

                    @if($topic->is_locked)

                        <i class="bi bi-lock-fill"></i>

                    @elseif($topic->is_pinned)

                        <i class="bi bi-pin-fill"></i>

                    @else

                        <i class="bi bi-chat-left-text-fill"></i>

                    @endif

                </div>


                {{-- MAIN INFORMATION --}}

                <div class="forum-topic-main">

                    <div class="forum-topic-title-row">

                        <a href="{{ route('forum.topic', [
                            'category' => $category->slug,
                            'topic' => $topic->slug,
                        ]) }}"
                           class="forum-topic-link">

                            {{ $topic->title }}

                        </a>


                        {{-- BADGES --}}

                        <div class="forum-topic-badges">

                            @if($topic->is_pinned)

                                <span class="forum-topic-badge pinned">

                                    <i class="bi bi-pin-fill me-1"></i>

                                    Pinned

                                </span>

                            @endif


@if($topic->new_replies_count > 0)

    <span class="forum-topic-badge new">

        <i class="bi bi-envelope-plus  me-1"></i>

        {{ $topic->new_replies_count }}
        {{ $topic->new_replies_count === 1 ? 'New Reply' : 'New Replies' }}

    </span>

@endif


                            @if($topic->is_locked)

                                <span class="forum-topic-badge locked">

                                    <i class="bi bi-lock-fill me-1"></i>

                                    Locked

                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- STARTED BY --}}

                    <div class="forum-topic-started">

                        <i class="bi bi-person-circle me-1"></i>

                        Started by

                        @if($topic->user)

                            <strong>
                                {{ $topic->user->name }}
                            </strong>

                        @else

                            <strong>
                                Unknown
                            </strong>

                        @endif

                        <span class="mx-1">·</span>

                        {{ $topic->created_at->diffForHumans() }}

                    </div>

                </div>


                {{-- STATS --}}

                <div class="forum-topic-stats">

                    <div class="forum-topic-stat">

                        <strong>

                            {{ $topic->posts_count }}

                        </strong>

                        <span>

                            <i class="bi bi-chat-left-text me-1"></i>

                            Posts

                        </span>

                    </div>


                    <div class="forum-topic-stat">

                        <strong>

                            {{ $topic->views }}

                        </strong>

                        <span>

                            <i class="bi bi-eye me-1"></i>

                            Views

                        </span>

                    </div>

                </div>


            {{-- LAST POST --}}

<div class="forum-last-post">

    @if($topic->lastPost)

        <a href="{{ route('forum.topic', [
            'category' => $category->slug,
            'topic' => $topic->slug,
        ]) }}#post-{{ $topic->lastPost->id }}"
           class="forum-last-post-link">

            <div class="forum-last-post-label">

                <i class="bi bi-arrow-return-right me-1"></i>

                Last post

            </div>

            <div class="forum-last-post-user">

                <i class="bi bi-person-fill me-1"></i>

                {{ $topic->lastPost->user->name ?? 'Unknown' }}

            </div>

            <div class="forum-last-post-time">

                <i class="bi bi-clock me-1"></i>

                {{ $topic->lastPost->created_at->diffForHumans() }}

            </div>

        </a>

    @else

        <div class="forum-last-post-label">

            <i class="bi bi-chat-left me-1"></i>

            No replies

        </div>

    @endif

</div>


                {{-- ARROW --}}

                <div class="forum-topic-arrow">

                    <i class="bi bi-chevron-right"></i>

                </div>

            </article>

        @empty


            {{-- EMPTY STATE --}}

            <div class="forum-empty-state">

                <div class="forum-empty-icon">

                    <i class="bi bi-chat-square-text"></i>

                </div>

                <h4>

                    No topics yet

                </h4>

                <p>

                    Be the first to start a discussion.

                </p>


                @auth

                    @if(!auth()->user()->forumblock)

                        <a href="{{ route('forum.topic.create', $category->slug) }}"
                           class="btn forum-new-topic-btn">

                            <i class="bi bi-plus-lg me-1"></i>

                            Start a Topic

                        </a>

                    @endif

                @endauth

            </div>

        @endforelse

    </div>


    {{-- =========================================================
         PAGINATION
         ========================================================= --}}

    @if($topics->hasPages())

        <div class="forum-pagination mt-4">

            {{ $topics->links('pagination::bootstrap-5') }}

        </div>

    @endif

</div>



@include('forum.partials.category-css')

@include('forum.partials.back-to-top')

@endsection