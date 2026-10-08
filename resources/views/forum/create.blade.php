@extends('layouts.app')

@section('content')

<div class="container forum-page py-4 py-lg-5">

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

                    <label class="form-label" for="forum-topic-title">
                        Topic Title
                    </label>

                    <input
                        type="text"
                        id="forum-topic-title" name="title"
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

                    <label class="form-label" for="forum-topic-body">
                        Message
                    </label>

                    {{-- BBCode Toolbar --}}
@include('forum.partials.bbcode-toolbar')


                    <textarea maxlength="10000"
                        id="forum-topic-body"
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

                    <div class="d-flex gap-2 mt-2 align-items-center">
                        <button type="button"
                                class="forum-preview-btn"
                                id="forum-create-preview-btn">
                            <i class="bi bi-eye me-1"></i>Preview
                        </button>
                    </div>

                    <div class="forum-post-preview-pane mt-2" style="display:none;" id="forum-create-preview"></div>

                </div>

                <div class="bbcode-help">
    <i class="bi bi-emoji-smile me-1"></i>
    For smilies use <strong>WIN + .</strong>
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




@include('forum.partials.common-css')
@include('forum.partials.scripts', ['draftKind' => 'create', 'draftId' => $category->id])

<style>
.bbcode-help {
    padding: 7px 2px 9px;
    color: var(--theme-muted, rgba(255, 255, 255, 0.45));
    font-size: var(--site-font-body, 13px);
}
.bbcode-help i {
    color: var(--theme-teal-text, #63d2c6);
}
.bbcode-help strong {
    color: var(--theme-muted, rgba(255, 255, 255, 0.7));
    font-weight: 700;
}
</style>

@endsection