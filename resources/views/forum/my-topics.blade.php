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
                <i class="bi bi-person-fill"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <h1 class="forum-category-title">My Topics</h1>
            <p class="forum-category-description mb-0">
                Topics you have participated in.
            </p>
        </div>
    </div>

    {{-- Topic List --}}
    <div class="forum-topic-list">

        @forelse($topics as $topic)

            <article class="forum-topic-row
                {{ $topic->is_pinned ? 'topic-pinned' : '' }}
                {{ $topic->is_locked ? 'topic-locked' : '' }}">

                <div class="forum-topic-icon">
                    @if($topic->is_locked)
                        <i class="bi bi-lock-fill"></i>
                    @elseif($topic->is_pinned)
                        <i class="bi bi-pin-fill"></i>
                    @else
                        <i class="bi bi-chat-left-text-fill"></i>
                    @endif
                </div>

                <div class="flex-grow-1">
                    @if($topic->category)
                    <a href="{{ route('forum.topic', ['category' => $topic->category->slug, 'topic' => $topic->slug]) }}"
                       class="forum-topic-link">
                        {{ $topic->title }}
                    </a>
                    <div class="forum-topic-meta">
                        in <a href="{{ route('forum.category', $topic->category->slug) }}">{{ $topic->category->name }}</a>
                        &middot; by {{ $topic->user->name ?? 'Unknown' }}
                        &middot; {{ $topic->updated_at->diffForHumans() }}
                    </div>
                    @else
                    <span class="forum-topic-link">{{ $topic->title }}</span>
                    @endif
                </div>

                <div class="forum-topic-stats d-none d-md-flex">
                    <span><i class="bi bi-chat-left-text me-1"></i>{{ $topic->posts_count }}</span>
                    <span><i class="bi bi-eye me-1"></i>{{ $topic->views }}</span>
                </div>

                <div class="forum-topic-arrow d-none d-md-flex">
                    <i class="bi bi-chevron-right"></i>
                </div>

            </article>

        @empty

            <div class="text-center text-secondary py-4">
                <i class="bi bi-chat-left-text" style="font-size:2rem;"></i>
                <p class="mt-2">You haven't participated in any topics yet.</p>
            </div>

        @endforelse

    </div>

    @if($topics->hasPages())
        <div class="forum-pagination mt-4">
            {{ $topics->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

@include('forum.partials.category-css')
@include('forum.partials.back-to-top')

@endsection