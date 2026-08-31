@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="mb-4">

        <a href="{{ route('forum.category', $category->slug) }}"
           class="text-muted text-decoration-none">

            <i class="bi bi-arrow-left"></i>

            {{ $category->name }}

        </a>

        <h1 class="mt-3">
            New Topic
        </h1>

    </div>


    <div class="card">

        <div class="card-body">

            <form method="POST"
                  action="{{ route('forum.topic.store', $category->slug) }}">

                @csrf


                <div class="mb-4">

                    <label class="form-label">
                        Topic Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}"
                        maxlength="255"
                        required
                    >

                    @error('title')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                <div class="mb-4">

                    <label class="form-label">
                        Message
                    </label>

                    <textarea
                        name="body"
                        rows="10"
                        class="form-control @error('body') is-invalid @enderror"
                        required
                    >{{ old('body') }}</textarea>

                    @error('body')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                <div class="d-flex gap-2">

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="bi bi-chat-square-text me-1"></i>

                        Create Topic

                    </button>

                    <a href="{{ route('forum.category', $category->slug) }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection