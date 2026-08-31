<div class="emoji-wrapper mt-3 mb-5">

    {{-- Toggle --}}
    <button type="button"
            class="emoji-toggle"
            onclick="toggleEmojiPicker()">
        😊 Emojis & Formatting
    </button>

    {{-- Picker --}}
    <div id="emoji-picker" class="emoji-picker glass p-2 mt-2">

        {{-- Search --}}
        <input type="text"
               class="form-control form-control-sm mb-2 emoji-search"
               placeholder="Search emoji…"
               onkeyup="filterEmojis(this.value)">

        <div class="d-flex justify-content-center flex-wrap gap-1">

            {{-- BBCode --}}
            <button class="btn btn-dark emoji-btn"
                    data-name="italic format"
                    onclick="insertBBCode('i')"
                    title="Italic">
                <i class="bi bi-type-italic"></i>
            </button>

            <button class="btn btn-dark emoji-btn"
                    data-name="underline format"
                    onclick="insertBBCode('u')"
                    title="Underline">
                <i class="bi bi-type-underline"></i>
            </button>

            <button class="btn btn-dark emoji-btn"
                    data-name="image img"
                    onclick="insertBBCode('img')"
                    title="Image">
                <i class="bi bi-card-image"></i>
            </button>

            {{-- Emojis --}}
<button type="button" class="btn btn-dark emoji-btn"
        data-name="smile happy grin"
        onclick="insertEmoji('😊')">😊</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="heart love"
        onclick="insertEmoji('❤️')">❤️</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="thumbs up like"
        onclick="insertEmoji('👍')">👍</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="wink"
        onclick="insertEmoji('😉')">😉</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="laugh lol funny"
        onclick="insertEmoji('😂')">😂</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="sad"
        onclick="insertEmoji('😞')">😞</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="love heart eyes"
        onclick="insertEmoji('😍')">😍</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="cool sunglasses"
        onclick="insertEmoji('😎')">😎</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="cry tears"
        onclick="insertEmoji('😭')">😭</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="angry mad"
        onclick="insertEmoji('😡')">😡</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="kiss"
        onclick="insertEmoji('😘')">😘</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="surprised wow"
        onclick="insertEmoji('😲')">😲</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="grin happy"
        onclick="insertEmoji('😁')">😁</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="star favorite"
        onclick="insertEmoji('⭐')">⭐</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="fire hot"
        onclick="insertEmoji('🔥')">🔥</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="trophy win"
        onclick="insertEmoji('🏆')">🏆</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="party celebrate"
        onclick="insertEmoji('🎉')">🎉</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="clap applause"
        onclick="insertEmoji('👏')">👏</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="confetti"
        onclick="insertEmoji('🎊')">🎊</button>

<button type="button" class="btn btn-dark emoji-btn"
        data-name="praise hands"
        onclick="insertEmoji('🙌')">🙌</button>

        </div>
    </div>
</div>


<style>

    .emoji-toggle {
    background: none;
    border: none;
    color: #ffc107;
    font-weight: 600;
    cursor: pointer;
    padding: 4px 0;
}

.emoji-picker {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-6px);
    transition: all .25s ease;
}

.emoji-picker.open {
    max-height: 400px;
    opacity: 1;
    transform: translateY(0);
}

.emoji-search {
    background: #111;
    color: #fff;
    border-radius: 8px;
}

.emoji-btn {
    font-size: 1.1rem;
    padding: 6px 8px;
}

</style>

<script>
function getActiveTextarea() {
    // If something is focused and it's a textarea, use it
    if (document.activeElement && document.activeElement.tagName === 'TEXTAREA') {
        return document.activeElement;
    }

    // fallback to main shoutbox textarea
    return document.getElementById('content');
}

function insertAtCursor(field, text) {
    if (!field) return;

    const start = field.selectionStart;
    const end = field.selectionEnd;

    field.value =
        field.value.substring(0, start) +
        text +
        field.value.substring(end);

    field.selectionStart = field.selectionEnd = start + text.length;
    field.focus();

    // trigger input event (for auto-grow + counter)
    field.dispatchEvent(new Event('input'));
}

function insertEmoji(code) {
    const textarea = getActiveTextarea();
    insertAtCursor(textarea, code);
}

function insertBBCode(tag) {
    const textarea = getActiveTextarea();
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);

    const open = `[${tag}]`;
    const close = `[/${tag}]`;

    const replacement = open + selectedText + close;

    textarea.value =
        textarea.value.substring(0, start) +
        replacement +
        textarea.value.substring(end);

    textarea.selectionStart = start + open.length;
    textarea.selectionEnd = start + open.length + selectedText.length;

    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}
</script>


<script>
function toggleEmojiPicker() {
    const picker = document.getElementById('emoji-picker');
    picker.classList.toggle('open');

    if (picker.classList.contains('open')) {
        setTimeout(() => {
            picker.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }, 150);
    }
}

function filterEmojis(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.emoji-btn').forEach(btn => {
        const name = btn.dataset.name || '';
        btn.style.display = name.includes(query) ? 'inline-flex' : 'none';
    });
}
</script>
