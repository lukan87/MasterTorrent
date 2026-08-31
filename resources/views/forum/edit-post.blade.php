@extends('layouts.app')

@section('content')

<div class="container py-5">

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

                    <textarea
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

    background: rgba(15,20,35,.96);

    border:
        1px solid rgba(255,255,255,.07);

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 15px 40px rgba(0,0,0,.25);

}


.forum-edit-header {

    padding: 20px 24px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    background:
        rgba(255,255,255,.025);

}


.forum-edit-header h3 {

    margin: 0;

    color: #fff;

    font-weight: 800;

}


.forum-edit-header small {

    color:
        rgba(255,255,255,.45);

}


.forum-edit-textarea {

    background:
        rgba(0,0,0,.2);

    border:
        1px solid rgba(255,255,255,.1);

    color: #fff;

    resize: vertical;

}


.forum-edit-textarea:focus {

    background:
        rgba(0,0,0,.25);

    color: #fff;

    border-color:
        rgba(59,130,246,.6);

    box-shadow:
        0 0 0 .2rem rgba(59,130,246,.1);

}


.forum-save-btn {

    border: 0;

    border-radius: 12px;

    padding: 11px 18px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color: #fff;

    font-weight: 700;

}


.forum-save-btn:hover {

    color: #fff;

    transform: translateY(-1px);

}


.forum-post-action {

    margin-left: auto;

    color: #93c5fd;

    text-decoration: none;

    font-weight: 600;

}


.forum-post-action:hover {

    color: #fff;

}

</style>

@endsection