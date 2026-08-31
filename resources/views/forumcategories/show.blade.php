@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Category header --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">
            <i class="bi bi-folder-fill me-2"></i>{{ $category->name }}
        </h1>
        <small class="text-muted">{{ $category->description }}</small>
    </div>

    @php $userClass = Auth::user()->user_class; @endphp

    

    {{-- Actions --}}
    <div class="d-flex gap-2">
        {{-- Back to forums --}}
        <a href="{{ route('forums.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Forums
        </a>

        {{-- Create forum --}}
        @if(\App\Models\UserClass::userHasPermission($userClass, 'create_forums'))
            <a href="{{ route('forums.create', $category->id) }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Create Forum
            </a>
        @endif
    </div>
</div>


    {{-- Forums --}}
    @if($category->forums->count() > 0)
        <div class="row g-3">
            @foreach($category->forums as $forum)
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-dark bg-opacity-50 forum-card">

                        <div class="card-body d-flex justify-content-between align-items-center gap-3">

                            {{-- Forum info --}}
                            <div class="flex-grow-1">
                                <a href="{{ route('forums.show', $forum->id) }}"
                                   class="fw-semibold fs-6 text-decoration-none d-flex align-items-center gap-2">
                                    @if($forum->is_locked)
                                        <i class="bi bi-lock-fill text-danger"></i>
                                    @else
                                        <i class="bi bi-chat-dots text-primary"></i>
                                    @endif
                                    {{ $forum->name }}
                                </a>

                                @if($forum->description)
                                    <div class="small text-muted mt-1">
                                        {{ $forum->description }}
                                    </div>
                                @endif
                            </div>

                            {{-- Status badge --}}
                            <div class="text-end">
                                @if($forum->is_locked)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-lock-fill me-1"></i> Locked
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-unlock-fill me-1"></i> Open
                                    </span>
                                @endif
                            </div>

                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty state --}}
        <div class="alert alert-secondary bg-dark bg-opacity-50 border-0">
            <i class="bi bi-info-circle me-1"></i>
            No forums in this category yet.
        </div>
    @endif

</div>

{{-- Subtle hover --}}
<style>
.forum-card {
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
}
.forum-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.25);
    background: rgba(255,255,255,.04);
}
</style>
@endsection
