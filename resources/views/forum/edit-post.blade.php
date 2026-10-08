@extends('layouts.app')

@section('content')

<div class="container forum-page py-4 py-lg-5">

    <div class="mb-4">

        <a href="{{ route('forum.topic', [
            'category' => $category->slug,
            'topic' => $topic->slug,
        ]) }}"
           class="text-muted text-decoration-none">

            <i class="bi bi-arrow-left me-1"></i>

            Back to topic

        </a>

    </div>


    <div class="forum-edit-card">

        <div class="forum-edit-header">

            <h3>

                <i class="bi bi-pencil-square me-2"></i>

                Edit Post

            </h3>

            <small>

                {{ $topic->title }}

            </small>

        </div>


        <div class="p-4">

            <form method="POST"
                  action="{{ route('forum.post.update', [
                      'category' => $category->slug,
                      'topic' => $topic->slug,
                      'post' => $post->id,
                  ]) }}">

                @csrf

                @method('PUT')


                <div class="mb-3">

@include('forum.partials.bbcode-toolbar')


                    <textarea
                        id="forum-edit-body"
    name="body"
    rows="10"
    class="form-control forum-edit-textarea @error('body') is-invalid @enderror"
    required
>{{ old('body', $post->body) }}</textarea>


                    @error('body')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                    <div class="d-flex gap-2 mt-2 align-items-center">
                        <button type="button"
                                class="forum-preview-btn"
                                id="forum-edit-preview-btn">
                            <i class="bi bi-eye me-1"></i>Preview
                        </button>
                    </div>

                    <div class="forum-post-preview-pane mt-2" style="display:none;" id="forum-edit-preview"></div>

                </div>


                <div class="d-flex gap-2">

                    <button type="submit"
                            class="btn forum-save-btn">

                        <i class="bi bi-check-lg me-1"></i>

                        Save Changes

                    </button>


                    <a href="{{ route('forum.topic', [
                        'category' => $category->slug,
                        'topic' => $topic->slug,
                    ]) }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>


<style>

.forum-edit-card {

    background: var(--theme-surface, rgba(10,13,23,.96));

    border:
        1px solid var(--theme-border, rgba(255,255,255,.07));

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 15px 40px var(--theme-shadow, rgba(0,0,0,.25));

}


.forum-edit-header {

    padding: 20px 24px;

    border-bottom:
        1px solid var(--theme-border, rgba(255,255,255,.06));

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.0175));

}


.forum-edit-header h3 {

    margin: 0;

    color: var(--theme-text, #fff);

    font-weight: 800;

}


.forum-edit-header small {

    color:
        var(--theme-muted, rgba(255,255,255,.45));

}


.forum-edit-textarea {

    background:
        var(--theme-control, rgba(0,0,0,.2));

    border:
        1px solid var(--theme-border, rgba(255,255,255,.1));

    color: var(--theme-text, #fff);

    resize: vertical;

}


.forum-edit-textarea:focus {

    background:
        var(--theme-control, rgba(0,0,0,.25));

    color: var(--theme-text, #fff);

    border-color:
        var(--theme-blue-border, rgba(59,130,246,.6));

    box-shadow: 0 0 0 .2rem var(--theme-shadow, rgba(99,210,198,.1));

}


.forum-save-btn {

    border: 0;

    border-radius: 12px;

    padding: 11px 18px;

    background:
        linear-gradient(135deg,var(--theme-blue-action, #2563eb),var(--theme-purple-action, #7c3aed));

    color: var(--theme-on-action, #fff);

    font-weight: 700;

}


.forum-save-btn:hover {

    color: var(--theme-text, #fff);

    transform: translateY(-1px);

}


.forum-post-action {

    margin-left: auto;

    color: var(--theme-blue-text, #93c5fd);

    text-decoration: none;

    font-weight: 600;

}


.forum-post-action:hover {

    color: var(--theme-text, #fff);

}
</style>

@include('forum.partials.common-css')
@include('forum.partials.scripts', ['draftKind' => 'edit', 'draftId' => $post->id])

@endsection