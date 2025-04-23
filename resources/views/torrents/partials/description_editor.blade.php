<div class="mb-2">
    <div class="btn-toolbar" role="toolbar" aria-label="Editor toolbar">
        <div class="btn-group me-2" role="group" aria-label="Formatting">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('b')">
                <i class="bi bi-type-bold"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('i')">
                <i class="bi bi-type-italic"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('u')">
                <i class="bi bi-type-underline"></i>
            </button>
        </div>

        <div class="btn-group me-2" role="group" aria-label="Media">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('img')">
                <i class="bi bi-image"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('url')">
                <i class="bi bi-link-45deg"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('quote')">
                <i class="bi bi-chat-left-quote"></i>
            </button>
        </div>

        <div class="btn-group" role="group" aria-label="Layout">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('center')">
                <i class="bi bi-align-center"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTag('spoiler')">
                <i class="bi bi-eye-slash"></i>
            </button>
            
            <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">
                <i class="bi bi-youtube"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function insertTag(tag) {
        const textarea = document.getElementById('description');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end, textarea.value.length);
        const newText = `[${tag}]${selectedText}[/${tag}]`;

        textarea.value = before + newText + after;

        // Re-focus and move cursor
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = before.length + newText.length;
    }
</script>
