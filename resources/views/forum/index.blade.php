@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="mb-4">
        <h1>Forum</h1>
        <p class="text-muted">
            Welcome to the FileIplay community.
        </p>
    </div>

@forelse($categories as $category)

    <a href="{{ route('forum.category', $category->slug) }}"
       class="text-decoration-none">

        <div class="card mb-3 forum-category-card">

            <div class="card-body d-flex align-items-center gap-3">

                <div class="forum-category-icon">
                    <i class="bi {{ $category->icon ?? 'bi-chat' }}"></i>
                </div>

                <div class="flex-grow-1">

                    <h4 class="mb-1">
                        {{ $category->name }}
                    </h4>

                    @if($category->description)
                        <p class="text-muted mb-0">
                            {{ $category->description }}
                        </p>
                    @endif

                </div>

                <div class="text-end">

                    <strong>
                        {{ $category->topics_count }}
                    </strong>

                    <div class="text-muted small">
                        Topics
                    </div>

                </div>

                <i class="bi bi-chevron-right text-muted"></i>

            </div>

        </div>

    </a>

@empty

        <div class="alert alert-info">
            No forum categories have been created yet.
        </div>

    @endforelse

</div>

@endsection