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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       POST PREVIEW (Edit form)
       ============================================================ */
    (function () {
        var previewBtn = document.getElementById('forum-edit-preview-btn');
        var previewPane = document.getElementById('forum-edit-preview');
        var textarea = document.getElementById('forum-edit-body');
        if (!previewBtn || !previewPane || !textarea) return;

        previewBtn.addEventListener('click', function () {
            var isHidden = previewPane.style.display === 'none';
            if (isHidden) {
                var bbcode = textarea.value;
                if (!bbcode.trim()) {
                    previewPane.innerHTML = '<em class="text-secondary">Nothing to preview.</em>';
                } else {
                    var html = bbcode
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/\[b\](.*?)\[\/b\]/gi, '<strong>$1</strong>')
                        .replace(/\[i\](.*?)\[\/i\]/gi, '<em>$1</em>')
                        .replace(/\[u\](.*?)\[\/u\]/gi, '<u>$1</u>')
                        .replace(/\[center\](.*?)\[\/center\]/gi, '<div class="text-center">$1</div>')
                        .replace(/\[quote(?:=(.*?))?\](.*?)\[\/quote\]/gi, '<blockquote>$2</blockquote>')
                        .replace(/\[code\](.*?)\[\/code\]/gi, '<pre><code>$1</code></pre>')
                        .replace(/\[spoiler\](.*?)\[\/spoiler\]/gi, '<span style="background:#333;color:#333">$1</span>')
                        .replace(/\[url\](.*?)\[\/url\]/gi, '<a href="$1" target="_blank">$1</a>')
                        .replace(/\[img\](.*?)\[\/img\]/gi, '<img src="$1" style="max-width:100%;">')
                        .replace(/\[youtube\](.*?)\[\/youtube\]/gi, '<iframe src="https://www.youtube.com/embed/$1" style="width:100%;height:315px" allowfullscreen></iframe>')
                        .replace(/\[hr\]/gi, '<hr>')
                        .replace(/\n/g, '<br>');
                    previewPane.innerHTML = html;
                }
                previewPane.style.display = '';
                previewBtn.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Edit';
            } else {
                previewPane.style.display = 'none';
                previewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview';
            }
        });
    })();

    /* ============================================================
       AUTO-SAVE DRAFTS (Edit form)
       ============================================================ */
    (function () {
        var textarea = document.getElementById('forum-edit-body');
        if (!textarea) return;

        var draftKey = 'forum_draft_edit_{{ $topic->id }}_{{ $post->id }}';
        var debounceTimer = null;

        var saved = localStorage.getItem(draftKey);
        if (saved && !textarea.value.trim()) {
            textarea.value = saved;
            var toast = document.createElement('div');
            toast.className = 'draft-restore-toast';
            toast.innerHTML = '<i class="bi bi-journal-text me-1"></i>Draft restored. ' +
                '<button id="draft-keep">Keep</button>' +
                '<button id="draft-dismiss" class="draft-dismiss">Dismiss</button>';
            document.body.appendChild(toast);

            var keepBtn = document.getElementById('draft-keep');
            var dismissBtn = document.getElementById('draft-dismiss');
            if (keepBtn) keepBtn.addEventListener('click', function () { toast.remove(); });
            if (dismissBtn) dismissBtn.addEventListener('click', function () {
                textarea.value = '';
                localStorage.removeItem(draftKey);
                toast.remove();
            });
        }

        textarea.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (textarea.value.trim()) {
                    localStorage.setItem(draftKey, textarea.value);
                } else {
                    localStorage.removeItem(draftKey);
                }
            }, 2000);
        });

        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                localStorage.removeItem(draftKey);
            });
        }
    })();

});
</script>

@endsection