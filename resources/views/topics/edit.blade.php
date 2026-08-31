@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Page title --}}
    <div class="mb-4 text-center">
        <h1 class="fw-bold mb-1">Edit Topic</h1>
        <div class="text-muted small">
            Update the title and content of your discussion
        </div>
    </div>

    {{-- Edit card --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card bg-dark bg-opacity-50 border-0 shadow-lg rounded-4">
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('topics.update', $topic) }}">
                        @csrf
                        @method('PUT')

                        {{-- Title --}}
                        <div class="mb-4">
                            <label class="form-label text-light fw-semibold">
                                Topic title
                            </label>
                            <input type="text"
                                   name="title"
                                   class="form-control form-control-lg bg-dark text-white border-secondary"
                                   value="{{ old('title', $topic->title) }}"
                                   placeholder="Enter topic title">
                        </div>

                        {{-- Content --}}
                        <div class="mb-4">
                            <label class="form-label text-light fw-semibold">
                                Topic content
                            </label>
                            <textarea name="content"
                                      rows="8"
                                      class="form-control bg-dark text-white border-secondary"
                                      placeholder="Write the content here...">{{ old('content', $topic->posts()->oldest()->first()?->content) }}</textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('topics.show', $topic) }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>

                            <button class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modern polish --}}
<style>
.form-control::placeholder {
    color: rgba(255,255,255,0.4);
}

.form-control:focus {
    background-color: #1e1e1e;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25);
}

.card {
    backdrop-filter: blur(6px);
}
</style>
@endsection
