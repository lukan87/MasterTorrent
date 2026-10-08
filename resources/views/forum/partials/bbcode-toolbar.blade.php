{{-- =========================================================
     BBCode Toolbar Partial
     Self-contained: toolbar HTML + CSS + JS (deduplicated via @once).
     Place it inside a <form>, directly above a <textarea name="body">.
     ========================================================= --}}

<div class="bbcode-toolbar" role="toolbar" aria-label="BBCode formatting">

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="b"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Bold" aria-label="Bold"
    >
        <strong>B</strong>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="i"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Italic" aria-label="Italic"
    >
        <em>I</em>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="u"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Underline" aria-label="Underline"
    >
        <u>U</u>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="center"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Center text" aria-label="Center text"
    >
        <i class="bi bi-text-center"></i>
    </button>

    <span class="bbcode-divider"></span>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="quote"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Quote" aria-label="Quote"
    >
        <i class="bi bi-quote"></i>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="code"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Code" aria-label="Code"
    >
        <i class="bi bi-code-slash"></i>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="spoiler"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Spoiler" aria-label="Spoiler"
    >
        <i class="bi bi-eye-slash"></i>
    </button>

    <span class="bbcode-divider"></span>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="url"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Insert link" aria-label="Insert link"
    >
        <i class="bi bi-link-45deg"></i>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="img"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Insert image" aria-label="Insert image"
    >
        <i class="bi bi-image"></i>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="youtube"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Insert YouTube video" aria-label="Insert YouTube video"
    >
        <i class="bi bi-youtube"></i>
    </button>

    <span class="bbcode-divider"></span>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="list"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Create list" aria-label="Create list"
    >
        <i class="bi bi-list-ul"></i>
    </button>

    <button
        type="button"
        class="bbcode-btn"
        data-bbcode="hr"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Horizontal line" aria-label="Horizontal line"
    >
        <i class="bi bi-dash-lg"></i>
    </button>

@once('forum-bbcode-css')
<style>
    .bbcode-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: 9px 10px;
        margin-bottom: 0;
        border: 1px solid var(--theme-border, rgba(203, 213, 225, 0.12));
        border-bottom: 0;
        border-radius: 12px 12px 0 0;
        background: var(--theme-surface, rgba(10,15,27,0.88));
    }

    .bbcode-btn {
        width: 34px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid var(--theme-border, rgba(203, 213, 225, 0.10));
        border-radius: 7px;
        background: var(--theme-surface-alt, rgba(255,255,255,0.0315));
        color: var(--theme-text, #b8c7d9);
        font-size: var(--site-font-body, 13px);
        cursor: pointer;
        transition:
            color 0.18s ease,
            background 0.18s ease,
            border-color 0.18s ease,
            transform 0.18s ease;
    }

    .bbcode-btn:hover {
        color: var(--theme-teal-text, #63d2c6);
        background: var(--theme-teal-soft, rgba(99, 210, 198, 0.10));
        border-color: var(--theme-teal-border, rgba(99, 210, 198, 0.30));
        transform: translateY(-1px);
    }

    .bbcode-btn:active {
        transform: translateY(0);
    }

    .bbcode-btn:focus-visible {
        outline: 2px solid var(--theme-teal-border, rgba(99, 210, 198, 0.55));
        outline-offset: 2px;
    }

    .bbcode-divider {
        width: 1px;
        height: 22px;
        margin: 0 3px;
        background: var(--theme-surface-alt, rgba(203,213,225,0.084));
    }

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
@endonce
@once('forum-bbcode-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bbcode-toolbar').forEach(function (toolbar) {

        const form = toolbar.closest('form');

        const textarea = form
            ? form.querySelector('textarea[name="body"]')
            : toolbar.parentElement.querySelector('textarea');

        if (!textarea) {
            console.warn('BBCode toolbar: textarea not found.');
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
                        (selected || '') +
                        '\n[/list]';
                    break;

                case 'hr':
                    replacement = '[hr]';
                    break;

                default:
                    return;
            }

            // Normal BBCode tags
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
});
</script>
@endonce