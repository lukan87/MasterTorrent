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

                    {{-- BBCode Toolbar --}}
@include('forum.partials.bbcode-toolbar')


                    <textarea
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




<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       POST PREVIEW (Create form)
       ============================================================ */
    (function () {
        var previewBtn = document.getElementById('forum-create-preview-btn');
        var previewPane = document.getElementById('forum-create-preview');
        var textarea = document.getElementById('forum-topic-body');
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
       AUTO-SAVE DRAFTS (Create form)
       ============================================================ */
    (function () {
        var textarea = document.getElementById('forum-topic-body');
        if (!textarea) return;

        var draftKey = 'forum_draft_create_{{ $category->slug }}';
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

<style>
.bbcode-help {
    padding: 7px 2px 9px;
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.95rem;
}
.bbcode-help i {
    color: #63d2c6;
}
.bbcode-help strong {
    color: rgba(255, 255, 255, 0.7);
    font-weight: 700;
}
</style>

@endsection