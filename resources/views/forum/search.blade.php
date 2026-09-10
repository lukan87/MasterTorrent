@extends('layouts.app')

@section('content')

<div class="container py-5">

    {{-- Breadcrumb --}}
    <div class="forum-breadcrumb mb-4">
        <a href="{{ route('forum.index') }}" class="forum-breadcrumb-link">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Forum</span>
        </a>
    </div>

    {{-- Header --}}
    <div class="forum-category-header mb-4">
        <div>
            <div class="forum-category-icon">
                <i class="bi bi-search"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <h1 class="forum-category-title">Forum Search</h1>
        </div>
    </div>

    {{-- Search Form --}}
    <form method="GET" action="{{ route('forum.search') }}" class="mb-4">
        <div class="forum-search-bar">
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Search topics and posts..."
                value="{{ old('q', $query) }}"
                autofocus
            >
            <button type="submit" class="btn btn-search">
                <i class="bi bi-search me-1"></i>
                Search
            </button>
        </div>
    </form>

    {{-- Results --}}
    @if(mb_strlen($query) >= 2)

        <p class="text-secondary mb-3">
            Found {{ $results->count() }} result{{ $results->count() !== 1 ? 's' : '' }}
            for "<strong>{{ e($query) }}</strong>"
        </p>

        <div class="forum-topic-list">

            @forelse($results as $result)

                @if($result instanceof \App\Models\ForumTopic)
                    <article class="forum-topic-row">
                        <div class="forum-topic-icon">
                            @if($result->is_locked)
                                <i class="bi bi-lock-fill"></i>
                            @elseif($result->is_pinned)
                                <i class="bi bi-pin-fill"></i>
                            @else
                                <i class="bi bi-chat-left-text-fill"></i>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            @if($result->category)
                            <a href="{{ route('forum.topic', ['category' => $result->category->slug, 'topic' => $result->slug]) }}"
                               class="forum-topic-link">
                                {{ $result->title }}
                            </a>
                            <div class="forum-topic-meta">
                                in <a href="{{ route('forum.category', $result->category->slug) }}">{{ $result->category->name }}</a>
                                &middot; by {{ $result->user->name ?? 'Unknown' }}
                                &middot; {{ $result->created_at->diffForHumans() }}
                            </div>
                            @else
                            <span class="forum-topic-link">{{ $result->title }}</span>
                            @endif
                        </div>

                        <div class="forum-topic-stats d-none d-md-flex">
                            <span><i class="bi bi-chat-left-text me-1"></i>{{ $result->posts_count }}</span>
                            <span><i class="bi bi-eye me-1"></i>{{ $result->views }}</span>
                        </div>
                    </article>

                @elseif($result instanceof \App\Models\ForumPost && $result->topic)
                    <article class="forum-topic-row">
                        <div class="forum-topic-icon">
                            <i class="bi bi-chat-left-text-fill"></i>
                        </div>

                        <div class="flex-grow-1">
                            <span class="text-secondary" style="font-size:0.8rem;">Reply in</span>
                            @if($result->topic->category)
                            <a href="{{ route('forum.topic', ['category' => $result->topic->category->slug, 'topic' => $result->topic->slug, 'post' => $result->id]) }}#post-{{ $result->id }}"
                               class="forum-topic-link">
                                {{ $result->topic->title }}
                            </a>
                            @else
                            <span class="forum-topic-link">{{ $result->topic->title }}</span>
                            @endif
                            <div class="forum-topic-meta">
                                by {{ $result->user->name ?? 'Unknown' }}
                                &middot; {{ $result->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </article>
                @endif

            @empty

                <div class="text-center text-secondary py-4">
                    <i class="bi bi-search" style="font-size:2rem;"></i>
                    <p class="mt-2">No results found.</p>
                </div>

            @endforelse

        </div>

    @else

        <div class="text-center text-secondary py-4">
            <i class="bi bi-search" style="font-size:2rem;"></i>
            <p class="mt-2">Type at least 2 characters to search.</p>
        </div>

    @endif

</div>

@include('forum.partials.category-css')
@include('forum.partials.back-to-top')

@endsection