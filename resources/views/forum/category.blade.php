@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="mb-4">

        <a href="{{ route('forum.index') }}"
           class="text-muted text-decoration-none">

            <i class="bi bi-arrow-left"></i>
            Forum

        </a>

       <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

    <div>

        <h1 class="mt-3">
            {{ $category->name }}
        </h1>

        @if($category->description)
            <p class="text-muted">
                {{ $category->description }}
            </p>
        @endif

    </div>

    @auth

        @if(!auth()->user()->forumblock)

            <a href="{{ route('forum.topic.create', $category->slug) }}"
               class="btn btn-primary">

                <i class="bi bi-plus-lg me-1"></i>

                New Topic

            </a>

        @endif

    @endauth

</div>

        @if($category->description)

            <p class="text-muted">
                {{ $category->description }}
            </p>

        @endif

    </div>


    <div class="card">

        <div class="card-body">

            @forelse($topics as $topic)

                <div class="border-bottom py-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h5 class="mb-1">

                                @if($topic->is_pinned)
                                    <i class="bi bi-pin-fill text-warning"></i>
                                @endif

                                @if($topic->is_locked)
                                    <i class="bi bi-lock-fill text-danger"></i>
                                @endif

                               <a href="{{ route('forum.topic', [
    'category' => $category->slug,
    'topic' => $topic->slug,
]) }}"
   class="text-decoration-none">

    {{ $topic->title }}

</a>

                            </h5>

                            <small class="text-muted">

                                Started by
                                {{ $topic->user->name ?? 'Unknown' }}

                                ·

                                {{ $topic->created_at->diffForHumans() }}

                            </small>

                        </div>

                        <div class="text-end">

                            <strong>
                                {{ $topic->posts_count }}
                            </strong>

                            <div class="text-muted small">
                                Posts
                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center py-5">

                    <i class="bi bi-chat-square-text fs-1 text-muted"></i>

                    <h4 class="mt-3">
                        No topics yet
                    </h4>

                    <p class="text-muted">
                        Be the first to start a discussion.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

    <div class="mt-4">
        {{ $topics->links() }}
    </div>

</div>

@endsection