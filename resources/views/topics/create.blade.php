@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Page header --}}
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-2"><i class="bi bi-pencil-square me-2"></i>Create New Topic</h1>
        <small class="text-muted">in forum: <a href="{{ route('forums.show', $forum->id) }}" class="text-decoration-none">{{ $forum->name }}</a></small>
    </div>

    {{-- Form card --}}
    <div class="card bg-dark bg-opacity-25 shadow-sm border-0">
        <div class="card-body">

            <form method="POST" action="{{ route('topics.store', $forum->id) }}">
                @csrf

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Topic Title</label>
                    <input type="text" name="title" id="title" class="form-control form-control-lg bg-dark bg-opacity-50 text-white border-0" placeholder="Enter your topic title..." required>
                </div>

                {{-- Content --}}
                <div class="mb-3">
                    <label for="content" class="form-label fw-semibold">Content</label>
                    <textarea name="content" id="content" rows="6" class="form-control bg-dark bg-opacity-50 text-white border-0" placeholder="Write your post description here..." required></textarea>
                </div>

                {{-- Submit button --}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-1"></i> Create Topic
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

{{-- Optional CSS for modern card look --}}
<style>
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(72, 180, 97, 0.25);
        border-color: #48b461;
    }

    .card:hover {
        transform: translateY(-2px);
        transition: all 0.2s;
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }

    textarea.form-control {
        resize: vertical;
    }
</style>
@endsection
