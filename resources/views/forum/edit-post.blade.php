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

                <div class="bbcode-toolbar" role="toolbar" aria-label="BBCode formatting">

        <button type="button"
                class="bbcode-btn"
                data-bbcode="b"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Bold">
            <strong>B</strong>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="i"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Italic">
            <em>I</em>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="u"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Underline">
            <u>U</u>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="center"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Center text">
            <i class="bi bi-text-center"></i>
        </button>

        <span class="bbcode-divider"></span>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="quote"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Quote">
            <i class="bi bi-quote"></i>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="code"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Code">
            <i class="bi bi-code-slash"></i>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="spoiler"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Spoiler">
            <i class="bi bi-eye-slash"></i>
        </button>

        <span class="bbcode-divider"></span>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="url"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Insert link">
            <i class="bi bi-link-45deg"></i>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="img"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Insert image">
            <i class="bi bi-image"></i>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="youtube"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Insert YouTube video">
            <i class="bi bi-youtube"></i>
        </button>

        <span class="bbcode-divider"></span>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="list"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Create list">
            <i class="bi bi-list-ul"></i>
        </button>

        <button type="button"
                class="bbcode-btn"
                data-bbcode="hr"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Horizontal line">
            <i class="bi bi-dash-lg"></i>
        </button>

    </div>


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

    box-shadow: 0 0 0 .2rem rgba(99,210,198,.1);

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

/* =========================================================
   BBCode TOOLBAR
   ========================================================= */

.bbcode-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 9px 10px;
    margin-bottom: 0;
    border: 1px solid rgba(203, 213, 225, 0.12);
    border-bottom: 0;
    border-radius: 12px 12px 0 0;
    background: rgba(15, 23, 42, 0.88);
}

.bbcode-btn {
    width: 34px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid rgba(203, 213, 225, 0.10);
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.045);
    color: #b8c7d9;
    font-size: 0.82rem;
    cursor: pointer;
    transition:
        color 0.18s ease,
        background 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.bbcode-btn:hover {
    color: #63d2c6;
    background: rgba(99, 210, 198, 0.10);
    border-color: rgba(99,210,198,.6);
    transform: translateY(-1px);
}

.bbcode-btn:active {
    transform: translateY(0);
}

.bbcode-btn:focus-visible {
    outline: 2px solid rgba(99, 210, 198, 0.55);
    outline-offset: 2px;
}

.bbcode-divider {
    width: 1px;
    height: 22px;
    margin: 0 3px;
    background: rgba(203, 213, 225, 0.12);
}

/* Make textarea connect visually to toolbar */

.bbcode-toolbar + textarea {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

@media (max-width: 576px) {

    .bbcode-toolbar {
        gap: 5px;
        padding: 8px;
    }

    .bbcode-btn {
        width: 32px;
        height: 30px;
    }

    .bbcode-divider {
        display: none;
    }
}

</style>



<script>
document.addEventListener('DOMContentLoaded', function () {

    // Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        new bootstrap.Tooltip(element);
    });

    const textarea = document.getElementById('forum-edit-body');
    const toolbar = document.querySelector('.bbcode-toolbar');

    if (!textarea || !toolbar) {
        console.warn('BBCode toolbar: textarea or toolbar not found.');
        return;
    }

    toolbar.addEventListener('click', function (event) {

        const button = event.target.closest('.bbcode-btn');

        if (!button) {
            return;
        }

        event.preventDefault();

        const tag = button.dataset.bbcode;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        const selected = textarea.value.substring(start, end);

        let before = '';
        let after = '';
        let replacement = '';

        switch (tag) {

            case 'b':
                before = '[b]';
                after = '[/b]';
                break;

            case 'i':
                before = '[i]';
                after = '[/i]';
                break;

            case 'u':
                before = '[u]';
                after = '[/u]';
                break;

            case 'center':
                before = '[center]';
                after = '[/center]';
                break;

            case 'quote':
                before = '[quote]';
                after = '[/quote]';
                break;

            case 'code':
                before = '[code]';
                after = '[/code]';
                break;

            case 'spoiler':
                before = '[spoiler]';
                after = '[/spoiler]';
                break;

            case 'url':
                before = '[url]';
                after = '[/url]';
                break;

            case 'img':
                before = '[img]';
                after = '[/img]';
                break;

            case 'youtube':
                before = '[youtube]';
                after = '[/youtube]';
                break;

            case 'list':
                replacement =
                    '[list]\n' +
                    '[*]' +
                    selected +
                    '\n[/list]';
                break;

            case 'hr':
                replacement = '[hr]';
                break;

            default:
                return;
        }

        if (!replacement) {
            replacement = before + selected + after;
        }

        textarea.focus();

        textarea.setRangeText(
            replacement,
            start,
            end,
            'end'
        );

        // Put cursor between opening and closing tags
        if (before && after) {

            const cursorPosition = start + before.length;

            textarea.setSelectionRange(
                cursorPosition,
                cursorPosition
            );
        }

        textarea.dispatchEvent(new Event('input', {
            bubbles: true
        }));

    });

});
</script>
@endsection